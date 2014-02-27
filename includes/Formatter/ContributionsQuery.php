<?php

namespace Flow\Formatter;

use ContribsPager;
use Flow\Data\RevisionStorage;
use Flow\DbFactory;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Model\Workflow;
use Flow\Data\RawSql;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Exception\FlowException;
use User;
use BagOStuff;
use Flow\Container;

class ContributionsQuery {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * @var TreeRepository
	 */
	protected $treeRepo;

	/**
	 * @var DBFactory
	 */
	protected $dbFactory;

	/**
	 * @var UUID[] Associative array of post ID to root post's UUID object.
	 */
	protected $rootPostIdCache = array();

	/**
	 * @var PostRevision[] Associative array of post ID to PostRevision object.
	 */
	protected $postCache = array();

	/**
	 * @var Workflow[] Associative array of workflow ID to Workflow object.
	 */
	protected $workflowCache = array();

	/**
	 * @param ManagerGroup $storage
	 * @param BagOStuff $cache
	 * @param TreeRepository $treeRepo
	 * @param DBFactory $dbFactory
	 */
	public function __construct( ManagerGroup $storage, BagOStuff $cache, TreeRepository $treeRepo, DbFactory $dbFactory ) {
		$this->storage = $storage;
		$this->cache = $cache;
		$this->treeRepo = $treeRepo;
		$this->dbFactory = $dbFactory;
	}

