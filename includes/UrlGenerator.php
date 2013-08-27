<?php

namespace Flow;

use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use SpecialPage;
use Title;

class UrlGenerator {
	public function __construct( ObjectManager $workflowStorage ) {
		$this->storage = $workflowStorage;
	}

	public static function buildUrl( $title, $action, $query ) {
		$query['action'] = $action;
		return SpecialPage::getTitleFor( 'Flow', $title->getPrefixedText() )
			->getFullUrl( $query );
	}

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

		return self::buildUrl( $workflow->getArticleTitle(), $action, $query );
	}
}
