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

// Autoload
$dir = __DIR__ . '/';
require $dir . 'Resources.php';

$wgExtensionMessagesFiles['Flow'] = $dir . 'Flow.i18n.php';

$wgAutoloadClasses['FlowInsertDefaultDefinitions'] = $dir . 'maintenance/FlowInsertDefaultDefinitions.php';

// Classes fulfilling the mediawiki extension architecture
// note: SRP would say a 'FlowHooks' class should not exist
$wgAutoloadClasses['FlowHooks'] = $dir . 'Hooks.php';

// Various helper classes
$wgAutoloadClasses['Pimple'] = $dir . 'vendor/Pimple.php';
$wgAutoloadClasses['Flow\Container'] = $dir . 'includes/Container.php';
$wgAutoloadClasses['Flow\DbFactory'] = $dir . 'includes/DbFactory.php';
$wgAutoloadClasses['Flow\ParsoidUtils'] = $dir . 'includes/ParsoidUtils.php';
$wgAutoloadClasses['Flow\Templating'] = $dir . 'includes/Templating.php';
$wgAutoloadClasses['Flow\UrlGenerator'] = $dir . 'includes/UrlGenerator.php';
$wgAutoloadClasses['Flow\View'] = $dir . 'includes/View.php';
$wgAutoloadClasses['Flow\WorkflowLoader'] = $dir . 'includes/WorkflowLoader.php';
$wgAutoloadClasses['Flow\WorkflowLoaderFactory'] = $dir . 'includes/WorkflowLoader.php';
$wgAutoloadClasses['Flow\OccupationController'] = $dir . 'includes/TalkpageManager.php';
$wgAutoloadClasses['Flow\TalkpageManager'] = $dir . 'includes/TalkpageManager.php';
$wgAutoloadClasses['Flow\NotificationFormatter'] = $dir . 'includes/Notifications/Formatter.php';
$wgAutoloadClasses['Flow\NotificationController'] = $dir . 'includes/Notifications/Controller.php';
$wgAutoloadClasses['Flow\FlowActions'] = $dir . 'includes/FlowActions.php';
$wgAutoloadClasses['Flow\RevisionActionPermissions'] = $dir . 'includes/RevisionActionPermissions.php';

// Classes that model our data
$wgAutoloadClasses['Flow\Model\Definition'] = $dir . 'includes/Model/Definition.php';
$wgAutoloadClasses['Flow\Model\Metadata'] = $dir . 'includes/Model/Metadata.php';
$wgAutoloadClasses['Flow\Model\AbstractRevision'] = $dir . 'includes/Model/AbstractRevision.php';
$wgAutoloadClasses['Flow\Model\PostRevision'] = $dir . 'includes/Model/PostRevision.php';
$wgAutoloadClasses['Flow\Model\Header'] = $dir . 'includes/Model/Header.php';
$wgAutoloadClasses['Flow\Model\TopicListEntry'] = $dir . 'includes/Model/TopicListEntry.php';
$wgAutoloadClasses['Flow\Model\Workflow'] = $dir . 'includes/Model/Workflow.php';
$wgAutoloadClasses['Flow\Model\UUID'] = "$dir/includes/Model/UUID.php";

// Helpers for templating
$wgAutoloadClasses['Flow\View\PostActionMenu'] = "$dir/includes/View/PostActionMenu.php";
$wgAutoloadClasses['Flow\View\History\History'] = "$dir/includes/View/History/History.php";
$wgAutoloadClasses['Flow\View\History\HistoryRecord'] = "$dir/includes/View/History/HistoryRecord.php";
$wgAutoloadClasses['Flow\View\History\HistoryBundle'] = "$dir/includes/View/History/HistoryBundle.php";
$wgAutoloadClasses['Flow\View\History\HistoryRenderer'] = "$dir/includes/View/History/HistoryRenderer.php";
$wgAutoloadClasses['Flow\View\Post'] = "$dir/includes/View/Post.php";