	/**
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return string|bool false on failure
	 */
	public function getResults( ContribsPager $pager, $offset, $limit, $descending ) {
		// build DB query conditions
		$conditions = $this->buildConditions( $pager, $offset, $descending );

		$types = array(
			// revision class => block type
			'PostRevision' => 'topic',
			'Header' => 'header',
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
					$result = $this->buildResult( $pager, $revision, $blockType );
				} catch ( FlowException $e ) {
					$result = false;
					\MWExceptionHandler::logException( $e );
				}
				if ( $result ) {
					$results[] = $result;
				}
			}
		}

		return $results;
	}

	protected function loadMetadataBatch( $results ) {
		// Batch load data related to a list of revisions
		$postIds = array();
		$workflowIds = array();
		$previousRevisionIds = array();
		foreach( $results as $result ) {
			if ( $result instanceof PostRevision ) {
				// If top-level, then just get the workflow.
				// Otherwise we need to find the root post.
				if ( $result->isTopicTitle() ) {
					$workflowIds[] = $result->getPostId();
				} else {
					$postIds[] = $result->getPostId();
				}
			} elseif ( $result instanceof Header ) {
				$workflowIds[] = $result->getWorkflowId();
			}

			$previousRevisionIds[get_class( $result )][] = $result->getPrevRevisionId();
		}

		$rootPostIds = array_filter( $this->treeRepo->findRoots( $postIds ) );
		$rootPostRequests = array();
		foreach( $rootPostIds as $postId ) {
			$rootPostRequests[] = array( 'tree_rev_descendant_id' => $postId );
		}

		$rootPostResult = $this->storage->findMulti(
			'PostRevision',
			$rootPostRequests,
			array(
				'SORT' => 'rev_id',
				'ORDER' => 'DESC',
				'LIMIT' => 1,
			)
		);

		if ( count( $rootPostResult ) > 0 ) {
			$rootPosts = call_user_func_array( 'array_merge', $rootPostResult ); // Muahaha
		} else {
			$rootPosts = array();
		}

		// Workflow IDs are the same as root post IDs
		// So any post IDs that *are* root posts + found root post IDs + header workflow IDs
		// should cover the lot.
		$workflows = $this->storage->getMulti( 'Workflow', array_merge( $rootPostIds, $workflowIds ) );

		// preload all previous revisions
		$previousRevisions = array();
		foreach ( $previousRevisionIds as $revisionType => $ids ) {
			// get rid of null-values (for original revisions, without previous revision)
			$ids = array_filter( $ids );
			$previousRevisions = $this->storage->getMulti( $revisionType, $ids );
		}

		$this->postCache = array_merge( $this->postCache, $rootPosts, $previousRevisions, $results );
		$this->rootPostIdCache = array_merge( $this->rootPostIdCache, $rootPostIds );
		$this->workflowCache = array_merge( $this->workflowCache, $workflows );
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

			$conditions[] = 'rev_user_id > '. (int) $minUserId;
			if ( $excludeUserIds ) {
				// better safe than sorry - make sure everything's an int
				$excludeUserIds = array_map( 'intval', $excludeUserIds );
				$conditions[] = 'rev_user_id NOT IN (' . implode( ',', $excludeUserIds ) .')';
			}
		} else {
			$uid = User::idFromName( $pager->target );
			if ( $uid ) {
				$conditions['rev_user_id'] = $uid;
			} else {
				$conditions['rev_user_ip'] = $pager->target;
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
					array( 'flow_revision', 'flow_header_revision', 'flow_workflow' ),
					array( '*' ),
					$conditions,
					__METHOD__,
					array(
						'LIMIT' => $limit,
						'ORDER BY' => 'rev_id DESC',
					),
					array(
						'flow_header_revision' => array(
							'INNER JOIN',
							array( 'header_rev_id = rev_id' )
						),
						'flow_workflow' => array(
							'INNER JOIN',
							array( 'workflow_id = header_workflow_id' )
						),
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
	 * @param \ResultWrapper $rows
	 * @param string $revisionClass Class of revision object to build: PostRevision|Header
	 * @return array
	 */
	protected function loadRevisions( \ResultWrapper $rows, $revisionClass ) {
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
	 * @param AbstractRevision $revision
	 * @param string $blockType Block name (e.g. "topic", "header")
	 * @return \stdClass
	 */
	protected function buildResult( ContribsPager $pager, AbstractRevision $revision, $blockType ) {
		$uuid = $revision->getRevisionId();
		$timestamp = $uuid->getTimestamp();
		$fakeRow = array();

		$workflow = $this->getWorkflow( $revision );
		if ( !$workflow ) {
			wfWarn( __METHOD__ . ": could not locate workflow for revision " . $revision->getRevisionId()->getAlphadecimal() );
			return false;
		}

		// other contributions entries
		$fakeRow[$pager->getIndexField()] = $timestamp; // used for navbar
		$fakeRow['page_namespace'] = $workflow->getArticleTitle()->getNamespace();
		$fakeRow['page_title'] = $workflow->getArticleTitle()->getDBkey();
		$fakeRow['revision'] = $revision;
		$fakeRow['previous_revision'] = $this->getPreviousRevision( $revision );
		$fakeRow['workflow'] = $workflow;
		$fakeRow['blocktype'] = $blockType;

		if ( $blockType == 'topic' && $revision instanceof PostRevision ) {
			$fakeRow['root_post'] = $this->getRootPost( $revision );
		}

		// just to make sure entries will never be confused with anything else
		$fakeRow['flow_contribution'] = 'flow';

		return (object) $fakeRow;
	}

	/**
	 * @param AbstractRevision $revision
	 * @return Workflow
	 * @throws \MWException
	 */
	protected function getWorkflow( AbstractRevision $revision ) {
		if ( $revision instanceof PostRevision ) {
			$rootPostId = $this->getRootPostId( $revision );

			return $this->getWorkflowById( $rootPostId );
		} elseif ( $revision instanceof Header ) {
			return $this->getWorkflowById( $revision->getWorkflowId() );
		} else {
			throw new \MWException( 'Unsupported revision type ' . get_class( $revision ) );
		}
	}

	/**
	 * Retrieves the previous revision for a given AbstractRevision
	 * @param  AbstractRevision $revision The revision to retrieve the previous revision for.
	 * @return AbstractRevision|null      AbstractRevision of the previous revision or null if no previous revision.
	 */
	protected function getPreviousRevision( AbstractRevision $revision ) {
		$previousRevisionId = $revision->getPrevRevisionId();

		// original post; no previous revision
		if ( $previousRevisionId === null ) {
			return null;
		}

		if ( !isset( $this->postCache[$previousRevisionId->getAlphadecimal()] ) ) {
			$this->postCache[$previousRevisionId->getAlphadecimal()] =
				$this->storage->get( 'PostRevision', $previousRevisionId );
		}

		return $this->postCache[$previousRevisionId->getAlphadecimal()];
	}

	/**
	 * Retrieves the root post for a given PostRevision
	 * @param  PostRevision $revision The revision to retrieve the root post for.
	 * @return PostRevision           PostRevision of the root post.
	 * @throws \MWException
	 */
	protected function getRootPost( PostRevision $revision ) {
		$rootPostId = $this->getRootPostId( $revision );

		if ( !isset( $this->postCache[$rootPostId->getAlphadecimal()] ) ) {
			$this->postCache[$rootPostId->getAlphadecimal()] =
				$this->storage->get( 'PostRevision', $rootPostId );
		}

		$rootPost = $this->postCache[$rootPostId->getAlphadecimal()];
		if ( $rootPost && !$rootPost->isTopicTitle() ) {
			throw new \MWException( "Not a topic title: " . $rootPost->getRevisionId() );
		}

		return $rootPost;
	}

	/**
	 * Gets the root post ID for a given PostRevision
	 * @param  PostRevision $revision The revision to get the root post ID for.
	 * @return UUID                   The UUID for the root post.
	 * @throws \MWException
	 */
	protected function getRootPostId( PostRevision $revision ) {
		$postId = $revision->getPostId();
		if ( $revision->isTopicTitle() ) {
			return $postId;
		} elseif ( isset( $this->rootPostIdCache[$postId->getAlphadecimal()] ) ) {
			return $this->rootPostIdCache[$postId->getAlphadecimal()];
		} else {
			throw new \MWException( "Unable to find root post ID for post $postId" );
		}
	}

	/**
	 * Gets a Workflow object given its ID
	 * @param  UUID   $workflowId The Workflow ID to retrieve.
	 * @return Workflow           The Workflow.
	 */
	protected function getWorkflowById( UUID $workflowId ) {
		if ( isset( $this->workflowCache[$workflowId->getAlphadecimal()] ) ) {
			return $this->workflowCache[$workflowId->getAlphadecimal()];
		} else {
			return $this->workflowCache[$workflowId->getAlphadecimal()] = $this->storage->get( 'Workflow', $workflowId );
		}
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
