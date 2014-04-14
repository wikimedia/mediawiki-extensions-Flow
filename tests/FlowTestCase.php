<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\RecentChanges as RecentChangesHandler;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Model\UUID;
use User;

/**
 * @group Flow
 * @group Database
 */
class FlowTestCase extends FlowTestCase {
	/**
	 * @param mixed $data
	 * @return string
	 */
	protected function dataToString( $data ) {
		foreach ( $data as $key => $value ) {
			if ( $value instanceof UUID ) {
				$value = 'UUID: ' . $value->getAlphadecimal();
			}
		}

		return parent::dataToString( $data );
	}
}
