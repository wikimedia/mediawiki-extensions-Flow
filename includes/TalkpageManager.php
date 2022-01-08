<?php

namespace Flow;

use ExtensionRegistry;
use Flow\Content\BoardContent;
use Flow\Exception\InvalidInputException;
use Flow\Model\Workflow;
use MediaWiki\Extension\CentralAuth\User\CentralAuthUser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserGroupManager;
use Status;
use Title;
use User;
use WikiMap;
use WikiPage;

class TalkpageManager implements OccupationController {
	/**
	 * @var UserGroupManager
	 */
	private $userGroupManager;

	/**
	 * @var string[]
	 */
	protected $allowedPageNames = [];

	/**
	 * Cached talk page manager user
	 * @var User
	 */
	protected $talkPageManagerUser;

	/**
	 * @param UserGroupManager $userGroupManager
	 */
	public function __construct( UserGroupManager $userGroupManager ) {
		$this->userGroupManager = $userGroupManager;
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
	 * before even attempting to create revisions.
	 *
	 * @param WikiPage $page
	 * @param Workflow $workflow
	 * @return Status Status for revision creation; On success (including if it already
	 *  had a top-most Flow revision), it will return a good status with an associative
	 *  array value.  $status->getValue()['revision-record'] will be a RevisionRecord
	 *  $status->getValue()['already-existed'] will be set to true if no revision needed
	 *  to be created
	 * @throws InvalidInputException
	 */
	public function ensureFlowRevision( WikiPage $page, Workflow $workflow ) {
		$revision = $page->getRevisionRecord();

		if ( $revision !== null ) {
			$content = $revision->getContent( SlotRecord::MAIN );
			if ( $content instanceof BoardContent && $content->getWorkflowId() ) {
				// Revision is already a valid BoardContent
				return Status::newGood( [
					'revision' => $revision,
					'already-existed' => true,
				] );
			}
		}

		$status = $page->doUserEditContent(
			new BoardContent( CONTENT_MODEL_FLOW_BOARD, $workflow->getId() ),
			$this->getTalkpageManager(),
			wfMessage( 'flow-talk-taken-over-comment' )->plain(),
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC
		);
		$value = $status->getValue();
		$value['already-existed'] = false;
		$status->setResult( $status->isOK(), $value );

		return $status;
	}

	/**
	 * @inheritDoc
	 */
	public function checkIfCreationIsPossible( Title $title, $mustNotExist = true, $forWrite = true ) {
		// Only allow converting a non-existent page to Flow
		if ( $mustNotExist ) {
			if ( $title->exists( $forWrite ? Title::GAID_FOR_UPDATE : 0 ) ) {
				return Status::newFatal( 'flow-error-allowcreation-already-exists' );
			}
		}

		return Status::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function checkIfUserHasPermission( Title $title, User $user ) {
		$services = MediaWikiServices::getInstance();
		if (
			// If the title is default-Flow, the user always has permission
			$services->getSlotRoleRegistry()->getRoleHandler( SlotRecord::MAIN )
				->getDefaultModel( $title ) === CONTENT_MODEL_FLOW_BOARD ||

			// Gate this on the flow-create-board right, essentially giving
			// wiki communities control over if Flow board creation is allowed
			// to everyone or just a select few.
			$services->getPermissionManager()
				->userCan( 'flow-create-board', $user, $title )
		) {
			return Status::newGood();
		} else {
			return Status::newFatal( 'flow-error-allowcreation-flow-create-board' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function safeAllowCreation( Title $title, User $user, $mustNotExist = true, $forWrite = true ) {
		$status = Status::newGood();

		$technicallyAllowedStatus = $this->checkIfCreationIsPossible( $title, $mustNotExist, $forWrite );

		$permissionStatus = $this->checkIfUserHasPermission( $title, $user );

		$status->merge( $technicallyAllowedStatus );
		$status->merge( $permissionStatus );

		if ( $status->isOK() ) {
			$this->forceAllowCreation( $title );
		}

		return $status;
	}

	/**
	 * @inheritDoc
	 */
	public function forceAllowCreation( Title $title ) {
		/*
		 * Tracks which titles are allowed so that when
		 * BoardContentHandler::canBeUsedOn is called for this title, it
		 * can verify this title was explicitly allowed.
		 */
		$this->allowedPageNames[] = $title->getPrefixedDBkey();
	}

	/**
	 * Before creating a flow board, BoardContentHandler::canBeUsedOn will be
	 * called to verify it's ok to create it.
	 * That, in turn, will call this, which will check if the title we want to
	 * turn into a Flow board was allowed to create (with allowedPageNames)
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	public function canBeUsedOn( Title $title, User $user ) {
		// If the user has rights, mark the page as allowed
		// For MovePage
		$this->safeAllowCreation( $title, $user, /* $mustNotExist = */ true );

		return // default content model already
			MediaWikiServices::getInstance()->getSlotRoleRegistry()->getRoleHandler( SlotRecord::MAIN )
				->getDefaultModel( $title ) === CONTENT_MODEL_FLOW_BOARD ||

			// explicitly allowed via safeAllowCreation()
			in_array( $title->getPrefixedDBkey(), $this->allowedPageNames );
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

		$user = User::newSystemUser( FLOW_TALK_PAGE_MANAGER_USER, [ 'steal' => true ] );

		if ( ExtensionRegistry::getInstance()->isLoaded( 'CentralAuth' ) ) {
			// Attach to CentralAuth if a global account already
			// exists
			$ca = CentralAuthUser::getPrimaryInstance( $user );
			if ( $ca->exists() && !$ca->isAttached() ) {
				$ca->attach( WikiMap::getCurrentWikiId(), 'admin' );
			}
		}

		$groups = $this->userGroupManager->getUserGroups( $user );
		foreach ( [ 'bot', 'flow-bot' ] as $group ) {
			if ( !in_array( $group, $groups ) ) {
				$this->userGroupManager->addUserToGroup( $user, $group );
			}
		}

		$this->talkPageManagerUser = $user;
		return $user;
	}
}
