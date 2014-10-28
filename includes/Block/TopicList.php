<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\Pager\Pager;
use Flow\Data\Pager\PagerPage;
use Flow\Formatter\TopicListFormatter;
use Flow\Formatter\TopicListQuery;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Templating;
use Flow\Exception\FailCommitException;

class TopicListBlock extends AbstractBlock {

	/**
	 * @var array
	 */
	protected $supportedPostActions = array( 'new-topic' );

	/**
	 * @var array
	 */
	protected $supportedGetActions = array( 'view', 'view-topiclist' );

	// @Todo - fill in the template names
	protected $templates = array(
		'view' => '',
		'new-topic' => 'newtopic',
	);

	/**
	 * @var Workflow|null
	 */
	protected $topicWorkflow;

	/**
	 * @var TopicListEntry|null
	 */
	protected $topicListEntry;

	/**
	 * @var PostRevision|null
	 */
	protected $topicTitle;

	/**
	 * @var PostRevision|null
	 */
	protected $firstPost;

	/**
	 * @var array
	 *
	 * Associative array mapping topic ID (in alphadecimal form) to PostRevision for the topic root.
	 */
	protected $topicRootRevisionCache = array();

	protected function validate() {
		// for now, new topic is considered a new post; perhaps some day topic creation should get it's own permissions?
		if ( !$this->permissions->isAllowed( null, 'new-post' ) ) {
			$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
			return;
		}
		if ( !isset( $this->submitted['topic'] ) || !is_string( $this->submitted['topic'] ) ) {
			$this->addError( 'topic', $this->context->msg( 'flow-error-missing-title' ) );
			return;
		}
		$this->submitted['topic'] = trim( $this->submitted['topic'] );
		if ( strlen( $this->submitted['topic'] ) === 0 ) {
			$this->addError( 'topic', $this->context->msg( 'flow-error-missing-title' ) );
			return;
		}
		if ( mb_strlen( $this->submitted['topic'] ) > PostRevision::MAX_TOPIC_LENGTH ) {
			$this->addError( 'topic', $this->context->msg( 'flow-error-title-too-long', PostRevision::MAX_TOPIC_LENGTH ) );
			return;
		}

		if ( trim( $this->submitted['content'] ) === '' ) {
			$this->addError( 'content', $this->context->msg( 'flow-error-missing-content' ) );
			return;
		}

		// creates Workflow, Revision & TopicListEntry objects to be inserted into storage
		list( $this->topicWorkflow, $this->topicListEntry, $this->topicTitle, $this->firstPost ) = $this->create();

		if ( !$this->checkSpamFilters( null, $this->topicTitle ) ) {
			return;
		}
		if ( $this->firstPost && !$this->checkSpamFilters( null, $this->firstPost ) ) {
			return;
		}
	}

	/**
	 * Creates the objects about to be inserted into storage:
	 * * $this->topicWorkflow
	 * * $this->topicListEntry
	 * * $this->topicTitle
	 * * $this->firstPost
	 *
	 * @throws \MWException
	 * @throws \Flow\Exception\FailCommitException
	 * @return array Array of [$topicWorkflow, $topicListEntry, $topicTitle, $firstPost]
	 */
	protected function create() {
		$title = $this->workflow->getArticleTitle();
		$user = $this->context->getUser();
		$topicWorkflow = Workflow::create( 'topic', $user, $title );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );
		$topicTitle = PostRevision::create( $topicWorkflow, $this->submitted['topic'] );

		$firstPost = null;
		if ( !empty( $this->submitted['content'] ) ) {
			$firstPost = $topicTitle->reply( $topicWorkflow, $user, $this->submitted['content'] );
			$topicTitle->setChildren( array( $firstPost ) );
		}


