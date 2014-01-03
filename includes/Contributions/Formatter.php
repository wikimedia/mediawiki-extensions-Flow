<?php

namespace Flow\Contributions;

use Flow\AbstractFormatter;
use ContribsPager;
use Html;

class Formatter extends AbstractFormatter {
	/**
	 * @param ContribsPager $pager
	 * @param stdClass $row
	 * @return string|bool HTML for contributions entry, or false on failure
	 */
	public function format( ContribsPager $pager, $row ) {
		// Get all necessary objects
		$workflow = $row->workflow;
		$revision = $row->revision;
		$lang = $pager->getLanguage();
		$user = $pager->getUser();
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
			$linksContent = wfMessage( 'parentheses' )->rawParams( $linksContent )->escaped();
		}

		// Put it all together
		return
			$formattedTime . ' ' .
			$linksContent . ' . . ' .
			$charDiff . ' . . ' .
			$description;
	}
}
