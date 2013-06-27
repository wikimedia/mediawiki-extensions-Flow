<?php

namespace Flow;

use Flow\Model\Workflow;
use Flow\Data\ObjectManager;
use Title;

class UrlGenerator {
	public function __construct( ObjectManager $workflowStorage ) {
		$this->storage = $workflowStorage;
	}

	//
	public function generateUrl( Workflow $workflow, $action = 'view', $flow = null ) {
		$query = array( 'action' => $action, 'workflowId' => $workflow->getId() );

		return Title::newFromText( 'Flow/' . $workflow->getTitleFullText(), NS_SPECIAL )
			->getFullURL( http_build_query( $query ) );
	}

	// Doesn't have a saved object id yet
	public function generateUrlForCreate( Workflow $workflow,  $action = 'create' ) {
		$query = array( 'action' => $action, 'flowId' => $workflow->getDefinitionId() );

		return Title::newFromText( 'Flow/' . $workflow->getTitleFullText(), NS_SPECIAL )
			->getFullURL( http_build_query( $query ) );
	}

	public function generateUrlForId( $workflowId, $action = 'view' ) {
		$workflow = $this->storage->get( $workflowId ); // need title full text
		if ( !$workflow ) {
			throw new \MWException( "Unknown flow object: $workflowId" );
		}
		$query = array(
			'action' => $action,
			'workflowId' => $workflowId,
		);
		return Title::newFromText( 'Flow/' . $workflow->getTitleFullText(), NS_SPECIAL )
			->getFullURL( http_build_query( $query ) );
	}
}
