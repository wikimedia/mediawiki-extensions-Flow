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

$wgMessagesDirs['Flow'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['Flow'] = $dir . 'Flow.i18n.php';

$wgAutoloadClasses['FlowInsertDefaultDefinitions'] = $dir . 'maintenance/FlowInsertDefaultDefinitions.php';

// Classes fulfilling the mediawiki extension architecture
// note: SRP would say a 'FlowHooks' class should not exist
$wgAutoloadClasses['FlowHooks'] = $dir . 'Hooks.php';

// Various vendor classes
$wgAutoloadClasses['Pimple'] = $dir . 'vendor/Pimple.php';
$wgAutoloadClasses['LightnCandy'] = $dir . 'vendor/lightncandy.php';
$wgAutoloadClasses['LCRun2'] = $dir . 'vendor/lightncandy.php';

// Various helper classes
$wgAutoloadClasses['Flow\Container'] = $dir . 'includes/Container.php';
$wgAutoloadClasses['Flow\DbFactory'] = $dir . 'includes/DbFactory.php';
$wgAutoloadClasses['Flow\Templating'] = $dir . 'includes/Templating.php';
$wgAutoloadClasses['Flow\TemplateHelper'] = $dir . 'includes/TemplateHelper.php';
$wgAutoloadClasses['Flow\Parsoid\Utils'] = $dir . 'includes/Parsoid/Utils.php';
$wgAutoloadClasses['Flow\Parsoid\Controller'] = $dir . 'includes/Parsoid/Controller.php';
$wgAutoloadClasses['Flow\Parsoid\ContentFixer'] = $dir . 'includes/Parsoid/ContentFixer.php';
$wgAutoloadClasses['Flow\Parsoid\Redlinker'] = $dir . 'includes/Parsoid/Redlinker.php';
$wgAutoloadClasses['Flow\Parsoid\BadImageRemover'] = $dir . 'includes/Parsoid/BadImageRemover.php';
$wgAutoloadClasses['Flow\Anchor'] = $dir . 'includes/Anchor.php';
$wgAutoloadClasses['Flow\BaseUrlGenerator'] = $dir . 'includes/BaseUrlGenerator.php';
$wgAutoloadClasses['Flow\Parsoid\ReferenceExtractor'] = $dir . 'includes/Parsoid/ReferenceExtractor.php';
$wgAutoloadClasses['Flow\UrlGenerator'] = $dir . 'includes/UrlGenerator.php';
$wgAutoloadClasses['Flow\View'] = $dir . 'includes/View.php';
$wgAutoloadClasses['Flow\WorkflowLoader'] = $dir . 'includes/WorkflowLoader.php';
$wgAutoloadClasses['Flow\WorkflowLoaderFactory'] = $dir . 'includes/WorkflowLoader.php';
$wgAutoloadClasses['Flow\OccupationController'] = $dir . 'includes/TalkpageManager.php';
$wgAutoloadClasses['Flow\TalkpageManager'] = $dir . 'includes/TalkpageManager.php';
$wgAutoloadClasses['Flow\NotificationFormatter'] = $dir . 'includes/Notifications/Formatter.php';
$wgAutoloadClasses['Flow\NotificationController'] = $dir . 'includes/Notifications/Controller.php';
$wgAutoloadClasses['Flow\SpamFilter\Controller'] = $dir . 'includes/SpamFilter/Controller.php';
$wgAutoloadClasses['Flow\SpamFilter\SpamFilter'] = $dir . 'includes/SpamFilter/SpamFilter.php';
$wgAutoloadClasses['Flow\SpamFilter\SpamRegex'] = $dir . 'includes/SpamFilter/SpamRegex.php';
$wgAutoloadClasses['Flow\SpamFilter\SpamBlacklist'] = $dir . 'includes/SpamFilter/SpamBlacklist.php';
$wgAutoloadClasses['Flow\SpamFilter\AbuseFilter'] = $dir . 'includes/SpamFilter/AbuseFilter.php';
$wgAutoloadClasses['Flow\FlowActions'] = $dir . 'includes/FlowActions.php';
$wgAutoloadClasses['Flow\RevisionActionPermissions'] = $dir . 'includes/RevisionActionPermissions.php';
$wgAutoloadClasses['Flow\TermsOfUse'] = $dir . 'includes/TermsOfUse.php';
$wgAutoloadClasses['Flow\ReferenceClarifier'] = $dir . 'includes/ReferenceClarifier.php';

// Classes that model our data
$wgAutoloadClasses['Flow\Model\Definition'] = $dir . 'includes/Model/Definition.php';
$wgAutoloadClasses['Flow\Model\Metadata'] = $dir . 'includes/Model/Metadata.php';
$wgAutoloadClasses['Flow\Model\AbstractRevision'] = $dir . 'includes/Model/AbstractRevision.php';
$wgAutoloadClasses['Flow\Model\PostRevision'] = $dir . 'includes/Model/PostRevision.php';
$wgAutoloadClasses['Flow\Model\Header'] = $dir . 'includes/Model/Header.php';
$wgAutoloadClasses['Flow\Model\AbstractSummary'] = $dir . 'includes/Model/AbstractSummary.php';
$wgAutoloadClasses['Flow\Model\PostSummary'] = $dir . 'includes/Model/PostSummary.php';
$wgAutoloadClasses['Flow\Model\TopicListEntry'] = $dir . 'includes/Model/TopicListEntry.php';
$wgAutoloadClasses['Flow\Model\Workflow'] = $dir . 'includes/Model/Workflow.php';
$wgAutoloadClasses['Flow\Model\UUID'] = "$dir/includes/Model/UUID.php";
$wgAutoloadClasses['Flow\Collection\AbstractCollection'] = $dir . 'includes/Collection/AbstractCollection.php';
$wgAutoloadClasses['Flow\Collection\CollectionCache'] = $dir . 'includes/Collection/CollectionCache.php';
$wgAutoloadClasses['Flow\Collection\LocalCacheAbstractCollection'] = $dir . 'includes/Collection/LocalCacheAbstractCollection.php';
$wgAutoloadClasses['Flow\Collection\PostCollection'] = $dir . 'includes/Collection/PostCollection.php';
$wgAutoloadClasses['Flow\Collection\HeaderCollection'] = $dir . 'includes/Collection/HeaderCollection.php';
$wgAutoloadClasses['Flow\Collection\PostSummaryCollection'] = $dir . 'includes/Collection/PostSummaryCollection.php';

// Helpers for templating
$wgAutoloadClasses['Flow\View\PostActionMenu'] = "$dir/includes/View/PostActionMenu.php";
$wgAutoloadClasses['Flow\View\History\History'] = "$dir/includes/View/History/History.php";
$wgAutoloadClasses['Flow\View\History\HistoryRecord'] = "$dir/includes/View/History/HistoryRecord.php";
$wgAutoloadClasses['Flow\View\History\HistoryBundle'] = "$dir/includes/View/History/HistoryBundle.php";
$wgAutoloadClasses['Flow\View\History\HistoryRenderer'] = "$dir/includes/View/History/HistoryRenderer.php";
$wgAutoloadClasses['Flow\View\Post'] = "$dir/includes/View/Post.php";
$wgAutoloadClasses['Flow\View\RevisionCreatable'] = "$dir/includes/View/Revision.php";
$wgAutoloadClasses['Flow\View\RevisionView'] = "$dir/includes/View/Revision.php";
$wgAutoloadClasses['Flow\View\PostRevisionView'] = "$dir/includes/View/Revision.php";
$wgAutoloadClasses['Flow\View\HeaderRevisionView'] = "$dir/includes/View/Revision.php";
$wgAutoloadClasses['Flow\View\PostSummaryRevisionView'] = "$dir/includes/View/Revision.php";

// Classes that deal with database interaction between database and the models
$wgAutoloadClasses['Flow\Repository\TreeRepository'] = $dir . 'includes/Repository/TreeRepository.php';
$wgAutoloadClasses['Flow\Repository\MultiGetList'] = $dir . 'includes/Repository/MultiGetList.php';
$wgAutoloadClasses['Flow\Data\ManagerGroup'] = $dir . 'includes/Data/ManagerGroup.php';
$wgAutoloadClasses['Flow\Data\ObjectLocator'] = $dir . 'includes/Data/ObjectLocator.php';
$wgAutoloadClasses['Flow\Data\ObjectManager'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\LifecycleHandler'] = $dir . 'includes/Data/LifecycleHandler.php';
$wgAutoloadClasses['Flow\Data\Index'] = $dir . 'includes/Data/Index.php';
$wgAutoloadClasses['Flow\Data\FeatureIndex'] = $dir . 'includes/Data/FeatureIndex.php';
$wgAutoloadClasses['Flow\Data\UniqueFeatureIndex'] = $dir . 'includes/Data/UniqueFeatureIndex.php';
$wgAutoloadClasses['Flow\Data\TopKIndex'] = $dir . 'includes/Data/TopKIndex.php';
$wgAutoloadClasses['Flow\Data\TopicHistoryIndex'] = $dir . 'includes/Data/TopicHistoryIndex.php';
$wgAutoloadClasses['Flow\Data\TopicHistoryStorage'] = $dir . 'includes/Data/TopicHistoryStorage.php';
$wgAutoloadClasses['Flow\Data\BoardHistoryStorage'] = $dir . 'includes/Data/BoardHistoryStorage.php';
$wgAutoloadClasses['Flow\Data\BoardHistoryIndex'] = $dir . 'includes/Data/BoardHistoryIndex.php';
$wgAutoloadClasses['Flow\Data\ObjectStorage'] = $dir . 'includes/Data/ObjectStorage.php';
$wgAutoloadClasses['Flow\Data\DbStorage'] = $dir . 'includes/Data/DbStorage.php';
$wgAutoloadClasses['Flow\Data\BasicDbStorage'] = $dir . 'includes/Data/BasicDbStorage.php';
$wgAutoloadClasses['Flow\Data\ObjectMapper'] = $dir . 'includes/Data/ObjectMapper.php';
$wgAutoloadClasses['Flow\Data\BasicObjectMapper'] = $dir . 'includes/Data/BasicObjectMapper.php';
$wgAutoloadClasses['Flow\Data\CachingObjectMapper'] = $dir . 'includes/Data/CachingObjectMapper.php';
$wgAutoloadClasses['Flow\Data\BufferedCache'] = $dir . 'includes/Data/BufferedCache.php';
$wgAutoloadClasses['Flow\Data\LocalBufferedCache'] = $dir . 'includes/Data/LocalBufferedCache.php';
$wgAutoloadClasses['Flow\Data\SortArrayByKeys'] = $dir . 'includes/Data/SortArrayByKeys.php';
$wgAutoloadClasses['Flow\Data\RootPostLoader'] = $dir . 'includes/Data/RootPostLoader.php';
$wgAutoloadClasses['Flow\Data\MultiDimArray'] = $dir . 'includes/Data/MultiDimArray.php';
$wgAutoloadClasses['Flow\Data\ResultDuplicator'] = $dir . 'includes/Data/ResultDuplicator.php';
$wgAutoloadClasses['Flow\Data\Pager'] = $dir . 'includes/Data/Pager.php';
$wgAutoloadClasses['Flow\Data\PagerPage'] = $dir . 'includes/Data/PagerPage.php';
$wgAutoloadClasses['Flow\Data\RecentChanges'] = $dir . 'includes/Data/RecentChanges.php';
$wgAutoloadClasses['Flow\Data\PostRevisionRecentChanges'] = $dir . 'includes/Data/PostRevisionRecentChanges.php';
$wgAutoloadClasses['Flow\Data\PostSummaryRecentChanges'] = $dir . 'includes/Data/PostSummaryRecentChanges.php';
$wgAutoloadClasses['Flow\Data\HeaderRecentChanges'] = $dir . 'includes/Data/HeaderRecentChanges.php';
$wgAutoloadClasses['Flow\Data\Compactor'] = $dir . 'includes/Data/Compactor.php';
$wgAutoloadClasses['Flow\Data\FeatureCompactor'] = $dir . 'includes/Data/FeatureCompactor.php';
$wgAutoloadClasses['Flow\Data\ShallowCompactor'] = $dir . 'includes/Data/ShallowCompactor.php';
$wgAutoloadClasses['Flow\Data\Merger'] = $dir . 'includes/Data/Merger.php';
$wgAutoloadClasses['Flow\Data\RawSql'] = $dir . 'includes/Data/RawSql.php';
$wgAutoloadClasses['Flow\Log\Logger'] = $dir . 'includes/Log/Logger.php';
$wgAutoloadClasses['Flow\Log\Formatter'] = $dir . 'includes/Log/Formatter.php';
$wgAutoloadClasses['Flow\Log\PostModerationLogger'] = $dir . 'includes/Log/PostModerationLogger.php';

// Collect data and format revisions into html
$wgAutoloadClasses['Flow\Formatter\AbstractFormatter'] = $dir . 'includes/Formatter/AbstractFormatter.php';
$wgAutoloadClasses['Flow\Formatter\AbstractQuery'] = $dir . 'includes/Formatter/AbstractQuery.php';
$wgAutoloadClasses['Flow\Formatter\FormatterRow'] = $dir . 'includes/Formatter/AbstractQuery.php';
$wgAutoloadClasses['Flow\Formatter\CheckUser'] = $dir . 'includes/Formatter/CheckUser.php';
$wgAutoloadClasses['Flow\Formatter\CheckUserQuery'] = $dir . 'includes/Formatter/CheckUserQuery.php';
$wgAutoloadClasses['Flow\Formatter\CheckUserRow'] = $dir . 'includes/Formatter/CheckUserQuery.php';
$wgAutoloadClasses['Flow\Formatter\Contributions'] = $dir . 'includes/Formatter/Contributions.php';
$wgAutoloadClasses['Flow\Formatter\ContributionsQuery'] = $dir . 'includes/Formatter/ContributionsQuery.php';
$wgAutoloadClasses['Flow\Formatter\ContributionsRow'] = $dir . 'includes/Formatter/ContributionsQuery.php';
$wgAutoloadClasses['Flow\Formatter\BoardHistory'] = $dir . 'includes/Formatter/BoardHistory.php';
$wgAutoloadClasses['Flow\Formatter\BoardHistoryQuery'] = $dir . 'includes/Formatter/BoardHistoryQuery.php';
$wgAutoloadClasses['Flow\Formatter\RecentChanges'] = $dir . 'includes/Formatter/RecentChanges.php';
$wgAutoloadClasses['Flow\Formatter\RecentChangesQuery'] = $dir . 'includes/Formatter/RecentChangesQuery.php';
$wgAutoloadClasses['Flow\Formatter\RecentChangesRow'] = $dir . 'includes/Formatter/RecentChangesQuery.php';
$wgAutoloadClasses['Flow\Formatter\SinglePostQuery'] = $dir . 'includes/Formatter/SinglePostQuery.php';
$wgAutoloadClasses['Flow\Formatter\PostSummaryQuery'] = $dir . 'includes/Formatter/PostSummaryQuery.php';
$wgAutoloadClasses['Flow\Formatter\TopicListQuery'] = $dir . 'includes/Formatter/TopicListQuery.php';
$wgAutoloadClasses['Flow\Formatter\TopicHistoryQuery'] = $dir . 'includes/Formatter/TopicHistoryQuery.php';
$wgAutoloadClasses['Flow\Formatter\TopicRow'] = $dir . 'includes/Formatter/TopicRow.php';
$wgAutoloadClasses['Flow\Formatter\IRCLineUrlFormatter'] = $dir . 'includes/Formatter/IRCLineUrlFormatter.php';

// Convert model instances into array of user-visible data
$wgAutoloadClasses['Flow\Formatter\RevisionFormatter'] = $dir . 'includes/Formatter/RevisionFormatter.php';
$wgAutoloadClasses['Flow\Formatter\TopicListFormatter'] = $dir . 'includes/Formatter/TopicListFormatter.php';
$wgAutoloadClasses['Flow\Formatter\TopicFormatter'] = $dir . 'includes/Formatter/TopicFormatter.php';

// On demand username loading from home wiki
$wgAutoloadClasses['Flow\Data\UserNameListener'] = $dir . 'includes/Data/UserNameListener.php';
$wgAutoloadClasses['Flow\Data\UserNameBatch'] = $dir . 'includes/Data/UserNameBatch.php';
$wgAutoloadClasses['Flow\Data\UserNameQuery'] = $dir . 'includes/Data/UserNameQuery.php';
$wgAutoloadClasses['Flow\Data\OneStepUserNameQuery'] = $dir . 'includes/Data/OneStepUserNameQuery.php';
$wgAutoloadClasses['Flow\Data\TwoStepUserNameQuery'] = $dir . 'includes/Data/TwoStepUserNameQuery.php';

// database interaction for singular models
$wgAutoloadClasses['Flow\Data\RevisionStorage'] = $dir . 'includes/Data/RevisionStorage.php';
$wgAutoloadClasses['Flow\Data\PostRevisionStorage'] = $dir . 'includes/Data/PostRevisionStorage.php';
$wgAutoloadClasses['Flow\Data\HeaderRevisionStorage'] = $dir . 'includes/Data/HeaderRevisionStorage.php';
$wgAutoloadClasses['Flow\Data\PostSummaryRevisionStorage'] = $dir . 'includes/Data/PostSummaryRevisionStorage.php';

// The individual workflow pieces
$wgAutoloadClasses['Flow\Block\BoardHistoryBlock'] = $dir . 'includes/Block/BoardHistory.php';
$wgAutoloadClasses['Flow\Block\Block'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\AbstractBlock'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\HeaderBlock'] = $dir . 'includes/Block/Header.php';
$wgAutoloadClasses['Flow\Block\TopicListBlock'] = $dir . 'includes/Block/TopicList.php';
$wgAutoloadClasses['Flow\Block\TopicBlock'] = $dir . 'includes/Block/Topic.php';
$wgAutoloadClasses['Flow\Block\TopicSummaryBlock'] = $dir . 'includes/Block/TopicSummary.php';

// Reference extraction and tracking
$wgAutoloadClasses['Flow\LinksTableUpdater'] = $dir . 'includes/LinksTableUpdater.php';
$wgAutoloadClasses['Flow\Model\Reference'] = "$dir/includes/Model/Reference.php";
$wgAutoloadClasses['Flow\Model\WikiReference'] = "$dir/includes/Model/Reference.php";
$wgAutoloadClasses['Flow\Model\URLReference'] = "$dir/includes/Model/Reference.php";
$wgAutoloadClasses['Flow\Data\ReferenceRecorder'] = "$dir/includes/Data/ReferenceRecorder.php";

// phpunit helper
$wgAutoloadClasses['Flow\Tests\FlowTestCase'] = $dir . 'tests/FlowTestCase.php';
$wgAutoloadClasses['Flow\Tests\PostRevisionTestCase'] = $dir . 'tests/PostRevisionTestCase.php';

// API modules
$wgAutoloadClasses['ApiQueryFlow'] = "$dir/includes/api/ApiQueryFlow.php";
$wgAutoloadClasses['ApiParsoidUtilsFlow'] = "$dir/includes/api/ApiParsoidUtilsFlow.php";
$wgAutoloadClasses['ApiFlow'] = "$dir/includes/api/ApiFlow.php";
$wgAutoloadClasses['ApiFlowBase'] = "$dir/includes/api/ApiFlowBase.php";
$wgAutoloadClasses['ApiFlowCloseOpenTopic'] = "$dir/includes/api/ApiFlowCloseOpenTopic.php";
$wgAutoloadClasses['ApiFlowEditHeader'] = "$dir/includes/api/ApiFlowEditHeader.php";
$wgAutoloadClasses['ApiFlowEditPost'] = "$dir/includes/api/ApiFlowEditPost.php";
$wgAutoloadClasses['ApiFlowEditTitle'] = "$dir/includes/api/ApiFlowEditTitle.php";
$wgAutoloadClasses['ApiFlowEditTopicSummary'] = "$dir/includes/api/ApiFlowEditTopicSummary.php";
$wgAutoloadClasses['ApiFlowModeratePost'] = "$dir/includes/api/ApiFlowModeratePost.php";
$wgAutoloadClasses['ApiFlowModerateTopic'] = "$dir/includes/api/ApiFlowModerateTopic.php";
$wgAutoloadClasses['ApiFlowNewTopic'] = "$dir/includes/api/ApiFlowNewTopic.php";
$wgAutoloadClasses['ApiFlowReply'] = "$dir/includes/api/ApiFlowReply.php";
$wgAutoloadClasses['ApiQueryPropFlowInfo'] = "$dir/includes/api/ApiQueryPropFlowInfo.php";

$wgAPIListModules['flow'] = 'ApiQueryFlow';
$wgAPIModules['flow-parsoid-utils'] = 'ApiParsoidUtilsFlow';
$wgAPIModules['flow'] = 'ApiFlow';
$wgAPIPropModules['flowinfo'] = 'ApiQueryPropFlowInfo';

// Special:Flow
$wgAutoloadClasses['Flow\SpecialFlow'] = $dir . 'SpecialFlow.php';
$wgExtensionMessagesFiles['FlowAlias'] = $dir . 'Flow.alias.php';
$wgSpecialPages['Flow'] = 'Flow\SpecialFlow';
$wgSpecialPageGroups['Flow'] = 'redirects';

// Housekeeping hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlowHooks::getSchemaUpdates';
//$wgHooks['GetPreferences'][] = 'FlowHooks::getPreferences';
$wgHooks['UnitTestsList'][] = 'FlowHooks::getUnitTests';
$wgHooks['MediaWikiPerformAction'][] = 'FlowHooks::onPerformAction';
$wgHooks['OldChangesListRecentChangesLine'][] = 'FlowHooks::onOldChangesListRecentChangesLine';
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
$wgHooks['MakeGlobalVariablesScript'][] = 'FlowHooks::onMakeGlobalVariablesScript';
$wgHooks['CheckUserInsertForRecentChange'][] = 'FlowHooks::onCheckUserInsertForRecentChange';
$wgHooks['SkinMinervaDefaultModules'][] = 'FlowHooks::onSkinMinervaDefaultModules';
$wgHooks['IRCLineURL'][] = 'FlowHooks::onIRCLineURL';
$wgHooks['FlowAddModules'][] = 'Flow\Parsoid\Utils::onFlowAddModules';
$wgHooks['WhatLinksHereProps'][] = 'FlowHooks::onWhatLinksHereProps';
$wgHooks['LinksUpdateConstructed'][] = 'FlowHooks::onLinksUpdateConstructed';

// Extension initialization
$wgExtensionFunctions[] = 'FlowHooks::initFlowExtension';

// User permissions
// Added to $wgFlowGroupPermissions instead of $wgGroupPermissions immediately,
// to easily fetch Flow-specific permissions in tests/PermissionsTest.php.
// If you wish to make local permission changes, add them to $wgGroupPermissions
// directly - tests will fail otherwise, since they'll be based on a different
// permissions config than what's assumed to test.
$wgFlowGroupPermissions = array();
$wgFlowGroupPermissions['user']['flow-hide'] = true;
$wgFlowGroupPermissions['user']['flow-close'] = true;
$wgFlowGroupPermissions['sysop']['flow-hide'] = true;
$wgFlowGroupPermissions['sysop']['flow-close'] = true;
$wgFlowGroupPermissions['sysop']['flow-delete'] = true;
$wgFlowGroupPermissions['sysop']['flow-edit-post'] = true;
$wgFlowGroupPermissions['oversight']['flow-suppress'] = true;
$wgGroupPermissions = array_merge_recursive( $wgGroupPermissions, $wgFlowGroupPermissions );

// Exception
$wgAutoloadClasses['Flow\Exception\FlowException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\InvalidInputException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\InvalidActionException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\InvalidDataException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\PermissionException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\DataModelException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\DataPersistenceException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\WikitextException'] = $dir . 'includes/Exception/ExceptionHandling.php';
$wgAutoloadClasses['Flow\Exception\NoIndexException'] = $dir . 'includes/Exception/ExceptionHandling.php';

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

// Pages to occupy is an array of normalised page names, e.g. array( 'User talk:Zomg' ).
$wgFlowOccupyPages = array();

// Namespaces to occupy is an array of NS_* constants, e.g. array( NS_USER_TALK ).
$wgFlowOccupyNamespaces = array();

// Max threading depth
$wgFlowMaxThreadingDepth = 3;

// A list of editors to use, in priority order
$wgFlowEditorList = array( 'none' );  // EXPERIMENTAL prepend 'visualeditor'

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

// The default length of time to cache flow data in memcache.  This value can be tuned
// in conjunction with measurements of cache hit/miss ratios to achieve the desired
// tradeoff between memory usage, db queries, and response time. The initial default
// of 3 days means Flow will attempt to keep in memcache all data models requested in
// the last 3 days.
$wgFlowCacheTime = 60 * 60 * 24 * 3;
$wgFlowCacheVersion = '4.3';

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

// Directory to store compiled templates. Set to false to require pre-compilation
$wgFlowTemplateTempDir = __DIR__ . '/handlebars';

