<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\Anchor;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Flow\Model\Workflow;
use EchoBasicFormatter;
use EchoEvent;
use Message;
use Title;
use User;

// could be renamed later if we have more formatters
class NewTopicFormatter extends NotificationFormatter {

	/**
	 * New Topic user 'event' as the iterator
	 */
	protected function generateBundleData( $event, $user, $type ) {
		$data = $this->getRawBundleData( $event, $user, $type );

		if ( !$data ) {
			return;
		}

		// bundle event is excluding base event
		$this->bundleData['event-count'] = count( $data ) + 1;
		$this->bundleData['use-bundle']  = $this->bundleData['event-count'] > 1;
	}

	/**
	 * @param $event EchoEvent
	 * @param $param string
	 * @param $message Message
	 * @param $user User
	 */
	protected function processParam( $event, $param, $message, $user ) {
		switch ( $param ) {
			case 'event-count':
				$message->numParams( $this->bundleData['event-count'] );
				break;
			default:
				parent::processParam( $event, $param, $message, $user );
				break;
		}
	}
}

