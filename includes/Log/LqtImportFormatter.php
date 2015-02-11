<?php

namespace Flow\Log;

use Flow\Container;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Message;
use Title;

class LqtImportFormatter extends \LogFormatter {

	public function getPreloadTitles() {
		$titles = array( $this->entry->getTarget() );
		$params = $this->entry->getParameters();
		$topic = Title::newFromText( $params['topic'] );
		if ( $topic ) {
			$titles[] = $topic;
		}

		return $titles;
	}

	/**
	 * Formats an activity log entry.
	 *
	 * @return string The log entry
	 */
	protected function getActionMessage() {
		$board = $this->entry->getTarget();
		$params = $this->entry->getParameters() + array(
			'topic' => '',
			'lqt_subject' => '',
		);
		$topic = Title::newFromText( $params['topic'] );

		$message = $this->msg( "logentry-import-lqt-to-flow-topic" )
			->params(
				$topic ? $topic->getPrefixedText() : '',
				$params['lqt_subject'],
				$board->getPrefixedText()
			);

		return $message;
	}
}
