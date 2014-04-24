<?php

namespace Flow;

use Flow\Exception\InvalidInputException;
use Article;
use ContentHandler;
use Revision;
use Title;
use User;
use SiteStatsUpdate;

// I got the feeling NinetyNinePercentController was a bit much.
interface OccupationController {
	public function isTalkpageOccupied( $title );
	public function ensureFlowRevision( Article $title );
}

class TalkpageManager implements OccupationController {

	protected $occupiedPages;

	/**
	 * @param array $occupiedNamespaces See documentation for $wgFlowOccupyNamespaces
	 * @param array $occupiedPages See documentation for $wgFlowOccupyPages
	 */
	public function __construct( array $occupiedNamespaces, array $occupiedPages ) {
		$this->occupiedNamespaces = $occupiedNamespaces;
		$this->occupiedPages = $occupiedPages;
	}

	/**
	 * Determines whether or not a talk page is "occupied" by Flow.
	 *
	 * Internally, determines whether or not 1% of the talk page contains
	 * 99% of the discussions.
	 * @param  Title  $title Title object to check for occupation status
	 * @return boolean True if the talk page is occupied, False otherwise.
	 */
	public function isTalkpageOccupied( $title ) {
		if ( !$title || !is_object( $title ) ) {
			// Invalid parameter
			return false;
		}

		return in_array( $title->getPrefixedText(), $this->occupiedPages )
			|| ( in_array( $title->getNamespace(), $this->occupiedNamespaces )
				&& !$title->isSubpage() );
	}

	/**
	 * When a page is taken over by Flow, add a revision.
	 * First, it provides a clearer history should Flow be disabled again later,
	 * and a descriptive message when people attempt to use regular API to fetch
	 * data for this "Page", which will no longer contain any useful content,
	 * since Flow has taken over.
	 * Also: Parsoid performs an API call to fetch page information, so we need
	 * to make sure a page actually exists ;)
	 *
	 * @param \Article $article
	 * @throws InvalidInputException
	 */
	public function ensureFlowRevision( Article $article ) {
		$title = $article->getTitle();
		if ( !$this->isTalkpageOccupied( $title ) ) {
			throw new InvalidInputException( 'Requested article is not Flow enabled', 'invalid-input' );
		}

		// comment to add to the Revision to indicate Flow taking over
		$comment = '/* Taken over by Flow */';

		$page = $article->getPage();
		$revision = $page->getRevision();

		// make sure a Flow revision has not yet been inserted
		if ( $revision === null || $revision->getComment( Revision::RAW ) != $comment ) {
			$message = wfMessage( 'flow-talk-taken-over' )->inContentLanguage()->text();
			$content = ContentHandler::makeContent( $message, $title );
			$page->doEditContent( $content, $comment, EDIT_SUPPRESS_RC, false, $this->getTalkpageUser() );
		}
	}

	/**
	 * Return the account used to add the revision made when a page is taken over
	 * by Flow, setting it up if it doesn't already exist.
	 * Based on code from MassMessage
	 * https://mediawiki.org/wiki/Extension:MassMessage
	 *
	 * @return User
	 */
	protected function getTalkpageUser() {
		$user = User::newFromName(
			wfMessage( 'flow-talk-username' )->inContentLanguage()->text()
		);
		$user->load();

		if ( $user->getId() && $user->mPassword == '' && $user->mNewpassword == '' ) {
			// The account has already been set up.
			return $user;
		}

		if ( !$user->getId() ) {
			$user->addToDatabase();
			$user->saveSettings();

			// Increment site_stats.ss_users.
			$ssu = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
			$ssu->doUpdate();
		} else {
			// Take over an existing account.
			$user->setPassword( null );
			$user->setEmail( null );
			$user->saveSettings();
		}

		$user->addGroup( 'bot' );
		return $user;
	}
}
