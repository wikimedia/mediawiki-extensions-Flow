<?php

namespace Flow;

use FlowHooks;
use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use SpecialPage;
use Title;

class UrlGenerator {
	public function __construct( ObjectManager $workflowStorage, OccupationController $occupationController ) {
		$this->occupationController = $occupationController;
		$this->storage = $workflowStorage;
	}

	/**
	 * Builds a URL for a given title and action, with a query string.
	 * @param  Title $title Title of the Flow Board to link to.
	 * @param  string $action Action to execute
	 * @param  array $query Associative array of query parameters
	 * @return String URL
	 */
	public function buildUrl( $title, $action, $query ) {
		$query['action'] = $action;

		$linkTitle = $this->occupationController->isTalkpageOccupied( $title )
			? $title
			: SpecialPage::getTitleFor( 'Flow', $title->getPrefixedText() );

		return $linkTitle->getFullUrl( $query );
	}

	/**
	 * Builds a URL to link to a given Workflow
	 * @param  Workflow|UUID $workflow The Workflow to link to
	 * @param  string $action The action to execute
	 * @param  array  $query Associative array of query parameters
	 * @return URL
	 */
	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		if ( ! $workflow instanceof Workflow ) {
			$workflowId = $workflow;
			// Only way to know what title the workflow points at
			$workflow = $this->storage->get( $workflowId );
			if ( !$workflow ) {
				throw new \MWException( "Unknown flow object: $workflowId" );
			}
		}
		
		if ( $workflow->isNew() ) {
			$query['definition'] = $workflow->getDefinitionId()->getHex();
		} else {
			$query['workflow'] = $workflow->getId()->getHex();
		}

		return $this->buildUrl( $workflow->getArticleTitle(), $action, $query );
	}
}
