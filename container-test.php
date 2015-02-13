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

return $container;
