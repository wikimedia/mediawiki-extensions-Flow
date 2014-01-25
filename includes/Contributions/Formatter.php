<?php

namespace Flow\Contributions;

use Flow\AbstractFormatter;
use IContextSource;
use Html;

class Formatter extends AbstractFormatter {
	/**
	 * @param IContextSource $ctx
	 * @param \stdClass $row
	 * @return string|bool HTML for contributions entry, or false on failure
	 */
	public function format( IContextSource $ctx, $row ) {
		// Get all necessary objects
		$workflow = $row->workflow;
		$revision = $row->revision;
		$lang = $ctx->getLanguage();
		$user = $ctx->getUser();
		$title = $workflow->getArticleTitle();

		// Fetch required data
		$charDiff = $this->getCharDiff( $revision, $row->previous_revision );
		$description = $this->getActionDescription( $workflow, $row->blocktype, $revision );
		$dateFormats = $this->getDateFormats( $revision, $user, $lang );
		$links = $this->buildActionLinks(
			$title,
			$revision->getChangeType(),
			$workflow->getId(),
			method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null
		);

		// Format timestamp: add link
		$formattedTime = $dateFormats['timeAndDate'];
		if ( $links ) {
			list( $url, $text ) = $links[count( $links ) - 1];
			$formattedTime = Html::element(
				'a',
				array(
					'href' => $url,
					'title' => $text
				),
				$formattedTime
			);
		} else {
			$links = array();
		}

		// If feedback should be hidden, a special class should be added
		if ( $revision->isModerated() ) {
			$formattedTime = '<span class="history-deleted">' . $formattedTime . '</span>';
		}

		// Format links
		foreach ( $links as &$link ) {
			list( $url, $text ) = $link;
			$link = Html::element(
				'a',
				array(
					'href' => $url,
					'title' => $text
				),
				$text
			);
		}
		$linksContent = $lang->pipeList( $links );
		if ( $linksContent ) {
			$linksContent = $ctx->msg( 'parentheses' )->rawParams( $linksContent )->escaped();
		}

		// Put it all together
		return
			$formattedTime . ' ' .
			$linksContent . ' . . ' .
			$charDiff . ' . . ' .
			$description;
	}
}
