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
	public function format( RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		if ( !isset( $params['flow-workflow-change'] ) ) {
			wfDebugLog( 'Flow', __METHOD__ . ': No flow-workflow-change attribute in rc ' . $rc->getAttribute( 'rc_id' ) );
			return null;
		}

		$change = $params['flow-workflow-change'];
		if ( !isset( $change['action'], $change['workflow'], $change['revision'] ) ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Malformed rc ' . $rc->getAttribute( 'rc_id' ) );
			return null;
		}

		$links = $this->serializer->buildActionLinks(
				$rc->getTitle(),
				$change['action'],
				UUID::create( $change['workflow'] ),
				UUID::create( $change['revision'] ),
				isset( $change['post'] ) ? UUID::create( $change['post'] ) : null
			);

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
