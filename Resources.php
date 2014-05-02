<?php

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$wgResourceModules += array(
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
			'ext.flow.vendor.handlebars',
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
			'new/flow-components.js',
			// flow-component must come before actual components
			'new/components/flow-board.js',
			'new/flow.js',
		),
		'dependencies' => array(
			'ext.flow.new.handlebars',
			'ext.flow.new.history',
			'ext.flow.vendor.storer',
			'ext.flow.vendor.jquery.ba-throttle-debounce',
			'mediawiki.jqueryMsg',
			'jquery.json',
			'jquery.conditionalScroll',
		),
	),
	'ext.flow.vendor.handlebars' => $flowResourceTemplate + array(
		'scripts' => 'new/vendor/handlebars-v2.0.0-alpha.2.js',
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
