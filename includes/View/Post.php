<?php

namespace Flow\View;

use Flow\Model\PostRevision;
use Flow\Templating;
use Linker;
use User;

class Post {
	protected $user;
	protected $actions;

	/**
	 * @param  User             $user    The User viewing posts
	 */
	public function __construct( User $user, PostRevision $post, PostActionMenu $actions ) {
		$this->user = $user;
		$this->post = $post;
		$this->actions = $actions;

		$this->creatorUserText = Templating::getUserText(
			$post->getCreator( $this->user ),
			$post
		);

	}

	public function replyPlaceholder() {
		static $cache;
		if ( isset( $cache[$this->creatorUserText] ) ) {
			return $cache[$this->creatorUserText];
		}
		return $cache[$this->creatorUserText] = wfMessage( 'flow-reply-placeholder', $this->creatorUserText )->text();
	}

	public function replySubmit() {
		static $cache;
		if ( isset( $cache[$this->creatorUserText] ) ) {
			return $this->creatorUserText;
		}
		return $cache[$this->creatorUserText] = wfMessage( 'flow-reply-submit', $this->creatorUserText )->text();
	}

	public function replyLink() {
		static $cache;
		if ( isset( $cache[$this->creatorUserText] ) ) {
			return $cache[$this->creatorUserText];
		}
		return $cache[$this->creatorUserText] = wfMessage( 'flow-reply-link', $this->creatorUserText )->escaped();
	}

	public function thankLink() {
		static $cache;
		if ( isset( $cache[$this->creatorUserText] ) ) {
			return $cache[$this->creatorUserText];
		}
		return $cache[$this->creatorUserText] = wfMessage( 'flow-thank-link', $this->creatorUserText)->escaped();
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
			static $cache = array();
			if ( !isset( $cache[$userId][$userText] ) ) {
				$cache[$userId][$userText] = Linker::userLink( $userId, $userText ) . Linker::userToolLinks( $userId, $userText );
			}
			return $cache[$userId][$userText];
		}
	}

	public function creatorToolLinks() {
		return $this->userToolLinks(
			$this->post->getCreatorId( $this->user ),
			$this->post->getCreatorName( $this->user )
		);
	}
	public function editPostButton( PostRevision $post, $buttonClass ) {
		if ( !$this->actions->isAllowed( 'edit-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'edit-post',
			wfMessage( 'flow-post-action-edit-post' )->plain(),
			$buttonClass
		);
	}

	public function postHistoryButton( PostRevision $post, $content ) {
		if ( $this->actions->isAllowed( 'post-history' ) ) {
			return $this->actions->getButton(
				'post-history',
				$content,
				'flow-action-history-link'
			);
		} else {
			return $content;
		}
	}

	public function hidePostButton( PostRevision $post, $buttonClass ) {
		if ( !$this->actions->isAllowed( 'hide-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'hide-post',
			wfMessage( 'flow-post-action-hide-post' )->plain(),
			$buttonClass
		);
	}

	public function deletePostButton( PostRevision $post, $buttonClass ) {
		if ( !$this->actions->isAllowed( 'delete-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'delete-post',
			wfMessage( 'flow-post-action-delete-post' )->plain(),
			$buttonClass
		);
	}

	public function suppressPostButton( PostRevision $post, $buttonClass ) {
		if ( !$this->actions->isAllowed( 'censor-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'censor-post',
			wfMessage( 'flow-post-action-censor-post' )->plain(),
			$buttonClass
		);
	}

	public function restorePostButton( PostRevision $post, $buttonClass ) {
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
		return $this->actions->isAllowedAny( 'hide-post', 'delete-post', 'censor-post', 'restore-post' );
	}

	/**
	* Gets a Flow-formatted plaintext human-readable identifier for a user.
	* Usually the user's name, but it can also return "an anonymous user",
	* or information about an item's moderation state.
	*
	* @param  User             $user    The User object to get a description of.
	* @param  AbstractRevision $rev     An AbstractRevision object to retrieve moderation state from.
	* @param  bool             $showIPs Whether or not to show IP addresses for anonymous users
	* @return String                    A human-readable identifier for the given User.
	*/
	public function getUserText( $user, $rev = null, $showIPs = false ) {
		return Templating::getUserText( $user, $rev, $showIPs );
	}
}
