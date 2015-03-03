<?php

use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\Header;
use Flow\RevisionActionPermissions;
use Flow\Log\ModerationLogger;
use Flow\Data\Listener\RecentChangesListener;

/**
 * Flow actions: key => value map with key being the action name.
 * The value consists of an array of these below keys (and appropriate values):
 * * performs-writes: Must be boolean true for any action that writes to the wiki.
 *     actions with this set will additionally require the core 'edit' permission.
 * * log_type: the Special:Log filter to save actions to; false means 'not logged'.
 * * rc_insert: whether or not to insert the write action into RC table.
 * * permissions: array of permissions, where each key is the existing post
 *     state and the value is the right required to execute the action.  A blank
 *     value means anyone can take the action.  However, an omitted key means
 *     no one can perform the action described by that key.
 * * links: the set of read links to generate and return in API responses
 * * actions: the set of write links to generate and return in API responses
 * * history: all history-related information:
 *   * i18n-message: the i18n message key for this change type
 *   * i18n-params: array of i18n parameters for the provided message (see
 *     AbstractFormatter::processParam phpdoc for more details)
 *   * class: classname to be added to the list-item for this changetype
 *   * bundle: array with, again, all of the above information if multiple types
 *     should be bundled (then the bundle i18n & class will be used to generate
 *     the list-item; clicking on it will reveal the individual history entries)
 * * watch: Used by the WatchTopicListener to auto-subscribe users to topics. Only
 *   value value currently is immediate.
 *   * immediate: watchlist the title in the current process
 * * rc_title: Either 'article' or 'owner' to select between Workflow::getArticleTitle
 *     or Workflow::getOwnerTitle being used as the related recentchanges entry on insert
 * * editcount: True to increment user's edit count for this action
 * * modules: Modules to insert with RL to html page for this action instead of the defaults
 * * moduleStyles: Style modules to insert with RL to html page for this action instead of the defaults
 */
