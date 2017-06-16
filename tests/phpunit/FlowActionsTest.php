<?php

namespace Flow\Tests;

use Flow\FlowActions;

/**
 * @group Flow
 */
class FlowActionsTest extends \MediaWikiTestCase {

	public function testAliasedTopLevelValues() {
		$actions = new FlowActions( [
			'something' => 'aliased',
			'aliased' => [
				'real' => 'value',
			],
		] );

		$this->assertEquals( 'value', $actions->getValue( 'something', 'real' ) );
	}
}
