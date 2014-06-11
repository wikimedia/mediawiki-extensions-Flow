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
			"flow_errors.handlebars",
			"flow_moderate_post.handlebars",
			"flow_moderate_topic.handlebars",
			"flow_newtopic_form.handlebars",
			"flow_post.handlebars",
			"flow_preview.handlebars",
			"flow_reply_form.handlebars",
			// Include dependent templates from handlebars/Makefile.
			"flow_block_loop.handlebars",
			"flow_topic.handlebars",
			"flow_topic_navigation.handlebars",
			"flow_topiclist_loop.handlebars",
			"flow_load_more.handlebars",
			"form_element.handlebars",
			"timestamp.handlebars",
		),
		'messages' => array(
			'flow-cancel',
			'flow-edit-header-submit',
			'flow-load-more',
			'flow-newest-topics',
			'flow-newtopic-content-placeholder',
			'flow-newtopic-save',
			'flow-newtopic-start-placeholder',
			'flow-post-action-delete-post',
			'flow-post-action-edit-post',
			'flow-post-action-hide-post',
			'flow-post-action-post-history',
			'flow-post-action-view',
			'flow-post-action-suppress-post',
			'flow-preview',
			'flow-recent-topics',
			'flow-reply-submit',
			'flow-sorting-tooltip',
			'flow-terms-of-use-edit',
			'flow-terms-of-use-new-topic',
			'flow-terms-of-use-reply',
			'flow-toggle-topics',
			'flow-topic-action-close-topic',
			'flow-topic-action-edit-title',
			'flow-topic-action-history',
			'flow-topic-action-resummarize-topic',
			'flow-topic-action-summarize-topic',
			'flow-topic-action-view',
		),
	),
	'ext.flow.new.styles' => $flowResourceTemplate + array(
		'styles' => array(
			'new/styles/mw-ui-flow.less',
			'new/styles/layout.less',
			'new/styles/interactive.less',
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
		)
	),
	'ext.flow.vendor.storer' => $flowResourceTemplate + array(
		'scripts' => 'new/vendor/Storer.js',
	),
	'ext.flow.vendor.jquery.ba-throttle-debounce' => $flowResourceTemplate + array(
		'scripts' => 'new/vendor/jquery.ba-throttle-debounce.js',
	),
);

$wgHooks['ResourceLoaderRegisterModules'][] = function( $resourceLoader ) use ( $flowResourceTemplate ) {
	if ( $resourceLoader->getModule( 'jquery.conditionalScroll' ) === null ) {
		$resourceLoader->register( 'jquery.conditionalScroll', $flowResourceTemplate + array(
			'scripts' => 'jquery.conditionalScroll.js',
		) );
	}
};
