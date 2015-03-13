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
	public function isTalkpageOccupied( $title, $checkContentModel = true );

	/**
	 * @param Article $title
	 * @param Workflow $workflow
	 * @return Revision|null
	 */
	public function ensureFlowRevision( Article $title, Workflow $workflow );

	/**
	 * @param Title $title
	 * @param User $user
	 * @return bool Returns true when the provided user has the rights to
	 *  convert $title from whatever it is now to a flow board.
	 */
	public function isCreationAllowed( Title $title, User $user );

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
	 * @param Title $title Title object to check for occupation status
	 * @param boolean $checkContentModel
	 * @return boolean True if the talk page is occupied, False otherwise.
	 */
	public function isTalkpageOccupied( $title, $checkContentModel = true ) {
		if ( !$title || !is_object( $title ) ) {
			// Invalid parameter
			return false;
		}

		if ( $title->isRedirect() ) {
			return false;
		}

		if ( in_array( $title->getPrefixedText(), $this->occupiedPages ) ) {
			return true;
		}
		if ( !$title->isSubpage() && in_array( $title->getNamespace(), $this->occupiedNamespaces ) ) {
			return true;
		}

		// If it was saved as a flow board, lets just believe the database.
		if ( $checkContentModel && $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		return false;
	}

	/**
	 * When a page is taken over by Flow, add a revision.
	 *
	 * First, it provides a clearer history should Flow be disabled again later,
	 * and a descriptive message when people attempt to use regular API to fetch
	 * data for this "Page", which will no longer contain any useful content,
	 * since Flow has taken over.
	 *
	 * Also: Parsoid performs an API call to fetch page information, so we need
	 * to make sure a page actually exists ;)
	 *
	 * This method does not do any security checks regarding content model changes
	 * or the like.  Those happen much earlier in the request and should be checked
	 * before even attempting to create revisions which, when written to the database,
	 * trigger this method through the OccupationListener.
	 *
	 * @param \Article $article
	 * @param Workflow $workflow
	 * @return Revision|null
	 * @throws InvalidInputException
	 */
	public function ensureFlowRevision( Article $article, Workflow $workflow ) {
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
			new BoardContent( CONTENT_MODEL_FLOW_BOARD, $workflow ),
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

	// TODO: This is confusing.  An is...Allowed method should not be mutating state.
	/**
	 * Checks whether the given user is allowed to create a board at the given
	 * title.
	 *
	 * If so, changes the state of the talk page manager to record this fact.
	 *
	 * @param Title $title Title to check
	 * @param User $user User who wants to create a board
	 */
	public function isCreationAllowed( Title $title, User $user ) {
		global $wgContentHandlerUseDB;

		// Arbitrary pages can only be enabled when content handler
		// can store that content model in the database.
		if ( !$wgContentHandlerUseDB ) {
			return false;
		}

		// Only allow converting a non-existant page to flow
		if ( $title->exists() ) {
			return false;
		}

		// Gate this on the flow-create-board right, essentially giving
		// wiki communities control over if flow board creation is allowed
		// to everyone or just a select few.
		if ( !$user->isAllowedAll( 'flow-create-board' ) ) {
			return false;
		}

		$this->allowCreation( $title );

		return true;
	}

	/**
	 * tracks which titles are allowed so that when
	 * BoardContentHandler::canBeUsedOn is called for this title, it
	 * can call self::isTalkpageOccupied and get a successful result.
	 */
	public function allowCreation( Title $title ) {
		$this->occupiedPages[] = $title->getPrefixedText();
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
				// user. Except unit tests which get a free pass.
				if ( defined( 'MW_PHPUNIT_TEST' ) ) {
					$candidateUser->addGroup( 'bot' );
					return $candidateUser;
				}
			}
		}

		if ( $user === null ) {
			throw new FlowException( 'All of the candidate usernames exist, but they are not configured as expected.' );
		}

		return $user;
	}
}
