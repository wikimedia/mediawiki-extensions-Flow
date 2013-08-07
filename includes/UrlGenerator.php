<?php

namespace Flow;

use Flow\Model\Workflow;
use Flow\Data\ObjectManager;
use Title;

class UrlGenerator {
	public function __construct( ObjectManager $workflowStorage ) {
		$this->storage = $workflowStorage;
	}

	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		if ( ! $workflow instanceof Workflow ) {
			$workflowId = $workflow;
			$workflow = $this->storage->get( $workflowId );
			if ( !$workflow ) {
				throw new \MWException( "Unknown flow object: $workflowId" );
			}
		}
		$query['action'] = $action;
		if ( $workflow->isNew() ) {
			$query['definition'] = $workflow->getDefinitionId()->getHex();
		} else {
			$query['workflow'] = $workflow->getId()->getHex();
		}

		return Title::newFromText( 'Flow/' . $workflow->getTitleFullText(), NS_SPECIAL )
			->getFullURL( http_build_query( $query ) );
	}
}
