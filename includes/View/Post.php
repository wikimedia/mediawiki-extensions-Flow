<?php

namespace Flow\View;

use Flow\Data\UserNameBatch;
use Flow\Model\PostRevision;
use Flow\Block\AbstractBlock;
use Flow\UrlGenerator;
use Linker;
use Html;
use Message;
use User;

class Post {
	protected $user;
	protected $post;
	protected $actions;
	protected $creatorUserText;
	protected $urlGenerator;

	/**
	 * @param User $user The User viewing posts
	 * @param PostRevision $post The revision object representing a post
	 * @param PostActionMenu $actions Action menus
	 * @param UrlGenerator $urlGenerator Url generator object
	 * @param UserNameBatch $usernames
	 */
	public function __construct( User $user, PostRevision $post, PostActionMenu $actions, UrlGenerator $urlGenerator, UserNameBatch $usernames ) {
		$this->user = $user;
		$this->post = $post;
		$this->actions = $actions;
		$this->urlGenerator = $urlGenerator;
		$this->usernames = $usernames;

		$this->creatorUserText = $usernames->get(
			wfWikiId(),
			$post->getCreatorId(),
			$post->getCreatorIp()
		);
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

	public function replyButton( $buttonClass ) {
		if ( !$this->post->isModerated() && $this->actions->isAllowed( 'reply' ) ) {
			return $this->actions->getButton(
				'reply',
				$this->replyLink(),
				$buttonClass
			);
		} else {
			return '';
		}
	}

	public function postInteractionLinks( $replyButtonClass, $editButtonClass ) {
		$items = array();

		$replyButton = $this->replyButton( $replyButtonClass );
		if ( $replyButton ) {
			$items[] = $replyButton;
		}
		$editButton = $this->editPostButton( $editButtonClass );
		if ( $editButton ) {
			$items[] = $editButton;
		}

		$classes = array( 'active' => 'mw-ui-button', 'inactive' => 'mw-ui-button mw-ui-disabled' );
		wfRunHooks( 'FlowAddPostInteractionLinks',
			array( $this->post, &$items, $classes ) );

		return implode(
			Html::element(
				'span',
				array( 'class' => 'mw-ui-button' ),
				wfMessage( 'flow-post-interaction-separator' )->text()
			),
			$items
		);
	}

	public function creator() {
		return $this->creatorUserText;
	}

	public function userToolLinks( $userId, $userText ) {
		if ( $userText instanceof Message ) {
			// username was moderated away, we dont know who this is
			return '';
		} else {
			return Linker::userLink( $userId, $userText ) . Linker::userToolLinks( $userId, $userText );
		}
	}

	public function creatorToolLinks() {
		return $this->userToolLinks( $this->post->getCreatorId(), $this->creatorUserText );
	}

	public function editPostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'edit-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'edit-post',
			wfMessage( 'flow-post-action-edit-post' )->escaped(),
			$buttonClass
		);
	}

	public function postHistoryLink( $blockName ) {
		return $this->actions->actionUrl(
			'post-history',
			array( $blockName . '_postId' => $this->post->getPostId()->getAlphadecimal() )
		);
	}

	public function hidePostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'hide-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'hide-post',
			wfMessage( 'flow-post-action-hide-post' )->escaped(),
			$buttonClass
		);
	}

	public function deletePostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'delete-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'delete-post',
			wfMessage( 'flow-post-action-delete-post' )->escaped(),
			$buttonClass
		);
	}

	public function suppressPostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'suppress-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'suppress-post',
			wfMessage( 'flow-post-action-suppress-post' )->escaped(),
			$buttonClass
		);
	}

	public function restorePostButton( $buttonClass ) {
		if ( !$this->actions->isAllowed( 'restore-post' ) ) {
			return '';
		}
		return $this->actions->getButton(
			'restore-post',
			wfMessage( 'flow-post-action-restore-post' )->escaped(),
			$buttonClass
		);
	}

	public function actions() {
		return $this->actions;
	}

	public function allowedAnyActions() {
		// This will need to change, but not sure best way
		return $this->actions->isAllowedAny( 'hide-post', 'delete-post', 'suppress-post', 'restore-post' );
	}

	protected function getLatestDiffLink( AbstractBlock $block ) {
		$compareLink = $this->urlGenerator->generateUrl(
			$block->getWorkflow(),
			'compare-revisions',
			array(
				$block->getName().'_newRevision' => $this->post->getRevisionId()->getAlphadecimal(),
				$block->getName().'_oldRevision' => $this->post->getPrevRevisionId()->getAlphadecimal()
			)
		);
		return $compareLink;
	}

	public function createModifiedTipsyLink( AbstractBlock $block ) {
		// Show the tipsy only if there is content change
		if ( !$this->post->isOriginalContent() ) {
			$link = Html::element(
				'a',
				array( 'class' => 'flow-content-modified-tipsy-link', 'href' => $this->getLatestDiffLink( $block ) ),
				wfMessage( 'flow-show-change' )->text()
			);
		} else {
			$link = '';
		}
		return $link;
	}

	public function createModifiedTipsyHtml( AbstractBlock $block ) {
		$html = '';
		// Show the tipsy only if there is content change
		if ( !$this->post->isOriginalContent() ) {
			$name = $this->usernames->get(
				wfWikiId(),
				$this->post->getLastContentEditUserId(),
				$this->post->getLastContentEditUserIp()
			);

			$html .= Html::openElement( 'div', array( 'class' => 'flow-content-modified-tipsy-flyout' ) );
			$html .= Html::element(
				'div',
				array( 'class' => 'flow-last-modified-user' ),
				wfMessage( 'flow-last-modified-by', $name )->text()
			);
			$html .= Html::openElement( 'div', array( 'class' => 'flow-show-change-link' ) );
			$html .= Html::element( 'a', array( 'href' => $this->getLatestDiffLink( $block ) ), wfMessage( 'flow-show-change' )->text() );
			$html .= Html::closeElement( 'div' );
			$html .= Html::closeElement( 'div' );
		}
		return $html;
	}
}
