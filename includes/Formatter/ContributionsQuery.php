<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Data\Storage\RevisionStorage;
use Flow\DbFactory;
use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use InvalidArgumentException;
use MediaWiki\Exception\MWExceptionHandler;
use MediaWiki\Pager\ContribsPager;
use MediaWiki\Pager\DeletedContribsPager;
use MediaWiki\User\UserIdentityLookup;
use MediaWiki\WikiMap\WikiMap;
use Wikimedia\Rdbms\IResultWrapper;
use Wikimedia\Rdbms\SelectQueryBuilder;

class ContributionsQuery extends AbstractQuery {

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/** @var UserIdentityLookup */
	private $userIdentityLookup;

	/**
	 * @param ManagerGroup $storage
	 * @param TreeRepository $treeRepo
	 * @param DbFactory $dbFactory
	 * @param FlowActions $actions
	 * @param UserIdentityLookup $userIdentityLookup
	 */
	public function __construct(
		ManagerGroup $storage,
		TreeRepository $treeRepo,
		DbFactory $dbFactory,
		FlowActions $actions,
		UserIdentityLookup $userIdentityLookup
	) {
		parent::__construct( $storage, $treeRepo );
		$this->dbFactory = $dbFactory;
		$this->actions = $actions;
		$this->userIdentityLookup = $userIdentityLookup;
	}

	/**
	 * @param ContribsPager|DeletedContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @param array $rangeOffsets Query range, in the format of [ endOffset, startOffset ]
	 * @return FormatterRow[]
	 */
	public function getResults( $pager, $offset, $limit, $descending, $rangeOffsets = [] ) {
		// When ORES hidenondamaging filter is used, Flow entries should be skipped
		// because they are not scored.
		if ( $pager->getRequest()->getBool( 'hidenondamaging' ) ) {
			return [];
		}

		// build DB query conditions
		$conditions = $this->buildConditions( $pager, $offset, $descending, $rangeOffsets );

		$types = [
			// revision class => block type
			'PostRevision' => 'topic',
			'Header' => 'header',
			'PostSummary' => 'topicsummary'
		];

		$results = [];
		foreach ( $types as $revisionClass => $blockType ) {
			// query DB for requested revisions
			$rows = $this->queryRevisions( $conditions, $limit, $revisionClass );

			// turn DB data into revision objects
			$revisions = $this->loadRevisions( $rows, $revisionClass );

			$this->loadMetadataBatch( $revisions );
			foreach ( $revisions as $revision ) {
				try {
					if ( $this->excludeFromContributions( $revision ) ) {
						continue;
					}

					$result = $pager instanceof ContribsPager ? new ContributionsRow : new DeletedContributionsRow;
					$result = $this->buildResult( $revision, $pager->getIndexField(), $result );
					$deleted = $result->currentRevision->isDeleted() || $result->workflow->isDeleted();

					if (
						$result instanceof ContributionsRow &&
						( $deleted || $result->currentRevision->isSuppressed() )
					) {
						// don't show deleted or suppressed entries in Special:Contributions
						continue;
					}
					if ( $result instanceof DeletedContributionsRow && !$deleted ) {
						// only show deleted entries in Special:DeletedContributions
						continue;
					}

					$results[] = $result;
				} catch ( FlowException $e ) {
					MWExceptionHandler::logException( $e );
				}
			}
		}

		return $results;
	}

	/**
	 * @param AbstractRevision $revision
	 * @return bool
	 */
	private function excludeFromContributions( AbstractRevision $revision ) {
		return (bool)$this->actions->getValue( $revision->getChangeType(), 'exclude_from_contributions' );
	}

	/**
	 * @param ContribsPager|DeletedContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @param array $rangeOffsets Query range, in the format of [ endOffset, startOffset ]
	 * @return array Query conditions
	 */
	protected function buildConditions( $pager, $offset, $descending, $rangeOffsets = [] ) {
		$conditions = [];

		$isContribsPager = $pager instanceof ContribsPager;
		$userIdentity = $this->userIdentityLookup->getUserIdentityByName( $pager->getTarget() );
		if ( $userIdentity && $userIdentity->isRegistered() ) {
			$conditions['rev_user_id'] = $userIdentity->getId();
			$conditions['rev_user_ip'] = null;
		} else {
			$conditions['rev_user_id'] = 0;
			$conditions['rev_user_ip'] = $pager->getTarget();
		}
		$conditions['rev_user_wiki'] = WikiMap::getCurrentWikiId();

		if ( $isContribsPager && $pager->isNewOnly() ) {
			$conditions['rev_parent_id'] = null;
			$conditions['rev_type'] = 'post';
		}

		$dbr = $this->dbFactory->getDB( DB_REPLICA );
		// Make offset parameter.
		if ( $offset ) {
			$offsetUUID = UUID::getComparisonUUID( $offset );
			$direction = $descending ? '>' : '<';
			$conditions[] = $dbr->buildComparison( $direction, [ 'rev_id' => $offsetUUID->getBinary() ] );
		}
		if ( $rangeOffsets ) {
			$endUUID = UUID::getComparisonUUID( $rangeOffsets[0] );
			$conditions[] = $dbr->buildComparison( '<', [ 'rev_id' => $endUUID->getBinary() ] );
			// The DeletedContribsPager is only a ReverseChronologicalPager for now.
			if ( count( $rangeOffsets ) > 1 && $rangeOffsets[1] ) {
				$startUUID = UUID::getComparisonUUID( $rangeOffsets[1] );
				$conditions[] = $dbr->buildComparison( '>=', [ 'rev_id' => $startUUID->getBinary() ] );
			}
		}

		// Find only within requested wiki/namespace
		$conditions['workflow_wiki'] = WikiMap::getCurrentWikiId();
		if ( $pager->getNamespace() !== '' ) {
			$conditions['workflow_namespace'] = $pager->getNamespace();
		}

		return $conditions;
	}

