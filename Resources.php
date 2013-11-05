<?php

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$wgResourceModules += array(
	'ext.flow.base' => $flowResourceTemplate + array(
		'styles' => array(
			'base/styles/header.less',
			'base/styles/container.less',
			'base/styles/form.less',
			'base/styles/actionbox.less',
			'base/styles/various.less',
			/*
			 * This is CSS that adds to/overrides Agora styles, meant to be
			 * moved to mediawiki.ui at a later point
			 */
			'mediawiki.ui/styles/agora-override-buttons.less',
			'mediawiki.ui/styles/agora-override-forms.less',
		),
		'scripts' => array(
			'base/ext.flow.base.js',
			'base/ui-functions.js',
			'base/init.js',
		),
		'dependencies' => array(
			'mediawiki.ui',
			'mediawiki.api',
			'jquery.json',
		),
		'messages' => array(
		),
	),
	'ext.flow.header' => $flowResourceTemplate + array(
		'styles' => 'header/styles/base.less',
		'scripts' => array(
			'header/editor-nonajax.js',
			'header/forms.js',
		),
		'dependencies' => array(
			'ext.flow.editor',
		),
	),
	'ext.flow.discussion' => $flowResourceTemplate + array(
		'styles' => array(
			'discussion/styles/topic.less',
			'discussion/styles/post.less',
		),
		'scripts' => array(
			'discussion/ui.js',
			'discussion/forms.js',
			'discussion/paging.js',
		),
		'dependencies' => array(
			'jquery.ui.core',
			'ext.flow.base',
			'ext.flow.editor',
			'jquery.spinner',
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
			'flow-edit-post-submit',
			'flow-paging-fwd',
			'flow-paging-rev',
			'flow-edit-header-submit',
			'flow-post-moderated-toggle-show',
			'flow-post-moderated-toggle-hide',
		),
		'position' => 'top',
	),
	'ext.flow.history' => $flowResourceTemplate + array(
		'styles' => 'history/styles/history.less',
		'scripts' => 'history/history.js',
	),
	'ext.flow.moderation' => $flowResourceTemplate + array(
		'scripts' => array(
			'moderation/moderation.js',
		),
		'messages' => array(
			'flow-moderation-title-censor',
			'flow-moderation-title-delete',
			'flow-moderation-title-hide',
			'flow-moderation-title-restore',
			'flow-moderation-intro-censor',
			'flow-moderation-intro-delete',
			'flow-moderation-intro-hide',
			'flow-moderation-intro-restore',
			'flow-moderation-confirm-censor',
			'flow-moderation-confirm-delete',
			'flow-moderation-confirm-hide',
			'flow-moderation-confirm-restore',
			'flow-moderation-confirmation-censor',
			'flow-moderation-confirmation-delete',
			'flow-moderation-confirmation-hide',
			'flow-moderation-confirmation-restore',
			'flow-moderation-reason-placeholder',
		),
		'dependencies' => array(
			'jquery.ui.core',
			'ext.flow.base',
			'jquery.ui.dialog',
			'jquery.spinner',
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
