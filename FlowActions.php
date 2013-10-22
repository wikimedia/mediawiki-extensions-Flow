<?php

use Flow\Model\PostRevision;
use Flow\PostActionPermissions;
use Flow\Log\Logger;
use Flow\UrlGenerator;
use Flow\Block\Block;

/**
 * Flow actions: key => value map with key being the action name.
 * The value consists of an array of these below keys (and appropriate values):
 * * log_type: the Special:Log filter to save actions to.
 * * permissions: array of permissions, where each key is the existing post
 *   state and value is the action required to execute the action.
 * * button-method: used in PostActionMenu, to generate GET (a) or POST (form)
 *   links for the action.
 * * history: all history-related information:
 *   * i18n-message: the i18n message key for this change type
 *   * i18n-params: array of i18n parameters for the provided message (see
 *     HistoryRecord::buildMessage phpdoc for more details)
 *   * class: classname to be added to the list-item for this changetype
 *   * bundle: array with, again, all of the above information if multiple types
 *     should be bundled (then the bundle i18n & class will be used to generate
 *     the list-item; clicking on it will reveal the individual history entries)
 */
$wgFlowActions = array(
	'create-header' => array(
		'log_type' => false,
		'permissions' => null,
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-create-header',
			// @todo: AFAIK, we don't have a board history yet, where this will be surfaced
			'class' => 'flow-history-create-header',
		),
	),

	'edit-header' => array(
		'log_type' => false,
		'permissions' => null,
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-header',
			// @todo: AFAIK, we don't have a board history yet, where this will be surfaced
			'class' => 'flow-history-edit-header',
		),
	),

	'edit-title' => array(
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-title',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $urlGenerator->generateUrl( $revision->getPostId() );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getContent( $user, 'wikitext' );
				},
				// @todo: find previous revision & return title of that revision
			),
			'class' => 'flow-history-edit-title',
		),
	),

	'new-post' => array(
		'log_type' => false,
		'permissions' => null,
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-new-post',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $urlGenerator->generateUrl( $revision->getPostId() );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getContent( $user, 'wikitext' );
				},
			),
			'class' => 'flow-history-new-post',
		),
	),

	'edit-post' => array(
		'log_type' => false,
		'permissions' => array(
			// no permissions needed for own posts
			PostRevision::MODERATED_NONE => function( PostRevision $post, PostActionPermissions $permissions ) {
					return $post->isCreator( $permissions->getUser() ) ? '' : 'flow-edit-post';
				}
		),
		'button-method' => 'GET',
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-post',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
					return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
				},
			),
			'class' => 'flow-history-edit-post',
		),
	),

	'hide-post' => array(
		'log_type' => false,
		'permissions' => array(
			// Permissions required to perform action. The key is the moderation state
			// of the post to perform the action against. The value is a string or array
			// of user rights that can allow this action.
			PostRevision::MODERATED_NONE => 'flow-hide',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-hid-post',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getModeratedByUserText();
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
					return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
				},
			),
			'class' => 'flow-history-hid-post',
		),
	),

	'delete-post' => array(
		'log_type' => 'delete',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-delete',
			PostRevision::MODERATED_HIDDEN => 'flow-delete',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-deleted-post',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getModeratedByUserText();
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
					return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
				},
			),
			'class' => 'flow-history-deleted-post',
		),
	),

	'censor-post' => array(
		'log_type' => 'suppress',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-censor',
			PostRevision::MODERATED_HIDDEN => 'flow-censor',
			PostRevision::MODERATED_DELETED => 'flow-censor',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-censored-post',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getModeratedByUserText();
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
					return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
				},
			),
			'class' => 'flow-history-censored-post',
		),
	),

	'restore-post' => array(
		'log_type' => function( PostRevision $post, Logger $logger ) {
			// Kind of log depends on the previous change type:
			// * if post was deleted, restore should go to deletion log
			// * if post was suppressed, restore should go to suppression log
			global $wgFlowActions;
			return $wgFlowActions[$post->getModerationState() . '-post']['log_type'];
		},
		'permissions' => array(
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-censor' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
			PostRevision::MODERATED_CENSORED => 'flow-censor',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-restored-post',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getModeratedByUserText();
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
					return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
				},
			),
			'class' => 'flow-history-restored-post',
		),
	),

	'post-history' => array(
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => '',
			PostRevision::MODERATED_DELETED => '',
			PostRevision::MODERATED_CENSORED => 'flow-censor',
		),
		'button-method' => 'GET',
	),

	'view' => array(
		'log_type' => false, // don't log views
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-censor' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
			PostRevision::MODERATED_CENSORED => 'flow-censor',
		),
		'button-method' => 'GET',
		'history' => array() // views don't generate history
	),

	'reply' => array(
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
		'history' => array(
			'i18n-message' => 'flow-rev-message-reply',
			'i18n-params' => array(
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return $revision->getUserText( $user );
				},
				function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
					$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
					return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
				},
			),
			'class' => 'flow-history-reply',
			'bundle' => array(
				'i18n-message' => 'flow-rev-message-reply-bundle',
				'i18n-params' => array(
					function ( array $revisions, UrlGenerator $urlGenerator, User $user, Block $block ) {
						return array( 'num' => count( $revisions ) );
					}
				),
				'class' => 'flow-history-bundle',
			),
		),
	),
);
