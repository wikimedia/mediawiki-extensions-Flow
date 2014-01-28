<?php

namespace Flow\Tests;

/**
 * @group Flow
 */
class FormatterTest extends \MediaWikiTestCase {

	protected function createFormatter( $class, $all = false ) {
		$urls = $this->getMock( 'Flow\UrlGenerator' );
		$messages = $this->getMock( 'Flow\ActionMessageRenderer' );
		$loader = $this->getMock( 'Flow\ActionDataLoader' );

		$formatter = new Flow\RecentChanges\Formatter( $urls, $messages, $loader );

		return $all ? array( $formatter, $urls, $messages, $loader ) : $formatter;
	}

}
