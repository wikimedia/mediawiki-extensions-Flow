<?php

$mobile = array(
	'targets' => array( 'desktop', 'mobile' ),
);

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$flowTemplatingResourceTemplate = array(
	'localBasePath' => __DIR__ . '/handlebars',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
	'class' => 'ResourceLoaderFileModule',
	'targets' => array( 'mobile', 'desktop' ),
);

$wgResourceModules += array(
	'ext.flow.contributions' => $flowResourceTemplate + array(
		'scripts' => array(
			'contributions/base.js',
		),
	),
	'ext.flow.contributions.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/history/history-line.less',
		),
	),
	'ext.flow.templating' => $flowTemplatingResourceTemplate + array(
		'dependencies' => array(
			'ext.mantle.handlebars',
			'moment',
		),
		'templates' => array(
			'flow_anon_warning.partial.handlebars',
			"flow_block_board-history.handlebars",
			"flow_block_header.handlebars",
			"flow_block_header_diff_view.handlebars",
			"flow_block_header_edit.handlebars",
			"flow_block_header_single_view.handlebars",
			"flow_block_loop.handlebars",
			"flow_block_topic.handlebars",
			"flow_block_topic_diff_view.handlebars",
			"flow_block_topic_edit_title.handlebars",
			"flow_block_topic_history.handlebars",
			"flow_block_topic_moderate_post.handlebars",
			"flow_block_topic_moderate_topic.handlebars",
			"flow_block_topic_single_view.handlebars",
			"flow_block_topiclist.handlebars",
			"flow_block_topicsummary_diff_view.handlebars",
			"flow_block_topicsummary_edit.handlebars",
			"flow_block_topicsummary_single_view.handlebars",
			"flow_board_navigation.partial.handlebars",
			"flow_board_toc_loop.handlebars",
			"flow_edit_post_ajax.partial.handlebars",
			"flow_edit_post.partial.handlebars",
			"flow_edit_topic_title.partial.handlebars",
			"flow_errors.partial.handlebars",
			"flow_form_buttons.partial.handlebars",
			"flow_header_detail.partial.handlebars",
			"flow_load_more.partial.handlebars",
			"flow_moderate_post_confirmation.partial.handlebars",
			"flow_moderate_post.partial.handlebars",
			"flow_moderate_topic_confirmation.partial.handlebars",
			"flow_moderate_topic.partial.handlebars",
			"flow_moderation_actions_list.partial.handlebars",
			"flow_newtopic_form.partial.handlebars",
			"flow_post_actions.partial.handlebars",
			"flow_post_author.partial.handlebars",
			"flow_post_inner.partial.handlebars",
			"flow_post_meta_actions.partial.handlebars",
			"flow_post_moderation_state.partial.handlebars",
			"flow_post_replies.partial.handlebars",
			"flow_post.handlebars",
			"flow_preview_warning.handlebars",
			"flow_reply_form.partial.handlebars",
			"flow_subscribed.partial.handlebars",
			"flow_tooltip_subscribed.partial.handlebars",
			"flow_tooltip.handlebars",
			"flow_topic.partial.handlebars",
			"flow_topic_titlebar_content.partial.handlebars",
			"flow_topic_titlebar_lock.partial.handlebars",
			"flow_topic_titlebar_summary.partial.handlebars",
			"flow_topic_titlebar_watch.partial.handlebars",
			"flow_topic_titlebar.partial.handlebars",
			"flow_topic_moderation_flag.partial.handlebars",
			"flow_topiclist_loop.partial.handlebars",
			"form_element.partial.handlebars",
			"timestamp.handlebars",
		),
		'messages' => array(
			'flow-anon-warning',
			'flow-cancel',
			'flow-edit-header-placeholder',
			'flow-edit-header-submit',
			'flow-edit-title-submit',
			'flow-load-more',
			'flow-newest-topics',
			'flow-newtopic-content-placeholder',
			'flow-newtopic-save',
			'flow-newtopic-start-placeholder',
			'flow-post-action-delete-post',
			'flow-post-action-undelete-post',
			'flow-post-action-edit-post',
			'flow-post-action-edit-post-submit',
			'flow-post-action-hide-post',
			'flow-post-action-unhide-post',
			'flow-post-action-post-history',
			'flow-post-action-view',
			'flow-post-action-suppress-post',
			'flow-post-action-unsuppress-post',
			'flow-post-action-restore-post',
			'flow-post-action-undo-moderation',
			"flow-preview-return-edit-post",
			'flow-preview',
			'flow-recent-topics',
			'flow-reply-submit',
			'flow-reply-topic-title-placeholder',
			'flow-sorting-tooltip-newest',
			'flow-sorting-tooltip-recent',
			'flow-summarize-topic-submit',
			'flow-unlock-topic-submit',
			'flow-lock-topic-submit',
			'flow-toggle-small-topics',
			'flow-toggle-topics',
			'flow-toggle-topics-posts',
			'flow-topic-comments',
			'flow-topic-action-hide-topic',
			'flow-topic-action-lock-topic',
			'flow-topic-action-delete-topic',
			'flow-topic-action-edit-title',
			'flow-topic-action-hide-topic',
			'flow-topic-action-history',
			'flow-topic-action-resummarize-topic',
			'flow-topic-action-summarize-topic',
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
			'flow-hide-post-content',
			'flow-delete-post-content',
			'flow-suppress-post-content',
			'flow-hide-title-content',
			'flow-delete-title-content',
			'flow-suppress-title-content',
			'talkpagelinktext',
			'flow-cancel-warning',
			// Moderation state
			'flow-lock-title-content',
			'flow-lock-post-content',
			'flow-hide-title-content',
			'flow-hide-post-content',
			'flow-delete-title-content',
			'flow-delete-post-content',
			'flow-suppress-title-content',
			'flow-suppress-post-content',
			// Previews
			'flow-preview-warning',
			'flow-anonymous',
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
			'flow-moderation-placeholder-unlock-topic',
			'flow-moderation-placeholder-unhide-topic',
			'flow-moderation-placeholder-undelete-topic',
			'flow-moderation-placeholder-unsuppress-topic',
			'flow-moderation-placeholder-hide-post',
			'flow-moderation-placeholder-delete-post',
			'flow-moderation-placeholder-suppress-post',
			'flow-moderation-placeholder-lock-topic',
			'flow-moderation-placeholder-hide-topic',
			'flow-moderation-placeholder-delete-topic',
			'flow-moderation-placeholder-suppress-topic',
			'flow-moderation-confirm-unhide-post',
			'flow-moderation-confirm-undelete-post',
			'flow-moderation-confirm-unsuppress-post',
			'flow-moderation-confirm-unlock-topic',
			'flow-moderation-confirm-unhide-topic',
			'flow-moderation-confirm-undelete-topic',
			'flow-moderation-confirm-unsuppress-topic',
			'flow-moderation-confirm-hide-post',
			'flow-moderation-confirm-delete-post',
			'flow-moderation-confirm-suppress-post',
			'flow-moderation-confirm-lock-topic',
			'flow-moderation-confirm-hide-topic',
			'flow-moderation-confirm-delete-topic',
			'flow-moderation-confirm-suppress-topic',
			'flow-moderation-confirmation-hide-topic',
			'flow-moderation-confirmation-delete-topic',
			'flow-moderation-confirmation-suppress-topic',
			'flow-topic-moderated-reason-prefix',
			// Undo actions
			'flow-post-undo-hide',
			'flow-post-undo-delete',
			'flow-post-undo-suppress',
			// Timestamps
			'flow-edited',
			'flow-edited-by',
			// Board header
			"flow-board-header-browse-topics-link",
		),
	),
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.modal' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/mediawiki.ui/modal.less',
		),
	) + $mobile,
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.text' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/mediawiki.ui/text.less',
		),
	) + $mobile,
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.form' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/mediawiki.ui/forms.less',
		),
	) + $mobile,
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.tooltips' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/mediawiki.ui/tooltips.less',
		),
	) + $mobile,
	'ext.flow.icons.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'wikiglyph/wikiglyphs.css',
			'wikiglyph/flow-override.less',
		),
	) + $mobile,
	'ext.flow.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/common.less',
			'styles/errors.less',
			'styles/history/history-line.less',
		),
	) + $mobile,
	'ext.flow.board.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/board/header.less',
			'styles/board/menu.less',
			'styles/board/navigation.less',
			'styles/board/moderated.less',
			'styles/board/timestamps.less',
			'styles/board/replycount.less',
			'styles/js.less',
			'styles/board/content-preview.less',
			'styles/board/form-actions.less',
			'styles/board/terms-of-use.less',
		),
	) + $mobile,
	'ext.flow.board.topic.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'styles/board/topic/titlebar.less',
			'styles/board/topic/meta.less',
			'styles/board/topic/post.less',
			'styles/board/topic/summary.less',
			'styles/board/topic/watchlist.less',
		),
	) + $mobile,
	'ext.flow.handlebars' => $flowResourceTemplate + array(
		'scripts' => array(
			'engine/misc/flow-handlebars.js',
		),
		'dependencies' => array(
			'ext.mantle.handlebars',
			// the timestamp helper uses the timestamp template
			'ext.flow.templating',
			'moment',
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
			// Also needed for event log
			'engine/components/board/base/flow-board-misc.js',
		),
		'dependencies' => array(
			'oojs',
			'ext.flow.handlebars', // prototype-based for progressiveEnhancement
			'ext.flow.jquery.findWithParent',
			'ext.flow.vendor.storer',
			'mediawiki.Title',
			'mediawiki.user',
			'mediawiki.Uri',
		),
	) + $mobile,
	'ext.flow' => $flowResourceTemplate + array(
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
			// Feature: Table of Contents
			'engine/components/board/features/flow-board-toc.js',

			// Component: FlowBoardHistoryComponent
			'engine/components/board/flow-boardhistory.js',
			// this must be last (of everything loaded.  otherwise a components
			// can be initialized before all the mixins are loaded.  Can we mixin
			// after initialization?)
			'flow-initialize.js',
		),
		'dependencies' => array(
			'ext.flow.components',
			'jquery.throttle-debounce',
			'mediawiki.jqueryMsg',
			'ext.flow.jquery.conditionalScroll',
			'mediawiki.api',
			'mediawiki.util',
		),
		'messages' => array(
			'flow-error-external',
			'flow-error-http',
			'flow-error-fetch-after-lock',
			'mw-ui-unsubmitted-confirm',
			'flow-reply-link',
		)
	) + $mobile,
	'ext.flow.vendor.storer' => $flowResourceTemplate + array(
		'scripts' => array(
			'vendor/Storer.js',
		),
	) + $mobile,
	'ext.flow.preview' => $flowResourceTemplate + array(
		'scripts' => array(
			'engine/components/board/features/flow-board-preview.js',
			// wfBaseConvert ported to js
			'engine/misc/flow-baseconvert.js',
		),
		'dependencies' => array(
			'ext.flow.components'
		),
	) + $mobile,
	'ext.flow.editor' => $flowResourceTemplate + array(
		'scripts' => array(
			'editor/ext.flow.editor.js',
		),
		'dependencies' => array(
			'ext.flow.parsoid',
			// specific editor (ext.flow.editors.*) dependencies (if any) will be loaded via JS
		),
	) + $mobile,
	'ext.flow.editors.none' => $flowResourceTemplate + array(
		'scripts' => array(
			'editor/editors/ext.flow.editors.none.js',
		),
	) + $mobile,
	'ext.flow.editors.visualeditor' => $flowResourceTemplate + array(
		'scripts' => 'editor/editors/ext.flow.editors.visualeditor.js',
		'dependencies' => array(
			'jquery.spinner',
			// ve dependencies will be loaded via JS
		),
	),

	'ext.flow.parsoid' => $flowResourceTemplate + array(
		'scripts' => array(
			'editor/ext.flow.parsoid.js',
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

$wgHooks['ResourceLoaderRegisterModules'][] = function ( ResourceLoader &$resourceLoader ) {
	global $wgFlowEventLogging, $wgResourceModules;

	// Only if EventLogging in Flow is enabled & EventLogging exists
	if ( $wgFlowEventLogging && class_exists( 'ResourceLoaderSchemaModule' ) ) {
		$resourceLoader->register( 'schema.FlowReplies', array(
			'class' => 'ResourceLoaderSchemaModule',
			'schema' => 'FlowReplies',
			// See https://meta.wikimedia.org/wiki/Schema:FlowReplies, below title
			'revision' => 10561344,
		) );

		// Add as dependency to Flow JS
		$wgResourceModules['ext.flow']['dependencies'][] = 'schema.FlowReplies';
	}

	return true;
};
