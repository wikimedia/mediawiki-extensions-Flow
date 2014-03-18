<?php

namespace Flow\Formatter;

use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use ChangesList;
use Html;
use Linker;
use RecentChange;

class RecentChanges extends AbstractFormatter {
	/**
	 * Check if the most recent action for an entity has been displayed already
	 */
	protected $displayStatus = array();

	/**
	 * @param ChangesList $cl
	 * @param RecentChange $rc
	 * @param bool $watchlist
	 * @return string|bool Output line, or false on failure
	 */
	public function format( ChangesList $cl, RecentChange $rc, $watchlist = false ) {
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

		// Only show most recent items for watchlist
		if ( $watchlist && $this->hideRecord( $revision, $changeData ) ) {
			return false;
		}

		if ( !$this->getPermissions( $user )->isAllowed( $revision, 'recentchanges' ) ) {
			return false;
		}

		$dateFormats = $this->getDateFormats( $revision, $user, $lang );
		$workflowLink = $this->workflowLink( $title, $workflowId );
		$workflowLink = Html::rawElement(
			'a',
			array( 'href' => $workflowLink[0] ),
			'<span class="mw-changeslist-date">' . $dateFormats['time'] . '</span>'
		);

		$diffLink = '';
		// @todo de-duplicate this, repeated in the Contributions formatter
		if ( in_array( $changeData['action'], array( 'edit-post', 'edit-header', 'edit-title' ) ) ) {
			list( $href, $msg ) = $this->revisionDiffLink(
				$title,
				$workflowId,
				$revision->getRevisionId(),
				$revision->getPrevRevisionId(),
				$revision->getRevisionType(),
				$changeData['block']
			);
			$diffLink = wfMessage( 'parentheses' )
				->rawParams( Html::rawElement(
					'a',
					array( 'href' => $href ),
					$msg->escaped()
				) )
				->escaped();
		}

		return $diffLink
			. ' '
			. Linker::link( $title )
			. $cl->msg( 'semicolon-separator' )->escaped()
			. ' '
			. $workflowLink
			. $this->changeSeparator()
			. ChangesList::showCharacterDifference(
				$rc->getAttribute( 'rc_old_len' ),
				$rc->getAttribute( 'rc_new_len' ),
				$cl
			  )
			. $this->changeSeparator()
			. ' '
			. $this->getActionDescription( $workflowId, $changeData['block'], $revision );
	}

	/**
	 * Determines if a flow record should be displayed in Special:Watchlist
	 */
	protected function hideRecord( AbstractRevision $revision, array $changeData ) {
		// Check for legacy action names and convert it
		$alias = $this->actions->getValue( $changeData['action'] );
		if ( is_string( $alias ) ) {
			$action = $alias;
		} else {
			$action = $changeData['action'];
		}
		// * Display the most recent new post, edit post, edit title for a topic
		// * Display the most recent header edit
		// * Display all new topic and moderation actions
		switch ( $action ) {
			case 'edit-header':
				if ( isset( $this->displayStatus['header-' . $changeData['workflow']] ) ) {
					return true;
				}
				$this->displayStatus['header-' . $changeData['workflow']] = true;
			break;

			case 'new-post':
			case 'reply':
			case 'edit-post':
			case 'edit-title':
				// A new topic
				if ( $revision->isTopicTitle() && $revision->isFirstRevision() ) {
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

	// @todo:
	// This is a temporary fix.
	// We will add a param to core hook to determine if this is watchlist page
	// Or add a method to the ChangesList to test for its $watchlist property
	public function isWatchList( array $classes ) {
		foreach ( $classes as $class ) {
			if ( substr( $class, 0, 10 ) === 'watchlist-' ) {
				return true;
			}
		}
		return false;
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}
}
