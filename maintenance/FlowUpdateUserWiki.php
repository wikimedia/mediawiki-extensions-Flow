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
class FlowUpdateUserWiki extends LoggedUpdateMaintenance {

	/**
	 * Used to track the number of current updated count
	 */
	private $updatedCount = 0;

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update xxx_user_wiki field in tables: flow_workflow, flow_tree_revision, flow_revision";
		$this->setBatchSize( 300 );
	}

	/**
	 * This is a top-to-bottom update, the process is like this:
	 * workflow -> header -> header revision -> history
	 * workflow -> topic list -> post tree revision -> post revision -> history
	 *
	 * Some side effect, the script will also update those *_user_wiki fields with
	 * empty *_user_id and *_user_ip, but this doesn't hurt. Alternatively, we could
	 * add a check user_id != 0 and user_ip is not null to the query, but this will
	 * result in more db queries
	 *
	 */
	protected function doDBUpdates() {
		$id = '';
		$count = $this->mBatchSize;
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		// If table flow_header_revision does not exist, that means the wiki
		// has run the data migration before or the wiki starts from scratch,
		// there is no point to run the script againt invalid tables
		if ( !$dbr->tableExists( 'flow_header_revision', __METHOD__ ) ) {
			return true;
		}

		while ( $count == $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_workflow', 'flow_definition' ),
				array( 'workflow_wiki', 'workflow_id', 'definition_type' ),
				array(
					'workflow_id > ' . $dbr->addQuotes( $id ),
					'workflow_definition_id = definition_id'
				),
				__METHOD__,
				array( 'ORDER BY' => 'workflow_id ASC', 'LIMIT' => $this->mBatchSize )
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
						$this->updateWorkflow( $workflow, $row->workflow_wiki );
					}
				}
			} else {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}
		}

		return true;
	}

	/**
	 * Update workflow
	 */
	private function updateWorkflow( $wf, $wiki ) {
		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );
		$res = $dbw->update(
			'flow_workflow',
			array( 'workflow_user_wiki' => $wiki ),
			array( 'workflow_id' => $wf->getId()->getBinary() )
		);
		if ( !$res ) {
			throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
		}

		$this->checkForSlave();
	}

	/**
	 * Update header
	 */
	private function updateHeader( $workflow, $wiki ) {
		$id = '';
		$count = $this->mBatchSize;
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		while ( $count == $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_header_revision', 'flow_revision' ),
				array( 'rev_id', 'rev_type' ),
				array(
					'rev_id > ' . $dbr->addQuotes( $id ),
					'header_rev_id = rev_id',
					'header_workflow_id' => $workflow->getId()->getBinary()
				),
				__METHOD__,
				array( 'ORDER BY' => 'header_rev_id ASC', 'LIMIT' => $this->mBatchSize )
			);
			if ( $res ) {
				foreach ( $res as $row ) {
					$count++;
					$id = $row->rev_id;
					$revision = Container::get( 'storage.header' )->get( UUID::create( $row->rev_id ) );
					if ( $revision ) {
						$this->updateRevision( $revision, $wiki );
					}
				}
			} else {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}

		}
	}

	/**
	 * Update topic list
	 */
	private function updateTopicList( $workflow, $wiki ) {
		$id = '';
		$count = $this->mBatchSize;
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		while ( $count == $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_topic_list' ),
				array( 'topic_id' ),
				array(
					'topic_list_id' => $workflow->getId()->getBinary(),
					'topic_id > ' . $dbr->addQuotes( $id ),
				),
				__METHOD__,
				array( 'ORDER BY' => 'topic_id ASC', 'LIMIT' => $this->mBatchSize )
			);
			if ( $res ) {
				$index = 0;
				foreach ( $res as $row ) {
					$count++;
					$index++;
					$id = $row->topic_id;
					$post = Container::get( 'loader.root_post' )->get( UUID::create( $row->topic_id ) );
					if ( $post ) {
						$this->updatePost( $post, $wiki );
					}
				}
			} else {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}
		}
	}

	/**
	 * Update post
	 */
	private function updatePost( $post, $wiki ) {
		$this->updateHistory( $post, $wiki );
		$this->updateRevision( $post, $wiki );
		foreach ( $post->getChildren() as $child ) {
			$this->updatePost( $child, $wiki );
		}
	}

	/**
	 * Update history revision
	 */
	private function updateHistory( PostRevision $post, $wiki ) {
		if ( $post->getPrevRevisionId() ) {
			$parent = Container::get( 'storage.post' )->get( UUID::create( $post->getPrevRevisionId() ) );
			if ( $parent ) {
				$this->updateRevision( $parent, $wiki );
				$this->updateHistory( $parent, $wiki );
			}
		}
	}

	/**
	 * Update either header or post revision
	 */
	private function updateRevision( $revision, $wiki ) {
		if ( !$revision ) {
			return;
		}
		$type = $revision->getRevisionType();

		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );
		$res = $dbw->update(
			'flow_revision',
			array(
				'rev_user_wiki' => $wiki,
				'rev_mod_user_wiki' => $wiki,
				'rev_edit_user_wiki' => $wiki,
			),
			array(
				'rev_id' => $revision->getRevisionId()->getBinary(),
			),
			__METHOD__
		);
		if ( !$res ) {
			throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
		}
		$this->checkForSlave();

		if ( $type === 'post' ) {
			$res = $dbw->update(
				'flow_tree_revision',
				array(
					'tree_orig_user_wiki' => $wiki,
				),
				array(
					'tree_rev_id' => $revision->getRevisionId()->getBinary(),
				),
				__METHOD__
			);
			if ( !$res ) {
				throw new \MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}
			$this->checkForSlave();
		}

	}

	private function checkForSlave() {
		global $wgFlowCluster;

		$this->updatedCount++;
		if ( $this->updatedCount > $this->mBatchSize ) {
			wfWaitForSlaves( false, false, $wgFlowCluster );
			$this->updatedCount = 0;
		}
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'FlowUpdateUserWiki';
	}
}

$maintClass = "FlowUpdateUserWiki";
require_once( DO_MAINTENANCE );
