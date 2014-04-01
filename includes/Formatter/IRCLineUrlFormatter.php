<?php

namespace Flow\Formatter;

use Flow\Model\UUID;
use RecentChange;

/**
 * Generates URL's to be inserted into the IRC
 * recent changes feed.
 */
class IRCLineUrlFormatter extends AbstractFormatter {

	/**
	 * @param RecentChange $rc
	 * @return string|null
	 */
	public function format( FormatterRow $row ) {
		$links = $this->serializer->buildActionLinks( $row );

		// Listed in order of preference
		$accept = array(
			'diff',
			'post-history', 'topic-history', 'board-history',
			'post', 'topic',
			'workflow'
		);

		foreach ( $accept as $key ) {
			if ( isset( $links[$key] ) ) {
				return $links[$key][0];
			}
		}

		wfDebugLog( 'Flow', __METHOD__
				. ': No url generated for action ' . $change['action']
				. ' on revision ' . $change['revision']
		);
		return null;
	}
}