		return array( $topicWorkflow, $topicListEntry, $topicTitle, $firstPost );
	}

	/**
	 * Create a new topic attached to the current topic list and write it
	 * out to storage.
	 */
	public function commit() {
		if ( $this->action !== 'new-topic' ) {
			throw new FailCommitException( 'Unknown commit action', 'fail-commit' );
		}

		$storage = $this->storage;
		$metadata = array(
			'workflow' => $this->topicWorkflow,
			'board-workflow' => $this->workflow,
			'topic-title' => $this->topicTitle,
			'first-post' => $this->firstPost,
		);

		$storage->put( $this->topicListEntry, $metadata );
		$storage->put( $this->topicTitle, $metadata );
		if ( $this->firstPost !== null ) {
			$storage->put( $this->firstPost, $metadata + array(
				'reply-to' => $this->topicTitle
			) );
		}
		// must be last because this will trigger OccupationController::ensureFlowRevision
		// to create the page within topic namespace, that will try and render, so the above
		// stuff needs to be in cache at least.
		$storage->put( $this->topicWorkflow, $metadata );

		$output = array(
			'created-topic-id' => $this->topicWorkflow->getId(),
			'created-post-id' => $this->firstPost ? $this->firstPost->getRevisionId() : null,
		);

		return $output;
	}

	public function renderApi( array $options ) {
		$response = array(
			'submitted' => $this->wasSubmitted() ? $this->submitted : $options,
			'errors' => $this->errors,
		);

		// Repeating the default until we use the API for everything (bug 72659)
		$isTocOnly = isset( $options['toconly'] ) ? $options['toconly'] : false;

		if ( $isTocOnly ) {
			/** @var TocTopicListFormatter $serializer */
			$serializer = Container::get( 'formatter.topiclist.toc' );
		} else {
			/** @var TopicListFormatter $serializer */
			$serializer = Container::get( 'formatter.topiclist' );
		}

		if ( $this->workflow->isNew() ) {
			return $response + $serializer->buildEmptyResult( $this->workflow );
		}

		// @todo remove the 'api' => true, its always api
		$findOptions = $this->getFindOptions( $options + array( 'api' => true ) );
		$page = $this->getPage( $findOptions );

		// sortby option
		if ( isset( $findOptions['sortby'] ) ) {
			$response['sortby'] = $findOptions['sortby'];
		// default is newest
		} else {
			$response['sortby'] = '';
		}

		$workflowIds = array();
		/** @var TopicListEntry $topicListEntry */
		foreach ( $page->getResults() as $topicListEntry ) {
			$workflowIds[] = $topicListEntry->getId();
		}

		if ( $isTocOnly ) {
			// We don't need any further data, so we skip the TopicListQuery.

			$mapping = array();
			foreach ( $workflowIds as $workflowId ) {
				$alphaWorkflowId = $workflowId->getAlphadecimal();
				$mapping[$alphaWorkflowId] = $this->topicRootRevisionCache[$alphaWorkflowId];
			}

			return $response + $serializer->formatApi( $this->workflow, $mapping, $page );
		}

		$workflows = $this->storage->getMulti( 'Workflow', $workflowIds );

		/** @var TopicListQuery $query */
		$query = Container::get( 'query.topiclist' );
		$found = $query->getResults( $page->getResults() );
		wfDebugLog( 'FlowDebug', 'Rendering topiclist for ids: ' . implode( ', ', array_map( function( UUID $id ) {
			return $id->getAlphadecimal();
		}, $workflowIds ) ) );

		return $response + $serializer->formatApi( $this->workflow, $workflows, $found, $page, $this->context );
	}

	public function getName() {
		return 'topiclist';
	}

	protected function getLimit( array $options ) {
		global $wgFlowDefaultLimit, $wgFlowMaxLimit;
		$limit = $wgFlowDefaultLimit;
		if ( isset( $options['limit'] ) ) {
			$requestedLimit = intval( $options['limit'] );
			$limit = min( $requestedLimit, $wgFlowMaxLimit );
			$limit = max( 0, $limit );
		}

		return $limit;
	}

	protected function getFindOptions( array $requestOptions ) {
		$findOptions = array();

		// Compute offset/limit
		$limit = $this->getLimit( $requestOptions );

		if ( isset( $requestOptions['offset-id'] ) && $requestOptions['offset-id'] ) {
			$findOptions['pager-offset'] = UUID::create( $requestOptions['offset-id'] );
		} elseif ( isset( $requestOptions['offset'] ) && $requestOptions['offset'] ) {
			$findOptions['pager-offset'] = intval( $requestOptions['offset'] );
		}

		if ( isset( $requestOptions['offset-dir'] ) && $requestOptions['offset-dir'] ) {
			$findOptions['pager-dir'] = $requestOptions['offset-dir'];
		}

		if ( isset( $requestOptions['api'] ) && $requestOptions['api'] ) {
			$findOptions['offset-elastic'] = false;
		}

		$findOptions['pager-limit'] = $limit;

		// Only support sortby = updated now, fall back to creation time by default otherwise.
		// To clear the sortby user preference, pass sortby with an empty value
		$sortByOption = '';
		$user = $this->context->getUser();
		if ( isset( $requestOptions['sortby'] ) ) {
			if ( $requestOptions['sortby'] === 'updated' ) {
				$sortByOption = 'updated';
			}
			if (
				isset( $requestOptions['savesortby'] )
				&& !$user->isAnon()
				&& $user->getOption( 'flow-topiclist-sortby' ) != $sortByOption
			) {
				$user->setOption( 'flow-topiclist-sortby', $sortByOption );
				$user->saveSettings();
			}
		} else {
			if ( !$user->isAnon() && $user->getOption( 'flow-topiclist-sortby' ) === 'updated' ) {
				 $sortByOption = 'updated';
			}
		}

		if ( $sortByOption === 'updated' ) {
			$findOptions = array(
				'sort' => 'workflow_last_update_timestamp',
				'order' => 'desc',
				// keep sortby so it can be used later for building links
				'sortby' => 'updated',
			) + $findOptions;
		}

		return $findOptions;
	}

	/**
	 * Gets a set of workflow IDs
	 * This filters result to only include unmoderated and locked topics.
	 *
	 * Also populates topicRootRevisionCache with a mapping from topic ID to the
	 * PostRevision for the topic root.
	 *
	 * @param array $findOptions
	 * @return PagerPage
	 */
	protected function getPage( array $findOptions ) {
		$pager = new Pager(
			$this->storage->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $this->workflow->getId() ),
			$findOptions
		);

		$postStorage = $this->storage->getStorage( 'PostRevision' );
		return $pager->getPage( function( array $found ) use ( $postStorage ) {
			$queries = array();
			/** @var TopicListEntry[] $found */
			foreach ( $found as $entry ) {
				$queries[] = array( 'rev_type_id' => $entry->getId() );
			}
			$posts = $postStorage->findMulti( $queries, array(
				'sort' => 'rev_id',
				'order' => 'DESC',
				'limit' => 1,
			) );
			$allowed = array();
			foreach ( $posts as $queryResult ) {
				$post = reset( $queryResult );
				if ( !$post->isModerated() || $post->isLocked() ) {
					$allowed[$post->getPostId()->getAlphadecimal()] = $post;
				}
			}
			foreach ( $found as $idx => $entry ) {
				if ( isset( $allowed[$entry->getId()->getAlphadecimal()] ) ) {
					$this->topicRootRevisionCache[$entry->getId()->getAlphadecimal()] = $allowed[$entry->getId()->getAlphadecimal()];
				} else {
					unset( $found[$idx] );
				}
			}

			return $found;
		} );
	}

	/**
	 * @param Templating $templating
	 * @param \OutputPage $out
	 */
	public function setPageTitle( Templating $templating, \OutputPage $out ) {
		if ( $this->action !== 'new-topic' ) {
			// Only new-topic should override page title, rest should default
			parent::setPageTitle( $templating, $out );
			return;
		}

		$title = $this->workflow->getOwnerTitle();
		$message = $out->msg( 'flow-newtopic-first-heading', $title->getPrefixedText() );
		$out->setPageTitle( $message );
		$out->setHtmlTitle( $message );
		$out->setSubtitle( '&lt; ' . \Linker::link( $title ) );
	}
}
