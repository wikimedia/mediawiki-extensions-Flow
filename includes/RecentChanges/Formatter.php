<?php

namespace Flow\RecentChanges;

use Flow\Model\AbstractRevision;
use Flow\AbstractFormatter;
use Flow\Model\UUID;
use ChangesList;
use Html;
use RecentChange;

class Formatter extends AbstractFormatter {

	/**
	 * Check if the most recent action for an entity has been displayed already
	 */
	protected $displayStatus = array();

	public function format( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow data missing in recent changes.' );
			return false;
		}

		$title = $rc->getTitle();
		$user = $cl->getUser();
		$lang = $cl->getLanguage();

		/*
		 * @todo:
		 * We should some day introduce a hook in core that allows us to hook
		 * into the full resultset, so we can first loop all entries and pre-
		 * load workflows & revisions all at once.
		 *
		 * Since that's not yet the case, multiple queries (up to $wgFeedLimit)
		 * may occur. Assuming revisions (yes) and workflow (less likely) are
		 * all different, there can be $wgFeedLimit * 2 queries to DB or cache.
		 */
		$workflow = $this->loadWorkflow( UUID::create( $changeData['workflow'] ) );
		if ( !$workflow ) {
			return false;
		}

		/**
		 * Check to make sure revision_type exists, this is to make sure corrupted
		 * flow recent change data doesn't throw error on the page.
		 * See bug 59106 for more detail
		 */
		if ( !$changeData['revision_type'] ) {
			return false;
		}

		$revision = $this->loadRevision( UUID::create( $changeData['revision'] ), $changeData['revision_type'] );
		if ( !$revision ) {
			return false;
		}

		if ( $this->hideRecord( $revision, $changeData ) ) {
			return false;
		}

		$links = (array) $this->buildActionLinks(
			$title,
			$revision->getChangeType(),
			$workflow->getId(),
			method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null
		);

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
			$linksContent = $cl->msg( 'parentheses' )->rawParams( $linksContent )->escaped()
				. $this->changeSeparator();
		}

		$dateFormats = $this->getDateFormats( $revision, $user, $lang );
		$formattedTime = '<span class="mw-changeslist-date">' . $dateFormats['time'] . '</span>';

		$workflowLink = $this->workflowLink( $title, $workflow->getId() );
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
			. $this->getActionDescription( $workflow, $changeData['block'], $revision );
	}

	/**
	 * Determines if a flow record should be displayed in Special:Watchlist
	 */
	protected function hideRecord( AbstractRevision $revision, array $changeData ) {
		// * Display the most recent new post, edit post, edit title for a topic
		// * Display the most recent header edit
		// * Display all new topic and moderation actions
		switch ( $changeData['action'] ) {
			case 'edit-header':
				if ( isset( $this->displayStatus['header-' . $changeData['workflow']] ) ) {
					return true;
				}
				$this->displayStatus['header-' . $changeData['workflow']] = true;
			break;

			case 'new-post':
			case 'reply':
			case 'edit-post':
			case 'flow-edit-post':
			case 'edit-title':
				// A new topic
				if ( !$revision->getReplyToId() && !$revision->getPrevRevisionId() ) {
					return false;
				}
				if ( isset( $this->displayStatus['topic-' . $changeData['workflow']] ) ) {
					return true;
				}
				$this->displayStatus['topic-' . $changeData['workflow']] = true;
			break;
		}

		return false;
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}
}
