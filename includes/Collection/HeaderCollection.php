<?php

namespace Flow\Collection;

use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Exception\FlowException;

class HeaderCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\Header';
	}

	public function getIdColumn() {
		return 'header_workflow_id';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		if ( $revision instanceof Header ) {
			return $revision->getWorkflowId();
		} else {
			throw new FlowException( 'Expected Header but received ' . get_class( $revision ) );
		}
	}
}
