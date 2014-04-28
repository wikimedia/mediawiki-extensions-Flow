<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
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
	protected $treeRepository;

	/**
	 * @var UUID[] Associative array of post ID to root post's UUID object.
	 */
	protected $rootPostIdCache = array();

	/**
	 * @var PostRevision[] Associative array of post ID to PostRevision object.
	 */
	protected $postCache = array();

	/**
	 * @var AbstractRevision[] Associative array of revision ID to AbstractRevision object
	 */
	protected $revisionCache = array();

	/**
	 * @var Workflow[] Associative array of workflow ID to Workflow object.
	 */
	protected $workflowCache = array();

	/**
	 * Array of collection ids mapping to their most recent revision ids.
	 *
	 * @var array
	 */
	protected $currentRevisionsCache = array();

	/**
	 * @param ManagerGroup $storage
	 * @param TreeRepository $treeRepository
	 */
	public function __construct( ManagerGroup $storage, TreeRepository $treeRepository ) {
		$this->storage = $storage;
		$this->treeRepository = $treeRepository;
	}

	/**
	 * Entry point for batch loading metadata for a variety of revisions
	 * into the internal cache.
	 *
	 * @param AbstractRevision[]|ResultWrapper $results
	 */
	protected function loadMetadataBatch( $results ) {
		// Batch load data related to a list of revisions
		$postIds = array();
		$workflowIds = array();
		$revisions = array();
		$previousRevisionIds = array();
		$collectionIds = array();
		foreach( $results as $result ) {
			if ( $result instanceof PostRevision ) {
				// If top-level, then just get the workflow.
				// Otherwise we need to find the root post.
				if ( $result->isTopicTitle() ) {
					$workflowIds[] = $result->getPostId();
				} else {
					$postIds[] = $result->getPostId();
				}
				$this->postCache[$result->getPostId()->getAlphadecimal()] = $result;
			} elseif ( $result instanceof Header ) {
				$workflowIds[] = $result->getWorkflowId();
			}

			$revisions[$result->getRevisionId()->getAlphadecimal()] = $result;
			$previousRevisionIds[get_class( $result )][] = $result->getPrevRevisionId();

			$collection = $result->getCollection();
			$collectionIds[get_class( $result )][] = $collection->getId();
		}

		// map from post Id to the related root post id
		$rootPostIds = array_filter( $this->treeRepository->findRoots( $postIds ) );

		$rootPostRequests = array();
		foreach( $rootPostIds as $postId ) {
			$rootPostRequests[] = array( 'rev_type_id' => $postId );
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
				$revisions[$root->getRevisionId()->getAlphadecimal()] = $root;
			}
		}

		// Workflow IDs are the same as root post IDs
		// So any post IDs that *are* root posts + found root post IDs + header workflow IDs
		// should cover the lot.
		$workflows = $this->storage->getMulti( 'Workflow', array_merge( $rootPostIds, $workflowIds ) );

		// preload all previous revisions
		foreach ( $previousRevisionIds as $revisionType => $ids ) {
			// get rid of null-values (for original revisions, without previous revision)
			$ids = array_filter( $ids );
			/** @var AbstractRevision[] $found */
			$found = $this->storage->getMulti( $revisionType, $ids );
			foreach ( $found as $rev ) {
				$revisions[$rev->getRevisionId()->getAlphadecimal()] = $rev;
			}
		}

		// preload all current versions
		foreach ( $collectionIds as $revisionType => $ids ) {
			$queries = array();
			foreach ( $ids as $uuid ) {
				$queries[] = array( 'rev_type_id' => $uuid );
			}

			/** @var AbstractRevision[] $found */
			$found = $this->storage->findMulti( $revisionType,
				$queries,
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);

			foreach ( $found as $rev ) {
				$rev = reset( $rev );
				$this->currentRevisionsCache[$rev->getCollectionId()->getAlphadecimal()] = $rev->getRevisionId();
				$revisions[$rev->getRevisionId()->getAlphadecimal()] = $rev;
			}
		}

		$this->revisionCache = array_merge( $this->revisionCache, $revisions );
		$this->postCache = array_merge( $this->postCache, $rootPosts );
		$this->rootPostIdCache = array_merge( $this->rootPostIdCache, $rootPostIds );
		$this->workflowCache = array_merge( $this->workflowCache, $workflows );
	}

	/**
	 * Build a stdClass object that contains all related data models necessary
	 * for rendering a revision.
	 *
	 * @param AbstractRevision $revision
	 * @param string $indexField The field used for pagination
	 * @param FormatterRow|null Row to populate
	 * @return FormatterRow
	 * @throws FlowException
	 */
	protected function buildResult( AbstractRevision $revision, $indexField, FormatterRow $row = null ) {
		$uuid = $revision->getRevisionId();
		$timestamp = $uuid->getTimestamp();

		$workflow = $this->getWorkflow( $revision );
		if ( !$workflow ) {
			throw new FlowException( "could not locate workflow for revision " . $revision->getRevisionId()->getAlphadecimal() );
		}

		$row = $row ?: new FormatterRow;
		$row->revision = $revision;
		$row->previousRevision = $this->getPreviousRevision( $revision );
		$row->currentRevision = $this->getCurrentRevision( $revision );
		$row->workflow = $workflow;
		// some core classes that process this row before our formatter
		// require a specific field to handle pagination
		if ( property_exists( $row, $indexField ) ) {
			$row->$indexField = $timestamp;
		}

		if ( $revision instanceof PostRevision ) {
			$row->rootPost = $this->getRootPost( $revision );
			$revision->setRootPost( $row->rootPost );
		}

		return $row;
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
		} elseif ( $revision instanceof PostSummary ) {
			return $this->getWorkflowById( $revision->getCollection()->getWorkflowId() );
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

		if ( !isset( $this->revisionCache[$previousRevisionId->getAlphadecimal()] ) ) {
			$this->revisionCache[$previousRevisionId->getAlphadecimal()] =
				$this->storage->get( 'PostRevision', $previousRevisionId );
		}

		return $this->revisionCache[$previousRevisionId->getAlphadecimal()];
	}

	/**
	 * Retrieves the current revision for a given AbstractRevision
	 * @param  AbstractRevision $revision The revision to retrieve the current revision for.
	 * @return AbstractRevision|null      AbstractRevision of the current revision.
	 */
	protected function getCurrentRevision( AbstractRevision $revision ) {
		$collectionId = $revision->getCollectionId();
		if ( !isset( $this->currentRevisionsCache[$collectionId->getAlphadecimal()] ) ) {
			$currentRevision = $revision->getCollection()->getLastRevision();

			$this->currentRevisionsCache[$collectionId->getAlphadecimal()] = $currentRevision->getRevisionId();
			$this->revisionCache[$currentRevision->getRevisionId()->getAlphadecimal()] = $currentRevision;
		}

		$currentRevisionId = $this->currentRevisionsCache[$collectionId->getAlphadecimal()];
		return $this->revisionCache[$currentRevisionId->getAlphaDecimal()];
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
			throw new \MWException( "Unable to find root post ID for post " . $postId->getAlphadecimal() );
		}
	}

	/**
	 * Gets a Workflow object given its ID
	 * @param  UUID   $workflowId The Workflow ID to retrieve.
	 * @return Workflow           The Workflow.
	 */
	protected function getWorkflowById( UUID $workflowId ) {
		$alpha = $workflowId->getAlphadecimal();
		if ( isset( $this->workflowCache[$alpha] ) ) {
			return $this->workflowCache[$alpha];
		} else {
			return $this->workflowCache[$alpha] = $this->storage->get( 'Workflow', $workflowId );
		}
	}
}

/**
 * Helper class represents a row of data from AbstractQuery
 */
class FormatterRow {
	/** @var AbstractRevision */
	public $revision;
	/** @var AbstractRevision|null */
	public $previousRevision;
	/** @var AbstractRevision */
	public $currentRevision;
	/** @var Workflow */
	public $workflow;
	/** @var string */
	public $indexFieldName;
	/** @var string */
	public $indexFieldValue;
	/** @var PostRevision|null */
	public $rootPost;

	// protect against typos
	public function __get( $attribute ) {
		throw new \MWException( "Accessing non-existent parameter: $attribute" );
	}

	// protect against typos
	public function __set( $attribute, $value ) {
		throw new \MWException( "Accessing non-existent parameter: $attribute" );
	}
}
