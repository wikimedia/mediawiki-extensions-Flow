<?php

$container = include __DIR__ . '/container.php';

// need a testcase to get at the mocking methods.
$testCase = new Flow\Tests\FlowTestCase();

// The default configuration of the SpamBlacklist extension reaches out to
// meta to collect the blacklist.  Rather than worying about this just turn it off.
$container['controller.spamblacklist'] = $testCase->getMockBuilder( 'Flow\\SpamFilter\\SpamBlacklist' )
	->disableOriginalConstructor()
	->getMock();

$container['controller.spamblacklist']->expects( $testCase->any() )
	->method( 'validate' )
	->will( $testCase->returnValue( Status::newGood() ) );

$converter = $container['content_converter'];
if ( $converter instanceof Flow\Parsoid\Converter\ParsoidConverter ) {
	// Decorate the content converter with a version that stores local fixtures so we don't have
	// external dependencies while testing.
	$container['content_converter'] = $c->share( function( $c ) use ( $converter ) {
		return new Flow\Parsoid\Converter\FilesystemCachingDecorator(
			$converter,
			__DIR__ . '/tests/phpunit/fixtures/parsoid'
		);
	} );
}

return $container;
