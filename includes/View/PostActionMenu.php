<?php

namespace Flow\View;

use Flow\Block\Block;
use Flow\Model\PostRevision;
use Flow\UrlGenerator;
use Html;
use User;

class PostActionMenu {
	// Received via constructor
	protected $urlGenerator;
	protected $block;
	protected $editToken;
	protected $post;
	protected $user;

	public function __construct( UrlGenerator $urlGenerator, User $user, Block $block, PostRevision $post, $editToken ) {
		$this->urlGenerator = $urlGenerator;
		$this->user = $user;
		$this->block = $block;
		$this->post = $post;
		$this->editToken = $editToken;
	}

	/**
	 * Returns action details.
	 *
	 * @param string $action
	 * @return array|bool Array of action details or false if invalid
	 */
	protected function getActionDetails( $action ) {
		$actions = array(
			// Not sure about mixing topic's and post's, although they are handled
			// the same currently.
			'hide-topic' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_NONE => 'flow-hide',
					PostRevision::MODERATED_HIDDEN => 'flow-hide',
				),
				'skip-state' => PostRevision::MODERATED_HIDDEN,
			),
			'delete-topic' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_NONE => 'flow-delete',
					PostRevision::MODERATED_HIDDEN => 'flow-delete',
				),
				'skip-state' => PostRevision::MODERATED_DELETED,
			),
			'censor-topic' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_NONE => 'flow-censor',
					PostRevision::MODERATED_HIDDEN => 'flow-censor',
				),
				'skip-state' => PostRevision::MODERATED_CENSORED,
			),
			'restore-topic' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_HIDDEN => 'flow-hide',
					PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
					PostRevision::MODERATED_CENSORED => 'flow-censor',
				),
				'skip-state' => PostRevision::MODERATED_NONE,
			),
			'hide-post' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_NONE => 'flow-hide',
				),
				'skip-state' => PostRevision::MODERATED_HIDDEN,
			),
			'delete-post' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_NONE => 'flow-delete',
					PostRevision::MODERATED_HIDDEN => 'flow-delete',
				),
				'skip-state' => PostRevision::MODERATED_DELETED,
			),
			'censor-post' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_NONE => 'flow-censor',
					PostRevision::MODERATED_HIDDEN => 'flow-censor',
					PostRevision::MODERATED_DELETED => 'flow-censor',
				),
				'skip-state' => PostRevision::MODERATED_CENSORED,
			),
			'restore-post' => array(
				'method' => 'POST',
				'permissions' => array(
					PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-censor' ),
					PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
					PostRevision::MODERATED_CENSORED => 'flow-censor',
				),
				'skip-state' => PostRevision::MODERATED_NONE,
			),
			'post-history' => array(
				'method' => 'GET',
				'permissions' => array(
					PostRevision::MODERATED_NONE => '',
					PostRevision::MODERATED_HIDDEN => '',
					PostRevision::MODERATED_DELETED => '',
					PostRevision::MODERATED_CENSORED => 'flow-censor',
				),
			),
			'edit-post' => array(
				'method' => 'GET',
				'permissions' => array(
					// no permissions needed for own posts
					PostRevision::MODERATED_NONE => $this->post->isAllowedToEdit( $this->user ) ? '' : 'flow-edit-post',
					PostRevision::MODERATED_HIDDEN => $this->post->isAllowedToEdit( $this->user ) ? '' : 'flow-edit-post',
					PostRevision::MODERATED_DELETED => $this->post->isAllowedToEdit( $this->user ) ? '' : 'flow-edit-post',
					PostRevision::MODERATED_CENSORED => $this->post->isAllowedToEdit( $this->user ) ? '' : 'flow-edit-post',
				),
			),
			'view' => array(
				'method' => 'GET',
				'permissions' => array(
					PostRevision::MODERATED_NONE => '',
					PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-censor' ),
					PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
					PostRevision::MODERATED_CENSORED => 'flow-censor',
				),
			),
		);

		return isset( $actions[$action] ) ? $actions[$action] : false;
	}

	/**
	 * Build a button for a certain action
	 *
	 * @param string $action
	 * @param string $content Make sure $content is safe HTML!
	 * @param string $class
	 * @return string|bool Button HTML or false on failure
	 */
	public function getButton( $action, $content, $class ) {
		$details = $this->getActionDetails( $action );

		if ( !$this->isAllowed( $action ) ) {
			return false;
		}

		$data = array( $this->block->getName() . '[postId]' => $this->post->getPostId()->getHex() );
		if ( $details['method'] === 'POST' ) {
			return $this->postAction( $action, $data, $content, $class );
		} else {
			return $this->getAction( $action, $data, $content, $class );
		}
	}

	/**
	 * Check if a user is allowed to perform (a) certain action(s).
	 *
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed( $action ) {
		$details = $this->getActionDetails( $action );

		// check if permission is set for this action
		$state = $this->post->getModerationState();
		if ( !isset( $details['permissions'][$state] ) ) {
			return false;
		}
		// Action transitions to new state, post is already in that state
		if ( isset( $details['skip-state'] ) && $details['skip-state'] === $state  ) {
			return false;
		}

		// check if user is allowed to perform action
		return call_user_func_array(
			array( $this->user, 'isAllowedAny' ),
			(array) $details['permissions'][$this->post->getModerationState()]
		);
	}

	/**
	 * Check if a user is allowed to perform certain actions.
	 *
	 * @param string $action
	 * @param string[optional] $action2 Overloadable to check if either of the provided actions are allowed
	 * @return bool
	 */
	public function isAllowedAny( $action /* [, $action2 [, ... ]] */ ) {
		$actions = func_get_args();
		$allowed = false;

		foreach ( $actions as $action ) {
			$allowed |= $this->isAllowed( $action );

			// as soon as we've found one that is allowed, break
			if ( $allowed ) {
				break;
			}
		}

		return $allowed;
	}

	/**
	 * Create form for actions that require POST.
	 *
	 * @param string $action
	 * @param array $data
	 * @param string $content
	 * @param string $class
	 * @return string
	 */
	protected function postAction( $action, array $data, $content, $class ) {
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
				'name' => $name,
				'value' => $value,
			) );
		}

		$output[] = Html::element( 'input', array(
			'type' => 'submit',
			'class' => $class,
			'value' => $content,
		) ) .
		Html::closeElement( 'form' );

		return implode( '', $output );
	}

	/**
	 * Create link for actions that require GET.
	 *
	 * @param string $action
	 * @param array $data
	 * @param string $content
	 * @param string $class
	 * @return string
	 */
	protected function getAction( $action, array $data, $content, $class ) {
		$url = $this->urlGenerator->generateUrl(
			$this->block->getWorkflowId(),
			$action,
			$data
		);

		return Html::rawElement(
			'a',
			array(
				'href' => $url,
				'class' => $class,
			),
			$content
		);
	}
}
