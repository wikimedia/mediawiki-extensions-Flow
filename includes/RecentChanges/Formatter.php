<?php

namespace Flow\RecentChanges;

use Flow\AbstractFormatter;
use Flow\Model\UUID;
use ChangesList;
use Html;
use RecentChange;

class Formatter extends AbstractFormatter {
	public function format( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow data missing in recent changes.' );
			return false;
		}

		$title = $rc->getTitle();
		$user = $cl->getUser();
		$ctx = $cl->getContext();
		$lang = $ctx->getLanguage();

		$workflowId = UUID::create( $changeData['workflow'] );

		/**
		 * Check to make sure revision_type exists, this is to make sure corrupted
		 * flow recent change data doesn't throw error on the page.
		 * See bug 59106 for more detail
		 */
		if ( !$changeData['revision_type'] ) {
			return false;
		}

		/*
		 * @todo:
		 * We should some day introduce a hook in core that allows us to hook
		 * into the full resultset, so we can first loop all entries and pre-
		 * load revisions all at once.
		 *
		 * Since that's not yet the case, multiple queries (up to $wgFeedLimit)
		 * may occur. There can be $wgFeedLimit * 2 queries to DB or cache.
		 */
		$revision = $this->loadRevision( UUID::create( $changeData['revision'] ), $changeData['revision_type'] );
		if ( !$revision ) {
			return false;
		}

		$links = (array) $this->buildActionLinks(
			$title,
			$revision->getChangeType(),
			$workflowId,
			method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null
		);

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
			$linksContent = $cl->msg( 'parentheses' )->rawParams( $linksContent )->escaped()
				. $this->changeSeparator();
		}

		$dateFormats = $this->getDateFormats( $revision, $user, $lang );
		$formattedTime = '<span class="mw-changeslist-date">' . $dateFormats['time'] . '</span>';

		$workflowLink = $this->workflowLink( $title, $workflowId );
		$workflowLink = Html::element(
			'a',
			array( 'href' => $workflowLink[0] ),
			$workflowLink[1]
		);

		return $linksContent
			. $workflowLink
			. $cl->msg( 'semicolon-separator' )->escaped()
			. $formattedTime
			. ' '
			. $this->changeSeparator()
			. ' '
			. $this->getActionDescription( $workflowId, $changeData['block'], $revision );
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}
}
