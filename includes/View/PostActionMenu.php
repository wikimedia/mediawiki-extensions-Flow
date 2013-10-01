<?php

namespace Flow\View;

use Flow\Block\Block;
use Flow\Model\PostRevision;
use Flow\UrlGenerator;
use Html;

class PostActionMenu {
	// Received via constructor
	protected $urlGenerator;

	// Received for each call of self::get
	protected $block;
	protected $editToken;
	protected $post;
	protected $user;

	public function __construct( UrlGenerator $urlGenerator ) {
		$this->urlGenerator = $urlGenerator;
	}

	public function get( $user, Block $block, PostRevision $post, $editToken ) {
		// For this type of shared-state to remain consistent self::get *must* be the only public methodctio
		$this->user = $user;
		$this->block = $block;
		$this->post = $post;
		$this->editToken = $editToken;

		$actions = array();

		switch( $post->getModerationState() ) {
		case $post::MODERATED_NONE:
			if ( $user->isAllowed( 'flow-hide' ) ) {
				$actions['hide'] = $this->postAction( 'hide-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-hide-post-link mw-ui-destructive mw-ui-destructive-low' );
			}
			if ( $user->isAllowed( 'flow-delete' ) ) {
				$actions['delete'] = $this->postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-delete-post-link mw-ui-destructive mw-ui-destructive-medium' );
			}
			if ( $user->isAllowed( 'flow-censor' ) ) {
				$actions['censor'] = $this->postAction( 'censor-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-censor-post-link mw-ui-destructive' );
			}
			$actions['history'] = $this->getAction( 'post-history' );
			if ( $post->isAllowedToEdit( $user ) ) {
				$actions['edit-post'] = $this->getAction( 'edit-post' );
			}
			break;

		case $post::MODERATED_HIDDEN:
			if ( $user->isAllowed( 'flow-hide' ) ) {
				$actions['restore'] = $this->postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-restore-post-link mw-ui-constructive mw-ui-destructive-low' );
			}
			if ( $user->isAllowed( 'flow-delete' ) ) {
				$actions['delete'] = $this->postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-delete-post-link mw-ui-destructive mw-ui-destructive-medium' );
			}
			if ( $user->isAllowed( 'flow-censor' ) ) {
				$actions['censor'] = $this->postAction( 'censor-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-censor-post-link mw-ui-destructive' );
			}
			$actions['history'] = $this->getAction( 'post-history' );
			break;

		case $post::MODERATED_DELETED:
			if ( $user->isAllowedAny( 'flow-delete', 'flow-censor' ) ) {
				$actions['restore'] = $this->postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-restore-post-link mw-ui-constructive' );
			}
			$actions['history'] = $this->getAction( 'post-history' );
			break;

		case $post::MODERATED_CENSORED:
			if ( !$user->isAllowed( 'flow-censor' ) ) {
				// no children, no nothing
				return;
			}
			$actions['restore'] = $this->postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'flow-restore-post-link mw-ui-constructive' );
			$actions['history'] = $this->getAction( 'post-history' );
			break;
		}

		// Default always-available actions
		$actions['permalink'] = $this->getAction( 'view' );

		return $actions;
	}

	protected function postAction( $action, array $data = array(), $class ) {
		// actions that change things must be post requests
		$output = array(
			Html::openElement( 'form', array(
				'method' => 'POST',
				'action' => $this->urlGenerator->generateUrl( $this->block->getWorkflowId(), $action )
			) ),
			Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $this->editToken ) )
		);

		foreach ( $data as $name => $value ) {
			$output[] = Html::element( 'input', array(
				'type' => 'hidden',
				'name' => $this->block->getName() . "[$name]",
				'value' => $value,
			) );
		}
		// Messages: flow-post-action-censor-post, flow-post-action-delete-post,
		// flow-post-action-hide-post, flow-post-action-restore-post
		$output[] = Html::element( 'input', array(
			'type' => 'submit',
			'class' => "mw-ui-button $class",
			'value' => wfMessage( "flow-post-action-$action" )->plain(),
		) ) .
		Html::closeElement( 'form' );

		return implode( '', $output );
	}

	protected function getAction( $action ) {
		$url = $this->urlGenerator->generateUrl(
			$this->block->getWorkflowId(),
			$action,
			array(
				$this->block->getName() . '[postId]' => $this->post->getPostId()->getHex(),
			)
		);
		// Messages: flow-post-action-view, flow-post-action-post-history, flow-post-action-edit-post
		return Html::element(
			'a',
			array(
				'href' => $url,
			),
			wfMessage( "flow-post-action-$action" )->plain()
		);
	}
}
