<?php

namespace Flow;

use Flow\Data\ObjectManager;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Title;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\FlowException;

class UrlGenerator {
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
	 * @todo We should probably have more descriptive names than
	 * generateUrl and buildUrl
	 * @param  Title $title Title of the Flow Board to link to.
	 * @param  string $action Action to execute
	 * @param  array $query Associative array of query parameters
	 * @return String URL
	 */
	public function buildUrl( $title, $action = 'view', array $query = array() ) {
		/** @var Title $linkTitle */
		/** @var string $query */
		list( $linkTitle, $query ) = $this->buildUrlData( $title, $action, $query );

		return $linkTitle->getFullUrl( $query );
	}

	/**
	 * Builds a URL for a given title and action, with a query string.
	 * @todo We should probably have more descriptive names than
	 * generateUrl and buildUrl
	 * @param  Title $title Title of the Flow Board to link to.
	 * @param  string $action Action to execute
	 * @param  array $query Associative array of query parameters
	 * @return array Two element array, first element is the title to link to
	 * and the second element is the query string to use.
	 */
	public function buildUrlData( $title, $action = 'view', array $query = array() ) {
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
		} elseif ( $revision instanceof PostSummary ) {
			if ( $specificRevision ) {
				$data['postsummary_revId'] = $revision->getRevisionId()->getAlphadecimal();
			}
			$action = 'topic-summary-view';
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
}
