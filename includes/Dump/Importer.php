<?php

namespace Flow\Dump;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\OccupationController;
use MWException;
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
	 * The most recently imported board workflow (if any).
	 *
	 * @var Workflow|null
	 */
	protected $boardWorkflow;

	/**
	 * The most recently imported topic workflow (if any).
	 *
	 * @var Workflow|null
	 */
	protected $topicWorkflow;

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
	 * @param array $metadata
	 */
	protected function put( $object, array $metadata = array() ) {
		if ( $this->storage ) {
			$this->storage->put( $object, array( 'imported' => true ) + $metadata );

			// prevent memory from being filled up
			$this->storage->clear();

			// keep workflow objects around, so follow-up `put`s (e.g. to update
			// last_update_timestamp) don't confuse it for a new object
			foreach ( array( $this->boardWorkflow, $this->topicWorkflow ) as $object ) {
				if ( $object ) {
					$this->storage->getStorage( get_class( $object ) )->merge( $object );
				}
			}
		}
	}

	public function handleBoard() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter board handler for ' . $id );

		$uuid = UUID::create( $id );
		$title = \Title::newFromDBkey( $this->importer->nodeAttribute( 'title' ) );

		$this->boardWorkflow = Workflow::fromStorageRow( array(
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
		$creationStatus = $occupationController->safeAllowCreation( $title, $occupationController->getTalkpageManager() );
		if ( !$creationStatus->isOK() ) {
			throw new MWException( $creationStatus->getWikiText() );
		}

		$ensureStatus = $occupationController->ensureFlowRevision( new \Article( $title ), $this->boardWorkflow );
		if ( !$ensureStatus->isOK() ) {
			throw new MWException( $ensureStatus->getWikiText() );
		}

		$this->put( $this->boardWorkflow, array() );
	}

	public function handleHeader() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter description handler for ' . $id );

		$metadata = array( 'workflow' => $this->boardWorkflow );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\Header', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision, $metadata );
		}

		/** @var Header $revision */
		$revision = end( $revisions );
		$this->boardWorkflow->updateLastUpdated( $revision->getRevisionId() );
		$this->put( $this->boardWorkflow, array() );
	}

	public function handleTopic() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter topic handler for ' . $id );

		$uuid = UUID::create( $this->importer->nodeAttribute( 'id' ) );
		$title = $this->boardWorkflow->getArticleTitle();

		$this->topicWorkflow = Workflow::fromStorageRow( array(
			'workflow_id' => $uuid->getAlphadecimal(),
			'workflow_type' => 'topic',
			'workflow_wiki' => wfWikiID(),
			'workflow_page_id' => $title->getArticleID(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDBkey(),
			'workflow_last_update_timestamp' => $uuid->getTimestamp( TS_MW ),
		) );
		$topicListEntry = TopicListEntry::create( $this->boardWorkflow, $this->topicWorkflow );

		$metadata = array(
			'board-workflow' => $this->boardWorkflow,
			'workflow' => $this->topicWorkflow,
			// @todo: topic-title & first-post? (used only in NotificationListener)
		);

		$this->put( $this->topicWorkflow, $metadata );
		$this->put( $topicListEntry, $metadata );
	}

	public function handlePost() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter post handler for ' . $id );

		$metadata = array(
			'workflow' => $this->topicWorkflow
			// @todo: topic-title? (used only in NotificationListener)
		);

		$revisions = $this->getRevisions( array( 'Flow\\Model\\PostRevision', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision, $metadata );
		}

		/** @var PostRevision $revision */
		$revision = end( $revisions );
		$this->topicWorkflow->updateLastUpdated( $revision->getRevisionId() );
		$this->put( $this->topicWorkflow, $metadata );
	}

	public function handleSummary() {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter summary handler for ' . $id );

		$metadata = array( 'workflow' => $this->topicWorkflow );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\PostSummary', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision, $metadata );
		}

		/** @var PostSummary $revision */
		$revision = end( $revisions );
		$this->topicWorkflow->updateLastUpdated( $revision->getRevisionId() );
		$this->put( $this->topicWorkflow, $metadata );
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

		return $revisions;
	}

	/**
	 * @param callable $callback The relevant fromStorageRow callback
	 * @return AbstractRevision
	 */
	protected function getRevision( $callback ) {
		$id = $this->importer->nodeAttribute( 'id' );
		$this->importer->debug( 'Enter revision handler for ' . $id );

		// isEmptyElement will no longer be valid after we've started iterating
		// the attributes
		$empty = $this->importer->getReader()->isEmptyElement;

		$attribs = array();

		$this->importer->getReader()->moveToFirstAttribute();
		do {
			$attribs[$this->importer->getReader()->name] = $this->importer->getReader()->value;
		} while ( $this->importer->getReader()->moveToNextAttribute() );

		// now that we've moved inside the node (to fetch attributes),
		// nodeContents() is no longer reliable: is uses isEmptyContent (which
		// will now no longer respond with 'true') to see if the node should be
		// skipped - use the value we've fetched earlier!
		$attribs['content'] = $empty ? '' : $this->importer->nodeContents();

		// make sure there are no leftover key columns (unknown to $attribs)
		$keys = array_intersect_key( array_flip( Exporter::$map ), $attribs );
		// now make sure $values columns are in the same order as $keys are
		// (array_merge) and there are no leftover columns (array_intersect_key)
		$values = array_intersect_key( array_merge( $keys, $attribs ), $keys );
		// combine them
		$attribs = array_combine( $keys, $values );

		// now fill in missing attributes
		$keys = array_fill_keys( array_keys( Exporter::$map ), null );
		$attribs += $keys;

		return call_user_func( $callback, $attribs );
	}
}
