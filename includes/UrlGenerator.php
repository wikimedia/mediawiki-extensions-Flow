<?php

namespace Flow;

use FlowHooks;
use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use Title;

class UrlGenerator {
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
		$query['action'] = $action;
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
	 * @return Array Two element array, first element is the title to link to,
	 * second element is the query string
	 */
	public function generateUrl( $workflow, $action = 'view', array $query = array(), $fragment = '' ) {
		list( $linkTitle, $query ) = $this->generateUrlData( $workflow, $action, $query );

		if ( $fragment ) {
			$linkTitle->setFragment( '#' . $fragment );
		}

		return $linkTitle->getFullUrl( $query );
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
	 * second element is the query string
	 */
	public function generateUrlData( $workflow, $action = 'view', array $query = array() ) {
		if ( ! $workflow instanceof Workflow ) {
			$workflowId = $workflow;
			// Only way to know what title the workflow points at
			$workflow = $this->storage->get( $workflowId );
			if ( !$workflow ) {
				throw \MWException( 'Invalid workflow: ' . $workflowId );
			}
		}

		if ( $workflow->isNew() ) {
			$query['definition'] = $workflow->getDefinitionId()->getHex();
		} else {
			// TODO: workflow parameter is only necessary if the definition is non-unique.  Likely need to pass
			// ManagerGroup into this class rather than the workflow ObjectManager so we can fetch definition as
			// needed.
			$query['workflow'] = $workflow->getId()->getHex();
		}

		return $this->buildUrlData( $workflow->getArticleTitle(), $action, $query );
	}
}
