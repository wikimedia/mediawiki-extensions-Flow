<?php

namespace Flow;

use Flow\Data\ObjectManager;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\FlowException;
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

	/**
	 * @param OccupationController $occupationController
	 */
	public function __construct( OccupationController $occupationController ) {
		$this->occupationController = $occupationController;
	}

	/**
	 * @param Workflow $workflow
	 */
	public function withWorkflow( Workflow $workflow ) {
		$this->workflows[$workflow->getId()->getAlphadecimal()] = $workflow;
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
		if ( $action !== 'view' ) {
			$query['action'] = $action;
		}
		return $title->getFullUrl( $query );
	}

	/**
	 * Builds a URL to link to a given Workflow.  This method is in the
	 * process of being removed.  It exist only for the benefit of
	 * PostActionMenu which is generic to the point that it requires this
	 * method.  The PostActionMenu is probably going away when we move to
	 * the lightncandy/handlebars frontend.
	 *
	 * @deprecated 1.23
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
		if ( !$workflow->isNew() ) {
			// TODO: workflow parameter is only necessary if the definition is non-unique.  Likely need to pass
			// ManagerGroup into this class rather than the workflow ObjectManager so we can fetch definition as
			// needed.
			$query['workflow'] = $workflow->getId()->getAlphadecimal();
		}

		if ( $action !== 'view' ) {
			$query['action'] = $action;
		}

		return array( $workflow->getArticleTitle(), $query );
	}

	/**
	 * @param Workflow|UUID $workflow
	 * @return Workflow
	 * @throws InvalidDataException
	 * @throws InvalidInputException
	 */
	protected function resolveWorkflow( $workflow ) {
		if ( $workflow instanceof UUID ) {
			$alpha = $workflow->getAlphadecimal();
			if ( !isset( $this->workflows[$alpha] ) ) {
				throw new InvalidInputException( 'Unloaded workflow: ' . $alpha , 'invalid-workflow' );
			}
			$workflow = $this->workflows[$alpha];
		} elseif ( !$workflow instanceof Workflow ) {
			// otherwise calling a method on $workflow will fatal error
			throw new InvalidDataException( '$workflow is not UUID or Workflow instance' );
		}
		return $workflow;
	}

	/**
	 * @param Title|null $title
	 * @param UUID|null $workflowId
	 * @return Title
	 * @throws FlowException
	 */
	protected function resolveTitle( Title $title = null, UUID $workflowId = null ) {
		if ( $title !== null ) {
			return $title;
		}
		if ( $workflowId === null ) {
			throw new FlowException( 'No title or workflow given' );
		}

		$alpha = $workflowId->getAlphadecimal();
		if ( !isset( $this->workflows[$alpha] ) ) {
			throw new InvalidInputException( 'Unloaded workflow:' . $alpha, 'invalid-workflow' );
		}

		return $this->workflows[$alpha]->getArticleTitle();
	}
}
