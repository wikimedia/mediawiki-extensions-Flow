<?php

namespace Flow\Search\Iterators;

use Flow\DbFactory;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Repository\RootPostLoader;
use stdClass;

class TopicIterator extends AbstractIterator {
	/**
	 * @var PostRevision
	 */
	protected $previous;

	/**
	 * @var RootPostLoader
	 */
	protected $rootPostLoader;

	/**
	 * @var bool
	 */
	public $orderByUUID = false;

	public function __construct( DbFactory $dbFactory, RootPostLoader $rootPostLoader ) {
		parent::__construct( $dbFactory );
		$this->rootPostLoader = $rootPostLoader;
	}

	/**
	 * Instead of querying for revisions (which is what we actually need), we'll
	 * just query the workflow table, which will save us some complicated joins.
	 * The workflow_id for a topic title (aka root post) is the same as its
	 * collection id, so we can pass that to the root post loader and *poof*, we
	 * have our revisions!
	 *
	 * @inheritDoc
	 */
	protected function query() {
		if ( $this->orderByUUID ) {
			$order = 'workflow_id';
		} else {
			$order = 'workflow_last_update_timestamp';
		}
		return $this->dbr->newSelectQueryBuilder()
			// for root post (topic title), workflow_id is the same as its rev_type_id
			->select( [ 'workflow_id', 'workflow_last_update_timestamp' ] )
			->from( 'flow_workflow' )
			->where( [ 'workflow_type' => 'topic' ] )
			->andWhere( $this->conditions )
			->orderBy( $order )
			->caller( __METHOD__ )
			->fetchResultSet();
	}

	/**
	 * @inheritDoc
	 */
	protected function transform( stdClass $row ) {
		$root = UUID::create( $row->workflow_id );

		// we need to fetch all data via rootloader because we'll want children
		// to be populated
		return $this->rootPostLoader->get( $root );
	}
}
