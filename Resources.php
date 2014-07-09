<?php

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$flowTemplatingResourceTemplate = $flowResourceTemplate + array(
	'localTemplateBasePath' => __DIR__ . '/handlebars',
	'class' => 'ResourceLoaderTemplateModule',
	'targets' => array( 'mobile', 'desktop' ),
);

$wgResourceModules += array(
	'ext.flow.templating' => $flowTemplatingResourceTemplate + array(
		'class' => 'ResourceLoaderTemplateModule',
		'dependencies' => 'ext.mantle.handlebars',
		'localTemplateBasePath' => $dir . 'handlebars',
		'templates' => array(
			'flow_anon_warning.handlebars',
			"flow_block_board-history.handlebars",
			"flow_block_header.handlebars",
			"flow_block_header_diff_view.handlebars",
			"flow_block_header_edit.handlebars",
			"flow_block_header_single_view.handlebars",
			"flow_block_topic.handlebars",
			"flow_block_topic_diff_view.handlebars",
			"flow_block_topic_edit_post.handlebars",
			"flow_block_topic_edit_title.handlebars",
			"flow_block_topic_history.handlebars",
			"flow_block_topic_moderate_post.handlebars",
			"flow_block_topic_moderate_topic.handlebars",
			"flow_block_topic_single_view.handlebars",
			"flow_block_topiclist.handlebars",
			"flow_block_topicsummary_diff_view.handlebars",
			"flow_block_topicsummary_edit.handlebars",
			"flow_block_topicsummary_single_view.handlebars",
			"flow_board.handlebars",
			"flow_board_collapsers_subcomponent.handlebars",
			"flow_board_navigation.handlebars",
			"flow_edit_post.handlebars",
			"flow_edit_topic_title.handlebars",
			"flow_errors.handlebars",
			"flow_moderate_post.handlebars",
			"flow_moderate_topic.handlebars",
			"flow_newtopic_form.handlebars",
			"flow_post.handlebars",
			"flow_post_author.handlebars",
			"flow_post_actions.handlebars",
			"flow_post_meta_actions.handlebars",
			"flow_post_replies.handlebars",
			"flow_reply_form.handlebars",
			// Include dependent templates from handlebars/Makefile.
			"flow_block_loop.handlebars",
			"form_element.handlebars",
			"flow_load_more.handlebars",
			"flow_tooltip.handlebars",
			"flow_tooltip_topic_subscription.handlebars",
			"flow_topic.handlebars",
			"flow_topic_titlebar.handlebars",
			"flow_topic_titlebar_close.handlebars",
			"flow_topic_navigation.handlebars",
			"flow_topic_titlebar_content.handlebars",
			"flow_topic_titlebar_summary.handlebars",
			"flow_topiclist_loop.handlebars",
			"flow_preview_button.handlebars",
			"flow_preview_warning.handlebars",
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
			"flow-preview-return-edit-post",
			'flow-preview',
			'flow-recent-topics',
			'flow-reply-submit',
			'flow-reply-topic-title-placeholder',
			'flow-sorting-tooltip',
			'flow-summarize-topic-submit',
			'flow-reopen-topic-submit',
			'flow-close-topic-submit',
			'flow-toggle-small-topics',
			'flow-toggle-topics',
			'flow-toggle-topics-posts',
			'flow-topic-action-hide-topic',
			'flow-topic-action-close-topic',
			'flow-topic-action-delete-topic',
			'flow-topic-action-edit-title',
			'flow-topic-action-hide-topic',
			'flow-topic-action-history',
			'flow-topic-action-resummarize-topic',
			'flow-topic-action-summarize-topic',
			'flow-topic-action-reopen-topic',
			'flow-topic-action-suppress-topic',
			'flow-topic-action-view',
			'flow-topic-action-hide-topic',
			'flow-topic-action-unhide-topic',
			'flow-topic-action-delete-topic',
			'flow-topic-action-undelete-topic',
			'flow-topic-action-suppress-topic',
			'flow-topic-action-unsuppress-topic',
			'flow-topic-action-restore-topic',
			'flow-hide-post-content',
			'flow-delete-post-content',
			'flow-suppress-post-content',
			'flow-hide-title-content',
			'flow-delete-title-content',
			'flow-suppress-title-content',
			'talkpagelinktext',
			'flow-cancel-warning',
			// Moderation state
			'flow-close-title-content',
			'flow-close-post-content',
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
			// Terms of use
			'flow-terms-of-use-new-topic',
			'flow-terms-of-use-reply',
			'flow-terms-of-use-edit',
			'flow-terms-of-use-summarize',
			'flow-terms-of-use-close-topic',
			'flow-terms-of-use-reopen-topic',
		),
	),
	// @todo: replace with mediawiki ui buttons
	'ext.flow.mediawiki.ui.buttons' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/mediawiki.ui/buttons.less',
		),
	),
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.form' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/mediawiki.ui/forms.less',
		),
	),
	// @todo: upstream to mediawiki ui
	'ext.flow.mediawiki.ui.tooltips' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/mediawiki.ui/tooltips.less',
		),
	),
	'ext.flow.icons.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/icons.less',
		),
	),
	'ext.flow.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/common.less',
			'new/styles/errors.less',
			// @todo: consolidate with mediawiki.ui forms
			'new/styles/forms.less',
		),
	),
	'ext.flow.board.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/board/collapser.less',
			'new/styles/board/header.less',
			'new/styles/board/menu.less',
			'new/styles/board/navigation.less',
			'new/styles/board/moderated.less',
			'new/styles/board/timestamps.less',
			'new/styles/board/replycount.less',
		),
	),
	'ext.flow.board.topic.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/board/topic/navigation.less',
			'new/styles/board/topic/navigator.less',
			'new/styles/board/topic/titlebar.less',
			'new/styles/board/topic/meta.less',
			'new/styles/board/topic/post.less',
			'new/styles/board/topic/summary.less',
		),
	),
	// @todo: Remove when cache clears
	'ext.flow.new.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/mediawiki.ui/buttons.less',
			'new/styles/mediawiki.ui/forms.less',
			'new/styles/mediawiki.ui/tooltips.less',
			'new/styles/icons.less',
			'new/styles/common.less',
			'new/styles/board/collapser.less',
			'new/styles/board/header.less',
			'new/styles/board/menu.less',
			'new/styles/board/navigation.less',
			'new/styles/board/moderated.less',
			'new/styles/board/timestamps.less',
			'new/styles/board/replycount.less',
			'new/styles/board/topic/navigation.less',
			'new/styles/board/topic/navigator.less',
			'new/styles/board/topic/titlebar.less',
			'new/styles/board/topic/meta.less',
			'new/styles/board/topic/post.less',
			'new/styles/board/topic/summary.less',
			'new/styles/forms.less',
		),
	),
	'ext.flow.new.handlebars' => $flowResourceTemplate + array(
		'scripts' => array(
			'new/flow-handlebars.js',
		),
		'dependencies' => array(
			'ext.mantle.handlebars',
		),
	),
	'ext.flow.new.history' => $flowResourceTemplate + array(
		'scripts' => array(
			'new/flow-history.js',
		),
	),
	'ext.flow.new' => $flowResourceTemplate + array(
		'scripts' => array(
			'new/mw-ui.enhance.js',
			'new/flow-api.js',
			'new/flow-components.js',
			// flow-component must come before actual components
			'new/components/flow-board.js',
			'new/flow.js',
		),
		'dependencies' => array(
			'ext.flow.templating', // ResourceLoader templating
			'ext.flow.new.handlebars', // prototype-based for progressiveEnhancement
			'ext.flow.new.history',
			'ext.flow.vendor.storer',
			'ext.flow.vendor.jquery.ba-throttle-debounce',
			'mediawiki.jqueryMsg',
			'jquery.json',
			'jquery.conditionalScroll',
		),
		'messages' => array(
			'flow-error-http',
		)
	),
	'ext.flow.vendor.storer' => $flowResourceTemplate + array(
		'scripts' => array(
			'new/vendor/Storer.js',
		),
	),
	'ext.flow.vendor.jquery.ba-throttle-debounce' => $flowResourceTemplate + array(
		'scripts' => array(
			'new/vendor/jquery.ba-throttle-debounce.js',
		),
	),
);

$wgHooks['ResourceLoaderRegisterModules'][] = function( $resourceLoader ) use ( $flowResourceTemplate ) {
	if ( $resourceLoader->getModule( 'jquery.conditionalScroll' ) === null ) {
		$resourceLoader->register( 'jquery.conditionalScroll', $flowResourceTemplate + array(
			'scripts' => 'jquery.conditionalScroll.js',
		) );
	}
};
