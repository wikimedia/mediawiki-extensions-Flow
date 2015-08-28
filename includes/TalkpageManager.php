<?php

namespace Flow;

use Flow\Content\BoardContent;
use Flow\Exception\InvalidInputException;
use Flow\Model\Workflow;
use Article;
use CentralAuthUser;
use ContentHandler;
use Status;
use Title;
use User;

interface OccupationController {
	/**
	 * @param Article $title
	 * @param Workflow $workflow
	 * @return Status
	 */
	public function ensureFlowRevision( Article $title, Workflow $workflow );

	/**
	 * @param Title $title
	 * @param User $user
	 * @param bool $mustNotExist Whether the page is required to not exist; defaults to
	 *   true.
	 * @return Status Returns successful status when the provided user has the rights to
	 *  convert $title from whatever it is now to a flow board; otherwise, specifies
	 *  the error.
	 */
	public function allowCreation( Title $title, User $user, $mustNotExist = true );

	/**
	 * Gives a user object used to manage talk pages
	 *
	 * @return User User to manage talkpages
	 * @throws \MWException If a user cannot be created.
	 */
	public function getTalkpageManager();
}

class TalkpageManager implements OccupationController {
	/**
	 * @var Title[]
	 */
	protected $allowCreation = array();

	/**
	 * Cached talk page manager user
	 * @var User
	 */
	protected $talkPageManagerUser;

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
	 * before even attempting to create revisions.
	 *
	 * @param \Article $article
	 * @param Workflow $workflow
	 * @return Status Status for revision creation; On success (including if it already
	 *  had a top-most Flow revision), it will return a good status with an associative
	 *  array value.  $status->getValue()['revision'] will be a Revision
	 *  $status->getValue()['already-existed'] will be set to true if no revision needed
	 *  to be created
	 * @throws InvalidInputException
	 */
	public function ensureFlowRevision( Article $article, Workflow $workflow ) {
		$page = $article->getPage();
		$revision = $page->getRevision();

		if ( $revision !== null ) {
			$content = $revision->getContent();
			if ( $content instanceof BoardContent && $content->getWorkflowId() ) {
				// Revision is already a valid BoardContent
				return Status::newGood( array(
					'revision' => $revision,
					'already-existed' => true,
				) );
			}
		}

		$status = $page->doEditContent(
			new BoardContent( CONTENT_MODEL_FLOW_BOARD, $workflow->getId() ),
			wfMessage( 'flow-talk-taken-over-comment' )->plain(),
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$this->getTalkpageManager()
		);
		$value = $status->getValue();
		$value['already-existed'] = false;
		$status->setResult( $status->isOK(), $value );

		return $status;
	}

	/**
	 * Checks whether the given user is allowed to create a board at the given
	 * title and allows it to be created.
	 *
	 * @param Title $title Title to check
	 * @param User $user User who wants to create a board
	 * @param bool $mustNotExist Whether the page is required to not exist; defaults to
	 *   true.
	 * @return Status Returns successful status when the provided user has the rights to
	 *  convert $title from whatever it is now to a flow board; otherwise, specifies
	 *  the error.
	 */
	public function allowCreation( Title $title, User $user, $mustNotExist = true ) {
		global $wgContentHandlerUseDB;

		// Arbitrary pages can only be enabled when content handler
		// can store that content model in the database.
		if ( !$wgContentHandlerUseDB ) {
			return Status::newFatal( 'flow-error-allowcreation-no-usedb' );
		}

		// Only allow converting a non-existent page to flow
		if ( $mustNotExist ) {
			// Make sure existence status is up to date
			$title->getArticleID( Title::GAID_FOR_UPDATE );

			if ( $title->exists() ) {
				return Status::newFatal( 'flow-error-allowcreation-already-exists' );
			}
		}

		// Gate this on the flow-create-board right, essentially giving
		// wiki communities control over if flow board creation is allowed
		// to everyone or just a select few.
		if ( !$user->isAllowedAll( 'flow-create-board' ) ) {
			return Status::newFatal( 'flow-error-allowcreation-flow-create-board' );
		}

		/*
		 * tracks which titles are allowed so that when
		 * BoardContentHandler::canBeUsedOn is called for this title, it
		 * can verify this title was explicitly allowed.
		 */
		$this->allowCreation[] = $title->getPrefixedDBkey();

		return Status::newGood();
	}

	/**
	 * Before creating a flow board, BoardContentHandler::canBeUsedOn will be
	 * called to verify it's ok to create it.
	 * That, in turn, will call this, which will check if the title we want to
	 * turn into a Flow board was allowed to create (with static::allowCreation)
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function canBeUsedOn( Title $title ) {
		return
			// default content model already
			ContentHandler::getDefaultModelFor( $title ) === CONTENT_MODEL_FLOW_BOARD ||
			// explicitly allowed via allowCreation()
			in_array( $title->getPrefixedDBkey(), $this->allowCreation );
	}

	/**
	 * Gives a user object used to manage talk pages
	 *
	 * @return User User to manage talkpages
	 */
	public function getTalkpageManager() {
		if ( $this->talkPageManagerUser !== null ) {
			return $this->talkPageManagerUser;
		}

		$user = User::newFromName( FLOW_TALK_PAGE_MANAGER_USER );

		if ( $user->getId() === 0 ) {
			// Does not exist, lets create it
			$user->loadDefaults( FLOW_TALK_PAGE_MANAGER_USER );
			$user->addToDatabase();
			if ( class_exists( 'CentralAuthUser' ) ) {
				// Attach to CentralAuth if a global account already
				// exists
				$ca = CentralAuthUser::getInstance( $user );
				if ( $ca->exists() ) {
					$ca->attach( wfWikiID(), 'admin' );
				}
			}
		}

		$groups = $user->getGroups();
		foreach ( array( 'bot', 'flow-bot' ) as $group ) {
			if ( !in_array( $group, $groups ) ) {
				$user->addGroup( $group );
			}
		}

		$this->talkPageManagerUser = $user;
		return $user;
	}
}
