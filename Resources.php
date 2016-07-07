<?php

$mobile = array(
	'targets' => array( 'desktop', 'mobile' ),
);

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
);

$wgResourceModules += array(
	'ext.flow.contributions' => $flowResourceTemplate + array(
		'scripts' => array(
			'contributions/base.js',
		),
	),
	'ext.flow.contributions.styles' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/history/history-line.less',
		),
	),
	'ext.flow.templating' => array(
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'Flow',
		'scripts' => array(
			'modules/engine/misc/flow-handlebars.js',
		),
		'dependencies' => array(
			'mediawiki.template.handlebars',
			'moment',
		),
		'templates' => array(
			'handlebars/flow_anon_warning.partial.handlebars',
			'handlebars/flow_block_board-history.handlebars',
			'handlebars/flow_block_header.handlebars',
			'handlebars/flow_block_header_diff_view.handlebars',
			'handlebars/flow_block_header_edit.handlebars',
			'handlebars/flow_block_header_single_view.handlebars',
			'handlebars/flow_block_loop.handlebars',
			'handlebars/flow_block_topic.handlebars',
			'handlebars/flow_block_topic_diff_view.handlebars',
			'handlebars/flow_block_topic_edit_title.handlebars',
			'handlebars/flow_block_topic_history.handlebars',
			'handlebars/flow_block_topic_moderate_post.handlebars',
			'handlebars/flow_block_topic_moderate_topic.handlebars',
			'handlebars/flow_block_topic_single_view.handlebars',
			'handlebars/flow_block_topiclist.handlebars',
			'handlebars/flow_block_topicsummary_diff_view.handlebars',
			'handlebars/flow_block_topicsummary_edit.handlebars',
			'handlebars/flow_block_topicsummary_single_view.handlebars',
			'handlebars/flow_board_navigation.partial.handlebars',
			'handlebars/flow_edit_post.partial.handlebars',
			'handlebars/flow_edit_topic_title.partial.handlebars',
			'handlebars/flow_editor_switcher.partial.handlebars',
			'handlebars/flow_errors.partial.handlebars',
			'handlebars/flow_form_cancel_button.partial.handlebars',
			'handlebars/flow_header_title.partial.handlebars',
			'handlebars/flow_header_detail.partial.handlebars',
			'handlebars/flow_header_categories.partial.handlebars',
			'handlebars/flow_header_footer.partial.handlebars',
			// HACK: Get rid of this when the description uses js ooui widgets
			'handlebars/flow_header_detail_oldsystem.partial.handlebars',
			'handlebars/flow_load_more.partial.handlebars',
			'handlebars/flow_moderate_post_confirmation.partial.handlebars',
			'handlebars/flow_moderate_post.partial.handlebars',
			'handlebars/flow_moderate_topic_confirmation.partial.handlebars',
			'handlebars/flow_moderate_topic.partial.handlebars',
			'handlebars/flow_moderation_actions_list.partial.handlebars',
			'handlebars/flow_newtopic_form.partial.handlebars',
			'handlebars/flow_post_actions.partial.handlebars',
			'handlebars/flow_post_author.partial.handlebars',
			'handlebars/flow_post_inner.partial.handlebars',
			'handlebars/flow_post_meta_actions.partial.handlebars',
			'handlebars/flow_post_moderation_state.partial.handlebars',
			'handlebars/flow_post_replies.partial.handlebars',
			'handlebars/flow_post_partial.partial.handlebars',
			'handlebars/flow_post.handlebars',
			'handlebars/flow_reply_form.partial.handlebars',
			'handlebars/flow_subscribed.partial.handlebars',
			'handlebars/flow_tooltip_subscribed.partial.handlebars',
			'handlebars/flow_tooltip.handlebars',
			'handlebars/flow_topic.partial.handlebars',
			'handlebars/flow_topic_titlebar_content.partial.handlebars',
			'handlebars/flow_topic_titlebar_lock.partial.handlebars',
			'handlebars/flow_topic_titlebar_summary.partial.handlebars',
			'handlebars/flow_topic_titlebar_watch.partial.handlebars',
			'handlebars/flow_topic_titlebar.partial.handlebars',
			'handlebars/flow_topic_moderation_flag.partial.handlebars',
			'handlebars/flow_topiclist_loop.partial.handlebars',
			'handlebars/form_element.partial.handlebars',
			'handlebars/timestamp.handlebars',
		),
		'messages' => array(
			'flow-anon-warning',
			'flow-cancel',
			'flow-skip-summary',
			'flow-edit-summary-placeholder',
			'flow-summary-authored',
			'flow-summary-edited',
			'flow-board-header',
			'flow-board-collapse-description',
			'flow-board-expand-description',
			'flow-edit-header-link',
			'flow-edit-header-placeholder',
			'flow-edit-header-submit',
			'flow-edit-header-submit-anonymously',
			'flow-edit-title-submit',
			'flow-edit-title-submit-anonymously',
			'flow-edit-post-submit',
			'flow-edit-post-submit-anonymously',
			'flow-load-more',
			'flow-newest-topics',
			'flow-newtopic-content-placeholder',
			'flow-newtopic-save',
			'flow-newtopic-save-anonymously',
			'flow-newtopic-start-placeholder',
			'flow-post-action-delete-post',
			'flow-post-action-undelete-post',
			'flow-post-action-edit-post',
			'flow-post-action-edit-post-submit',
			'flow-post-action-edit-post-submit-anonymously',
			'flow-post-action-hide-post',
			'flow-post-action-unhide-post',
			'flow-post-action-post-history',
			'flow-post-action-view',
			'flow-post-action-suppress-post',
			'flow-post-action-unsuppress-post',
			'flow-post-action-restore-post',
			'flow-post-action-undo-moderation',
			'flow-recent-topics',
			'flow-reply-topic-title-placeholder',
			'flow-sorting-tooltip-newest',
			'flow-sorting-tooltip-recent',
			'flow-toggle-small-topics',
			'flow-toggle-topics',
			'flow-toggle-topics-posts',
			'flow-topic-collapse-siderail',
			'flow-topic-comments',
			'flow-topic-expand-siderail',
			'flow-show-comments-title',
			'flow-hide-comments-title',
			'flow-topic-action-hide-topic',
			'flow-topic-action-lock-topic',
			'flow-topic-action-delete-topic',
			'flow-topic-action-edit-title',
			'flow-topic-action-hide-topic',
			'flow-topic-action-history',
			'flow-topic-action-resummarize-topic',
			'flow-topic-action-summarize-topic',
			'flow-topic-action-update-topic-summary',
			'flow-topic-action-unlock-topic',
			'flow-topic-action-suppress-topic',
			'flow-topic-action-view',
			'flow-topic-action-hide-topic',
			'flow-topic-action-unhide-topic',
			'flow-topic-action-delete-topic',
			'flow-topic-action-undelete-topic',
			'flow-topic-action-suppress-topic',
			'flow-topic-action-unsuppress-topic',
			'flow-topic-action-restore-topic',
			'flow-topic-action-undo-moderation',
			'flow-topic-action-watchlist-add',
			'flow-topic-action-watchlist-remove',
			'flow-hide-post-content',
			'flow-delete-post-content',
			'flow-suppress-post-content',
			'flow-hide-title-content',
			'flow-delete-title-content',
			'flow-suppress-title-content',
			'talkpagelinktext',
			'flow-cancel-warning',
			// Moderation state
			'flow-hide-title-content',
			'flow-hide-post-content',
			'flow-delete-title-content',
			'flow-delete-post-content',
			'flow-suppress-title-content',
			'flow-suppress-post-content',
			// Core messages needed
			'blocklink',
			'contribslink',
			// Terms of use
			'flow-terms-of-use-new-topic',
			'flow-terms-of-use-reply',
			'flow-terms-of-use-edit',
			'flow-terms-of-use-summarize',
			'flow-terms-of-use-lock-topic',
			'flow-terms-of-use-unlock-topic',
			'flow-no-more-fwd',
			// Tooltip
			'flow-topic-notification-subscribe-title',
			'flow-topic-notification-subscribe-description',
			'flow-board-notification-subscribe-title',
			'flow-board-notification-subscribe-description',
			// Moderation
			'flow-moderation-title-unhide-post',
			'flow-moderation-title-undelete-post',
			'flow-moderation-title-unsuppress-post',
			'flow-moderation-title-unhide-topic',
			'flow-moderation-title-undelete-topic',
			'flow-moderation-title-unsuppress-topic',
			'flow-moderation-title-hide-post',
			'flow-moderation-title-delete-post',
			'flow-moderation-title-suppress-post',
			'flow-moderation-title-hide-topic',
			'flow-moderation-title-delete-topic',
			'flow-moderation-title-suppress-topic',
			'flow-moderation-placeholder-unhide-post',
			'flow-moderation-placeholder-undelete-post',
			'flow-moderation-placeholder-unsuppress-post',
			'flow-moderation-placeholder-unhide-topic',
			'flow-moderation-placeholder-undelete-topic',
			'flow-moderation-placeholder-unsuppress-topic',
			'flow-moderation-placeholder-hide-post',
			'flow-moderation-placeholder-delete-post',
			'flow-moderation-placeholder-suppress-post',
			'flow-moderation-placeholder-hide-topic',
			'flow-moderation-placeholder-delete-topic',
			'flow-moderation-placeholder-suppress-topic',
			'flow-moderation-confirm-unhide-post',
			'flow-moderation-confirm-undelete-post',
			'flow-moderation-confirm-unsuppress-post',
			'flow-moderation-confirm-unhide-topic',
			'flow-moderation-confirm-undelete-topic',
			'flow-moderation-confirm-unsuppress-topic',
			'flow-moderation-confirm-hide-post',
			'flow-moderation-confirm-delete-post',
			'flow-moderation-confirm-suppress-post',
			'flow-moderation-confirm-hide-topic',
			'flow-moderation-confirm-delete-topic',
			'flow-moderation-confirm-suppress-topic',
			'flow-moderation-confirmation-hide-topic',
			'flow-moderation-confirmation-delete-topic',
			'flow-moderation-confirmation-suppress-topic',
			'flow-topic-moderated-reason-prefix',
			'flow-rev-message-lock-topic-reason',
			'flow-rev-message-restore-topic-reason',
			// Undo actions
			'flow-post-undo-hide',
			'flow-post-undo-delete',
			'flow-post-undo-suppress',
			'flow-topic-undo-hide',
			'flow-topic-undo-delete',
			'flow-topic-undo-suppress',
			// Timestamps
			'flow-edited',
			'flow-edited-by',
			// Board header
			"flow-board-header-browse-topics-link",
			// editor switching
			"flow-wikitext-editor-help",
			"flow-wikitext-editor-help-and-preview",
			"flow-wikitext-editor-help-uses-wikitext",
			"flow-wikitext-editor-help-preview-the-result",
		),
	) + $mobile,
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.text' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/mediawiki.ui/text.less',
		),
	) + $mobile,
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.form' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/mediawiki.ui/forms.less',
		),
	) + $mobile,
	'ext.flow.styles.base' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/common.less',
			'styles/editors-common.less',
			'styles/errors.less',
			'styles/history/history-line.less',
		),
	) + $mobile,
	'ext.flow.board.styles' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/board/header.less',
			'styles/board/menu.less',
			'styles/board/navigation.less',
			'styles/board/moderated.less',
			'styles/board/timestamps.less',
			'styles/board/replycount.less',
			'styles/js.less',
			'styles/board/form-actions.less',
			'styles/board/terms-of-use.less',
			'styles/board/editor-switcher.less',
		),
	) + $mobile,
	'ext.flow.board.topic.styles' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/board/topic/titlebar.less',
			'styles/board/topic/meta.less',
			'styles/board/topic/post.less',
			'styles/board/topic/summary.less',
			'styles/board/topic/watchlist.less',
		),
	) + $mobile,
	// MediaWiki Handlebars provider.  Should not have anything Flow-specific
	'mediawiki.template.handlebars' => array(
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'Flow',
		'scripts' => array(
			'vendor/modules/handlebars-v3.0.0.js',
			'modules/handlebars.js',
		),
		'dependencies' => array(
			'mediawiki.template',
		),
	) + $mobile,
	'ext.flow.components' => $flowResourceTemplate + array(
		'scripts' => array(
			'engine/components/flow-registry.js',
			'engine/components/flow-component.js',
			// FlowApi
			'engine/misc/flow-api.js',
			// FlowEventLog
			'engine/misc/flow-eventlog.js',
			// FlowComponent must come before actual components
			'engine/components/common/flow-component-engines.js',
			'engine/components/common/flow-component-events.js',

			// Component: BoardAndHistoryBase
			// Base class for both FlowBoardComponent and FlowBoardHistoryComponent
			// Implements common methods between them, such as topic namespace checking
			'engine/components/board/base/flow-boardandhistory-base.js',

			// Component: FlowBoardComponent
			'engine/components/board/flow-board.js',
		),
		'dependencies' => array(
			'oojs',
			'ext.flow.templating', // prototype-based for progressiveEnhancement
			'ext.flow.jquery.findWithParent',
			'ext.flow.vendor.storer',
			'mediawiki.Title',
			'mediawiki.user',
			'mediawiki.Uri',
		),
	) + $mobile,
	'ext.flow.dm' => $flowResourceTemplate + array(
		'scripts' => array( // Component order is important
			'flow/mw.flow.js',
			'flow/dm/mw.flow.dm.js',
			'flow/dm/mw.flow.dm.Content.js',
			'flow/dm/mw.flow.dm.Item.js',
			'flow/dm/mixins/mw.flow.dm.List.js',
			'flow/dm/api/mw.flow.dm.APIHandler.js',
			'flow/dm/mw.flow.dm.Captcha.js',
			'flow/dm/mw.flow.dm.RevisionedContent.js',
			'flow/dm/mw.flow.dm.ModeratedRevisionedContent.js',
			'flow/dm/mw.flow.dm.BoardDescription.js',
			'flow/dm/mw.flow.dm.System.js',
			'flow/dm/mw.flow.dm.Post.js',
			'flow/dm/mw.flow.dm.Topic.js',
			'flow/dm/mw.flow.dm.Board.js',
			'flow/dm/mw.flow.dm.CategoryItem.js',
			'flow/dm/mw.flow.dm.Categories.js',
		),
		'dependencies' => array(
			'oojs'
		)
	) + $mobile,
	'ext.flow.ui' => $flowResourceTemplate + array(
		'scripts' => array(
			'flow/ui/mw.flow.ui.js',
			'flow/ui/widgets/mw.flow.ui.CaptchaWidget.js',
			'flow/ui/mw.flow.ui.Overlay.js',
			'flow/ui/mw.flow.ui.CancelConfirmDialog.js',
			'flow/ui/widgets/mw.flow.ui.TopicMenuSelectWidget.js',
			'flow/ui/widgets/mw.flow.ui.ToCWidget.js',
			'flow/ui/widgets/mw.flow.ui.ReorderTopicsWidget.js',
			'flow/ui/widgets/mw.flow.ui.NavigationWidget.js',
			'flow/ui/widgets/mw.flow.ui.ReplyWidget.js',
			'flow/ui/widgets/mw.flow.ui.EditPostWidget.js',
			'flow/ui/widgets/mw.flow.ui.EditTopicSummaryWidget.js',
			'flow/ui/widgets/mw.flow.ui.SidebarExpandWidget.js',
			'flow/ui/widgets/mw.flow.ui.NewTopicWidget.js',
			'flow/ui/widgets/mw.flow.ui.TopicTitleWidget.js',

			'flow/ui/widgets/editor/editors/mw.flow.ui.AbstractEditorWidget.js',
			'flow/ui/widgets/editor/editors/mw.flow.ui.WikitextEditorWidget.js',
			'flow/ui/widgets/editor/editors/mw.flow.ui.VisualEditorWidget.js',
			'flow/ui/widgets/editor/mw.flow.ui.AnonWarningWidget.js',
			'flow/ui/widgets/editor/mw.flow.ui.EditorSwitcherWidget.js',
			'flow/ui/widgets/editor/mw.flow.ui.EditorControlsWidget.js',
			'flow/ui/widgets/editor/mw.flow.ui.EditorWidget.js',
			'flow/ui/widgets/editor/mw.flow.ui.SwitchToVeTool.js',
			'flow/ui/widgets/mw.flow.ui.BoardDescriptionWidget.js',
			'flow/ui/widgets/mw.flow.ui.CategoryItemWidget.js',
			'flow/ui/widgets/mw.flow.ui.CategoriesWidget.js',
		),
		'styles' => array(
			'styles/flow/mw.flow.ui.Overlay.less',
			'styles/flow/widgets/mw.flow.ui.NavigationWidget.less',
			'styles/flow/widgets/mw.flow.ui.TopicMenuSelectWidget.less',
			'styles/flow/widgets/mw.flow.ui.ReorderTopicsWidget.less',
			'styles/flow/widgets/mw.flow.ui.ReplyWidget.less',
			'styles/flow/widgets/mw.flow.ui.SidebarExpandWidget.less',
			'styles/flow/widgets/mw.flow.ui.NewTopicWidget.less',

			'styles/flow/widgets/editor/mw.flow.ui.AnonWarningWidget.less',
			'styles/flow/widgets/editor/mw.flow.ui.EditorControlsWidget.less',
			'styles/flow/widgets/editor/mw.flow.ui.EditorSwitcherWidget.less',
			'styles/flow/widgets/editor/mw.flow.ui.EditorWidget.less',
			'styles/flow/widgets/editor/editors/mw.flow.ui.WikitextEditorWidget.less',
			'styles/flow/widgets/mw.flow.ui.CategoryItemWidget.less',
			'styles/flow/widgets/mw.flow.ui.CategoriesWidget.less',
			'styles/flow/widgets/mw.flow.ui.TopicTitleWidget.less',
		),
		'messages' => array(
			'flow-error-parsoid-failure',
			'flow-error-default',
			'flow-dialog-cancelconfirm-title',
			'flow-dialog-cancelconfirm-message',
			'flow-dialog-cancelconfirm-keep',
			'flow-dialog-cancelconfirm-discard',
			'flow-wikitext-switch-editor-tooltip',
			'red-link-title',
			'pagecategories',
			'colon-separator'
		),
		'dependencies' => array (
			'oojs-ui',
			'es5-shim',
			'ext.flow.dm',
			'oojs-ui.styles.icons-editing-advanced',
			// This module may not exist, so a dummy version is conditionally added in Hooks.php
			'ext.visualEditor.supportCheck',
		)
	) + $mobile,
	'ext.flow' => $flowResourceTemplate + array(
		'position' => 'top',
		'styles' => array(
			'styles/mediawiki.ui/modal.less',
			'styles/mediawiki.ui/tooltips.less'
		),
		'scripts' => array( // Component order is important
			// MW UI
			'engine/misc/mw-ui.enhance.js',
			'engine/misc/mw-ui.modal.js',

			// Feature: flow-menu
			'engine/components/common/flow-component-menus.js',

			'engine/components/board/base/flow-board-api-events.js',
			'engine/components/board/base/flow-board-interactive-events.js',
			'engine/components/board/base/flow-board-load-events.js',
			// Feature: Load More
			'engine/components/board/features/flow-board-loadmore.js',
			// Feature: Board Navigation Header
			'engine/components/board/features/flow-board-navigation.js',
			// Feature: Side Rail
			'engine/components/board/features/flow-board-side-rail.js',
			// Feature: VisualEditor
			'engine/components/board/features/flow-board-visualeditor.js',
			// Feature: Switch between editors
			'engine/components/board/features/flow-board-switcheditor.js',

			// Component: FlowBoardHistoryComponent
			'engine/components/board/flow-boardhistory.js',
			// this must be last (of everything loaded.  otherwise a components
			// can be initialized before all the mixins are loaded.  Can we mixin
			// after initialization?)
			'mw.flow.Initializer.js',
			'flow-initialize.js',
		),
		'dependencies' => array(
			'ext.flow.components',
			'ext.flow.editor',
			'jquery.throttle-debounce',
			'mediawiki.jqueryMsg',
			'ext.flow.jquery.conditionalScroll',
			'ext.flow.ui',
			'mediawiki.api',
			'mediawiki.util',
			'mediawiki.api.options', // required by switch-editor feature
		),
		'messages' => array(
			'flow-error-external',
			'flow-error-http',
			'mw-ui-unsubmitted-confirm',
			'flow-reply-link',
			'flow-reply-link-anonymously',
		)
	) + $mobile,
	'ext.flow.vendor.storer' => array(
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'Flow',
		'scripts' => array(
			'vendor/modules/Storer.js',
		),
	) + $mobile,
	'ext.flow.undo' => $flowResourceTemplate + array(
		'position' => 'bottom',
		'scripts' => array(
			'engine/components/flow-undo-page.js',
		),
		// minimal subset for the undo pages
		'dependencies' => array(
			'ext.flow',
		),
	) + $mobile,
	'ext.flow.editor' => $flowResourceTemplate + array(
		'scripts' => array(
			'editor/editors/ext.flow.editors.AbstractEditor.js',
			'editor/ext.flow.editor.js',
		),
		'dependencies' => array(
			'oojs',
			'mediawiki.user',
			// specific editor (ext.flow.editors.*) dependencies (if any) will be loaded via JS
		),
	) + $mobile,
	'ext.flow.editors.none' => $flowResourceTemplate + array(
		'scripts' => array(
			'editor/editors/ext.flow.editors.none.js',
		),
		'messages' => array(
			'flow-wikitext-switch-editor-tooltip',
		),
		'dependencies' => array(
			'oojs-ui',
		)
	) + $mobile,

	// Basically this is just all the Flow-specific VE stuff, except ext.flow.editors.visualeditor.js,
	// That needs to register itself even if the browser doesn't support VE (so we can tell
	// the editor dispatcher that).  But we want to reduce what we load if the browser can't actually
	// use VE.
	'ext.flow.visualEditor' => $flowResourceTemplate + array(
		'scripts' => array(
			'editor/editors/visualeditor/mw.flow.ve.Target.js',
			'editor/editors/visualeditor/mw.flow.ve.UserCache.js',
			'editor/editors/visualeditor/ui/inspectors/mw.flow.ve.ui.MentionInspector.js',
			'editor/editors/visualeditor/ui/tools/mw.flow.ve.ui.MentionInspectorTool.js',
			// MentionInspectorTool must be after MentionInspector and before MentionContextItem.
			'editor/editors/visualeditor/ui/contextitem/mw.flow.ve.ui.MentionContextItem.js',
			'editor/editors/visualeditor/ui/widgets/mw.flow.ve.ui.MentionTargetInputWidget.js',
			'editor/editors/visualeditor/ui/tools/mw.flow.ve.ui.SwitchEditorTool.js',
			'editor/editors/visualeditor/ui/actions/mw.flow.ve.ui.SwitchEditorAction.js',
			'editor/editors/visualeditor/mw.flow.ve.CommandRegistry.js',
			'editor/editors/visualeditor/mw.flow.ve.SequenceRegistry.js',
		),
		'styles' => array(
			'editor/editors/visualeditor/mw.flow.ve.Target.less',
			'editor/editors/visualeditor/ui/mw.flow.ve.ui.Icons.less',
		),
		'skinStyles' => array(
			'vector' => array(
				'editor/editors/visualeditor/mw.flow.ve.Target-vector.less',
			),
			'monobook' => array(
				'editor/editors/visualeditor/mw.flow.ve.Target-monobook.less',
			),
		),
		'dependencies' => array(
			'es5-shim',
			'ext.visualEditor.core',
			'ext.visualEditor.core.desktop',
			'ext.visualEditor.data',
			'ext.visualEditor.icons',
			// See comment at bottom of mw.flow.ve.Target.js.
			'ext.visualEditor.mediawiki',
			'ext.visualEditor.desktopTarget',
			'ext.visualEditor.mwimage',
			'ext.visualEditor.mwlink',
			'ext.visualEditor.mwtransclusion',
			'ext.visualEditor.standalone',
			'oojs-ui.styles.icons-editing-advanced',
			'site',
			'user',
			'mediawiki.api',
			'ext.flow.editors.none', // needed to figure out if that editor is supported, for switch button
		),
		'messages' => array(
			'flow-ve-mention-context-item-label',
			'flow-ve-mention-inspector-title',
			'flow-ve-mention-inspector-remove-label',
			'flow-ve-mention-inspector-invalid-user',
			'flow-ve-mention-placeholder',
			'flow-ve-mention-tool-title',
			'flow-ve-switch-editor-tool-title',
		),
	),

	// Actual VE is currently not supported on mobile since we use the desktop target, but we still
	// need this part to load (and reject it in isSupported)
	'ext.flow.editors.visualeditor' => $flowResourceTemplate + array(
		'scripts' => 'editor/editors/visualeditor/ext.flow.editors.visualeditor.js',
		'dependencies' => array(
			// ve dependencies will be loaded via JS
		),
	) + $mobile,

	// This integrates with core mediawiki.messagePoster, and the module name
	// must be exactly this.
	'mediawiki.messagePoster.flow-board' => $flowResourceTemplate + array(
		'scripts' => array(
			'messagePoster/ext.flow.messagePoster.js',
		),
		'dependencies' => array(
			'oojs',
			'mediawiki.api',
			'mediawiki.messagePoster',
		),
	) + $mobile,
	'ext.flow.jquery.conditionalScroll' => $flowResourceTemplate + array(
		'scripts' => array(
			'engine/misc/jquery.conditionalScroll.js',
		),
	) + $mobile,
	'ext.flow.jquery.findWithParent' => $flowResourceTemplate + array(
		'scripts' => array(
			'engine/misc/jquery.findWithParent.js',
		),
	) + $mobile,
);
