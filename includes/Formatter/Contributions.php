<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use ContribsPager;
use Html;

class Contributions extends AbstractFormatter {
	/**
	 * @param ContribsPager $pager
	 * @param \stdClass $row
	 * @return string|bool HTML for contributions entry, or false on failure
	 */
	public function format( ContribsPager $pager, $row ) {
		try {
			return $this->formatReal( $pager, $row );
		} catch ( FlowException $e ) {
			\MWExceptionHandler::logException( $e );
			return false;
		}
	}

	protected function formatReal( ContribsPager $pager, $row ) {
		// Get all necessary objects
		$workflow = $row->workflow;
		$revision = $row->revision;
		$ctx = $pager->getContext();
		$lang = $pager->getLanguage();
		$user = $pager->getUser();
		$title = $workflow->getArticleTitle();

		if ( !$this->getPermissions( $user )->isRevisionAllowed( $revision, 'contributions' ) ) {
			return false;
		}

		// Fetch required data
		$charDiff = $this->getCharDiff( $revision, $row->previous_revision );
		$description = $this->getActionDescription( $workflow->getId(), $row->blocktype, $revision );
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
			list( $url, $message ) = end( $links );
			$formattedTime = Html::element(
				'a',
				array(
					'href' => $url,
					'title' => $message->setContext( $ctx )->text(),
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
			list( $url, $message ) = $link;
			$text = $message->setContext( $ctx )->text();
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
			$linksContent = $pager->msg( 'parentheses' )->rawParams( $linksContent )->escaped();
		}

		$diffLink = '';
		if ( in_array( $revision->getChangeType(), array( 'edit-post', 'edit-header', 'edit-title' ) ) ) {
			list( $href, $msg ) = $this->revisionDiffLink(
				$title,
				$workflow->getId(),
				$revision->getRevisionId(),
				$revision->getPrevRevisionId()
			);
			$diffLink = wfMessage( 'parentheses' )
				->rawParams( Html::rawElement(
					'a',
					array( 'href' => $href ),
					$msg->escaped()
				) )
				->escaped();
		}


		// Put it all together
		return
			$formattedTime . ' ' .
			$linksContent . ' . . ' .
			$charDiff . ' . . ' .
			$description
			. ' '
			. $diffLink;
	}
}
