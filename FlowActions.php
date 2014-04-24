<?php

use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\Header;
use Flow\RevisionActionPermissions;
use Flow\Log\Logger;
use Flow\Data\RecentChanges;

/**
 * Flow actions: key => value map with key being the action name.
 * The value consists of an array of these below keys (and appropriate values):
 * * performs-writes: Must be boolean true for any action that writes to the wiki.
 *     actions with this set will additionally require the core 'edit' permission.
 * * log_type: the Special:Log filter to save actions to.
 * * rc_insert: whether or not to insert the write action into RC table.
 * * permissions: array of permissions, where each key is the existing post
 *   state and value is the action required to execute the action.
 * * button-method: used in PostActionMenu, to generate GET (a) or POST (form)
 *   links for the action.
 * * links: the set of links to generate and return in api responses
 * * history: all history-related information:
 *   * i18n-message: the i18n message key for this change type
 *   * i18n-params: array of i18n parameters for the provided message (see
 *     AbstractFormatter::processParam phpdoc for more details)
 *   * class: classname to be added to the list-item for this changetype
 *   * bundle: array with, again, all of the above information if multiple types
 *     should be bundled (then the bundle i18n & class will be used to generate
 *     the list-item; clicking on it will reveal the individual history entries)
 */
$wgFlowActions = array(
	'create-header' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
		'button-method' => 'POST',
		'links' => array( 'board-history', 'workflow', 'header-revision' ),
		'actions' => array( 'edit-header' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-create-header',
			'i18n-params' => array(
				'user-links',
				'user-text',
			),
			'class' => 'flow-history-create-header',
		),
	),

	'edit-header' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
		'button-method' => 'POST',
		'links' => array( 'board-history', 'diff-header', 'workflow', 'header-revision' ),
		'actions' => array( 'edit-header' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-header',
			'i18n-params' => array(
				'user-links',
				'user-text',
			),
			'class' => 'flow-history-edit-header',
		),
	),

	'create-topic-summary' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostSummary::MODERATED_NONE => '',
			PostSummary::MODERATED_CLOSED => array( 'flow-hide', 'flow-close', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_SUPPRESSED => array( 'flow-suppress' ),
		),
		'button-method' => 'GET',
		'links' => array( 'topic', 'topic-history' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-create-topic-summary',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-of-summary',
			),
			'class' => 'flow-history-create-topic-summary',
		),
	),

	'edit-topic-summary' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostSummary::MODERATED_NONE => '',
			PostSummary::MODERATED_CLOSED => array( 'flow-hide', 'flow-close', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_SUPPRESSED => array( 'flow-suppress' ),
		),
		'button-method' => 'GET',
		'links' => array( 'topic', 'topic-history', 'diff-post-summary' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-topic-summary',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-of-summary',
			),
			'class' => 'flow-history-edit-topic-summary',
		),
	),

	'edit-title' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
		'links' => array( 'topic', 'topic-history', 'diff-post', 'topic-revision' ),
		'actions' => array( 'edit-title', 'lock-topic', 'hide-topic', 'delete-topic', 'suppress-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-title',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'workflow-url',
				'wikitext',
				'prev-wikitext',
			),
			'class' => 'flow-history-edit-title',
		),
	),

	// The name 'new-post' is perhaps deceiving, this is always the topic title.
	// Normal posts are the 'reply' type.
	'new-post' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'POST',
		'links' => array( 'topic-history', 'topic', 'post', 'topic-revision' ),
		'actions' => array( 'reply', 'edit-topic', 'hide-topic', 'delete-topic', 'supress-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-new-post',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'workflow-url',
				'wikitext',
			),
			'class' => 'flow-history-new-post',
		),
	),

	'edit-post' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			// no permissions needed for own posts
			PostRevision::MODERATED_NONE => function( PostRevision $post, RevisionActionPermissions $permissions ) {
					return $post->isCreator( $permissions->getUser() ) ? '' : 'flow-edit-post';
				}
		),
		'button-method' => 'GET',
		'links' => array( 'post-history', 'topic', 'post', 'diff-post', 'post-revision' ),
		'actions' => array( 'reply', 'edit-post', 'hide-post', 'delete-post', 'supress-post' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-post',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-url',
				'topic-of-post',
			),
			'class' => 'flow-history-edit-post',
		),
	),

	'hide-post' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			// Permissions required to perform action. The key is the moderation state
			// of the post to perform the action against. The value is a string or array
			// of user rights that can allow this action.
			PostRevision::MODERATED_NONE => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'post-history', 'post-revision' ),
		'actions' => array( 'reply', 'edit-post', 'hide-post', 'delete-post', 'supress-post' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-hid-post',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'post-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-hide-post',
		),
	),

	'hide-topic' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'post', 'post-history', 'topic-revision' ),
		'actions' => array( 'reply', 'edit-topic', 'hide-topic', 'delete-topic', 'supress-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-hid-topic',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'workflow-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-hide-topic',
		),
	),

	'delete-post' => array(
		'performs-writes' => true,
		'log_type' => 'delete',
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_HIDDEN => array( 'flow-delete', 'flow-suppress' ),
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'post-history', 'post-revision' ),
		'actions' => array( 'reply', 'edit-post', 'hide-post', 'delete-post', 'supress-post' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-deleted-post',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'post-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-delete-post',
		),
	),

	'delete-topic' => array(
		'performs-writes' => true,
		'log_type' => 'delete',
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_HIDDEN => array( 'flow-delete', 'flow-suppress' ),
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'topic-history', 'topic-revision' ),
		'actions' => array( 'reply', 'edit-topic', 'hide-topic', 'delete-topic', 'supress-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-deleted-topic',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'workflow-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-delete-topic',
		),
	),

	'suppress-post' => array(
		'performs-writes' => true,
		'log_type' => 'suppress',
		'rc_insert' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-suppress',
			PostRevision::MODERATED_HIDDEN => 'flow-suppress',
			PostRevision::MODERATED_DELETED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'topic-history', 'post-revision' ),
		'actions' => array( 'reply', 'edit-post', 'hide-post', 'delete-post', 'supress-post' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-suppressed-post',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'post-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-suppress-post',
		),
	),

	'suppress-topic' => array(
		'performs-writes' => true,
		'log_type' => 'suppress',
		'rc_insert' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => 'flow-suppress',
			PostRevision::MODERATED_HIDDEN => 'flow-suppress',
			PostRevision::MODERATED_DELETED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'topic-history', 'topic-revision' ),
		'actions' => array( 'reply', 'edit-topic', 'hide-topic', 'delete-topic', 'supress-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-suppressed-topic',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'workflow-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-suppress-topic',
		),
	),

	'close-topic' => array(
		'performs-writes' => true,
		'log_type' => 'close',
		'rc_insert' => true,
		'permissions' => array(
			// Only non-moderated topic can be closed
			PostRevision::MODERATED_NONE => array( 'flow-hide', 'flow-close', 'flow-delete', 'flow-suppress' ),
		),
		'button-method' => 'GET',
		'links' => array( 'topic', 'topic-history' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-closed-topic',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'workflow-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-closed-topic',
		),
	),

	'restore-post' => array(
		'performs-writes' => true,
		'log_type' => function( PostRevision $revision, Logger $logger ) {
			$post = $revision->getCollection();
			$previousRevision = $post->getPrevRevision( $revision );
			if ( $previousRevision ) {
				// Kind of log depends on the previous change type:
				// * if post was deleted, restore should go to deletion log
				// * if post was suppressed, restore should go to suppression log
				global $wgFlowActions;
				return $wgFlowActions[$previousRevision->getModerationState() . '-post']['log_type'];
			}

			return '';
		},
		'rc_insert' => function( PostRevision $revision, RecentChanges $recentChanges ) {
			$post = $revision->getCollection();
			$previousRevision = $post->getPrevRevision( $revision );
			if ( $previousRevision ) {
				// * if post was hidden/deleted, restore can go to RC
				// * if post was suppressed, restore can not go to RC
				global $wgFlowActions;
				return $wgFlowActions[$previousRevision->getModerationState() . '-post']['rc_insert'];
			}

			return true;
		},
		'permissions' => array(
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'post-history', 'post-revision' ),
		'actions' => array( 'reply', 'edit-post', 'hide-post', 'delete-post', 'supress-post' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-restored-post',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'post-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => function( PostRevision $revision ) {
				$previous = $revision->getCollection()->getPrevRevision( $revision );
				$state = $previous->getModerationState();
				return "flow-history-un$state-post";
			}
		),
	),

	'restore-topic' => array(
		'performs-writes' => true,
		'log_type' => function( PostRevision $revision, Logger $logger ) {
			$post = $revision->getCollection();
			$previousRevision = $post->getPrevRevision( $revision );
			if ( $previousRevision ) {
				// Kind of log depends on the previous change type:
				// * if topic was deleted, restore should go to deletion log
				// * if topic was suppressed, restore should go to suppression log
				global $wgFlowActions;
				return $wgFlowActions[$previousRevision->getModerationState() . '-topic']['log_type'];
			}

			return '';
		},
		'rc_insert' => function( PostRevision $revision, RecentChanges $recentChanges ) {
				$post = $revision->getCollection();
				$previousRevision = $post->getPrevRevision( $revision );
				if ( $previousRevision ) {
					// * if topic was hidden/deleted, restore can go to RC
					// * if topic was suppressed, restore can not go to RC
					global $wgFlowActions;
					return $wgFlowActions[$previousRevision->getModerationState() . '-topic']['rc_insert'];
				}

				return true;
			},
		'permissions' => array(
			PostRevision::MODERATED_CLOSED => array( 'flow-hide', 'flow-close', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'POST',
		'links' => array( 'topic', 'topic-history', 'topic-revision' ),
		'actions' => array( 'reply', 'edit-topic', 'hide-topic', 'delete-topic', 'supress-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-restored-topic',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'workflow-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => function( PostRevision $revision ) {
				$previous = $revision->getCollection()->getPrevRevision( $revision );
				$state = $previous->getModerationState();
				return "flow-history-un$state-topic";
			}
		),
	),

	'view' => array(
		'performs-writes' => false,
		'log_type' => false, // don't log views
		'rc_insert' => false, // won't even be called, actually; only for writes
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => function( AbstractRevision $post, RevisionActionPermissions $permissions ) {
				// visible for logged in users (or anyone with hide/close permission)
				return $permissions->getUser()->isLoggedIn() ? '' : array( 'flow-hide' );
			},
			PostRevision::MODERATED_CLOSED => '',
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'GET',
		'links' => array(), // @todo
		'actions' => array(), // view is not a recorded change type, no actions will be requested
		'history' => array() // views don't generate history
	),

	'reply' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'button-method' => 'GET',
		'links' => array( 'topic-history', 'topic', 'post', 'post-revision' ),
		'actions' => array( 'reply', 'edit-post', 'hide-post', 'delete-post', 'supress-post' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-reply',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-url',
				'topic-of-post',
				'summary',
			),
			'class' => 'flow-history-reply',
			'bundle' => array(
				'i18n-message' => 'flow-rev-message-reply-bundle',
				'i18n-params' => array(
					'bundle-count'
				),
				'class' => 'flow-history-bundle',
			),
		),
	),

	'history' => array(
		'performs-writes' => false,
		'log_type' => false,
		'rc_insert' => false, // won't even be called, actually; only for writes
		'permissions' => array(
			PostRevision::MODERATED_NONE => function( AbstractRevision $revision, RevisionActionPermissions $permissions ) {
				static $previousCollectionId;

				/*
				 * To check permissions, both the current revision (revision-
				 * specific moderation state)& the last revision (global
				 * collection moderation state) will always be checked.
				 * This one has special checks to make sure "restore" actions
				 * are hidden when the user has no permissions to see the
				 * moderation state they were restored from.
				 * We don't want that test to happen; otherwise, when a post
				 * has just been restored in the most recent revisions, that
				 * would result in none of the previous revisions being
				 * available (because a user would need permissions for the the
				 * state the last revision was restored from)
				 */
				$collection = $revision->getCollection();
				if ( $previousCollectionId && $collection->getId()->equals( $previousCollectionId ) ) {
					// doublecheck that this run is indeed against the most
					// recent revision, to get the global collection state
					try {
						/** @var CollectionCache $cache */
						$cache = \Flow\Container::get( 'collection.cache' );
						$lastRevision = $cache->getLastRevisionFor( $revision );
						if ( $revision->getRevisionId()->equals( $lastRevision->getRevisionId() ) ) {
							$previousCollectionId = null;
							return '';
						}
					} catch ( Exception $e ) {
						// nothing to do here; if fetching last revision failed,
						// we're just not testing any stored revision; that's ok
					}
				}
				$previousCollectionId = $collection->getId();

				/*
				 * If a revision was the result of a restore-action, we have
				 * to look at the previous revision what the original moderation
				 * status was; permissions for the restore-actions visibility
				 * is the same as the moderation (e.g. if user can't see
				 * suppress actions, he can't see restores from suppress.
				 */
				if ( strpos( $revision->getChangeType(), 'restore-' ) === 0 ) {
					$previous = $collection->getPrevRevision( $revision );

					if ( $previous === null || $previous->getModerationState() === AbstractRevision::MODERATED_NONE ) {
						return '';
					}

					return $permissions->getPermission( $previous, 'history' );
				}

				return '';
			},
			PostRevision::MODERATED_HIDDEN => '',
			PostRevision::MODERATED_CLOSED => '',
			PostRevision::MODERATED_DELETED => '',
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'button-method' => 'GET',
		'history' => array() // views don't generate history
	),

	// log & all other formatters have same config as history
	'log' => 'history',
	'recentchanges' => 'history',
	'contributions' => 'history',
	'checkuser' => 'history',

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
	/*
	 * Backwards compatibility for old (separated) history actions
	 */
	'post-history' => 'history',
	'topic-history' => 'history',
	'board-history' => 'history',
);
