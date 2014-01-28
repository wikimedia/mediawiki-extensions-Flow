<?php

namespace Flow\Tests;
use FlowInsertDefaultDefinitions;

use MediaWikiTestCase;

class TestCase extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		$maint = new FlowInsertDefaultDefinitions();

		$maint->loadParamsAndArgs( null, array( 'quiet' => true ) );

		$maint->execute();
	}
}