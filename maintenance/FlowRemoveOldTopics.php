<?php

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\Utils\RawSql;
use Flow\DbFactory;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Repository\TreeRepository;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * @ingroup Maintenance
 */
class FlowRemoveOldTopics extends Maintenance {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var TreeRepository
	 */
	protected $treeRepo;

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	public function __construct() {
		parent::__construct();

		$this->mDescription = "Deletes old topics";

		$this->addOption( 'date', 'Date cutoff (in any format understood by wfTimestamp), topics older than this date will be deleted.', true, true );

		$this->setBatchSize( 10 );
	}

	public function execute() {
		$this->storage = Container::get( 'storage' );
		$this->treeRepo = Container::get( 'repository.tree' );
		$this->dbFactory = Container::get( 'db.factory' );

		$timestamp = wfTimestamp( TS_MW, $this->getOption( 'date' ) );
		$this->removeWorkflows( $timestamp );
		$this->removeHeader( $timestamp );
		// @todo: output how many were removed?
	}

	protected function removeHeader( $timestamp ) {
		// @todo: do I actually want to remove header? what if it's been updated since?
		// @todo: remove references
	}

	/**
	 * @param string $timestamp Timestamp in TS_MW format
	 * @throws \Flow\Exception\FlowException
	 */
	protected function removeWorkflows( $timestamp ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		// start from around unix epoch - there can be no Flow data before that
		$startId = UUID::getComparisonUUID( '1' );
		do {
			$workflows = $this->storage->find(
				'Workflow',
				array(
					new RawSql( 'workflow_id > ' . $dbr->addQuotes( $startId->getBinary() ) ),
					'workflow_wiki' => wfWikiId(),
					'workflow_type' => 'topic',
					new RawSql( 'workflow_last_update_timestamp < ' . $dbr->addQuotes( $timestamp ) ),
				),
				array( 'limit' => $this->mBatchSize )
			);

			if ( empty( $workflows ) ) {
				break;
			}

			// prepare for next batch
			$startId = end( $workflows )->getId();

			// @todo: remove everything else!
			foreach ( $workflows as $workflow ) {
				$this->removeTopicList( $workflow );
				$this->removeSummary( $workflow );
				$this->removePosts( $workflow );
			}

			var_dump( count( $workflows ) . ' workflows' );
//			$storage->multiRemove( $workflows ); // @todo

			$this->dbFactory->waitForSlaves();
		} while ( !empty( $workflows ) );
	}

	protected function removeTopicList( Workflow $workflow ) {
		$entries = $this->storage->find( 'TopicListEntry', array( 'topic_id' => $workflow->getId() ) );
		if ( $entries ) {
			var_dump( count( $entries ) . ' topiclist entries' );
//			$this->storage->multiRemove( $entries ); // @todo
		}
	}

	protected function removeSummary( Workflow $workflow ) {
		$revisions = $this->storage->find( 'PostSummary', array( 'rev_type_id' => $workflow->getId() ) );
		if ( $revisions ) {
			foreach ( $revisions as $revision ) {
				$this->removeReferences( $revision );
			}

			var_dump( count( $revisions ) . ' summaries' );
//			$this->storage->multiRemove( $revisions ); // @todo
		}
	}

	protected function removePosts( Workflow $workflow ) {
		// fetch all children (posts) from a topic
		$subtree = $this->treeRepo->fetchSubtreeIdentityMap( $workflow->getId() );

		$conds = array();
		foreach ( $subtree as $id => $data ) {
			$conds[] = array( 'rev_type_id' => UUID::create( $id ) );
		}

		$posts = $this->storage->findMulti( 'PostRevision', $conds );
		foreach ( $posts as $revisions ) {
			foreach ( $revisions as $revision ) {
				$this->removeReferences( $revision );
			}

			var_dump( count( $revisions ) . ' post revisions' );
//			$this->storage->multiRemove( $revisions ); // @todo
//			$this->treeRepo->delete( $revision->getCollectionId() ); // @todo
		}

		// @todo: remove TreeRepo data...
	}

	protected function removeReferences( AbstractRevision $revision ) {
		$wikiReferences = $this->storage->find( 'WikiReference', array(
			'ref_src_wiki' => wfWikiId(),
			'ref_src_object_type' => $revision->getRevisionType(),
			'ref_src_object_id' => $revision->getCollectionId(),
		) );
		if ( $wikiReferences ) {
			var_dump( count( $wikiReferences ) . ' wiki references' );
//			$this->storage->multiRemove( $wikiReferences ); // @todo
		}

		$urlReferences = $this->storage->find( 'URLReference', array(
			'ref_src_wiki' => wfWikiId(),
			'ref_src_object_type' => $revision->getRevisionType(),
			'ref_src_object_id' => $revision->getCollectionId(),
		) );
		if ( $urlReferences ) {
			var_dump( count( $urlReferences ) . ' url references' );
//			$this->storage->multiRemove( $urlReferences ); // @todo
		}
	}
}

$maintClass = 'FlowRemoveOldTopics'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
