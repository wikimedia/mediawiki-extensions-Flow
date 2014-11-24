<?php

namespace Flow;

use Flow\Content\BoardContent;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Flow\Model\Workflow;
use Article;
use Revision;
use Title;
use User;

// I got the feeling NinetyNinePercentController was a bit much.
interface OccupationController {
	/**
	 * @param Title $title
	 * @return bool
	 */
	public function isTalkpageOccupied( $title );

	/**
	 * @param Article $title
	 * @param Workflow $workflow
	 * @return Revision|null
	 */
	public function ensureFlowRevision( Article $title, Workflow $workflow );

	/**
	 * Gives a user object used to manage talk pages
	 *
	 * @return User User to manage talkpages
	 * @throws MWException If a user cannot be created.
	 */
	public function getTalkpageManager();
}

class TalkpageManager implements OccupationController {
	/**
	 * @var int[]
	 */
	protected $occupiedNamespaces;

	/**
	 * @var string[]
	 */
	protected $occupiedPages;

	/**
	 * @param int[] $occupiedNamespaces See documentation for $wgFlowOccupyNamespaces
	 * @param string[] $occupiedPages See documentation for $wgFlowOccupyPages
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
	 *
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


		// Comment to add to the Revision to indicate Flow taking over
		$comment = '/* Taken over by Flow */';

		$page = $article->getPage();
		$revision = $page->getRevision();

		if ( $revision !== null ) {
			if ( $revision->getComment( Revision::RAW ) == $comment ) {
				// Revision was created by this process
				return null;
			}
			$content = $revision->getContent();
			if ( $content instanceof BoardContent && $content->getWorkflowId() ) {
				// Revision is already a valid BoardContent
				return null;
			}
		}

		$doing = true;
		$status = $page->doEditContent(
			new BoardContent( 'flow-board', $workflow ),
			$comment,
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$this->getTalkpageManager()
		);
		$doing = false;

		if ( $status->isGood() && isset( $status->value['revision'] ) ) {
			return $status->value['revision'];
		}

		return null;
	}

	/**
	 * Gives a user object used to manage talk pages
	 *
	 * @return User User to manage talkpages
	 * @throws MWException If both of the names already exist, but are not properly
	 *  configured.
	 */
	public function getTalkpageManager() {
		$userNameCandidates = array(
			wfMessage( 'flow-talk-username' )->inContentLanguage()->text(),
			'Flow talk page manager',
		);

		$user = null;

		foreach ( $userNameCandidates as $name ) {
			$candidateUser = User::newFromName( $name );

			if ( $candidateUser->getId() === 0 ) {
				$user = User::createNew( $name );
				$user->addGroup( 'bot' );
				break;
			} else {
				// Exists

				$groups = $candidateUser->getGroups();
				if ( in_array( 'bot', $groups ) ) {
					// We created this user earlier.
					$user = $candidateUser;
					break;
				}

				// If it exists, but is not a bot, someone created this
				// without setting it up as expected, so go on to the next
				// user.
			}
		}

		if ( $user === null ) {
			throw new FlowException( 'All of the candidate usernames exist, but they are not configured as expected.' );
		}

		return $user;
	}
}
