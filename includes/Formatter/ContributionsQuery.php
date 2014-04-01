<?php

namespace Flow\Formatter;

use BagOStuff;
use ContribsPager;
use Flow\Container;
use Flow\Data\RevisionStorage;
use Flow\DbFactory;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Exception\FlowException;
use ResultWrapper;
use User;

class ContributionsQuery extends AbstractQuery {

	/**
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * @var DBFactory
	 */
	protected $dbFactory;

	/**
	 * @param ManagerGroup $storage
	 * @param BagOStuff $cache
	 * @param TreeRepository $treeRepo
	 * @param DBFactory $dbFactory
	 */
	public function __construct( ManagerGroup $storage, TreeRepository $treeRepo, BagOStuff $cache, DbFactory $dbFactory ) {
		parent::__construct( $storage, $treeRepo );
		$this->cache = $cache;
		$this->dbFactory = $dbFactory;
	}

	/**
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return FormatterRow[]
	 */
	public function getResults( ContribsPager $pager, $offset, $limit, $descending ) {
		// build DB query conditions
		$conditions = $this->buildConditions( $pager, $offset, $descending );

		$types = array(
			// revision class => block type
			'PostRevision' => 'topic',
			'Header' => 'header',
			'PostSummary' => 'topicsummary'
		);

		$results = array();
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
					$result = new ContributionsRow;
					$result = $this->buildResult( $revision, $pager->getIndexField(), $result );
					$results[] = $result;
				} catch ( FlowException $e ) {
					\MWExceptionHandler::logException( $e );
				}
			}
		}

		return $results;
	}

	/**
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return array Query conditions
	 */
	protected function buildConditions( ContribsPager $pager, $offset, $descending ) {
		// Work out user condition
		if ( $pager->contribs == 'newbie' ) {
			list( $minUserId, $excludeUserIds ) = $this->getNewbieConditionInfo( $pager );

			$conditions[] = 'rev_user_id > '. (int)$minUserId;
			if ( $excludeUserIds ) {
				// better safe than sorry - make sure everything's an int
				$excludeUserIds = array_map( 'intval', $excludeUserIds );
				$conditions[] = 'rev_user_id NOT IN ( ' . implode( ',', $excludeUserIds ) .' )';
				$conditions['rev_user_ip'] = null;
				$conditions['rev_user_wiki'] = wfWikiId();
			}
		} else {
			$uid = User::idFromName( $pager->target );
			if ( $uid ) {
				$conditions['rev_user_id'] = $uid;
				$conditions['rev_user_ip'] = null;
				$conditions['rev_user_wiki'] = wfWikiId();
			} else {
				$conditions['rev_user_id'] = 0;
				$conditions['rev_user_ip'] = $pager->target;
				$conditions['rev_user_wiki'] = wfWikiId();
			}
		}

		// Make offset parameter.
		if ( $offset ) {
			$dbr = $this->dbFactory->getDB( DB_SLAVE );
			$offsetUUID = UUID::getComparisonUUID( $offset );
			$direction = $descending ? '>' : '<';
			$conditions[] = "rev_id $direction " . $dbr->addQuotes( $offsetUUID->getBinary() );
		}

		// Find only within requested wiki/namespace
		$conditions['workflow_wiki'] = wfWikiId();
		if ( $pager->namespace !== '' ) {
			$conditions['workflow_namespace'] = $pager->namespace;
		}

		return $conditions;
	}

	/**
	 * @param array $conditions
	 * @param int $limit
	 * @param string $revisionClass Storage type (e.g. "PostRevision", "Header")
	 * @return ResultWrapper|boolean false on failure
	 * @throws \MWException
	 */
	protected function queryRevisions( $conditions, $limit, $revisionClass ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		switch ( $revisionClass ) {
			case 'PostRevision':
				return $dbr->select(
					array(
						'flow_revision', // revisions to find
						'flow_tree_revision', // resolve to post id
						'flow_tree_node', // resolve to root post (topic title)
						'flow_workflow', // resolve to workflow, to test if in correct wiki/namespace
					),
					array( '*' ),
					$conditions,
					__METHOD__,
					array(
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					),
					array(
						'flow_tree_revision' => array(
							'INNER JOIN',
							array( 'tree_rev_id = rev_id' )
						),
						'flow_tree_node' => array(
							'INNER JOIN',
							array(
								'tree_descendant_id = tree_rev_descendant_id',
								// the one with max tree_depth will be root,
								// which will have the matching workflow id
							)
						),
						'flow_workflow' => array(
							'INNER JOIN',
							array( 'workflow_id = tree_ancestor_id' )
						),
					)
				);
				break;

			case 'Header':
				return $dbr->select(
					array( 'flow_revision', 'flow_workflow' ),
					array( '*' ),
					$conditions,
					__METHOD__,
					array(
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					),
					array(
						'flow_workflow' => array(
							'INNER JOIN',
							array( 'workflow_id = rev_type_id' , 'rev_type' => 'header' )
						),
					)
				);
				break;

			case 'PostSummary':
				return $dbr->select(
					array( 'flow_revision', 'flow_workflow', 'flow_tree_node' ),
					array( '*' ),
					$conditions + array(
						'workflow_id = tree_ancestor_id',
						'tree_descendant_id = rev_type_id',
						'rev_type' => 'post-summary'
					),
					__METHOD__,
					array(
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					)
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
		$revisions = array();
		foreach ( $rows as $row ) {
			$revisions[UUID::create( $row->rev_id )->getAlphadecimal()] = (array) $row;
		}

		// get content in external storage
		$revisions = RevisionStorage::mergeExternalContent( array( $revisions ) );
		$revisions = reset( $revisions );

		// we have all required data to build revision
		$mapper = $this->storage->getStorage( $revisionClass )->getMapper();
		$revisions = array_map( array( $mapper, 'fromStorageRow' ), $revisions );

		// @todo: we may already be able to build workflowCache (and rootPostIdCache) from this DB data

		return $revisions;
	}

	/**
	 * @param ContribsPager $pager
	 * @return array [minUserId, excludeUserIds]
	 */
	protected function getNewbieConditionInfo( ContribsPager $pager ) {
		// unlike most of Flow, this one doesn't use wfForeignMemcKey; needs
		// to be wiki-specific
		$key = wfMemcKey( 'flow', '', 'maxUserId', Container::get( 'cache.version' ) );
		$max = $this->cache->get( $key );
		if ( $max === false ) {
			// max user id not present in cache; fetch from db & save to cache for 1h
			$max = (int) $pager->getDatabase()->selectField( 'user', 'MAX(user_id)', '', __METHOD__ );
			$this->cache->set( $key, $max, 60 * 60 );
		}
		$minUserId = (int) ( $max - $max / 100 );

		// exclude all users withing groups with bot permission
		$excludeUserIds = array();
		$groupsWithBotPermission = User::getGroupsWithPermission( 'bot' );
		if ( count( $groupsWithBotPermission ) ) {
			$rows = $pager->getDatabase()->select(
				array( 'user', 'user_groups' ),
				'user_id',
				array(
					'user_id > ' . $minUserId,
					'ug_group' => $groupsWithBotPermission
				),
				__METHOD__,
				array(),
				array(
					'user_groups' => array(
						'INNER JOIN',
						array( 'ug_user = user_id' )
					)
				)
			);

			$excludeUserIds = array();
			foreach ( $rows as $row ) {
				$excludeUserIds[] = $row->user_id;
			}
		}

		return array( $minUserId, $excludeUserIds );
	}
}

class ContributionsRow extends FormatterRow {
	public $rev_timestamp;
}
