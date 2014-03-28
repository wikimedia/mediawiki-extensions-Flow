<?php

namespace Flow;

use Flow\Data\ObjectManager;
use Flow\Data\PagerPage;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\FlowException;
use Message;
use RawMessage;
use Title;

/**
 * BaseUrlGenerator contains the logic for putting together the bits of url
 * data, without generating any specific urls.
 */
abstract class BaseUrlGenerator {
	/**
	 * @var OccupationController
	 */
	protected $occupationController;

	/**
	 * @var ObjectManager Workflow storage
	 */
	protected $storage;

	/**
	 * @var Workflow[] Cached array of already loaded workflows
	 */
	protected $workflows = array();

	public function __construct( ObjectManager $workflowStorage, OccupationController $occupationController ) {
		$this->occupationController = $occupationController;
		$this->storage = $workflowStorage;
	}

	/**
	 * Builds a URL for a given title and action, with a query string.
	 *
	 * @todo We should probably have more descriptive names than
	 * generateUrl and buildUrl
	 * @param  Title $title Title of the Flow Board to link to.
	 * @param  string $action Action to execute
	 * @param  array $query Associative array of query parameters
	 * @return String URL
	 */
	protected function buildUrl( $title, $action = 'view', array $query = array() ) {
		/** @var Title $linkTitle */
		/** @var string $query */
		list( $linkTitle, $query ) = $this->buildUrlData( $title, $action, $query );

		return $linkTitle->getFullUrl( $query );
	}

	/**
	 * Builds a URL for a given title and action, with a query string.
	 *
	 * @todo We should probably have more descriptive names than
	 * generateUrl and buildUrl
	 * @param  Title $title Title of the Flow Board to link to.
	 * @param  string $action Action to execute
	 * @param  array $query Associative array of query parameters
	 * @return array Two element array, first element is the title to link to
	 * and the second element is the query string to use.
	 */
	protected function buildUrlData( $title, $action = 'view', array $query = array() ) {
		if ( $action === 'view' ) {
			// view is implicit
			unset( $query['action'] );
		} else {
			$query['action'] = $action;
		}
		return array( $title, $query );
	}

	/**
	 * Builds a URL to link to a given Workflow
	 *
	 * @todo We should probably have more descriptive names than
	 * generateUrl and buildUrl
	 * @param  Workflow|UUID $workflow The Workflow to link to
	 * @param  string $action The action to execute
	 * @param  array  $query Associative array of query parameters
	 * @param  string $fragment URL hash fragment
	 * @return string URL
	 * @todo Make this protected
	 */
	public function generateUrl( $workflow, $action = 'view', array $query = array(), $fragment = '' ) {
		/** @var Title $linkTitle */
		/** @var string $query */
		list( $linkTitle, $query ) = $this->generateUrlData( $workflow, $action, $query );

		$title = clone $linkTitle;

		if ( $fragment ) {
			$title->setFragment( '#' . $fragment );
		}

		return $title->getFullUrl( $query );
	}

	/**
	 * Generate a block viewing url based on a revision
	 *
	 * @param Workflow|UUID $workflow The Workflow to link to
	 * @param AbstractRevision $revision The revision to build the block
	 * @param boolean $specificRevision whether to show specific revision
	 * @return string URL
	 * @throws FlowException When the type of $revision is unrecognized
	 */
	public function generateBlockUrl( $workflow, AbstractRevision $revision, $specificRevision = false ) {
		$data = array();
		$action = 'view';
		if ( $revision instanceof PostRevision ) {
			if ( !$revision->isTopicTitle() ) {
				$data['topic_postId'] = $revision->getPostId()->getAlphadecimal();
			}

			if ( $specificRevision ) {
				$data['topic_revId'] = $revision->getRevisionId()->getAlphadecimal();
			}
		} elseif ( $revision instanceof Header ) {
			if ( $specificRevision ) {
				$data['header_revId'] = $revision->getRevisionId()->getAlphadecimal();
			}
			$action = 'header-view';
		} else {
			throw new FlowException( 'Unknown revision type:' . get_class( $revision ) );
		}

		return $this->generateUrl( $workflow, $action, $data );
	}

