<?php

namespace Flow\Formatter;

use ContribsPager;
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
	 */
	public function __construct( ManagerGroup $storage, BagOStuff $cache, TreeRepository $treeRepo ) {
		$this->storage = $storage;
		$this->cache = $cache;
		$this->treeRepo = $treeRepo;
	}

	/**
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return string|bool false on failure
	 */
	public function getResults( ContribsPager $pager, $offset, $limit, $descending ) {
		$target = $pager->target;
		$conditions = array( 'rev_user_wiki' => wfWikiId() );

		// Work out user condition
		if ( $pager->contribs == 'newbie' ) {
			list( $minUserId, $excludeUserIds ) = $this->getNewbieConditionInfo( $pager );

			$conditions[] = new RawSql( 'rev_user_id > '. (int) $minUserId );
			if ( $excludeUserIds ) {
				// better safe than sorry - make sure everything's an int
				$excludeUserIds = array_map( 'intval', $excludeUserIds );
				$conditions[] = new RawSql( 'rev_user_id NOT IN (' . implode( ',', $excludeUserIds ) .')' );
			}
		} else {
			$uid = User::idFromName( $target );
			if ( $uid ) {
				$conditions['rev_user_id'] = $uid;
			} else {
				$conditions['rev_user_ip'] = $target;
			}
		}

		// Make offset parameter.
		if ( $offset ) {
			$offsetUUID = UUID::getComparisonUUID( $offset );
			$direction = $descending ? '>' : '<';
			$offsetCondition = function( $db ) use ( $direction, $offsetUUID ) {
				return "rev_id $direction " . $db->addQuotes( $offsetUUID->getBinary() );
			};
			$conditions[] = new RawSql( $offsetCondition );
		}

		$types = array(
			// revision class => block type
			'PostRevision' => 'topic',
			'Header' => 'header',
		);

		$results = array();
		foreach ( $types as $revisionClass => $blockType ) {
			$revisions = $this->findRevisions( $pager, $conditions, $limit, $revisionClass );
			$this->loadMetadataBatch( $revisions );
			foreach ( $revisions as $revision ) {
				try {
					$result = $this->buildResult( $pager, $revision, $blockType );
				} catch ( FlowException $e ) {
					$result = false;
					// Comment out for now since we expect some flow exceptions, when gerrit 111952 is
					// merged, then we will turn this back on so we can catch unexpected exceptions.
					//\MWExceptionHandler::logException( $e );
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
	 * @param ContribsPager $pager
	 * @param array $conditions
	 * @param int $limit
	 * @param string $revisionClass Storage type (e.g. "PostRevision", "Header")
	 * @return mixed
	 */
	protected function findRevisions( ContribsPager $pager, $conditions, $limit, $revisionClass ) {
		return $this->storage->find( $revisionClass, $conditions, array(
			'LIMIT' => $limit,
		) );
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
