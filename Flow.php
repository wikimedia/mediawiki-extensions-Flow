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
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Flow',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Flow',
	'author' => array( 'Erik Bernhardson', 'Mathias Mulle', 'Benny Situ' ),
	'descriptionmsg' => 'flow-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgExtensionMessagesFiles['Flow'] = $dir . 'Flow.i18n.php';

// Classes fulfilling the mediawiki extension architecture
// note: SRP would say a 'FlowHooks' class should not exist
$wgAutoloadClasses['FlowHooks'] = $dir . 'Hooks.php';

// Various helper classes
$wgAutoloadClasses['Pimple'] = $dir . 'vendor/Pimple.php';
$wgAutoloadClasses['Flow\Container'] = $dir . 'includes/Container.php';
$wgAutoloadClasses['Flow\DbFactory'] = $dir . 'includes/DbFactory.php';
$wgAutoloadClasses['Flow\Templating'] = $dir . 'includes/Templating.php';
$wgAutoloadClasses['Flow\UrlGenerator'] = $dir . 'includes/UrlGenerator.php';
$wgAutoloadClasses['Flow\WorkflowLoader'] = $dir . 'includes/WorkflowLoader.php';
$wgAutoloadClasses['Flow\WorkflowLoaderFactory'] = $dir . 'includes/WorkflowLoader.php';

// Classes that model our data
$wgAutoloadClasses['Flow\Model\Definition'] = $dir . 'includes/Model/Definition.php';
$wgAutoloadClasses['Flow\Model\Metadata'] = $dir . 'includes/Model/Metadata.php';
$wgAutoloadClasses['Flow\Model\AbstractRevision'] = $dir . 'includes/Model/AbstractRevision.php';
$wgAutoloadClasses['Flow\Model\PostRevision'] = $dir . 'includes/Model/PostRevision.php';
$wgAutoloadClasses['Flow\Model\Summary'] = $dir . 'includes/Model/Summary.php';
$wgAutoloadClasses['Flow\Model\TopicListEntry'] = $dir . 'includes/Model/TopicListEntry.php';
$wgAutoloadClasses['Flow\Model\Workflow'] = $dir . 'includes/Model/Workflow.php';
$wgAutoloadClasses['Flow\Model\UUID'] = "$dir/includes/Model/UUID.php";

// Classes that deal with database interaction between database and the models
$wgAutoloadClasses['Flow\Repository\TreeRepository'] = $dir . 'includes/Repository/TreeRepository.php';
$wgAutoloadClasses['Flow\Repository\MultiGetList'] = $dir . 'includes/Repository/MultiGetList.php';
$wgAutoloadClasses['Flow\Data\ManagerGroup'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\ObjectLocator'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\ObjectManager'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\LifecycleHandler'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\Index'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\UniqueIndex'] = $dir . 'includes/Data/ObjectManager.php';
$wgAutoloadClasses['Flow\Data\SecondaryIndex'] = $dir . 'includes/Data/ObjectManager.php';
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

// database interaction for singular models
$wgAutoloadClasses['Flow\Data\PostRevisionStorage'] = $dir . 'includes/Data/RevisionStorage.php';
$wgAutoloadClasses['Flow\Data\SummaryRevisionStorage'] = $dir . 'includes/Data/RevisionStorage.php';

// The individual workflow pieces
$wgAutoloadClasses['Flow\Block\Block'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\AbstractBlock'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\BlockView'] = $dir . 'includes/Block/Block.php';
$wgAutoloadClasses['Flow\Block\SummaryBlock'] = $dir . 'includes/Block/Summary.php';
$wgAutoloadClasses['Flow\Block\SummaryView'] = $dir . 'includes/Block/Summary.php';
$wgAutoloadClasses['Flow\Block\TopicListBlock'] = $dir . 'includes/Block/TopicList.php';
$wgAutoloadClasses['Flow\Block\TopicListView'] = $dir . 'includes/Block/TopicList.php';
$wgAutoloadClasses['Flow\Block\TopicBlock'] = $dir . 'includes/Block/Topic.php';
$wgAutoloadClasses['Flow\Block\TopicView'] = $dir . 'includes/Block/Topic.php';

// Special page for rendering flows
$wgAutoloadClasses['SpecialFlow'] = $dir . 'special/SpecialFlow.php';
$wgSpecialPages['Flow'] = 'SpecialFlow';
$wgSpecialPageGroups['Flow'] = 'unknown';

// API modules
$wgAutoloadClasses['ApiQueryFlow'] = "$dir/includes/api/ApiQueryFlow.php";
$wgAPIListModules['flow'] = 'ApiQueryFlow';

// Housekeeping hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlowHooks::getSchemaUpdates';
//$wgHooks['GetPreferences'][] = 'FlowHooks::getPreferences';
$wgHooks['UnitTestsList'][] = 'FlowHooks::getUnitTests';

// Extension initialization
$wgExtensionFunctions[] = 'FlowHooks::initFlowExtension';

$flowResourceTemplate = array(
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'Flow/modules',
	'group' => 'ext.flow',
);

$wgResourceModules += array(
	'ext.flow.base' => $flowResourceTemplate + array(
		'styles' => 'base/ext.flow.base.css',
		'scripts' => 'base/ext.flow.base.js',
		'dependencies' => array(
			'ext.visualEditor.standalone',
		),
		'messages' => array(
		),
	),
);

// Configuration

// URL for more information about the Flow notification system
$wgFlowHelpPage = '//www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:Flow';

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

// Flow Configuration for EventLogging
$wgFlowConfig = array(
	'version' => '0.1.0',
);

// When visiting the flow for an article but not specifying what type of workflow should be viewed,
// use this workflow
$wgFlowDefaultWorkflow = 'discussion';

$wgFlowParsoidURL = 'http://localhost:8000';
$wgFlowParsoidPrefix = '_wikitext';
$wgFlowParsoidTimeout = 100;