	/**
	 * Returns the title/query string to link to a given Workflow
	 *
	 * @todo We should probably have more descriptive names than
	 * generateUrl and buildUrl
	 * @param  Workflow|UUID $workflow The Workflow to link to
	 * @param  string $action The action to execute
	 * @param  array  $query Associative array of query parameters
	 * @return Array Two element array, first element is the title to link to,
	 * @throws InvalidDataException
	 * @throws InvalidInputException
	 * second element is the query string
	 */
	public function generateUrlData( $workflow, $action = 'view', array $query = array() ) {
		$workflow = $this->resolveWorkflow( $workflow );
		if ( $workflow->isNew() ) {
			$query['definition'] = $workflow->getDefinitionId()->getAlphadecimal();
		} else {
			// TODO: workflow parameter is only necessary if the definition is non-unique.  Likely need to pass
			// ManagerGroup into this class rather than the workflow ObjectManager so we can fetch definition as
			// needed.
			$query['workflow'] = $workflow->getId()->getAlphadecimal();
		}

		return $this->buildUrlData( $workflow->getArticleTitle(), $action, $query );
	}

	public function withWorkflow( Workflow $workflow ) {
		$this->workflows[$workflow->getId()->getAlphadecimal()] = $workflow;
	}

	/**
	 * @param Workflow|UUID $workflow
	 */
	protected function resolveWorkflow( $workflow ) {
		if ( $workflow instanceof UUID ) {
			// Only way to know what title the workflow points at
			$workflowId = $workflow;
			if ( isset( $this->workflows[$workflowId->getAlphadecimal()] ) ) {
				$workflow = $this->workflows[$workflowId->getAlphadecimal()];
			} else {
				$workflow = $this->storage->get( $workflowId );
				if ( !$workflow ) {
					throw new InvalidInputException( 'Invalid workflow: ' . $workflowId, 'invalid-workflow' );
				}
				$this->workflows[$workflowId->getAlphadecimal()] = $workflow;
			}
		} elseif ( !$workflow instanceof Workflow ) {
			// otherwise calling a method on $workflow will fatal error
			throw new InvalidDataException( '$workflow is not UUID or Workflow instance' );
		}
		return $workflow;
	}

	protected function resolveTitle( Title $title = null, UUID $workflowId ) {
		if ( $title ) {
			return $title;
		}
		$alpha = $workflowId->getAlphadecimal();
		if ( isset( $this->workflows[$alpha] ) ) {
			return $this->workflows[$alpha]->getArticleTitle();
		}
		$workflow = $this->storage->get( $workflowId );
		if ( !$workflow ) {
			throw new FlowException( 'Could not locate workflow ' . $alpha );
		}
		$this->workflows[$alpha] = $workflow;
		return $workflow->getArticleTitle();
	}
}

/**
 * UrlGenerator extends the BaseUrlGenerator by adding specific link
 * types.  All methods return Anchor instances.
 */
class UrlGenerator extends BaseUrlGenerator {

	/**
	 * Reverse pagination on a topic list.
	 *
	 * @var Title|null $title Page the workflow is on, resolved from workflowId when null
	 * @var UUID $workflowId The topic list being paginated
	 * @var UUID $topicId The last topic seen
	 * @return Anchor
	 */
	public function topicsBeforeLink( Title $title = null, UUID $workflowId, PagerPage $page ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'view',
			array(
				'topiclist_offset-id' => $offset,
				'topiclist_offset-dir' => $direction,
				'topiclist_limit' => $limit,
			)
		);

