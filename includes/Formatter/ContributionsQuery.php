<?php

namespace Flow\Formatter;

use BagOStuff;
use ContribsPager;
use DeletedContribsPager;
use Flow\Container;
use Flow\Data\Storage\RevisionStorage;
use Flow\DbFactory;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Exception\FlowException;
use Flow\FlowActions;
use User;
use Wikimedia\Rdbms\ResultWrapper;

class ContributionsQuery extends AbstractQuery {

	/**
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @param ManagerGroup $storage
	 * @param TreeRepository $treeRepo
	 * @param BagOStuff $cache
	 * @param DbFactory $dbFactory
	 * @param FlowActions $actions
	 */
	public function __construct(
		ManagerGroup $storage,
		TreeRepository $treeRepo,
		BagOStuff $cache,
		DbFactory $dbFactory,
		FlowActions $actions
	) {
		parent::__construct( $storage, $treeRepo );
		$this->cache = $cache;
		$this->dbFactory = $dbFactory;
		$this->actions = $actions;
	}

	/**
	 * @param ContribsPager|DeletedContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return FormatterRow[]
	 */
	public function getResults( $pager, $offset, $limit, $descending ) {
		// build DB query conditions
		$conditions = $this->buildConditions( $pager, $offset, $descending );

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
			if ( !$rows ) {
				continue;
			}

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
					\MWExceptionHandler::logException( $e );
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
	 * @return array Query conditions
	 */
	protected function buildConditions( $pager, $offset, $descending ) {
		$conditions = [];

		// Work out user condition
		if ( property_exists( $pager, 'contribs' ) && $pager->contribs == 'newbie' ) {
			list( $minUserId, $excludeUserIds ) = $this->getNewbieConditionInfo( $pager );

			$conditions['rev_user_wiki'] = wfWikiID();
			$conditions[] = 'rev_user_id > '. (int)$minUserId;
			if ( $excludeUserIds ) {
				// better safe than sorry - make sure everything's an int
				$excludeUserIds = array_map( 'intval', $excludeUserIds );
				$conditions[] = 'rev_user_id NOT IN ( ' . implode( ',', $excludeUserIds ) .' )';
				$conditions['rev_user_ip'] = null;
			}
		} else {
			$uid = User::idFromName( $pager->target );
			if ( $uid ) {
				$conditions['rev_user_id'] = $uid;
				$conditions['rev_user_ip'] = null;
				$conditions['rev_user_wiki'] = wfWikiID();
			} else {
				$conditions['rev_user_id'] = 0;
				$conditions['rev_user_ip'] = $pager->target;
				$conditions['rev_user_wiki'] = wfWikiID();
			}
		}

		if ( property_exists( $pager, 'newOnly' ) && $pager->newOnly ) {
			$conditions['rev_parent_id'] = null;
			$conditions['rev_type'] = 'post';
		}

		// Make offset parameter.
		if ( $offset ) {
			$dbr = $this->dbFactory->getDB( DB_REPLICA );
			$offsetUUID = UUID::getComparisonUUID( $offset );
			$direction = $descending ? '>' : '<';
			$conditions[] = "rev_id $direction " . $dbr->addQuotes( $offsetUUID->getBinary() );
		}

		// Find only within requested wiki/namespace
		$conditions['workflow_wiki'] = wfWikiID();
		if ( $pager->namespace !== '' ) {
			$conditions['workflow_namespace'] = $pager->namespace;
		}

		return $conditions;
	}

	/**
	 * @param array $conditions
	 * @param int $limit
	 * @param string $revisionClass Storage type (e.g. "PostRevision", "Header")
	 * @return ResultWrapper|false false on failure
	 * @throws \MWException
	 */
	protected function queryRevisions( $conditions, $limit, $revisionClass ) {
		$dbr = $this->dbFactory->getDB( DB_REPLICA );

		switch ( $revisionClass ) {
			case 'PostRevision':
				return $dbr->select(
					[
						'flow_revision', // revisions to find
						'flow_tree_revision', // resolve to post id
						'flow_tree_node', // resolve to root post (topic title)
						'flow_workflow', // resolve to workflow, to test if in correct wiki/namespace
					],
					[ '*' ],
					$conditions,
					__METHOD__,
					[
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					],
					[
						'flow_tree_revision' => [
							'INNER JOIN',
							[ 'tree_rev_id = rev_id' ]
						],
						'flow_tree_node' => [
							'INNER JOIN',
							[
								'tree_descendant_id = tree_rev_descendant_id',
								// the one with max tree_depth will be root,
								// which will have the matching workflow id
							]
						],
						'flow_workflow' => [
							'INNER JOIN',
							[ 'workflow_id = tree_ancestor_id' ]
						],
					]
				);
				break;

			case 'Header':
				return $dbr->select(
					[ 'flow_revision', 'flow_workflow' ],
					[ '*' ],
					$conditions,
					__METHOD__,
					[
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					],
					[
						'flow_workflow' => [
							'INNER JOIN',
							[ 'workflow_id = rev_type_id' , 'rev_type' => 'header' ]
						],
					]
				);
				break;

			case 'PostSummary':
				return $dbr->select(
					[ 'flow_revision', 'flow_tree_node', 'flow_workflow' ],
					[ '*' ],
					$conditions,
					__METHOD__,
					[
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					],
					[
						'flow_tree_node' => [
							'INNER JOIN',
							[ 'tree_descendant_id = rev_type_id', 'rev_type' => 'post-summary' ]
						],
						'flow_workflow' => [
							'INNER JOIN',
							[ 'workflow_id = tree_ancestor_id' ]
						]
					]
				);
				break;

			default:
				throw new \MWException( 'Unsupported revision type ' . $revisionClass );
				break;
		}
	}

	/**
	 * Turns DB data into revision objects.
	 *
	 * @param ResultWrapper $rows
	 * @param string $revisionClass Class of revision object to build: PostRevision|Header
	 * @return array
	 */
	protected function loadRevisions( ResultWrapper $rows, $revisionClass ) {
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
	 * @param ContribsPager|DeletedContribsPager $pager
	 * @return array [minUserId, excludeUserIds]
	 */
	protected function getNewbieConditionInfo( $pager ) {
		// unlike most of Flow, this one doesn't use wfForeignMemcKey; needs
		// to be wiki-specific
		$key = wfMemcKey( 'flow', '', 'maxUserId', Container::get( 'cache.version' ) );
		$max = $this->cache->get( $key );
		if ( $max === false ) {
			// max user id not present in cache; fetch from db & save to cache for 1h
			$max = (int)$pager->getDatabase()->selectField( 'user', 'MAX(user_id)', '', __METHOD__ );
			$this->cache->set( $key, $max, 60 * 60 );
		}
		$minUserId = (int)( $max - $max / 100 );

		// exclude all users within groups with bot permission
		$excludeUserIds = [];
		$groupsWithBotPermission = User::getGroupsWithPermission( 'bot' );
		if ( count( $groupsWithBotPermission ) ) {
			$db = $pager->getDatabase();
			$rows = $db->select(
				[ 'user', 'user_groups' ],
				'user_id',
				[
					'user_id > ' . $minUserId,
					'ug_group' => $groupsWithBotPermission,
					'ug_expiry IS NULL OR ug_expiry >= ' . $db->addQuotes( $db->timestamp() )
				],
				__METHOD__,
				[],
				[
					'user_groups' => [
						'INNER JOIN',
						[ 'ug_user = user_id' ]
					]
				]
			);

			$excludeUserIds = [];
			foreach ( $rows as $row ) {
				$excludeUserIds[] = $row->user_id;
			}
		}

		return [ $minUserId, $excludeUserIds ];
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

class ContributionsRow extends FormatterRow {
	public $rev_timestamp;
}

class DeletedContributionsRow extends FormatterRow {
	public $ar_timestamp;
}
