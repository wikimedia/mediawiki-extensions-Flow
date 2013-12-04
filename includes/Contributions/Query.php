<?php

namespace Flow\Contributions;

use ContribsPager;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Model\Workflow;
use Flow\Data\RawSql;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use User;
use BagOStuff;

class Query {
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
	 * @var array Associative array of hex post ID to root post's UUID object.
	 */
	protected $rootPostIdCache = array();

	/**
	 * @var array Associative array of root post ID to PostRevision object.
	 */
	protected $rootPostCache = array();

	/**
	 * @var array Associative array of workflow ID to Workflow object.
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
	 * @param $data array an array of results of all contribs queries, to be merged to form all contributions data
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return string|bool false on failure
	 */
	public function getResults( ContribsPager $pager, $offset, $limit, $descending ) {
		$target = $pager->target;
		$conditions = array();

		// Work out user condition
		if ( $pager->contribs == 'newbie' ) {
			$conditions['rev_user_id'] = $this->getNewbies( $pager );
		} else {
			$uid = User::idFromName( $target );
			if ( $uid ) {
				$conditions['rev_user_id'] = $uid;
			} else {
				$conditions['rev_user_text'] = $target;
			}
		}

		// Make offset parameter.
		if ( $offset ) {
			$offsetUUID = UUID::getComparisonUUID( $offset );
			$direction = $descending ? '<' : '>';
			$offsetCondition = function( $db ) use ( $direction, $offsetUUID ) {
				return "rev_id $direction " . $db->quote( $offsetUUID->getBinary() );
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
				$results[] = $this->buildResult( $pager, $revision, $blockType );
			}
		}

		return $results;
	}

	protected function loadMetadataBatch( $results ) {
		// Batch load data related to a list of revisions
		$postIds = array();
		$workflowIds = array();
		foreach( $results as $result ) {
			if ( $result instanceof PostRevision ) {
				$postIds[] = $result->getPostId();
			} elseif ( $result instanceof Header ) {
				$workflowIds[] = $result->getWorkflowId();
			}
		}

		$rootPostIds = array_filter( $this->treeRepo->findRoots( $postIds ) );
		$rootPosts = $this->storage->getMulti( 'PostRevision', array_values( $rootPostIds ) );
		// No harm in searching for a workflow that doesn't exist
		$workflows = $this->storage->getMulti( 'Workflow', array_merge( $rootPostIds, $postIds, $workflowIds ) );

		foreach( $rootPosts as $rootPost ) {
			if ( ! $rootPost->isTopicTitle() ) {
				throw new \MWException( "Not a topic title: ".$rootPost->getRevisionId() );
			}
		}

		$this->rootPostCache = array_merge( $this->rootPostCache, $rootPosts, $results );
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
	 * @return stdClass
	 */
	protected function buildResult( ContribsPager $pager, AbstractRevision $revision, $blockType ) {
		$uuid = $revision->getRevisionId();
		$timestamp = $uuid->getTimestamp();
		$fakeRow = array();

		$workflow = $this->getWorkflow( $revision );

		// other contributions entries
		$fakeRow[$pager->getIndexField()] = $timestamp; // used for navbar
		$fakeRow['page_namespace'] = $workflow->getArticleTitle()->getNamespace();
		$fakeRow['page_title'] = $workflow->getArticleTitle()->getDBkey();
		$fakeRow['revision'] = $revision;
		$fakeRow['workflow'] = $workflow;
		$fakeRow['blocktype'] = $blockType;

		if ( $blockType == 'topic' ) {
			$fakeRow['root-post'] = $this->getRootPost( $revision );
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
	 * Retrieves the root post for a given PostRevision
	 * @param  PostRevision $revision The revision to retrieve the root post for.
	 * @return PostRevision           PostRevision of the root post.
	 */
	protected function getRootPost( PostRevision $revision ) {
		$rootPostId = $this->getRootPostId( $revision );

		if ( isset( $this->rootPostCache[$rootPostId->getHex()] ) ) {
			return $this->rootPostCache[$rootPostId->getHex()];
		} else {
			return $this->rootPostCache[$rootPostId->getHex()] =
				$this->storage->get( 'PostRevision', $rootPostId );
		}
	}

	/**
	 * Gets the root post ID for a given PostRevision
	 * @param  PostRevision $revision The revision to get the root post ID for.
	 * @return UUID                   The UUID for the root post.
	 */
	protected function getRootPostId( PostRevision $revision ) {
		$postId = $revision->getPostId();
		if ( $revision->isTopicTitle() ) {
			return $postId;
		} elseif ( isset( $this->rootPostIdCache[$postId->getHex()] ) ) {
			return $this->rootPostIdCache[$postId->getHex()];
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
		if ( isset( $this->workflowCache[$workflowId->getHex()] ) ) {
			return $this->workflowCache[$workflowId->getHex()];
		} else {
			return $this->storage->get( 'Workflow', $workflowId );
		}
	}

	/**
	 * @param ContribsPager $pager
	 * @return array Array of user ids
	 */
	protected function getNewbies( ContribsPager $pager) {
		// unlike most of Flow, this one doesn't use wfForeignMemcKey; needs
		// to be wiki-specific
		$key = wfMemcKey( 'flow', '', 'maxUserId' );
		$max = $this->cache->get( $key );
		if ( $max === false ) {
			// max user id not present in cache; fetch from db & save to cache for 1h
			$max = (int) $pager->getDatabase()->selectField( 'user', 'MAX(user_id)', '', __METHOD__ );
			$this->cache->set( $key, $max, 60 * 60 );
		}

		// newbie = last 1% of users, without usergroup
		$rows = $pager->getDatabase()->select(
			array( 'user', 'user_groups' ),
			'user_id',
			array(
				'user_id > ' . (int) ( $max - $max / 100 ),
				'ug_group' => null
			),
			__METHOD__,
			array(),
			array(
				'user_groups' => array(
					'LEFT JOIN',
					array(
						'ug_user = user_id'
					)
				)
			)
		);

		$userIds = array();
		foreach ( $rows as $row ) {
			$userIds[] = $row->user_id;
		}

		return $userIds;
	}
}
