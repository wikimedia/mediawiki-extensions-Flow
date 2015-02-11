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
		global $wgContLang;

		$board = $this->entry->getTarget();
		$params = $this->entry->getParameters();
		$topic = Title::newFromText( $params['topic'] );

		$skin = $this->plaintext ? null : $this->context->getSkin();
		$language = $skin === null ? $wgContLang : $skin->getLanguage();
		// Give grep a chance to find the usages:
		// logentry-import-lqt-to-flow
		$message = wfMessage( "logentry-import-lqt-to-flow" )
			->params(
				$topic ? $topic->getPrefixedText() : '',
				isset( $params['lqt_subject'] ) ? $params['lqt_subject'] : '',
				$board->getPrefixedText()
			)
			->inLanguage( $language );

		if ( $this->plaintext ) {
			return $message;
		} else {
			return \Html::rawElement(
				'span',
				array( 'class' => 'plainlinks' ),
				$message->parse()
			);
		}
	}
}
