<?php

use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\Log\Logger;
use Flow\Block\Block;
use Flow\Templating;
use Flow\Container;
use \Message;

/**
 * Flow actions: key => value map with key being the action name.
 * The value consists of an array of these below keys (and appropriate values):
 * * performs-write: Must be boolean true for any action that writes to the wiki.
 *     actions with this set will additionally require the core 'edit' permission.
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
		'performs-writes' => true,
		'log_type' => false,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-create-header',
			'i18n-params' => array(
				function ( Header $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( Header $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
			),
			'class' => 'flow-history-create-header',
		),
	),

	'edit-header' => array(
		'performs-writes' => true,
		'log_type' => false,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-header',
			'i18n-params' => array(
				function ( Header $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( Header $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
			),
			'class' => 'flow-history-edit-header',
		),
	),

	'edit-title' => array(
		'performs-writes' => true,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-title',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $revision->getPostId() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					// make sure topic title isn't parsed
					$content = $templating->getContent( $revision, 'wikitext' );
					return array( 'raw' => htmlspecialchars( $content ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					$previousId = $revision->getPrevRevisionId();
					if ( $previousId ) {
						$previousRevision = Container::get( 'storage' )->get( get_class( $revision ), $previousId );
						// make sure topic title isn't parsed
						$content = $templating->getContent( $previousRevision, 'wikitext' );
						return array( 'raw' => htmlspecialchars( $content ) );
					}

					return '';
				},
			),
			'class' => 'flow-history-edit-title',
		),
	),

	'new-post' => array(
		'performs-writes' => true,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-new-post',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $revision->getPostId() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					// make sure topic title isn't parsed
					$content = $templating->getContent( $revision, 'wikitext' );
					return array( 'raw' => htmlspecialchars( $content ) );
				},
			),
			'class' => 'flow-history-new-post',
		),
	),

	'edit-post' => array(
		'performs-writes' => true,
		'log_type' => false,
		'permissions' => array(
			// no permissions needed for own posts
			PostRevision::MODERATED_NONE => function( PostRevision $post, RevisionActionPermissions $permissions ) {
					return $post->isCreator( $permissions->getUser() ) ? '' : 'flow-edit-post';
				}
		),
		'button-method' => 'GET',
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-post',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array(), 'flow-post-' . $revision->getPostId()->getHex() );
				},
			),
			'class' => 'flow-history-edit-post',
		),
	),

	'hide-post' => array(
		'performs-writes' => true,
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
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					$fragment = '';
					$permissions = $templating->getActionPermissions();
					if ( $permissions->isAllowed( $revision, 'view' ) ) {
						$fragment = 'flow-post-' . $revision->getPostId()->getHex();
					}
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array(), $fragment );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-hid-post',
		),
	),

	'hide-topic' => array(
		'performs-write' => true,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-hide',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-hid-topic',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-hid-topic',
		),
	),

	'delete-post' => array(
		'performs-writes' => true,
		'log_type' => 'delete',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-delete',
			PostRevision::MODERATED_HIDDEN => 'flow-delete',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-deleted-post',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					$fragment = '';
					$permissions = $templating->getActionPermissions();
					if ( $permissions->isAllowed( $revision, 'view' ) ) {
						$fragment = 'flow-post-' . $revision->getPostId()->getHex();
					}
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array(), $fragment );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-deleted-post',
		),
	),

	'delete-topic' => array(
		'performs-write' => true,
		'log_type' => 'delete',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-delete',
			PostRevision::MODERATED_HIDDEN => 'flow-delete',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-deleted-topic',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-deleted-topic',
		),
	),

	'suppress-post' => array(
		'performs-writes' => true,
		'log_type' => 'suppress',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-suppress',
			PostRevision::MODERATED_HIDDEN => 'flow-suppress',
			PostRevision::MODERATED_DELETED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-suppressed-post',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					$fragment = '';
					$permissions = $templating->getActionPermissions();
					if ( $permissions->isAllowed( $revision, 'view' ) ) {
						$fragment = 'flow-post-' . $revision->getPostId()->getHex();
					}
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array(), $fragment );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-suppressed-post',
		),
	),

	'suppress-topic' => array(
		'performs-write' => true,
		'log_type' => 'suppress',
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-suppress',
			PostRevision::MODERATED_HIDDEN => 'flow-suppress',
			PostRevision::MODERATED_DELETED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-suppressed-topic',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-suppressed-topic',
		),
	),

	'restore-post' => array(
		'performs-writes' => true,
		'log_type' => function( PostRevision $post, Logger $logger ) {
			// Kind of log depends on the previous change type:
			// * if post was deleted, restore should go to deletion log
			// * if post was suppressed, restore should go to suppression log
			global $wgFlowActions;
			return $wgFlowActions[$post->getModerationState() . '-post']['log_type'];
		},
		'permissions' => array(
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-restored-post',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array(), 'flow-post-' . $revision->getPostId()->getHex() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-restored-post',
		),
	),

	'restore-topic' => array(
		'performs-write' => true,
		'log_type' => function( PostRevision $topicTitle, Logger $logger ) {
			// Kind of log depends on the previous change type:
			// * if topic was deleted, restore should go to deletion log
			// * if topic was suppressed, restore should go to suppression log
			global $wgFlowActions;
			return $wgFlowActions[$topicTitle->getModerationState() . '-topic']['log_type'];
		},
		'permissions' => array(
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'history' => array(
			'i18n-message' => 'flow-rev-message-restored-topic',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getCreatorText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', array() );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return array( 'raw' => htmlspecialchars( $revision->getModeratedReason() ) );
				},
			),
			'class' => 'flow-history-restored-topic',
		),
	),

	'post-history' => array(
		'performs-writes' => false,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => '',
			PostRevision::MODERATED_DELETED => '',
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'GET',
	),

	'topic-history' => array(
		'performs-writes' => false,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => '',
			PostRevision::MODERATED_DELETED => '',
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'GET',
	),

	'board-history' => array(
		'performs-writes' => false,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => function( PostRevision $post, RevisionActionPermissions $permissions ) {
					// visible for logged in users (or anyone with hide permission)
					return $permissions->getUser()->isLoggedIn() ? '' : 'flow-hide';
				},
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'GET',
	),

	'view' => array(
		'performs-writes' => false,
		'log_type' => false, // don't log views
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => function( PostRevision $post, RevisionActionPermissions $permissions ) {
					// visible for logged in users (or anyone with hide permission)
					return $permissions->getUser()->isLoggedIn() ? '' : 'flow-hide';
				},
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'GET',
		'history' => array() // views don't generate history
	),

	'reply' => array(
		'performs-writes' => true,
		'log_type' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
		'history' => array(
			'i18n-message' => 'flow-rev-message-reply',
			'i18n-params' => array(
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return Message::rawParam( $templating->getUserLinks( $revision ) );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					return $templating->getUserText( $revision );
				},
				function ( PostRevision $revision, Templating $templating, User $user, UUID $workflowId, $blockType ) {
					$data = array( $blockType . '[postId]' => $revision->getPostId()->getHex() );
					return $templating->getUrlGenerator()->generateUrl( $workflowId, 'view', $data, 'flow-post-' . $revision->getPostId()->getHex() );
				},
			),
			'class' => 'flow-history-reply',
			'bundle' => array(
				'i18n-message' => 'flow-rev-message-reply-bundle',
				'i18n-params' => array(
					function ( array $revisions, Templating $templating, User $user, UUID $workflowId, $blockType ) {
						return array( 'num' => count( $revisions ) );
					}
				),
				'class' => 'flow-history-bundle',
			),
		),
	),

	/*
	 * Backwards compatibility; these are old values that may have made their
	 * way into the database. patch-rev_change_type_update.sql should take care
	 * of these, but just to be sure ;)
	 * Instead of having the correct config-array as value, you can just
	 * reference another action.
	 */
	'flow-rev-message-edit-title' => 'edit-title',
	'flow-edit-title' => 'edit-title',
	'flow-rev-message-new-post' => 'new-post',
	'flow-new-post' => 'new-post',
	'flow-rev-message-edit-post' => 'edit-post',
	'flow-edit-post' => 'edit-post',
	'flow-rev-message-reply' => 'reply',
	'flow-reply' => 'reply',
	'flow-rev-message-restored-post' => 'restore-post',
	'flow-post-restored' => 'restore-post',
	'flow-rev-message-hid-post' => 'hide-post',
	'flow-post-hidden' => 'hide-post',
	'flow-rev-message-deleted-post' => 'delete-post',
	'flow-post-deleted' => 'delete-post',
	'flow-rev-message-censored-post' => 'suppress-post',
	'flow-post-censored' => 'suppress-post',
	'flow-rev-message-edit-header' => 'edit-header',
	'flow-edit-summary' => 'edit-header',
	'flow-rev-message-create-header' => 'create-header',
	'flow-create-summary' => 'create-header',
	'flow-create-header' => 'create-header',
	/*
	 * Backwards compatibility for previous suppression terminology (=censor).
	 * patch-censor_to_suppress.sql should take care of all of these occurrences.
	 */
	'censor-post' => 'suppress-post',
	'censor-topic' => 'suppress-topic',
);
