<?php
/**
 * MediaWiki Extension: Flow
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * This program is distributed WITHOUT ANY WARRANTY.
 */

/**
 *
 * @file
 * @ingroup Extensions
 * @author Andrew Garrett
 */

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/Flow/Flow.php" );
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Flow',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Flow',
	'author' => array( 'Erik Bernhardson', 'Matthias Mullie', 'Benny Situ', 'Andrew Garrett' ),
	'descriptionmsg' => 'flow-desc',
);

// Constants
define( 'RC_FLOW', 142 ); // soon to be obsolete, random number chosen
define( 'NS_TOPIC', 2600 );

$wgNamespacesWithSubpages[NS_TOPIC] = false;
$wgNamespaceContentModels[NS_TOPIC] = 'flow-board';

$dir = __DIR__ . '/';
require $dir . 'Resources.php';

$wgMessagesDirs['Flow'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['Flow'] = $dir . 'Flow.i18n.php';
$wgExtensionMessagesFiles['FlowNamespaces'] = $dir . '/Flow.namespaces.php';

// This file is autogenerated by scripts/gen-autoload.php
require __DIR__ . '/autoload.php';

$wgAPIListModules['flow'] = 'ApiQueryFlow';
$wgAPIModules['flow-parsoid-utils'] = 'ApiParsoidUtilsFlow';
$wgAPIModules['flow'] = 'ApiFlow';
$wgAPIPropModules['flowinfo'] = 'ApiQueryPropFlowInfo';

// Special:Flow
$wgExtensionMessagesFiles['FlowAlias'] = $dir . 'Flow.alias.php';
$wgSpecialPages['Flow'] = 'Flow\SpecialFlow';
$wgSpecialPages['Flowify'] = 'Flow\SpecialFlowify';
$wgSpecialPageGroups['Flow'] = 'redirects';

// Housekeeping hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlowHooks::getSchemaUpdates';
$wgHooks['GetPreferences'][] = 'FlowHooks::onGetPreferences';
$wgHooks['UnitTestsList'][] = 'FlowHooks::getUnitTests';
$wgHooks['OldChangesListRecentChangesLine'][] = 'FlowHooks::onOldChangesListRecentChangesLine';
$wgHooks['ChangesListInsertArticleLink'][] = 'FlowHooks::onChangesListInsertArticleLink';
$wgHooks['ChangesListInitRows'][] = 'FlowHooks::onChangesListInitRows';
$wgHooks['SkinTemplateNavigation::Universal'][] = 'FlowHooks::onSkinTemplateNavigation';
$wgHooks['Article::MissingArticleConditions'][] = 'FlowHooks::onMissingArticleConditions';
$wgHooks['SpecialWatchlistGetNonRevisionTypes'][] = 'FlowHooks::onSpecialWatchlistGetNonRevisionTypes';
$wgHooks['UserGetReservedNames'][] = 'FlowHooks::onUserGetReservedNames';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'FlowHooks::onResourceLoaderGetConfigVars';
$wgHooks['ContribsPager::reallyDoQuery'][] = 'FlowHooks::onContributionsQuery';
$wgHooks['ContributionsLineEnding'][] = 'FlowHooks::onContributionsLineEnding';
$wgHooks['AbuseFilter-computeVariable'][] = 'FlowHooks::onAbuseFilterComputeVariable';
$wgHooks['AbortEmailNotification'][] = 'FlowHooks::onAbortEmailNotification';
$wgHooks['InfoAction'][] = 'FlowHooks::onInfoAction';
$wgHooks['SpecialCheckUserGetLinksFromRow'][] = 'FlowHooks::onSpecialCheckUserGetLinksFromRow';
$wgHooks['CheckUserInsertForRecentChange'][] = 'FlowHooks::onCheckUserInsertForRecentChange';
$wgHooks['SkinMinervaDefaultModules'][] = 'FlowHooks::onSkinMinervaDefaultModules';
$wgHooks['IRCLineURL'][] = 'FlowHooks::onIRCLineURL';
$wgHooks['FlowAddModules'][] = 'Flow\Parsoid\Utils::onFlowAddModules';
$wgHooks['WhatLinksHereProps'][] = 'FlowHooks::onWhatLinksHereProps';
$wgHooks['ResourceLoaderTestModules'][] = 'FlowHooks::onResourceLoaderTestModules';
$wgHooks['ShowMissingArticle'][] = 'Flow\Content\Content::onShowMissingArticle';
$wgHooks['MessageCache::get'][] = 'FlowHooks::onMessageCacheGet';
$wgHooks['WatchArticle'][] = 'FlowHooks::onWatchArticle';
$wgHooks['UnwatchArticle'][] = 'FlowHooks::onWatchArticle';
$wgHooks['CanonicalNamespaces'][] = 'FlowHooks::onCanonicalNamespaces';
$wgHooks['AbortMove'][] = 'FlowHooks::onAbortMove';
$wgHooks['TitleSquidURLs'][] = 'FlowHooks::onTitleSquidURLs';
$wgHooks['WatchlistEditorBuildRemoveLine'][] = 'FlowHooks::onWatchlistEditorBuildRemoveLine';
$wgHooks['WatchlistEditorBeforeFormRender'][] = 'FlowHooks::onWatchlistEditorBeforeFormRender';

// Extension initialization
$wgExtensionFunctions[] = 'FlowHooks::initFlowExtension';

// Flow Content Type
$wgContentHandlers['flow-board'] = 'Flow\Content\BoardContentHandler';

// User permissions
// Added to $wgFlowGroupPermissions instead of $wgGroupPermissions immediately,
// to easily fetch Flow-specific permissions in tests/PermissionsTest.php.
// If you wish to make local permission changes, add them to $wgGroupPermissions
// directly - tests will fail otherwise, since they'll be based on a different
// permissions config than what's assumed to test.
$wgFlowGroupPermissions = array();
$wgFlowGroupPermissions['user']['flow-hide'] = true;
$wgFlowGroupPermissions['user']['flow-lock'] = true;
$wgFlowGroupPermissions['sysop']['flow-hide'] = true;
$wgFlowGroupPermissions['sysop']['flow-lock'] = true;
$wgFlowGroupPermissions['sysop']['flow-delete'] = true;
$wgFlowGroupPermissions['sysop']['flow-edit-post'] = true;
$wgFlowGroupPermissions['oversight']['flow-suppress'] = true;
$wgGroupPermissions = array_merge_recursive( $wgGroupPermissions, $wgFlowGroupPermissions );

// Register Flow import paths
$wgResourceLoaderLESSImportPaths = array_merge( $wgResourceLoaderLESSImportPaths, array(
	$dir . "modules/new/styles/flow.less/",
) );

// Configuration

// URL for more information about the Flow notification system
$wgFlowHelpPage = '//www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:Flow';

// $wgFlowCluster will define what external DB server should be used.
// If set to false, the current database (wfGetDB) will be used to read/write
// data from/to. If Flow data is supposed to be stored on an external database,
// set the value of this variable to the $wgExternalServers key representing
// that external connection.
$wgFlowCluster = false;

// Database to use for Flow metadata.  Set to false to use the wiki db.  Any number of wikis can
// and should share the same Flow database.
$wgFlowDefaultWikiDb = false;

// Used for content storage.  False to store content in flow db. Otherwise a cluster or
// list of clusters to use with ExternalStore.  Provided clusters must exist in
// $wgExternalStores. Multiple clusters required for HA, so inserts can continue
// if one of the masters is down for maint or any other reason.
// ex:
//     $wgFlowExternalStore = array( 'DB://cluster24', 'DB://cluster25' );
$wgFlowExternalStore = false;

// By default, Flow will store data in wikitext format. It's also the format supported
// by the most basic "editor": none; in which case no conversion (Parsoid) will be needed.
// the only conversion needed it wikitext -> HTML for outputting the content, which will
// then be handled by the parser.
// On high-volume wikis, it's beneficial to save HTML to the database (to avoid having to
// parse it every time for output), but then you'll have to make sure Parsoid is up and
// running, as it'll be necessary to convert HTML to wikitext for the basic editor.
// (n.b. to use VisualEditor, you'll definitely need Parsoid, so if you do support VE,
// might as well set this to HTML right away)
$wgFlowContentFormat = 'html'; // possible values: html|wikitext XXX bug 70148 with wikitext

// Flow Parsoid config
// If null, VE's defaults (if available) will be used
$wgFlowParsoidURL = null; // defaults to $wgVisualEditorParsoidURL
$wgFlowParsoidPrefix = null; // defaults to $wgVisualEditorParsoidPrefix
$wgFlowParsoidTimeout = null; // defaults to $wgVisualEditorParsoidTimeout

// Flow Configuration for EventLogging
$wgFlowConfig = array(
	'version' => '0.1.0',
);

// When visiting the flow for an article but not specifying what type of workflow should be viewed,
// use this workflow
$wgFlowDefaultWorkflow = 'discussion';

// Limits for paging
$wgFlowDefaultLimit = 10;
$wgFlowMaxLimit = 100;

// Echo notification subscription preference
$wgDefaultUserOptions['echo-subscriptions-web-flow-discussion'] = true;
$wgDefaultUserOptions['echo-subscriptions-email-flow-discussion'] = false;

// Maximum number of users that can be mentioned in one comment
$wgFlowMaxMentionCount = 100;

// Max threading depth
$wgFlowMaxThreadingDepth = 3;

// A list of editors to use, in priority order
$wgFlowEditorList = array( 'none' );  // EXPERIMENTAL prepend 'visualeditor'

// Action details config file
require $dir . 'FlowActions.php';

// Register activity log formatter hooks
foreach( $wgFlowActions as $action => $options ) {
	if ( is_string( $options ) ) {
		continue;
	}
	if ( isset( $options['log_type'] ) ) {
		$log = $options['log_type'];

		// Some actions are more complex closures - to be added manually.
		if ( is_string( $log ) ) {
			$wgLogActionsHandlers["$log/flow-$action"] = 'Flow\Log\Formatter';
		}
	}
}
// Manually add that more complex actions
$wgLogActionsHandlers['delete/flow-restore-post'] = 'Flow\Log\Formatter';
$wgLogActionsHandlers['suppress/flow-restore-post'] = 'Flow\Log\Formatter';
$wgLogActionsHandlers['delete/flow-restore-topic'] = 'Flow\Log\Formatter';
$wgLogActionsHandlers['suppress/flow-restore-topic'] = 'Flow\Log\Formatter';

// Register URL actions
foreach( $wgFlowActions as $action => $options ) {
	if ( is_array( $options ) && isset( $options['handler-class'] ) ) {
		$wgActions[$action] = true;
	}
}

// Set this to false to disable all memcache usage.  Do not just turn the cache
// back on, it will be out of sync with the database.  There is not yet an official
// process for re-sync'ing the cache yet, currently the per-index versions would
// need to incremented(ask the flow team).
//
// This will reduce, but not necessarily kill, performance.  The queries issued
// will be the queries necessary to fill the cache rather than only the queries
// needed to answer the request.  A bit of a refactor in ObjectManager::findMulti
// to allow query without indexes, along with adjusting container.php to only
// include the indexes when this is true, would get most of the way twords making
// this a reasonably performant option.
$wgFlowUseMemcache = true;

// The default length of time to cache flow data in memcache.  This value can be tuned
// in conjunction with measurements of cache hit/miss ratios to achieve the desired
// tradeoff between memory usage, db queries, and response time. The initial default
// of 3 days means Flow will attempt to keep in memcache all data models requested in
// the last 3 days.
$wgFlowCacheTime = 60 * 60 * 24 * 3;
// A version string appended to cache keys. Bump this if cache format or logic changes.
// Flow can be a cross-wiki database accessed by wikis running different versions of the
// Flow code; WMF sometimes overrides this globally in wmf-config/CommonSettings.php
$wgFlowCacheVersion = '4.5';

// Custom group name for AbuseFilter
// Acceptable values:
// * a specific value for flow-specific filters
// * 'default' to use core filters; make sure they are compatible with both core
//   and Flow (e.g. Flow has no 'summary' variable to test on)
// * false to not use AbuseFilter
$wgFlowAbuseFilterGroup = 'flow';

// AbuseFilter emergency disable values for Flow
$wgFlowAbuseFilterEmergencyDisableThreshold = 0.10;
$wgFlowAbuseFilterEmergencyDisableCount = 50;
$wgFlowAbuseFilterEmergencyDisableAge = 86400; // One day.

// Actions that must pass through to MediaWiki on flow enabled pages
$wgFlowCoreActionWhitelist = array( 'info', 'protect', 'unprotect', 'unwatch', 'watch' );

// When set to true Flow will compile templates into their intermediate forms
// on every run.  When set to false Flow will use the versions already written
// to disk. Production should always have this set to false.
$wgFlowServerCompileTemplates = false;

