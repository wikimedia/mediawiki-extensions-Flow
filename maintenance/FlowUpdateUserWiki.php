<?php

use Flow\Container;
use Flow\Model\UUID;
use Flow\Model\Header;
use Flow\Model\PostRevision;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = dirname( __FILE__ ) . '/../../..';
}
require_once( "$IP/maintenance/Maintenance.php" );

/**
 * Update all xxx_user_wiki field to have the correct wiki name
 *
 * @ingroup Maintenance
 */
class FlowUpdateUserWiki extends Maintenance {

	/**
	 * Max number of records to process at a time
	 *
	 * @var int
	 */
	private $batchSize = 300;

	/**
	 * Use to track the number of updated count
	 */
	private $updatedCount = 0;

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update xxx_user_wiki field in talbes: flow_workflow, flow_subscription, flow_tree_revision, flow_revision";
	}

	/**
	 * This is a top-to-bottom update, the process is like this:
	 * workflow -> header -> header revision -> history
	 * workflow -> topic list -> post tree revision -> post revision -> history
	 */
	public function execute() {
		$id = '';
		$count = $this->batchSize;
		$dbw= Container::get( 'db.factory' )->getDB( DB_MASTER );
		
		while ( $count == $this->batchSize ) {
			$count = 0;
			$res = $dbw->select(
				array( 'flow_workflow', 'flow_definition' ),
				array( 'workflow_wiki', 'workflow_id', 'definition_type' ),
				array(
					'workflow_id > ' . $dbw->addQuotes( $id ),
					'workflow_definition_id = definition_id'
				),
				__METHOD__,
				array( 'ORDER BY' => 'workflow_id ASC', 'LIMIT' => $this->batchSize )
			);
			if ( $res ) {
				foreach ( $res as $row ) {
					$count++;
					$id = $row->workflow_id;
					$uuid = UUID::create( $row->workflow_id );
					$workflow = Container::get( 'storage.workflow' )->get( $uuid );
					if ( $workflow ) {
						// definition type 'topic' is always under a 'discussion' and they
						// will be handled while processing 'discussion'
						if ( $row->definition_type == 'discussion' ) {
							$this->updateHeader( $workflow, $row->workflow_wiki );
							$this->updateTopicList( $workflow, $row->workflow_wiki );
						}
						// update workflow at last step so this can start from over again
						// in case there is an error in the middle of updating others
						$this->updateWorkflow( $workflow, $row->workflow_wiki );
					}
				}
				$this->output( "processed $count records in " . __METHOD__ . "\n" );
			} else {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}
		}
	}

	/**
	 * Update workflow
	 */
	private function updateWorkflow( $wf, $wiki ) {
		$storage = Container::get( 'storage.workflow' );
		$reflection = new ReflectionClass( $wf );
		$prop = $reflection->getProperty( 'userWiki' );
		$prop->setAccessible( true );
		$prop->setValue( $wf, $wiki );
		
		$reflection = new ReflectionClass( $storage );
		$method = $reflection->getMethod( 'update' );
		$method->setAccessible( true );
		$method->invoke( $storage, $wf );
		
		
		$this->output( "processing workflow: " . $wf->getId()->getHex() . ' in ' . __METHOD__ . "\n" );
		$this->checkForSlave();
	}

	/**
	 * Update header
	 */
	protected function updateHeader( $workflow, $wiki ) {
		$id = '';
		$count = $this->batchSize;
		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );

		while ( $count == $this->batchSize ) {
			$count = 0;
			$res = $dbw->select(
				array( 'flow_header_revision', 'flow_revision' ),
				array( 'rev_id', 'rev_type' ),
				array(
					'rev_id > ' . $dbw->addQuotes( $id ),
					'header_rev_id = rev_id',
					'header_workflow_id' => $workflow->getId()->getBinary()
				),
				__METHOD__,
				array( 'ORDER BY' => 'header_rev_id ASC', 'LIMIT' => $this->batchSize )
			);
			if ( $res ) {
				foreach ( $res as $row ) {
					$count++;
					$id = $row->rev_id;
					$revision = Container::get( 'storage.header' )->get( UUID::create( $row->rev_id ) );
					if ( $revision ) {
						$this->updateHistory( $revision, $wiki, $revision );
						$this->updateRevision( $revision, $wiki );
					}
				}
			} else {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}

			$this->output( "processed $count records in " . __METHOD__ . "\n" );
		}
	}
	
	/**
	 * Update topic list
	 */
	protected function updateTopicList( $workflow, $wiki ) {
		$id = '';
		$count = $this->batchSize;
		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );

		while ( $count == $this->batchSize ) {
			$count = 0;
			$res = $dbw->select(
				array( 'flow_topic_list' ),
				array( 'topic_id' ),
				array( 'topic_list_id' => $workflow->getId()->getBinary() ),
				__METHOD__,
				array( 'ORDER BY' => 'topic_id ASC', 'LIMIT' => $this->batchSize )
			);
			if ( $res ) {
				$index = 0;
				foreach ( $res as $row ) {
					$count++;
					$index++;
					$id = $row->topic_id;
					$post = Container::get( 'loader.root_post' )->get( UUID::create( $row->topic_id ) );
					if ( $post ) {
						$post->setRootPost( $post );
						$this->updatePost( $post, $wiki, $post );
					}
				}
				$this->output( "processed $index topics in " . __METHOD__ . "\n" );
			} else {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );	
			}
			$this->output( "processed $count records in " . __METHOD__ . "\n" );
		}
	}

	/**
	 * Update post
	 */
	protected function updatePost( $post, $wiki, $root ) {
		$this->updateHistory( $post, $wiki, $root );
		$this->updateRevision( $post, $wiki );
		foreach ( $post->getChildren() as $child ) {
			$this->updatePost( $child, $wiki, $root );
		}
	}

	/**
	 * Update history revision
	 */
	protected function updateHistory( $post, $wiki, $root ) {
		if ( $post->getPrevRevisionId() ) {
			$parent = null;
			if ( $post->getRevisionType() === 'header' ) {
				$parent = Container::get( 'storage.header' )->get( UUID::create( $post->getPrevRevisionId() ) );
			} elseif ( $post->getRevisionType() === 'post' ) {
				$parent = Container::get( 'storage.post' )->get( UUID::create( $post->getPrevRevisionId() ) );
			}
			if ( $parent ) {
				if ( $post->getRevisionType() === 'post' ) {
					// DOUBLE CHECK THIS, some index like TopicHistoryIndex::onAfterUpdate()
					// requires a rootPost, would this overwrite the cache with an older history?
					$parent->setRootPost( $root );
				}
				$this->updateRevision( $parent, $wiki );
				$this->updateHistory( $parent, $wiki, $root );
			}
		}
	}
	
	/**
	 * Update either header or post revision
	 */
	protected function updateRevision( $revision, $wiki ) {
		$type = $revision->getRevisionType();
		if ( $type === 'post' ) {
			$storage = Container::get( 'storage.post' );
		} elseif ( $type === 'header' ) {
			$storage = Container::get( 'storage.header' );
		}

		if ( !$revision ) {
			return;
		}

		$reflection = new ReflectionClass( $revision );

		$prop = $reflection->getProperty( 'userWiki' );
		$prop->setAccessible( true );
		$prop->setValue( $revision, $wiki );

		$prop = $reflection->getProperty( 'moderatedByUserWiki' );
		$prop->setAccessible( true );
		$prop->setValue( $revision, $wiki );

		$prop = $reflection->getProperty( 'lastEditUserWiki' );
		$prop->setAccessible( true );
		$prop->setValue( $revision, $wiki );

		if ( $type === 'post' ) {
			$prop = $reflection->getProperty( 'origUserWiki' );
			$prop->setAccessible( true );
			$prop->setValue( $revision, $wiki );
		}

		$reflection = new ReflectionClass( '\Flow\Data\RevisionStorage' );
		$prop = $reflection->getProperty( 'allowedUpdateColumns' );
		$prop->setAccessible( true );
		$prop->setValue( array(
				'rev_mod_state',
				'rev_mod_user_id',
				'rev_mod_user_ip',
				'rev_mod_user_wiki',
				'rev_mod_timestamp',
				'rev_mod_reason',
				'rev_user_wiki',
				'rev_mod_user_wiki',
				'rev_edit_user_wiki',
				'tree_orig_user_wiki'
			)
		);
		
		$reflection = new ReflectionClass( $storage );
		$method = $reflection->getMethod( 'update' );
		$method->setAccessible( true );

		$method->invoke( $storage, $revision );

		$this->output( "processing $type: " . $revision->getRevisionId()->getHex() . ' in ' . __METHOD__ . "\n" );
		$this->checkForSlave();
	}

	private function checkForSlave() {
		$this->updatedCount++;
		if ( $this->updatedCount > $this->batchSize ) {
			wfWaitForSlaves();
			$this->updatedCount = 0;
		}
	}
}

$maintClass = "FlowUpdateUserWiki";
require_once( DO_MAINTENANCE );