	/**
	 * @param array $conditions
	 * @param int $limit
	 * @param string $revisionClass Storage type (e.g. "PostRevision", "Header")
	 * @return IResultWrapper
	 */
	protected function queryRevisions( $conditions, $limit, $revisionClass ) {
		$dbr = $this->dbFactory->getDB( DB_REPLICA );

		switch ( $revisionClass ) {
			case 'PostRevision':
				return $dbr->newSelectQueryBuilder()
					->select( '*' )
					// revisions to find
					->from( 'flow_revision' )
					// resolve to post id
					->join( 'flow_tree_revision', null, 'tree_rev_id = rev_id' )
					// resolve to root post (topic title)
					->join( 'flow_tree_node', null, [
						'tree_descendant_id = tree_rev_descendant_id'
						// the one with max tree_depth will be root,
						// which will have the matching workflow id
					] )
					// resolve to workflow, to test if in correct wiki/namespace
					->join( 'flow_workflow', null, 'workflow_id = tree_ancestor_id' )
					->where( $conditions )
					->limit( $limit )
					->orderBy( 'rev_id', SelectQueryBuilder::SORT_DESC )
					->caller( __METHOD__ )
					->fetchResultSet();

			case 'Header':
				return $dbr->newSelectQueryBuilder()
					->select( '*' )
					->from( 'flow_revision' )
					->join( 'flow_workflow', null, [ 'workflow_id = rev_type_id', 'rev_type' => 'header' ] )
					->where( $conditions )
					->limit( $limit )
					->orderBy( 'rev_id', SelectQueryBuilder::SORT_DESC )
					->caller( __METHOD__ )
					->fetchResultSet();

			case 'PostSummary':
				return $dbr->newSelectQueryBuilder()
					->select( '*' )
					->from( 'flow_revision' )
					->join( 'flow_tree_node', null, [ 'tree_descendant_id = rev_type_id', 'rev_type' => 'post-summary' ] )
					->join( 'flow_workflow', null, [ 'workflow_id = tree_ancestor_id' ] )
					->where( $conditions )
					->limit( $limit )
					->orderBy( 'rev_id', SelectQueryBuilder::SORT_DESC )
					->caller( __METHOD__ )
					->fetchResultSet();

			default:
				throw new InvalidArgumentException( 'Unsupported revision type ' . $revisionClass );
		}
	}

	/**
	 * Turns DB data into revision objects.
	 *
	 * @param IResultWrapper $rows
	 * @param string $revisionClass Class of revision object to build: PostRevision|Header
	 * @return AbstractRevision[]
	 */
	protected function loadRevisions( IResultWrapper $rows, $revisionClass ) {
		$revisions = [];
		foreach ( $rows as $row ) {
			$revisions[UUID::create( $row->rev_id )->getAlphadecimal()] = (array)$row;
		}

		// get content in external storage
		$res = [ $revisions ];
		$res = RevisionStorage::mergeExternalContent( $res );
		$revisions = reset( $res );

		// we have all required data to build revision
		$mapper = $this->storage->getStorage( $revisionClass )->getMapper();
		$revisions = array_map( [ $mapper, 'fromStorageRow' ], $revisions );

		// @todo: we may already be able to build workflowCache (and rootPostIdCache) from this DB data

		return $revisions;
	}

	/**
	 * When retrieving revisions from DB, self::mergeExternalContent will be
	 * called to fetch the content. This could fail, resulting in the content
	 * being a 'false' value.
	 *
	 * @inheritDoc
	 */
	public function validate( array $row ) {
		return !isset( $row['rev_content'] ) || $row['rev_content'] !== false;
	}
}