		return new Anchor(
			wfMessage( "flow-paging-$direction" ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Link to create new topic on a topiclist.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function newTopicLink( Title $title = null, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'new-topic',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'flow-topic-action-new' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Reply to an individual post in a topic workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function replyPostLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'reply',
			array( 'topic_replyTo' => $postId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'flow-post-action-reply' ),
			$linkTitle,
			$query
		);
	}

	/**.
	 * Edit the header at the specified workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 */
	public function editHeaderLink( Title $title = null, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'edit-header',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'flow-edit-header' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Edit the title of a topic workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 */
	public function editTitleLink( Title $title = null, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'edit-title',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'flow-edit-title' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * View the topic at the specified workflow.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function topicLink( Title $title = null, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'view',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'flow-link-topic' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * View a topic scrolled down to the provided post at the
	 * specified workflow.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function postLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'view',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'flow-link-post' ),
			$linkTitle,
			$query,
			'#flow-post-' . $postId->getAlphadecimal()
		);
	}

	/**
	 * Show the history of a specific post within a topic workflow
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function postHistoryLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'history',
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
			)
		);

		return new Anchor(
			wfMessage( 'hist' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Show the history of a workflow.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function workflowHistoryLink( Title $title = null, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'history',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			wfMessage( 'hist' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Show the history of a flow board.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function boardHistoryLink( Title $title ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$title,
			'history'
		);

		return new Anchor(
			wfMessage( 'hist' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Show the differences between two revisions of something.
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediatly prior.  If $oldRevId is provided shows the differences between
	 * $oldRevId and $revId.
	 *
	 * @param string $type
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @parma UUID|null $oldRevId
	 * @return Anchor
	 */
	protected function diffLink( $type, Title $title = null, UUID $workflowId, UUID $revId, UUID $oldRevId = null ) {
		switch( $type ) {
		case 'post':
			$prefix = 'topic_';
			break;
		case 'header':
			$prefix = 'header_';
			break;
		default:
			throw new FlowException( "Unknown diff link type: $type" );
		}

		$params = array(
			'workflow' => $workflowId->getAlphadecimal(),
			"{$prefix}newRevision" => $revId->getAlphadecimal(),
		);
		if ( $oldRevId !== null ) {
			$params["{$prefix}oldRevision"] = $oldRevId->getAlphadecimal();
		}

		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			"compare-$type-revisions",
			$params
		);

		return new Anchor(
			wfMessage( 'diff' ),
			$linkTitle,
			$query
		);
	}

	/**
	 * Show the differences between two revisions of a header
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediatly prior.  If $oldRevId is provided shows the differences between
	 * $oldRevId and $revId.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @param UUID|null $oldRevId
	 * @return Anchor
	 */
	public function diffHeaderLink( Title $title = null, UUID $workflowId, UUID $revId, UUID $oldRevId = null ) {
		return $this->diffLink( 'header', $title, $workflowId, $revId, $oldRevId );
	}

	/**
	 * Show the differences between two revisions of a post.
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediatly prior.  If $oldRevId is provided shows the differences between
	 * $oldRevId and $revId.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @return Anchor
	 */
	public function diffPostLink( Title $title = null, UUID $workflowId, UUID $revId, UUID $oldRevId = null ) {
		return $this->diffLink( 'post', $title, $workflowId, $revId, $oldRevId );
	}

	/**
	 * View the specified workflow.
	 *
	 * @param Title $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function workflowLink( Title $title = null, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->buildUrlData(
			$this->resolveTitle( $title, $workflowId ),
			'view',
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);

		return new Anchor(
			$linkTitle->getPrefixedText(),
			$linkTitle,
			$query
		);
	}

	/**
	 * View the flow board at the specified title
	 *
	 * Makes the assumption the title is flow-enabled.
	 *
	 * @param Title $title
	 * @return Anchor
	 */
	public function boardLink( Title $title ) {
		return new Anchor(
			$title->getPrefixedText(),
			$title
		);
	}
}

/**
 * Represents a mutable anchor as a Message instance along with
 * a title, query parameters, and a fragment.
 */
class Anchor {
	/**
	 * @var Message
	 */
	public $message;

	/**
	 * @var Title
	 */
	public $title;

	/**
	 * @var array
	 */
	public $query = array();

	/**
	 * @var string
	 */
	public $fragment;

	/**
	 * @param Message|string $message
	 * @param Title $title
	 * @param array $query
	 * @param string|null $fragment
	 */
	public function __construct( $message, Title $title, array $query = array(), $fragment = null ) {
		$this->title = $title;
		$this->query = $query;
		$this->fragment = $fragment;
		if ( $message instanceof Message ) {
			$this->message = $message;
		} else {
			// wrap non-messages into a message class
			$this->message = new RawMessage( '$1', array( $message ) );
		}
	}

	/**
	 * @return string
	 */
	public function getFullURL() {
		$title = $this->title;
		if ( $this->fragment ) {
			$title = clone $title;
			$title->setFragment( $this->fragment );
		}

		return $title->getFullUrl( $this->query );
	}
}
