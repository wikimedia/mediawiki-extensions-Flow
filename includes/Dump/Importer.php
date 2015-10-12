<?php

namespace Flow\Dump;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\OccupationController;
use WikiImporter;
use XMLReader;

class Importer {
	/**
	 * @var WikiImporter
	 */
	protected $importer;

	/**
	 * @var ManagerGroup|null
	 */
	protected $storage;

	/**
	 * @var array
	 */
	protected $metadata = array();

	/**
	 * @param WikiImporter $importer
	 */
	public function __construct( WikiImporter $importer ) {
		$this->importer = $importer;
	}

	/**
	 * @param ManagerGroup $storage
	 */
	public function setStorage( ManagerGroup $storage ) {
		$this->storage = $storage;
	}

	/**
	 * @param object $object
	 */
	protected function put( $object ) {
		if ( $this->storage ) {
			// XXX: The direct pass-through doesn't work because 'workflow' needs to be
			// the board workflow for board-level actions (e.g. link recording for the header
			// in ReferenceRecorder->onAfterInsert).
			$this->storage->put( $object, array( 'imported' => true ) + $this->metadata );

			// prevent memory from being filled up
			$this->storage->clear();

			// keep workflow objects around, so follow-up `put`s (e.g. to update
			// last_update_timestamp) don't confuse it for a new object
			foreach ( $this->metadata as $object ) {
				$this->storage->getStorage( get_class( $object ) )->merge( $object );
			}
		}
	}

	public function handleBoard() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter board handler for ' . $id );

		$uuid = UUID::create( $id );
		$title = \Title::newFromDBkey( $this->importer->nodeAttribute( 'title' ) );

		$workflow = Workflow::fromStorageRow( array(
			'workflow_id' => $uuid->getAlphadecimal(),
			'workflow_type' => 'discussion',
			'workflow_wiki' => wfWikiID(),
			'workflow_page_id' => $title->getArticleID(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDBkey(),
			'workflow_last_update_timestamp' => $uuid->getTimestamp( TS_MW ),
		) );

		// create page if it does not yet exist
		/** @var OccupationController $occupationController */
		$occupationController = Container::get( 'occupation_controller' );
		$occupationController->allowCreation( $title, $occupationController->getTalkpageManager() );
		$occupationController->ensureFlowRevision( new \Article( $title ), $workflow );

		$this->metadata = array();
		$this->metadata['board-workflow'] = $workflow;

		$this->put( $workflow );
	}

	public function handleHeader() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter description handler for ' . $id );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\Header', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision );
		}

		/** @var Workflow $workflow */
		$workflow = $this->metadata['board-workflow'];
		/** @var Header $revision */
		$revision = end( $revisions );
		$workflow->updateLastUpdated( $revision->getRevisionId() );
		$this->put( $workflow );
	}

	public function handleTopic() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter topic handler for ' . $id );

		$uuid = UUID::create( $this->importer->nodeAttribute( 'id' ) );
		/** @var Workflow $boardWorkflow */
		$boardWorkflow = $this->metadata['board-workflow'];
		$title = $boardWorkflow->getArticleTitle();

		$topicWorkflow = Workflow::fromStorageRow( array(
			'workflow_id' => $uuid->getAlphadecimal(),
			'workflow_type' => 'topic',
			'workflow_wiki' => wfWikiID(),
			'workflow_page_id' => $title->getArticleID(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDBkey(),
			'workflow_last_update_timestamp' => $uuid->getTimestamp( TS_MW ),
		) );
		$topicListEntry = TopicListEntry::create( $boardWorkflow, $topicWorkflow );

		$this->metadata['workflow'] = $topicWorkflow;

		$this->put( $topicWorkflow );
		$this->put( $topicListEntry );
	}

	public function handlePost() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter post handler for ' . $id );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\PostRevision', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision );
		}

		/** @var Workflow $workflow */
		$workflow = $this->metadata['workflow'];
		/** @var Header $revision */
		$revision = end( $revisions );
		$workflow->updateLastUpdated( $revision->getRevisionId() );
		$this->put( $workflow );
	}

	public function handleSummary() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter summary handler for ' . $id );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\PostSummary', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision );
		}

		/** @var Workflow $workflow */
		$workflow = $this->metadata['workflow'];
		/** @var Header $revision */
		$revision = end( $revisions );
		$workflow->updateLastUpdated( $revision->getRevisionId() );
		$this->put( $workflow );
	}

	/**
	 * @param callable $callback The relevant fromStorageRow callback
	 * @return AbstractRevision[]
	 */
	protected function getRevisions( $callback ) {
		$revisions = array();

		// keep processing <revision> nodes until </revisions>
		while ( $this->importer->getReader()->localName !== 'revisions' || $this->importer->getReader()->nodeType !== XMLReader::END_ELEMENT ) {
			if ( $this->importer->getReader()->localName === 'revision' ) {
				$revisions[] = $this->getRevision( $callback );
			}
			$this->importer->getReader()->read();
		}
		$this->importer->getReader()->read();

		return $revisions;
	}

	/**
	 * @param callable $callback The relevant fromStorageRow callback
	 * @return AbstractRevision
	 */
	protected function getRevision( $callback ) {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter revision handler for ' . $id );

		$attribs = array();

		$this->importer->getReader()->moveToFirstAttribute();
		do {
			$attribs[$this->importer->getReader()->name] = $this->importer->getReader()->value;
		} while ( $this->importer->getReader()->moveToNextAttribute() );

		$attribs['content'] = $this->importer->nodeContents();

		// make sure there are no leftover key columns (unknown to $attribs)
		$keys = array_intersect_key( array_flip( Exporter::$map ), $attribs );
		// now make sure $values columns are in the same order as $keys are
		// (array_merge) and there are no leftover columns (array_intersect_key)
		$values = array_intersect_key( array_merge( $keys, $attribs ), $keys );
		// combine them
		$attribs = array_combine( $keys, $values );

		return call_user_func( $callback, $attribs );
	}
}
