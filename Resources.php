<?php

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$wgResourceModules += array(
	'ext.flow.base' => $flowResourceTemplate + array(
		'styles' => array(
			'base/styles/container.less',
			'base/styles/form.less',
			'base/styles/actionbox.less',
			'base/styles/various.less',
			'base/styles/preview.less',
			/*
			 * This is CSS that adds to/overrides Agora styles, meant to be
			 * moved to mediawiki.ui at a later point
			 */
			'mediawiki.ui/styles/agora-override-buttons.less',
			'mediawiki.ui/styles/agora-override-forms.less',
		),
		'skinStyles' => array(
			'vector' => array(
				'base/styles/header.less',
			),
		),
		'scripts' => array(
			'base/ext.flow.base.js',
			'base/ui-functions.js',
			'base/init.js',
			'base/utils.js',
			'base/action.js',
		),
		'dependencies' => array(
			'mediawiki.util',
			'mediawiki.jqueryMsg',
			'mediawiki.ui',
			'mediawiki.api',
			'jquery.json',
			'jquery.tipsy',
		),
		'messages' => array(
			'flow-preview',
		),
	),
	'ext.flow.header' => $flowResourceTemplate + array(
		'styles' => 'header/styles/base.less',
		'scripts' => array(
			'header/forms.js',
		),
		'dependencies' => array(
			'ext.flow.editor',
		),
		'messages' => array(
			'flow-error-other',
			'flow-edit-header-submit',
			'flow-edit-header-submit-overwrite',
		),
	),
	'ext.flow.discussion' => $flowResourceTemplate + array(
		'styles' => array(
			'discussion/styles/topic.less',
			'discussion/styles/post.less',
			'discussion/styles/collapse.less',
			'discussion/styles/modified.less',
			'discussion/styles/nojs.less',
		),
		'scripts' => array(
			'discussion/ui.js',
			'discussion/topic.js',
			'discussion/post.js',
			'discussion/paging.js',
		),
		'dependencies' => array(
			'jquery.ui.core',
			'ext.flow.base',
			'ext.flow.editor',
			'jquery.spinner',
			'mediawiki.Title',
			'mediawiki.util',
			'jquery.byteLimit',
			'jquery.tipsy',
			'jquery.conditionalScroll',
			'ext.flow.parsoid',
			'mediawiki.jqueryMsg',
			'mediawiki.user',
		),
		'messages' => array(
			'flow-newtopic-start-placeholder',
			'flow-newtopic-title-placeholder',
			'flow-cancel',
			'flow-error-http',
			'flow-error-other',
			'flow-error-external',
			'flow-error-external-multi',
			'flow-edit-title-submit',
			'flow-edit-title-submit-overwrite',
			'flow-edit-post-submit',
			'flow-edit-post-submit-overwrite',
			'flow-paging-fwd',
			'flow-paging-rev',
			'flow-post-moderated-toggle-show',
			'flow-post-moderated-toggle-hide',
			'flow-anon-warning',
		),
		'position' => 'top',
	),
	'ext.flow.history' => $flowResourceTemplate + array(
		'styles' => array(
			'history/styles/history.less',
			'history/styles/diff.less',
		),
		'scripts' => 'history/history.js',
	),
	'ext.flow.moderation' => $flowResourceTemplate + array(
		'styles' => 'moderation/styles/moderation.less',
		'scripts' => 'moderation/moderation.js',
		'messages' => array(
			'flow-moderation-reason-placeholder',
			'flow-moderation-title-suppress-post',
			'flow-moderation-title-delete-post',
			'flow-moderation-title-hide-post',
			'flow-moderation-title-unsuppress-post',
			'flow-moderation-title-undelete-post',
			'flow-moderation-title-unhide-post',
			'flow-moderation-title-suppress-topic',
			'flow-moderation-title-delete-topic',
			'flow-moderation-title-hide-topic',
			'flow-moderation-title-unsuppress-topic',
			'flow-moderation-title-undelete-topic',
			'flow-moderation-title-unhide-topic',

			'flow-moderation-intro-suppress-post',
			'flow-moderation-intro-delete-post',
			'flow-moderation-intro-hide-post',
			'flow-moderation-intro-unsuppress-post',
			'flow-moderation-intro-undelete-post',
			'flow-moderation-intro-unhide-post',
			'flow-moderation-intro-suppress-topic',
			'flow-moderation-intro-delete-topic',
			'flow-moderation-intro-hide-topic',
			'flow-moderation-intro-unsuppress-topic',
			'flow-moderation-intro-undelete-topic',
			'flow-moderation-intro-unhide-topic',

			'flow-moderation-confirm-suppress-post',
			'flow-moderation-confirm-delete-post',
			'flow-moderation-confirm-hide-post',
			'flow-moderation-confirm-unsuppress-post',
			'flow-moderation-confirm-undelete-post',
			'flow-moderation-confirm-unhide-post',
			'flow-moderation-confirm-suppress-topic',
			'flow-moderation-confirm-delete-topic',
			'flow-moderation-confirm-hide-topic',
			'flow-moderation-confirm-unsuppress-topic',
			'flow-moderation-confirm-undelete-topic',
			'flow-moderation-confirm-unhide-topic',

			'flow-moderation-confirmation-suppress-post',
			'flow-moderation-confirmation-delete-post',
			'flow-moderation-confirmation-hide-post',
			'flow-moderation-confirmation-unsuppress-post',
			'flow-moderation-confirmation-undelete-post',
			'flow-moderation-confirmation-unhide-post',
			'flow-moderation-confirmation-suppress-topic',
			'flow-moderation-confirmation-delete-topic',
			'flow-moderation-confirmation-hide-topic',
			'flow-moderation-confirmation-unsuppress-topic',
			'flow-moderation-confirmation-undelete-topic',
			'flow-moderation-confirmation-unhide-topic',
		),
		'dependencies' => array(
			'jquery.ui.core',
			'ext.flow.base',
			'jquery.ui.dialog',
			'jquery.spinner',
			'jquery.byteLimit',
			'mediawiki.jqueryMsg',
			'mediawiki.user',
			'mediawiki.notify',
		),
	),
	'ext.flow.editor' => $flowResourceTemplate + array(
		'scripts' => 'editor/ext.flow.editor.js',
		'dependencies' => array(
			'ext.flow.base',
			'ext.flow.parsoid',
			// specific editor (ext.flow.editors.*) dependency will be loaded via JS
		),
	),
	'ext.flow.editors.visualeditor' => $flowResourceTemplate + array(
		'scripts' => 'editor/editors/ext.flow.editors.visualeditor.js',
		'dependencies' => array(
			'jquery.spinner',
			// ve dependencies will be loaded via JS
		),
	),
	'ext.flow.editors.none' => $flowResourceTemplate + array(
		'scripts' => 'editor/editors/ext.flow.editors.none.js',
		'dependencies' => array(
			'ext.flow.base',
		),
	),
	'ext.flow.editors.wikieditor' => $flowResourceTemplate + array(
		'scripts' => 'editor/editors/ext.flow.editors.wikieditor.js',
		'dependencies' => array(
			// wikieditor dependencies will be loaded via JS
		),
	),
	'ext.flow.parsoid' => $flowResourceTemplate + array(
		'scripts' => 'editor/ext.flow.parsoid.js',
		'dependencies' => array(
			'ext.flow.base',
		)
	),
);

$wgHooks['ResourceLoaderRegisterModules'][] = function( $resourceLoader ) use ( $flowResourceTemplate ) {
	if ( $resourceLoader->getModule( 'jquery.conditionalScroll' ) === null ) {
		$resourceLoader->register( 'jquery.conditionalScroll', $flowResourceTemplate + array(
			'scripts' => 'jquery.conditionalScroll.js',
		) );
	}
};
