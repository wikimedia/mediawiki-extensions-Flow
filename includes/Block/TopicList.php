<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\Pager\Pager;
use Flow\Data\Pager\PagerPage;
use Flow\Exception\FlowException;
use Flow\Formatter\TocTopicListFormatter;
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
		$topicWorkflow = Workflow::create( 'topic', $title );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );
		$topicTitle = PostRevision::create( $topicWorkflow, $user, $this->submitted['topic'] );

		$firstPost = null;
		if ( !empty( $this->submitted['content'] ) ) {
			$firstPost = $topicTitle->reply( $topicWorkflow, $user, $this->submitted['content'] );
			$topicTitle->setChildren( array( $firstPost ) );
		}


		return array( $topicWorkflow, $topicListEntry, $topicTitle, $firstPost );
	}

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
			'topic-page' => $this->topicWorkflow->getArticleTitle()->getPrefixedText(),
			'topic-id' => $this->topicTitle->getPostId(),
			'topic-revision-id' => $this->topicTitle->getRevisionId(),
			'post-id' => $this->firstPost ? $this->firstPost->getPostId() : null,
			'post-revision-id' => $this->firstPost ? $this->firstPost->getRevisionId() : null,
		);

		return $output;
	}

	public function renderApi( array $options ) {
		$response = array(
			'submitted' => $this->wasSubmitted() ? $this->submitted : $options,
			'errors' => $this->errors,
		);

		// Repeating the default until we use the API for everything (bug 72659)
		// Also, if this is removed other APIs (i.e. ApiFlowNewTopic) may need
		// to be adjusted if they trigger a rendering of this block.
		$isTocOnly = isset( $options['toconly'] ) ? $options['toconly'] : false;

		if ( $isTocOnly ) {
			/** @var TocTopicListFormatter $serializer */
			$serializer = Container::get( 'formatter.topiclist.toc' );
		} else {
			/** @var TopicListFormatter $serializer */
			$serializer = Container::get( 'formatter.topiclist' );
		}

		// @todo remove the 'api' => true, its always api
		$findOptions = $this->getFindOptions( $options + array( 'api' => true ) );

		// include the current sortby option.  Note that when 'user' is either
		// submitted or defaulted to this is the resulting sort. ex: newest
		$response['sortby'] = $findOptions['sortby'];

		if ( $this->workflow->isNew() ) {
			return $response + $serializer->buildEmptyResult( $this->workflow );
		}

		$page = $this->getPage( $findOptions );
		$workflowIds = array();
		/** @var TopicListEntry $topicListEntry */
		foreach ( $page->getResults() as $topicListEntry ) {
			$workflowIds[] = $topicListEntry->getId();
		}

		// TODO: We rely on $workflows being in the same order as $workflowIds, AFAICT (mainly for 'roots').
		// But is this actually guaranteed to be the case with the getMulti call?
		$workflows = $this->storage->getMulti( 'Workflow', $workflowIds );

		if ( $isTocOnly ) {
			// We don't need any further data, so we skip the TopicListQuery.

			$topicRootRevisionsByWorkflowId = array();
			$workflowsByWorkflowId = array();

			foreach ( $workflows as $workflow ) {
				$alphaWorkflowId = $workflow->getId()->getAlphadecimal();
				$topicRootRevisionsByWorkflowId[$alphaWorkflowId] = $this->topicRootRevisionCache[$alphaWorkflowId];
				$workflowsByWorkflowId[$alphaWorkflowId] = $workflow;
			}

			return $response + $serializer->formatApi( $this->workflow, $topicRootRevisionsByWorkflowId, $workflowsByWorkflowId, $page );
		}

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

		// @todo Once we migrate View.php to use the API directly
		// all defaults will be handled by API and not here.
		$requestOptions += array(
			'include-offset' => false,
			'offset-id' => false,
			'offset-dir' => 'fwd',
			'offset' => false,
			'api' => true,
			'sortby' => 'user',
		);

		$sortByOption = '';
		$user = $this->context->getUser();
		if ( strlen( $requestOptions['sortby'] ) === 0 ) {
			$requestOptions['sortby'] = 'user';
		}
		// the sortby option in $findOptions is not directly used for querying,
		// but is needed by the pager to generate appropriate pagination links.
		if ( $requestOptions['sortby'] === 'user' && !$user->isAnon() ) {
			$requestOptions['sortby'] = $user->getOption( 'flow-topiclist-sortby' );
		}
		switch( $requestOptions['sortby'] ) {
		case 'updated':
			$findOptions = array(
				'sortby' => 'updated',
				'sort' => 'workflow_last_update_timestamp',
				'order' => 'desc',
			) + $findOptions;

			if ( $requestOptions['offset'] ) {
				throw new FlowException( 'The `updated` sort order does not allow the `offset` parameter.  Please use `offset-id`.' );
			}
			break;

		case 'newest':
		default:
			$findOptions = array(
				'sortby' => 'newest',
				'sort' => 'topic_id',
				'order' => 'desc',
			) + $findOptions;

			if ( $requestOptions['offset-id'] ) {
				throw new FlowException( 'The `newest` sort order does not allow the `offset-id` parameter. Please use `offset`.' );
			}
		}

		if ( $requestOptions['offset-id'] ) {
			$findOptions['pager-offset'] = UUID::create( $requestOptions['offset-id'] );
		} elseif ( $requestOptions['offset'] ) {
			$findOptions['pager-offset'] = intval( $requestOptions['offset'] );
		}

		if ( $requestOptions['offset-dir'] ) {
			$findOptions['pager-dir'] = $requestOptions['offset-dir'];
		}

		if ( $requestOptions['include-offset'] ) {
			$findOptions['pager-include-offset'] = $requestOptions['include-offset'];
		}

		if ( $requestOptions['api'] ) {
			$findOptions['offset-elastic'] = false;
		}

		$findOptions['pager-limit'] = $limit;

		if (
			isset( $requestOptions['savesortby'] )
			&& !$user->isAnon()
			&& $user->getOption( 'flow-topiclist-sortby' ) != $findOptions['sortby']
		) {
			$user->setOption( 'flow-topiclist-sortby', $findOptions['sortby'] );
			$user->saveSettings();
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

		// Work around lack of $this in closures until we can use PHP 5.4+ features.
		$topicRootRevisionCache =& $this->topicRootRevisionCache;

		return $pager->getPage( function( array $found ) use ( $postStorage, &$topicRootRevisionCache ) {
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
					$topicRootRevisionCache[$entry->getId()->getAlphadecimal()] = $allowed[$entry->getId()->getAlphadecimal()];
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
