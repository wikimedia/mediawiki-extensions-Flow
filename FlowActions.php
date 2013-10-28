<?php

use Flow\Model\PostRevision;
use Flow\PostActionPermissions;
use Flow\Log\Logger;

/**
 * Flow actions: key => value map with key being the action name.
 * The value consists of an array of these below keys (and appropriate values):
 * * log_type: the Special:Log filter to save actions to.
 * * permissions: array of permissions, where each key is the existing post
 *   state and value is the action required to execute the action.
 * * button-method: used in PostActionMenu, to generate GET (a) or POST (form)
 *   links for the action.
 */
$wgFlowActions = array(
	'hide-post' => array(
		'log_type' => false,
		'permissions' => array(
			// Permissions required to perform action. The key is the moderation state
			// of the post to perform the action against. The value is a string or array
			// of user rights that can allow this action.
			PostRevision::MODERATED_NONE => 'flow-hide',
		),
		'button-method' => 'POST',
	),
	'delete-post' => array(
		'log_type' => 'delete',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-delete',
			PostRevision::MODERATED_HIDDEN => 'flow-delete',
		),
		'button-method' => 'POST',
	),
	'censor-post' => array(
		'log_type' => 'suppress',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-censor',
			PostRevision::MODERATED_HIDDEN => 'flow-censor',
			PostRevision::MODERATED_DELETED => 'flow-censor',
		),
		'button-method' => 'POST',
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
	'edit-post' => array(
		'log_type' => false,
		'permissions' => array(
			// no permissions needed for own posts
			PostRevision::MODERATED_NONE => function( PostRevision $post, PostActionPermissions $permissions ) {
				return $post->isCreator( $permissions->user ) ? '' : 'flow-edit-post';
			}
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
	),
	'reply' => array(
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
	),
	'edit-title' => array(
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
	),
);
