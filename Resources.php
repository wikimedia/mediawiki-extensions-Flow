<?php

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$wgResourceModules += array(
	'ext.flow.base' => $flowResourceTemplate + array(
		'styles' => array(
			/*
			 * This is CSS that add to/overrides Agora styles, meant to be
			 * moved to mediawiki.ui at a later point
			 */
			'mediawiki.ui/agora-override.css',
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
		'styles' => 'summary/base.less',
		'scripts' => 'summary/summary.js',
		'dependencies' => array(
			'ext.flow.editor',
		),
	),
	'ext.flow.discussion' => $flowResourceTemplate + array(
		'styles' => array(
			'discussion/base.less',
			'discussion/components/actionboxes.less',
			'discussion/components/container.less',
			'discussion/components/overlay.less',
			'discussion/components/post.less',
			'discussion/components/summary.less',
			'discussion/components/timestamp.less',
			'discussion/components/topic.less',
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
