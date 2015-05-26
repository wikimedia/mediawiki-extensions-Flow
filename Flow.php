<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Flow' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Flow'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['FlowAlias'] = __DIR__ . '/Flow.alias.php';
	$wgExtensionMessagesFiles['FlowNamespaces'] = __DIR__ . '/Flow.namespaces.php';
	/* wfWarn(
		'Deprecated PHP entry point used for Flow extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return;
} else {
	die( 'This version of the Flow extension requires MediaWiki 1.25+' );
}