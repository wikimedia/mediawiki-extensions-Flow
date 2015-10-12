<?php

namespace Flow\Dump;

use Exception;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\OccupationController;
use MWException;
use WikiImporter;
use XMLReader;

class Importer extends WikiImporter {
	/**
	 * @var ManagerGroup|null
	 */
	protected $storage;

	/**
	 * @var array
	 */
	protected $metadata = array();

	/**
	 * Copied from parent::doImport, but with different handle* calls
	 * for the different nodes we deal with.
	 *
	 * @return bool
	 * @throws Exception
	 * @throws MWException
	 * @throws null
	 */
	public function doImport() {
		// Calls to reader->read need to be wrapped in calls to
		// libxml_disable_entity_loader() to avoid local file
		// inclusion attacks (bug 46932).
		$oldDisable = libxml_disable_entity_loader( true );
		$this->reader->read();

		if ( $this->reader->localName != 'mediawiki' ) {
			libxml_disable_entity_loader( $oldDisable );
			throw new MWException( "Expected <mediawiki> tag, got " .
				$this->reader->localName );
		}
		$this->debug( "<mediawiki> tag is correct." );

		$this->debug( "Starting primary dump processing loop." );

		$keepReading = $this->reader->read();
		$skip = false;
		$rethrow = null;
		try {
			while ( $keepReading ) {
				$tag = $this->reader->localName;
				$type = $this->reader->nodeType;

				if ( $tag == 'mediawiki' && $type === XMLReader::END_ELEMENT ) {
					break;
				} elseif ( $tag == 'board' && $type === XMLReader::ELEMENT ) {
					$this->handleBoard();
				} elseif ( $tag == 'description' && $type === XMLReader::ELEMENT ) {
					$this->handleHeader();
				} elseif ( $tag == 'topic' && $type === XMLReader::ELEMENT ) {
					// prevent memory from being filled up
					/** @var ManagerGroup $storage */
					$storage = Container::get( 'storage' );
					$storage->clear();

					$this->handleTopic();
				} elseif ( $tag == 'post' && $type === XMLReader::ELEMENT ) {
					$this->handlePost();
				} elseif ( $tag == 'summary' && $type === XMLReader::ELEMENT ) {
					$this->handleSummary();
				} elseif ( $tag != '#text' ) {
					$this->warn( "Unhandled top-level XML tag $tag" );

					$skip = true;
				}

				if ( $skip ) {
					$keepReading = $this->reader->next();
					$skip = false;
					$this->debug( "Skip" );
				} else {
					$keepReading = $this->reader->read();
				}
			}
		} catch ( Exception $ex ) {
			$rethrow = $ex;
		}

		// finally
		libxml_disable_entity_loader( $oldDisable );
		$this->reader->close();

		if ( $rethrow ) {
			throw $rethrow;
		}

		return true;
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
			$this->storage->put( $object, $this->metadata );
		}
	}

	protected function handleBoard() {
		$id = $this->nodeAttribute( 'id' );
		$this->debug( 'Enter board handler for ' . $id );

		global $wgFlowDefaultWorkflow;

		$uuid = UUID::create( $id );
		$title = \Title::newFromDBkey( $this->nodeAttribute( 'title' ) );

		$workflow = Workflow::fromStorageRow( array(
			'workflow_id' => $uuid->getAlphadecimal(),
			'workflow_type' => $wgFlowDefaultWorkflow,
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

	protected function handleHeader() {
		$id = $this->nodeAttribute( 'id' );
		$this->debug( 'Enter description handler for ' . $id );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\Header', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision );
		}
	}

	protected function handleTopic() {
		$id = $this->nodeAttribute( 'id' );
		$this->debug( 'Enter topic handler for ' . $id );

		$uuid = UUID::create( $this->nodeAttribute( 'id' ) );
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

	protected function handlePost() {
		$id = $this->nodeAttribute( 'id' );
		$this->debug( 'Enter post handler for ' . $id );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\PostRevision', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision );
		}
	}

	protected function handleSummary() {
		$id = $this->nodeAttribute( 'id' );
		$this->debug( 'Enter summary handler for ' . $id );

		$revisions = $this->getRevisions( array( 'Flow\\Model\\PostSummary', 'fromStorageRow' ) );
		foreach ( $revisions as $revision ) {
			$this->put( $revision );
		}
	}

	/**
	 * @param callable $callback The relevant fromStorageRow callback
	 * @return AbstractRevision[]
	 */
	protected function getRevisions( $callback ) {
		$revisions = array();

		// keep processing <revision> nodes until </revisions>
		while ( $this->reader->localName !== 'revisions' || $this->reader->nodeType !== XMLReader::END_ELEMENT ) {
			if ( $this->reader->localName === 'revision' ) {
				$revisions[] = $this->getRevision( $callback );
			}
			$this->reader->read();
		}

		return $revisions;
	}

	/**
	 * @param callable $callback The relevant fromStorageRow callback
	 * @return AbstractRevision
	 */
	protected function getRevision( $callback ) {
		$id = $this->nodeAttribute( 'rev_id' );
		$this->debug( 'Enter revision handler for ' . $id );

		$data = array();

		$this->reader->moveToFirstAttribute();
		do {
			$data[$this->reader->name] = $this->reader->value;
		} while ( $this->reader->moveToNextAttribute() );

		$data['rev_content'] = $this->nodeContents();

		return call_user_func( $callback, $data );
	}
}
