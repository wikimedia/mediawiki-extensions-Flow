<?php

namespace Flow\Rendering;

use Flow\Container;
use Flow\Model\PostRevision;
use Flow\PostActionPermissions;
use Flow\Templating;
use Flow\View\PostActionMenu;
use Linker;
use User;

class Post extends TemplateRenderer {
	protected $user;
	protected $post;
	protected $actions;
	protected $creatorUserText;

	// @todo This method has too many responsibilities
	public function instantiate( array $parameters ) {
		parent::instantiate( $parameters + array(
			'template' => 'flow:post.html.php',
		) );
		global $wgFlowTokenSalt;

		$this->user = $parameters['user'];
		$this->block = $parameters['block'];
		$this->post = $parameters['post'];
		$this->urlGenerator = $parameters['urlGenerator'];

		if ( $this->post->isTopicTitle() ) {
			throw new \MWException( 'Cannot render topic title with ' . __CLASS__ );
		}

		$this->creatorUserText = $this->post->getCreatorName( $this->user );

		// @todo pass this in as a parameter later
		$actions = Container::get( 'flow_actions' );

		$this->actions = new PostActionMenu(
			$this->urlGenerator,
			$actions,
			new PostActionPermissions( $actions, $this->user ),
			$this->block,
			$this->post,
			$this->user->getEditToken( $wgFlowTokenSalt )
		);
	}

	public function getParameters() {
		return parent::getParameters() + array(
			'postView' => $this,
		);
	}

	public function getValidParameters() {
		$params = parent::getValidParameters() + array(
			'urlGenerator' => array(
				'required' => true,
				'description' => 'A URLGenerator object',
			),
			'user' => array(
				'required' => true,
				'description' => 'The User who is viewing the post',
			),
			'post' => array(
				'required' => true,
				'description' => 'The PostRevision object to show',
			),
			'block' => array(
				'required' => true,
				'description' => 'The Block object that this post is being shown in',
			),
		);

		// We handle this ourselves
		unset( $params['template'] );

		return $params;
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

	public function thankLink() {
		return wfMessage( 'flow-thank-link', $this->creatorUserText)->escaped();
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

	public function postHistoryButton( $content ) {
		if ( !$this->post->isFirstRevision() && $this->actions->isAllowed( 'post-history' ) ) {
			return $this->actions->getButton(
				'post-history',
				$content,
				'flow-action-history-link'
			);
		} else {
			return $content;
		}
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