$wgFlowActions = array(
	'create-header' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
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
		'editcount' => true,
	),

	'edit-header' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
		'links' => array( 'board-history', 'diff-header', 'workflow', 'header-revision' ),
		'actions' => array( 'edit-header', 'undo-edit-header' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-header',
			'i18n-params' => array(
				'user-links',
				'user-text',
			),
			'class' => 'flow-history-edit-header',
		),
		'handler-class' => 'Flow\Actions\EditHeaderAction',
		'editcount' => true,
	),

	// @todo this is almost copy/paste from edit-header except the handler-class. find
	// a way to share.
	'undo-edit-header' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			Header::MODERATED_NONE => '',
		),
		'links' => array( 'board-history', 'diff-header', 'workflow', 'header-revision' ),
		'actions' => array( 'edit-header', 'undo-edit-header' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-header',
			'i18n-params' => array(
				'user-links',
				'user-text',
			),
			'class' => 'flow-history-edit-header',
		),
		'handler-class' => 'Flow\Actions\UndoEditHeaderAction',
		'editcount' => true,
		// theis modules/moduleStyles is repeated in all the undo-* actions. Find a way to share.
		'modules' => array( 'ext.flow.undo' ),
		'moduleStyles' => array(
			'mediawiki.ui.button',
			'mediawiki.ui.input',
			'ext.flow.styles.base',
			'ext.flow.board.styles',
			'ext.flow.board.topic.styles',
		),
	),

	'create-topic-summary' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostSummary::MODERATED_NONE => '',
			PostSummary::MODERATED_LOCKED => array( 'flow-lock', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_SUPPRESSED => array( 'flow-suppress' ),
		),
		'links' => array( 'topic', 'topic-history', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-create-topic-summary',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-of-summary',
			),
			'class' => 'flow-history-create-topic-summary',
		),
		'editcount' => true,
	),

	'edit-topic-summary' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostSummary::MODERATED_NONE => '',
			PostSummary::MODERATED_LOCKED => array( 'flow-lock', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_SUPPRESSED => array( 'flow-suppress' ),
		),
		'links' => array( 'topic', 'topic-history', 'diff-post-summary', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'edit-topic-summary', 'lock-topic', 'restore-topic', 'undo-edit-topic-summary' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-topic-summary',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-of-summary',
			),
			'class' => 'flow-history-edit-topic-summary',
		),
		'handler-class' => 'Flow\Actions\EditTopicSummaryAction',
		'editcount' => true,
	),

	// @todo this is almost copy/paste from edit-topic-summary except the handler class. find a
	// way to share
	'undo-edit-topic-summary' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostSummary::MODERATED_NONE => '',
			PostSummary::MODERATED_LOCKED => array( 'flow-lock', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostSummary::MODERATED_SUPPRESSED => array( 'flow-suppress' ),
		),
		'links' => array( 'topic', 'topic-history', 'diff-post-summary', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'edit-topic-summary', 'lock-topic', 'restore-topic', 'undo-edit-topic-summary' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-edit-topic-summary',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'post-of-summary',
			),
			'class' => 'flow-history-edit-topic-summary',
		),
		'handler-class' => 'Flow\Actions\UndoEditTopicSummaryAction',
		'editcount' => true,
		'modules' => array( 'ext.flow.undo' ),
		'moduleStyles' => array(
			'mediawiki.ui.button',
			'mediawiki.ui.input',
			'ext.flow.styles.base',
			'ext.flow.board.styles',
			'ext.flow.board.topic.styles',
		),
	),

	'edit-title' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'links' => array( 'topic', 'topic-history', 'diff-post', 'topic-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-title', 'lock-topic', 'hide-topic', 'delete-topic', 'suppress-topic', 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
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
		'handler-class' => 'Flow\Actions\EditTitleAction',
		'watch' => array(
			'immediate' => array( 'Flow\\Data\\Listener\\ImmediateWatchTopicListener', 'getCurrentUser' ),
		),
		'editcount' => true,
	),

	// Normal posts are the 'reply' type.
	'new-topic' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'rc_title' => 'owner',
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'links' => array( 'topic-history', 'topic', 'post', 'topic-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-title', 'hide-topic', 'delete-topic', 'suppress-topic', 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
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
		'handler-class' => 'Flow\Actions\NewTopicAction',
		'watch' => array(
			'immediate' => array( 'Flow\\Data\\Listener\\ImmediateWatchTopicListener', 'getCurrentUser' ),
		),
		'editcount' => true,
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
		'root-permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'links' => array( 'post-history', 'topic-history', 'topic', 'post', 'diff-post', 'post-revision' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'restore-post', 'hide-post', 'delete-post', 'suppress-post', 'undo-edit-post' ),
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
		'handler-class' => 'Flow\Actions\EditPostAction',
		'watch' => array(
			'immediate' => array( 'Flow\\Data\\Listener\\ImmediateWatchTopicListener', 'getCurrentUser' ),
		),
		'editcount' => true,
	),

	// @todo this is almost (but not quite) copy/paste from 'edit-post'. find a way to share?
	'undo-edit-post' => array(
		'performs-writes' => true,
		'log_type' => false, // maybe?
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'root-permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'links' => array( 'post-history', 'topic-history', 'topic', 'post', 'diff-post', 'post-revision' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'restore-post', 'hide-post', 'delete-post', 'suppress-post', 'undo-edit-post' ),
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
		'handler-class' => 'Flow\Actions\UndoEditPostAction',
		'watch' => array(
			'immediate' => array( 'Flow\\Data\\Listener\\ImmediateWatchTopicListener', 'getCurrentUser' ),
		),
		'editcount' => true,
		'modules' => array( 'ext.flow.undo' ),
		'moduleStyles' => array(
			'mediawiki.ui.button',
			'mediawiki.ui.input',
			'ext.flow.styles.base',
			'ext.flow.board.styles',
			'ext.flow.board.topic.styles',
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
		'root-permissions' => array(
			// Can only hide within an unmoderated or hidden topic. This doesn't check for a specific
			// permissions because thats already done above in 'permissions', this just ensures the
			// topic is in an appropriate state.
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => '',
		),
		'links' => array( 'topic', 'post', 'post-history', 'topic-history', 'post-revision' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'restore-post', 'hide-post', 'delete-post', 'suppress-post' ),
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
		'links' => array( 'topic', 'post', 'topic-history', 'post-history', 'topic-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-title', 'restore-topic', 'hide-topic', 'delete-topic', 'suppress-topic' ),
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
		'links' => array( 'topic', 'post', 'post-history', 'topic-history', 'post-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'restore-post', 'hide-post', 'delete-post', 'suppress-post' ),
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
			PostRevision::MODERATED_LOCKED => array( 'flow-delete', 'flow-suppress' ),
		),
		'links' => array( 'topic', 'topic-history', 'topic-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-title', 'hide-topic', 'delete-topic', 'suppress-topic', 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
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
		'links' => array( 'topic', 'post', 'topic-history', 'post-revision' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'restore-post', 'hide-post', 'delete-post', 'suppress-post' ),
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
			PostRevision::MODERATED_LOCKED => 'flow-suppress',
		),
		'links' => array( 'topic', 'topic-history', 'topic-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-title', 'hide-topic', 'delete-topic', 'suppress-topic', 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
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

	'lock-topic' => array(
		'performs-writes' => true,
		'log_type' => 'lock',
		'rc_insert' => true,
		'permissions' => array(
			// Only non-moderated topic can be locked
			PostRevision::MODERATED_NONE => array( 'flow-lock', 'flow-delete', 'flow-suppress' ),
		),
		'links' => array( 'topic', 'topic-history', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'edit-topic-summary', 'restore-topic' ),
		'history' => array(
			'i18n-message' => 'flow-rev-message-locked-topic',
			'i18n-params' => array(
				'user-links',
				'user-text',
				'creator-text',
				'workflow-url',
				'moderated-reason',
				'topic-of-post',
			),
			'class' => 'flow-history-locked-topic',
		),
		'handler-class' => 'Flow\Actions\LockTopicAction',
	),

	'restore-post' => array(
		'performs-writes' => true,
		'log_type' => function( PostRevision $revision, ModerationLogger $logger ) {
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
		'rc_insert' => function( PostRevision $revision, RecentChangesListener $recentChanges ) {
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
		'links' => array( 'topic', 'post', 'post-history', 'post-revision' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'restore-post', 'hide-post', 'delete-post', 'suppress-post' ),
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
		'handler-class' => 'Flow\Actions\RestorePostAction',
	),

	'restore-topic' => array(
		'performs-writes' => true,
		'log_type' => function( PostRevision $revision, ModerationLogger $logger ) {
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
		'rc_insert' => function( PostRevision $revision, RecentChangesListener $recentChanges ) {
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
			PostRevision::MODERATED_LOCKED => array( 'flow-lock', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'links' => array( 'topic', 'topic-history', 'topic-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-title', 'hide-topic', 'delete-topic', 'suppress-topic', 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
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
		'handler-class' => 'Flow\Actions\RestoreTopicAction',
	),

	'view' => array(
		'performs-writes' => false,
		'log_type' => false, // don't log views
		'rc_insert' => false, // won't even be called, actually; only for writes
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			// Everyone has permission to see this, but hidden comments are only visible (collapsed) on permalinks directly to them.
			PostRevision::MODERATED_HIDDEN => '',
			PostRevision::MODERATED_LOCKED => '',
			PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-suppress' ),
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'links' => array(), // @todo
		'actions' => array(), // view is not a recorded change type, no actions will be requested
		'history' => array(), // views don't generate history
		'handler-class' => 'Flow\Actions\ViewAction',
	),

	'reply' => array(
		'performs-writes' => true,
		'log_type' => false,
		'rc_insert' => true,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'root-permissions' => array(
			PostRevision::MODERATED_NONE => '',
		),
		'links' => array( 'topic-history', 'topic', 'post', 'post-revision', 'watch-topic', 'unwatch-topic' ),
		'actions' => array( 'reply', 'thank', 'edit-post', 'hide-post', 'delete-post', 'suppress-post', 'edit-topic-summary', 'lock-topic', 'restore-topic' ),
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
		'handler-class' => 'Flow\Actions\ReplyAction',
		'watch' => array(
			'immediate' => array( 'Flow\\Data\\Listener\\ImmediateWatchTopicListener', 'getCurrentUser' ),
		),
		'editcount' => true,
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
						/** @var Flow\Collection\CollectionCache $cache */
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
			PostRevision::MODERATED_LOCKED => '',
			PostRevision::MODERATED_DELETED => '',
			PostRevision::MODERATED_SUPPRESSED => 'flow-suppress',
		),
		'history' => array(), // views don't generate history
		'handler-class' => 'Flow\Actions\HistoryAction',
	),

	// Pseudo-action to determine when to show thank links,
	// currently no limitation. if you can see revision you
	// can thank.
	'thank' => array(
		'performs-writes' => false,
		'permissions' => array(
			PostRevision::MODERATED_NONE => '',
			PostRevision::MODERATED_HIDDEN => '',
			PostRevision::MODERATED_LOCKED => '',
			PostRevision::MODERATED_DELETED => '',
			PostRevision::MODERATED_SUPPRESSED => '',
		),
	),

	// Actions not tied to a particular revision change_type
	// or just move these to a different file
	'compare-header-revisions' => array(
		'handler-class' => 'Flow\Actions\CompareHeaderRevisionsAction',
	),
	'view-header' => array(
		'handler-class' => 'Flow\Actions\ViewHeaderAction',
	),
	'compare-post-revisions' => array(
		'handler-class' => 'Flow\Actions\ComparePostRevisionsAction',
	),
	// @todo - This is a very bad action name, consolidate with view-post action
	'single-view' => array(
		'handler-class' => 'Flow\Actions\PostSingleViewAction',
	),
	'view-topic-summary' => array(
		'handler-class' => 'Flow\Actions\ViewTopicSummaryAction',
	),
	'compare-postsummary-revisions' => array(
		'handler-class' => 'Flow\Actions\ComparePostSummaryRevisionsAction',
	),
	'moderate-topic' => array(
		'handler-class' => 'Flow\Actions\ModerateTopicAction',
	),
	'moderate-post' => array(
		'handler-class' => 'Flow\Actions\ModeratePostAction',
	),
	'purge' => array(
		'handler-class' => 'Flow\Actions\PurgeAction',
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
	'flow-rev-message-new-post' => 'new-topic',
	'flow-new-post' => 'new-topic',
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

	// The new-topic type used to be called new-post
	'new-post' => 'new-topic',

	// BC for lock-topic, which used to be called differently
	'close-topic' => 'lock-topic',
	'close-open-topic' => 'lock-topic',
);
