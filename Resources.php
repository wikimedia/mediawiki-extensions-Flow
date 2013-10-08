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
		),
		'dependencies' => array(
			'mediawiki.ui',
			'mediawiki.api',
			'jquery.json',
		),
		'messages' => array(
		),
	),
	'ext.flow.summary' => $flowResourceTemplate + array(
		'styles' => 'summary/styles/base.less',
		'scripts' => 'summary/summary.js',
		'dependencies' => array(
			'ext.flow.editor',
		),
	),
	'ext.flow.discussion' => $flowResourceTemplate + array(
		'styles' => array(
			'discussion/styles/topic.less',
			'discussion/styles/post.less',
/*
			'discussion/styles/base.less',
			'discussion/styles/components/actionboxes.less',
			'discussion/styles/components/container.less',
			'discussion/styles/components/overlay.less',
			'discussion/styles/components/post.less',
			'discussion/styles/components/summary.less',
			'discussion/styles/components/timestamp.less',
			'discussion/styles/components/topic.less',
*/
		),
		'scripts' => array(
			'discussion/ui.js',
			'discussion/forms.js',
			'discussion/paging.js',
			'discussion/init.js',
		),
		'dependencies' => array(
			'jquery.ui.core',
			'ext.flow.base',
			'ext.flow.editor',
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
