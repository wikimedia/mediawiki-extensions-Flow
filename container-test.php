<?php

$container = include __DIR__ . '/container.php';

// need a testcase to get at the mocking methods.
$testCase = new Flow\Tests\FlowTestCase();

$container['controller.spamblacklist'] = $testCase->getMockBuilder( 'Flow\\SpamFilter\\SpamBlacklist' )
	->disableOriginalConstructor()
	->getMock();

$container['controller.spamblacklist']->expects( $testCase->any() )
	->method( 'validate' )
	->will( $testCase->returnValue( Status::newGood() ) );


foreach( array_unique( $container['storage.manager_list'] ) as $key ) {
	if ( !isset( $container["$key.listeners"] ) ) {
		continue;
	}

	$c->extend( "$key.listeners", function( $listeners ) use ( $key ) {
		return array_filter(
			$listeners,
			function( $listener ) {
				return !in_array( $listener, array(
					// putting together the right metadata for a commit is beyond the
					// scope of these tests
					'storage.post.listeners.notification',
					// Recent changes logging is outside the scope of tests, and
					// causes interaction issues
					'listener.recentchanges',
					// BoardHistory requires we also wire together TopicListEntry objects for
					// each revision, but that's also beyond our scope.
					'storage.board_history.indexes.primary',
				));
			},
			ARRAY_FILTER_USE_KEY
		);
	} );
}

return $container;
