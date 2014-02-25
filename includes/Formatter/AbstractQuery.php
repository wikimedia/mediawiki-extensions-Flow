<?php

namespace Flow\Formatter;

use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;

/**
 * Base class that collects the data necessary to utilize AbstractFormatter
 * based on a list of revisions. In some cases formatters will not utilize
 * this query and will instead receive data from a table such as the core
 * recentchanges.
 */
abstract class AbstractQuery {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

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
	 * @param TreeRepository $treeRepo
	 */
	public function __construct( ManagerGroup $storage, TreeRepository $treeRepo ) {
		$this->storage = $storage;
		$this->treeRepo = $treeRepo;
	}

	/**
	 * Entry point for batch loading metadata for a variety of revisions
	 * into the internal cache.
	 *
	 * @param AbstractRevision[] $results
	 */
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

		// map from post Id to the related root post id
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

		$rootPosts = array();
		if ( count( $rootPostResult ) > 0 ) {
			foreach ( $rootPostResult as $found ) {
				$root = reset( $found );
				$rootPosts[$root->getPostId()->getAlphadecimal()] = $root;
			}
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
			foreach ( $this->storage->getMulti( $revisionType, $ids ) as $rev ) {
				$previousRevisions[$rev->getRevisionId()->getAlphadecimal()] = $rev;
			}
		}

		$this->postCache = array_merge( $this->postCache, $rootPosts, $previousRevisions, $results );
		$this->rootPostIdCache = array_merge( $this->rootPostIdCache, $rootPostIds );
		$this->workflowCache = array_merge( $this->workflowCache, $workflows );
	}

	/**
	 * Build a stdClass object that contains all related data models necessary
	 * for rendering a revision.
	 *
	 * @param AbstractRevision $revision
	 * @param string $blockType Block name (e.g. "topic", "header")
	 * @param string $indexField The field used for pagination
	 * @return \stdClass
	 */
	protected function buildResult( AbstractRevision $revision, $blockType, $indexField ) {
		$uuid = $revision->getRevisionId();
		$timestamp = $uuid->getTimestamp();
		$fakeRow = array();

		$workflow = $this->getWorkflow( $revision );
		if ( !$workflow ) {
			wfWarn( __METHOD__ . ": could not locate workflow for revision " . $revision->getRevisionId()->getAlphadecimal() );
			return false;
		}

		// other contributions entries
		$fakeRow[$indexField] = $timestamp; // used for navbar
		$fakeRow['page_namespace'] = $workflow->getArticleTitle()->getNamespace();
		$fakeRow['page_title'] = $workflow->getArticleTitle()->getDBkey();
		$fakeRow['revision'] = $revision;
		$fakeRow['previous_revision'] = $this->getPreviousRevision( $revision );
		$fakeRow['workflow'] = $workflow;
		$fakeRow['blocktype'] = $blockType;

		if ( $revision instanceof PostRevision ) {
			$fakeRow['root_post'] = $this->getRootPost( $revision );
			if ( $fakeRow['root_post'] === null ) {
				wfWarn( __METHOD__ . ': no root post loaded for ' . $revision->getRevisionId()->getAlphadecimal() );
			} else {
				$revision->setRootPost( $fakeRow['root_post'] );
			}
		}

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
		if ( $revision->isTopicTitle() ) {
			return $revision;
		}
		$rootPostId = $this->getRootPostId( $revision );

		if ( !isset( $this->postCache[$rootPostId->getAlphadecimal()] ) ) {
			throw new \MwException( 'Did not load root post ' . $rootPostId->getAlphadecimal() );
		}

		$rootPost = $this->postCache[$rootPostId->getAlphadecimal()];
		if ( !$rootPost ) {
			throw new \MWException( 'Did not locate root post ' . $rootPostId->getAlphadecimal() );
		}
		if ( !$rootPost->isTopicTitle() ) {
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
}
