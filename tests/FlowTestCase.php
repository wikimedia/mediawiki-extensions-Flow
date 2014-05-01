<?php

namespace Flow\Tests;

use Flow\Model\UUID;
use \FlowInsertDefaultDefinitions;

class FlowTestCase extends \MediaWikiTestCase {

	protected function setUp() {
		parent::setUp();

		require_once( __DIR__ . '/../maintenance/FlowInsertDefaultDefinitions.php' );
		$maint = new FlowInsertDefaultDefinitions();

		$maint->loadParamsAndArgs( null, array( 'quiet' => true ) );

		$maint->execute();
	}

	/**
	 * @param mixed $data
	 * @return string
	 */
	protected function dataToString( $data ) {
		foreach ( $data as $key => $value ) {
			if ( $value instanceof UUID ) {
				$data[$key] = 'UUID: ' . $value->getAlphadecimal();
			}
		}

		return parent::dataToString( $data );
	}
}
