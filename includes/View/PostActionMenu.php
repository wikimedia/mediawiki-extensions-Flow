<?php

namespace Flow\View;

use Flow\Block\Block;
use Flow\Model\PostRevision;
use Flow\PostActionPermissions;
use Flow\UrlGenerator;
use Html;

class PostActionMenu {
	// Received via constructor
	protected $urlGenerator;
	protected $block;
	protected $editToken;
	protected $post;
	protected $user;

	public function __construct( UrlGenerator $urlGenerator, PostActionPermissions $permissions, Block $block, PostRevision $post, $editToken ) {
		$this->urlGenerator = $urlGenerator;
		$this->permissions = $permissions;
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
			'hide-post' => array(
				'method' => 'POST',
			),
			'delete-post' => array(
				'method' => 'POST',
			),
			'censor-post' => array(
				'method' => 'POST',
			),
			'restore-post' => array(
				'method' => 'POST',
			),
			'post-history' => array(
				'method' => 'GET',
			),
			'edit-post' => array(
				'method' => 'GET',
			),
			'view' => array(
				'method' => 'GET',
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
		if ( !$this->permissions->isAllowed( $this->post, $action ) ) {
			return false;
		}
		$data = array( $this->block->getName() . '[postId]' => $this->post->getPostId()->getHex() );
		$details = $this->getActionDetails( $action );
		if ( $details['method'] === 'POST' ) {
			return $this->postAction( $action, $data, $content, $class );
		} else {
			return $this->getAction( $action, $data, $content, $class );
		}
	}

	public function isAllowed( $action ) {
		return $this->permissions->isAllowed( $this->post, $action );
	}

	public function isAllowedAny( $action /* [, $action2 [, ... ]] */ ) {
		$arguments = func_get_args();
		array_unshift( $arguments, $this->post );

		return call_user_func_array( array( $this->permissions, 'isAllowedAny' ), $arguments );
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
		$output[] = Html::closeElement( 'form' );

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
