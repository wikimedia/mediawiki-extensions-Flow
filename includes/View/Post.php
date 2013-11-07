<?php

namespace Flow\View;

use Flow\Model\PostRevision;
use Flow\Templating;
use Linker;
use User;

class Post {
	protected $user;
	protected $post;
	protected $actions;
	protected $creatorUserText;

	/**
	 * @param  User             $user    The User viewing posts
	 */
	public function __construct( User $user, PostRevision $post, PostActionMenu $actions ) {
		$this->user = $user;
		$this->post = $post;
		$this->actions = $actions;

		$this->creatorUserText = $post->getCreatorName( $this->user );
	}

	public function replyPlaceholder() {
		return wfMessage( 'flow-reply-placeholder', $this->creatorUserText )->text();
	}

	public function replySubmit() {
		return wfMessage( 'flow-reply-submit', $this->creatorUserText )->text();
	}

	public function replyLink() {
		return wfMessage( 'flow-reply-link', $this->creatorUserText )->escaped();
	}

	public function moderatedTalkLink() {
		$user = User::newFromId( $this->post->getModeratedByUserId() );
		$title = $user->getTalkPage();

		return array(
			$title->getLinkUrl(),
			wfMessage(
				'flow-talk-link',
				$this->post->getModeratedByUserText()
			)->escaped()
		);
	}

	public function creator() {
		return $this->creatorUserText;
	}

	public function userToolLinks( $userId, $userText ) {
		if ( $userText instanceof MWMessage ) {
			// username was moderated away, we dont know who this is
			return '';
		} else {
			return Linker::userLink( $userId, $userText ) . Linker::userToolLinks( $userId, $userText );
		}
	}

	public function creatorToolLinks() {
		return $this->userToolLinks(
			$this->post->getCreatorId( $this->user ),
			$this->post->getCreatorName( $this->user )
		);
	}

	public function editPostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'edit-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'edit-post',
			wfMessage( 'flow-post-action-edit-post' )->plain(),
			$buttonClass
		);
	}

	public function postHistoryLink( ) {
		return $this->actions->actionUrl(
			'post-history'
		);
	}

	public function hidePostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'hide-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'hide-post',
			wfMessage( 'flow-post-action-hide-post' )->plain(),
			$buttonClass
		);
	}

	public function deletePostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'delete-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'delete-post',
			wfMessage( 'flow-post-action-delete-post' )->plain(),
			$buttonClass
		);
	}

	public function suppressPostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'censor-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'censor-post',
			wfMessage( 'flow-post-action-censor-post' )->plain(),
			$buttonClass
		);
	}

	public function restorePostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'restore-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'restore-post',
			wfMessage( 'flow-post-action-restore-post' )->plain(),
			$buttonClass
		);
	}

	public function actions() {
		return $this->actions;
	}

	public function allowedAnyActions() {
		// This will need to change, but not sure best way
		return $this->actions->isAllowedAny( 'hide-post', 'delete-post', 'censor-post', 'restore-post' );
	}
}
