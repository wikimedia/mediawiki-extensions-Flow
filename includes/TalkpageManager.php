<?php

namespace Flow;

use Flow\Content\BoardContent;
use Flow\Exception\InvalidInputException;
use Flow\Model\Workflow;
use Article;
use Revision;
use Title;
use User;

// I got the feeling NinetyNinePercentController was a bit much.
interface OccupationController {
	public function isTalkpageOccupied( $title );
	public function ensureFlowRevision( Article $title, Workflow $workflow );
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
	 * @param Workflow $workflow
	 * @return Revision|null
	 * @throws InvalidInputException
	 */
	public function ensureFlowRevision( Article $article, Workflow $workflow ) {
		$title = $article->getTitle();
		if ( !$this->isTalkpageOccupied( $title ) ) {
			throw new InvalidInputException( 'Requested article is not Flow enabled', 'invalid-input' );
		}

		// Break loops (because doEditContent requires rendering, which will load the workflow, which will call this function)
		static $doing = false;
		if ( $doing ) {
			return null;
		}
		$doing = true;

		// Comment to add to the Revision to indicate Flow taking over
		$comment = '/* Taken over by Flow */';

		$page = $article->getPage();
		$revision = $page->getRevision();

		// Add a revision only if a Flow revision has not yet been inserted.
		if (
			$revision === null ||
			$revision->getComment( Revision::RAW ) != $comment ||
			(
				! $revision->getContent() instanceof BoardContent ||
				! $revision->getContent()->getWorkflowId()
			)
		) {
			$status = $page->doEditContent(
				new BoardContent( 'flow-board', $workflow ),
				$comment,
				EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
				false,
				$this->getTalkpageManager()
			);

			if ( $status->isGood() && isset( $status->value['revision'] ) ) {
				$doing = false;
				return $status->value['revision'];
			}
		}

		$doing = false;
		return null;
	}

	/**
	 * Gives a user object used to manage talk pages
	 * @return User User to manage talkpages
	 */
	public function getTalkpageManager() {
		$user = User::newFromName(
			wfMessage( 'flow-talk-username' )->inContentLanguage()->text()
		);
		// Use the English fallback if the localized username is invalid or if a user
		// with the name exists.
		if ( $user === false || $user->getId() !== 0 ) {
			$user = User::newFromName( 'Flow talk page manager', false );
		}

		// prevent newtalk notification for takeover edit
		$user->mRights[] = 'nominornewtalk';

		return $user;
	}
}