// Classes that deal with database interaction between database and the models
$wgAutoloadClasses['Flow\Repository\TreeRepository'] = $dir . 'includes/Repository/TreeRepository.php';
$wgAutoloadClasses['Flow\Repository\MultiGetList'] = $dir . 'includes/Repository/MultiGetList.php';
$wgAutoloadClasses['Flow\Data\ManagerGroup'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\ObjectLocator'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\ObjectManager'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\LifecycleHandler'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\Index'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\UniqueFeatureIndex'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\TopKIndex'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\TopicHistoryIndex'] = $dir . 'includes/Data/RevisionStorage.php';
$wgAutoloadClasses['Flow\Data\BoardHistoryStorage'] = $dir . 'includes/Data/BoardHistoryStorage.php';
$wgAutoloadClasses['Flow\Data\BoardHistoryIndex'] = $dir . 'includes/Data/BoardHistoryStorage.php';
$wgAutoloadClasses['Flow\Data\ObjectStorage'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\BasicDbStorage'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\ObjectMapper'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\BasicObjectMapper'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\BufferedCache'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\LocalBufferedCache'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\SortArrayByKeys'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\RootPostLoader'] = $dir . 'includes/Data/RootPostLoader.php';
$wgAutoloadClasses['Flow\Data\MultiDimArray'] = $dir . 'includes/Data/MultiDimArray.php';
$wgAutoloadClasses['Flow\Data\ResultDuplicator'] = $dir . 'includes/Data/MultiDimArray.php';
$wgAutoloadClasses['Flow\Data\Pager'] = $dir . 'includes/Data/Pager.php';
$wgAutoloadClasses['Flow\Data\PagerPage'] = $dir . 'includes/Data/PagerPage.php';
$wgAutoloadClasses['Flow\Data\RecentChanges'] = $dir . 'includes/Data/RecentChanges.php';
$wgAutoloadClasses['Flow\Data\PostRevisionRecentChanges'] = $dir . 'includes/Data/RecentChanges.php';
$wgAutoloadClasses['Flow\Data\HeaderRecentChanges'] = $dir . 'includes/Data/RecentChanges.php';
$wgAutoloadClasses['Flow\Data\Merger'] = $dir . 'includes/Data/RevisionStorage.php';
$wgAutoloadClasses['Flow\RecentChanges\Formatter'] = $dir . 'includes/RecentChanges/Formatter.php';
$wgAutoloadClasses['Flow\Log\Logger'] = $dir . 'includes/Log/Logger.php';
$wgAutoloadClasses['Flow\Log\Formatter'] = $dir . 'includes/Log/Formatter.php';
$wgAutoloadClasses['Flow\Log\PostModerationLogger'] = $dir . 'includes/Log/PostModerationLogger.php';

// database interaction for singular models
$wgAutoloadClasses['Flow\Data\RevisionStorage'] = $dir . 'includes/Data/RevisionStorage.php';
$wgAutoloadClasses['Flow\Data\PostRevisionStorage'] = $dir . 'includes/Data/RevisionStorage.php';
$wgAutoloadClasses['Flow\Data\HeaderRevisionStorage'] = $dir . 'includes/Data/RevisionStorage.php';

// The individual workflow pieces
$wgAutoloadClasses['Flow\Block\Block'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\AbstractBlock'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\BlockView'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\HeaderBlock'] = $dir . 'includes/Block/Header.php';
$wgAutoloadClasses['Flow\Block\TopicListBlock'] = $dir . 'includes/Block/TopicList.php';
$wgAutoloadClasses['Flow\Block\TopicListView'] = $dir . 'includes/Block/TopicList.php';
$wgAutoloadClasses['Flow\Block\TopicBlock'] = $dir . 'includes/Block/Topic.php';
$wgAutoloadClasses['Flow\Block\TopicView'] = $dir . 'includes/Block/Topic.php';

// API modules
$wgAutoloadClasses['ApiQueryFlow'] = "$dir/includes/api/ApiQueryFlow.php";
$wgAutoloadClasses['ApiParsoidUtilsFlow'] = "$dir/includes/api/ApiParsoidUtilsFlow.php";
$wgAutoloadClasses['ApiFlow'] = "$dir/includes/api/ApiFlow.php";
$wgAPIListModules['flow'] = 'ApiQueryFlow';
$wgAPIModules['flow-parsoid-utils'] = 'ApiParsoidUtilsFlow';
$wgAPIModules['flow'] = 'ApiFlow';

// Housekeeping hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlowHooks::getSchemaUpdates';
//$wgHooks['GetPreferences'][] = 'FlowHooks::getPreferences';
$wgHooks['UnitTestsList'][] = 'FlowHooks::getUnitTests';
$wgHooks['ApiTokensGetTokenTypes'][] = 'FlowHooks::onApiTokensGetTokenTypes';
$wgHooks['MediaWikiPerformAction'][] = 'FlowHooks::onPerformAction';
$wgHooks['OldChangesListRecentChangesLine'][] = 'FlowHooks::onOldChangesListRecentChangesLine';
$wgHooks['SkinTemplateNavigation::Universal'][] = 'FlowHooks::onSkinTemplateNavigation';
$wgHooks['Article::MissingArticleConditions'][] = 'FlowHooks::onMissingArticleConditions';
$wgHooks['SpecialWatchlistGetNonRevisionTypes'][] = 'FlowHooks::onSpecialWatchlistGetNonRevisionTypes';
$wgHooks['UserGetReservedNames'][] = 'FlowHooks::onUserGetReservedNames';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'FlowHooks::onResourceLoaderGetConfigVars';

// Extension initialization
$wgExtensionFunctions[] = 'FlowHooks::initFlowExtension';

// User permissions
$wgGroupPermissions['autoconfirmed']['flow-hide'] = true;
$wgGroupPermissions['sysop']['flow-hide'] = true;
$wgGroupPermissions['sysop']['flow-delete'] = true;
$wgGroupPermissions['sysop']['flow-edit-post'] = true;
$wgGroupPermissions['oversight']['flow-suppress'] = true;

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
$wgFlowContentFormat = 'wikitext'; // possible values: wikitext|html

// Flow Parsoid config
// If null, VE's defaults (if available) will be used
$wgFlowParsoidURL = null; // defaults to $wgVisualEditorParsoidURL
$wgFlowParsoidPrefix = null; // defaults to $wgVisualEditorParsoidPrefix
$wgFlowParsoidTimeout = null; // defaults to $wgVisualEditorParsoidTimeout

// Flow Configuration for EventLogging
$wgFlowConfig = array(
	'version' => '0.1.0',
);

// Salt used to generate edit tokens for authenticating Flow actions
$wgFlowTokenSalt = 'flow';

// When visiting the flow for an article but not specifying what type of workflow should be viewed,
// use this workflow
$wgFlowDefaultWorkflow = 'discussion';

// Limits for paging
$wgFlowDefaultLimit = 10;
$wgFlowMaxLimit = 50;

// Echo notification subscription preference
$wgDefaultUserOptions['echo-subscriptions-web-flow-discussion'] = true;
$wgDefaultUserOptions['echo-subscriptions-email-flow-discussion'] = true;

// Maximum number of users that can be mentioned in one comment
$wgFlowMaxMentionCount = 100;

// Pages to occupy is an array of normalised page names, e.g. array( 'User talk:Zomg' ).
$wgFlowOccupyPages = array();

// Namespaces to occupy is an array of NS_* constants, e.g. array( NS_USER_TALK ).
$wgFlowOccupyNamespaces = array();

// Max threading depth
$wgFlowMaxThreadingDepth = 2;

// A list of editors to use, in priority order
$wgFlowEditorList = array( 'visualeditor', 'none' );

// Action details config file
require $dir . 'FlowActions.php';

// Register activity log formatter hooks
foreach( $wgFlowActions as $action => $options ) {
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
