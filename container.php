<?php

use Flow\Data\Index\PostRevisionBoardHistoryIndex;
use Flow\Data\Index\PostRevisionTopicHistoryIndex;
use Flow\Data\Index\PostSummaryRevisionBoardHistoryIndex;
use Flow\Data\Index\TopKIndex;
use Flow\Data\Index\UniqueFeatureIndex;
use Flow\Data\Mapper\BasicObjectMapper;
use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Data\ObjectLocator;
use Flow\Data\ObjectManager;
use Flow\Data\Storage\BasicDbStorage;
use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Data\Storage\PostRevisionBoardHistoryStorage;
use Flow\Data\Storage\PostSummaryRevisionBoardHistoryStorage;
use Flow\Data\Storage\PostSummaryRevisionStorage;
use Flow\Data\Storage\TopicListStorage;
use MediaWiki\MediaWikiServices;

// This lets the index handle the initial query from HistoryPager,
// even when the UI limit is 500.  An extra item is requested
// so we know whether to link the pagination.
if ( !defined( 'FLOW_HISTORY_INDEX_LIMIT' ) ) {
	define( 'FLOW_HISTORY_INDEX_LIMIT', 501 );
}

// 501 * OVERFETCH_FACTOR from HistoryQuery + 1
// Basically, this is so we can try to fetch enough extra to handle
// exclude_from_history without retrying.
if ( !defined( 'FLOW_BOARD_TOPIC_HISTORY_POST_INDEX_LIMIT' ) ) {
	define( 'FLOW_BOARD_TOPIC_HISTORY_POST_INDEX_LIMIT', 682 );
}

$c = new Flow\Container;

// MediaWiki
$c['user'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowUser' );
};

// Flow config
$c['flow_actions'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowActions' );
};

// Always returns the correct database for flow storage
$c['db.factory'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowDbFactory' );
};

// Database Access Layer external from main implementation
$c['repository.tree'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowTreeRepository' );
};

$c['url_generator'] = static function ( $c ) {
	return new Flow\UrlGenerator(
		$c['storage.workflow.mapper']
	);
};

$c['watched_items'] = static function ( $c ) {
	return new Flow\WatchedTopicItems(
		$c['user'],
		wfGetDB( DB_REPLICA, 'watchlist' )
	);
};

$c['permissions'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowPermissions' );
};

$c['lightncandy'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowTemplateHandler' );
};

$c['templating'] = static function ( $c ) {
	global $wgArticlePath;

	$wikiLinkFixer = new Flow\Parsoid\Fixer\WikiLinkFixer(
		MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch()
	);
	$badImageRemover = new Flow\Parsoid\Fixer\BadImageRemover(
		[ MediaWikiServices::getInstance()->getBadFileLookup(), 'isBadFile' ]
	);
	$baseHrefFixer = new Flow\Parsoid\Fixer\BaseHrefFixer( $wgArticlePath );
	$extLinkFixer = new Flow\Parsoid\Fixer\ExtLinkFixer();
	$contextFixer = new Flow\Parsoid\ContentFixer(
		$wikiLinkFixer,
		$badImageRemover,
		$baseHrefFixer,
		$extLinkFixer
	);

	return new Flow\Templating(
		$c['repository.username'],
		$contextFixer,
		$c['permissions']
	);
};

// New Storage Impl
$c['flowcache'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowCache' );
};

// Batched username loader
$c['repository.username'] = static function ( $c ) {
	return new Flow\Repository\UserNameBatch(
		new Flow\Repository\UserName\TwoStepUserNameQuery(
			$c['db.factory']
		)
	);
};
$c['collection.cache'] = static function ( $c ) {
	return new Flow\Collection\CollectionCache();
};
// Individual workflow instances
$c['storage.workflow.mapper'] = static function ( $c ) {
	return CachingObjectMapper::model(
		\Flow\Model\Workflow::class,
		[ 'workflow_id' ]
	);
};
$c['storage.workflow'] = static function ( $c ) {
	$workflowBackend = new BasicDbStorage(
		$c['db.factory'],
		'flow_workflow',
		[ 'workflow_id' ]
	);
	$workflowPrimaryIndex = new UniqueFeatureIndex(
		$c['flowcache'],
		$workflowBackend,
		$c['storage.workflow.mapper'],
		'flow_workflow:v2:pk',
		[ 'workflow_id' ]
	);
	$workflowTitleLookupIndex = new TopKIndex(
		$c['flowcache'],
		$workflowBackend,
		$c['storage.workflow.mapper'],
		'flow_workflow:title:v2:',
		[ 'workflow_wiki', 'workflow_namespace', 'workflow_title_text', 'workflow_type' ],
		[
			'shallow' => $workflowPrimaryIndex,
			'limit' => 1,
			'sort' => 'workflow_id'
		]
	);
	$indexes = [
		$workflowPrimaryIndex,
		$workflowTitleLookupIndex,
	];
	$topicPageCreationListener = new Flow\Data\Listener\TopicPageCreationListener(
		$c['occupation_controller'],
		$c['deferred_queue']
	);
	$workflowTopicListListener = new Flow\Data\Listener\WorkflowTopicListListener(
		$c['storage.topic_list'],
		$c['storage.topic_list.indexes.last_updated']
	);
	$listeners = [
		'listener.topicpagecreation' => $topicPageCreationListener,
		'storage.workflow.listeners.topiclist' => $workflowTopicListListener,
	];
	return new ObjectManager(
		$c['storage.workflow.mapper'],
		$workflowBackend,
		$c['db.factory'],
		$indexes,
		$listeners
	);
};
$c['listener.recentchanges'] = static function ( $c ) {
	// Recent change listeners go out to external services and
	// as such must only be run after the transaction is commited.
	return new Flow\Data\Listener\DeferredInsertLifecycleHandler(
		$c['deferred_queue'],
		new Flow\Data\Listener\RecentChangesListener(
			$c['flow_actions'],
			$c['repository.username'],
			new Flow\Data\Utils\RecentChangeFactory,
			$c['formatter.irclineurl']
		)
	);
};
$c['listeners.notification'] = static function ( $c ) {
	// Defer notifications triggering till end of request so we could get
	// article_id in the case of a new topic
	return new Flow\Data\Listener\DeferredInsertLifecycleHandler(
		$c['deferred_queue'],
		new Flow\Data\Listener\NotificationListener(
			$c['controller.notification']
		)
	);
};

$c['storage.post_board_history.backend'] = static function ( $c ) {
	return new PostRevisionBoardHistoryStorage( $c['db.factory'] );
};
$c['storage.post_board_history.indexes.primary'] = static function ( $c ) {
	return new PostRevisionBoardHistoryIndex(
		$c['flowcache'],
		// backend storage
		$c['storage.post_board_history.backend'],
		// data mapper
		$c['storage.post.mapper'],
		// key prefix
		'flow_revision:topic_list_history:post:v2',
		// primary key
		[ 'topic_list_id' ],
		// index options
		[
			'limit' => FLOW_BOARD_TOPIC_HISTORY_POST_INDEX_LIMIT,
			'sort' => 'rev_id',
			'order' => 'DESC'
		],
		$c['storage.topic_list']
	);
};

$c['storage.post_board_history'] = static function ( $c ) {
	$indexes = [ $c['storage.post_board_history.indexes.primary'] ];
	return new ObjectLocator(
		$c['storage.post.mapper'],
		$c['storage.post_board_history.backend'],
		$c['db.factory'],
		$indexes
	);
};

$c['storage.post_summary_board_history.backend'] = static function ( $c ) {
	return new PostSummaryRevisionBoardHistoryStorage( $c['db.factory'] );
};
$c['storage.post_summary_board_history.indexes.primary'] = static function ( $c ) {
	return new PostSummaryRevisionBoardHistoryIndex(
		$c['flowcache'],
		// backend storage
		$c['storage.post_summary_board_history.backend'],
		// data mapper
		$c['storage.post_summary.mapper'],
		// key prefix
		'flow_revision:topic_list_history:post_summary:v2',
		// primary key
		[ 'topic_list_id' ],
		// index options
		[
			'limit' => FLOW_HISTORY_INDEX_LIMIT,
			'sort' => 'rev_id',
			'order' => 'DESC'
		],
		$c['storage.topic_list']
	);
};

$c['storage.post_summary_board_history'] = static function ( $c ) {
	$indexes = [ $c['storage.post_summary_board_history.indexes.primary'] ];
	return new ObjectLocator(
		$c['storage.post_summary.mapper'],
		$c['storage.post_summary_board_history.backend'],
		$c['db.factory'],
		$indexes
	);
};

$c['storage.header'] = static function ( $c ) {
	global $wgFlowExternalStore;
	$headerMapper = CachingObjectMapper::model( \Flow\Model\Header::class, [ 'rev_id' ] );
	$headerBackend = new HeaderRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore
	);
	$headerPrimaryIndex = new UniqueFeatureIndex(
		$c['flowcache'],
		$headerBackend,
		$headerMapper,
		'flow_header:v2:pk',
		[ 'rev_id' ] // primary key
	);
	$headerHeaderLookupIndex = new TopKIndex(
		$c['flowcache'],
		$headerBackend,
		$headerMapper,
		'flow_header:workflow:v3',
		[ 'rev_type_id' ],
		[
			'limit' => FLOW_HISTORY_INDEX_LIMIT,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $headerPrimaryIndex,
			'create' => static function ( array $row ) {
				return $row['rev_parent_id'] === null;
			},
		]
	);
	$indexes = [
		$headerPrimaryIndex,
		$headerHeaderLookupIndex
	];
	$userNameListener = new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		[
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki'
		]
	);
	$listeners = [
		'reference.recorder' => $c['reference.recorder'],
		'storage.header.listeners.username' => $userNameListener,
		'listeners.notification' => $c['listeners.notification'],
		'listener.recentchanges' => $c['listener.recentchanges'],
		'listener.editcount' => $c['listener.editcount'],
	];
	return new ObjectManager(
		$headerMapper,
		$headerBackend,
		$c['db.factory'],
		$indexes,
		$listeners
	);
};

$c['storage.post_summary.mapper'] = static function ( $c ) {
	return CachingObjectMapper::model(
		\Flow\Model\PostSummary::class,
		[ 'rev_id' ]
	);
};
$c['storage.post_summary.listeners.username'] = static function ( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		[
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki'
		]
	);
};
$c['storage.post_summary'] = static function ( $c ) {
	global $wgFlowExternalStore;
	$postSummaryBackend = new PostSummaryRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore
	);
	$postSummaryPrimaryIndex = new UniqueFeatureIndex(
		$c['flowcache'],
		$postSummaryBackend,
		$c['storage.post_summary.mapper'],
		'flow_post_summary:v2:pk',
		[ 'rev_id' ]
	);
	$postSummaryTopicLookupIndex = new TopKIndex(
		$c['flowcache'],
		$postSummaryBackend,
		$c['storage.post_summary.mapper'],
		'flow_post_summary:workflow:v3',
		[ 'rev_type_id' ],
		[
			'limit' => FLOW_HISTORY_INDEX_LIMIT,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $postSummaryPrimaryIndex,
			'create' => static function ( array $row ) {
				return $row['rev_parent_id'] === null;
			},
		]
	);
	$indexes = [
		$postSummaryPrimaryIndex,
		$postSummaryTopicLookupIndex,
	];
	$listeners = [
		'listener.recentchanges' => $c['listener.recentchanges'],
		'storage.post_summary.listeners.username' => $c['storage.post_summary.listeners.username'],
		'listeners.notification' => $c['listeners.notification'],
		'storage.post_summary_board_history.indexes.primary' => $c['storage.post_summary_board_history.indexes.primary'],
		'listener.editcount' => $c['listener.editcount'],
		'reference.recorder' => $c['reference.recorder'],
	];
	return new ObjectManager(
		$c['storage.post_summary.mapper'],
		$postSummaryBackend,
		$c['db.factory'],
		$indexes,
		$listeners
	);
};

$c['storage.topic_list.mapper'] = static function ( $c ) {
	// Must be BasicObjectMapper, due to variance in when
	// we have workflow_last_update_timestamp
	return BasicObjectMapper::model(
		\Flow\Model\TopicListEntry::class
	);
};
$c['storage.topic_list.backend'] = static function ( $c ) {
	return new TopicListStorage(
		// factory and table
		$c['db.factory'],
		'flow_topic_list',
		[ 'topic_list_id', 'topic_id' ]
	);
};
/// In reverse order by topic last_updated
$c['storage.topic_list.indexes.last_updated'] = static function ( $c ) {
	return new TopKIndex(
		$c['flowcache'],
		$c['storage.topic_list.backend'],
		$c['storage.topic_list.mapper'],
		'flow_topic_list_last_updated:list',
		[ 'topic_list_id' ],
		[
			'sort' => 'workflow_last_update_timestamp',
			'order' => 'desc'
		]
	);
};
$c['storage.topic_list'] = static function ( $c ) {
	// Lookup from topic_id to its owning board id
	$topicListPrimaryIndex = new UniqueFeatureIndex(
		$c['flowcache'],
		$c['storage.topic_list.backend'],
		$c['storage.topic_list.mapper'],
		'flow_topic_list:topic',
		[ 'topic_id' ]
	);
	// Lookup from board to contained topics
	// In reverse order by topic_id
	$topicListReverseLookupIndex = new TopKIndex(
		$c['flowcache'],
		$c['storage.topic_list.backend'],
		$c['storage.topic_list.mapper'],
		'flow_topic_list:list',
		[ 'topic_list_id' ],
		[ 'sort' => 'topic_id' ]
	);
	$indexes = [
		$topicListPrimaryIndex,
		$topicListReverseLookupIndex,
		$c['storage.topic_list.indexes.last_updated'],
	];
	return new ObjectManager(
		$c['storage.topic_list.mapper'],
		$c['storage.topic_list.backend'],
		$c['db.factory'],
		$indexes
	);
};
$c['storage.post.mapper'] = static function ( $c ) {
	return CachingObjectMapper::model(
		\Flow\Model\PostRevision::class,
		[ 'rev_id' ]
	);
};
$c['storage.post.backend'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowPostRevisionStorage' );
};
$c['storage.post.listeners.moderation_logging'] = static function ( $c ) {
	$moderationLogger = new Flow\Log\ModerationLogger(
		$c['flow_actions']
	);
	return new Flow\Data\Listener\ModerationLoggingListener(
		$moderationLogger
	);
};
$c['storage.post.indexes.primary'] = static function ( $c ) {
	return new UniqueFeatureIndex(
		$c['flowcache'],
		$c['storage.post.backend'],
		$c['storage.post.mapper'],
		'flow_revision:v4:pk',
		[ 'rev_id' ]
	);
};
$c['storage.post'] = static function ( $c ) {
	// Each bucket holds a list of revisions in a single post
	$postPostLookupIndex = new TopKIndex(
		$c['flowcache'],
		$c['storage.post.backend'],
		$c['storage.post.mapper'],
		'flow_revision:descendant',
		[ 'rev_type_id' ],
		[
			'limit' => 100,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.post.indexes.primary'],
			'create' => static function ( array $row ) {
				// return true to create instead of merge index
				return $row['rev_parent_id'] === null;
			},
		]
	);
	$indexes = [
		$c['storage.post.indexes.primary'],
		$postPostLookupIndex,
		$c['storage.post_topic_history.indexes.topic_lookup']
	];
	$userNameListener = new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		[
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki',
			'tree_orig_user_id' => 'tree_orig_user_wiki'
		]
	);
	// Auto-subscribe users to the topic after performing specific actions
	$watchTopicListener = new Flow\Data\Listener\ImmediateWatchTopicListener(
		$c['watched_items']
	);
	$listeners = [
		'reference.recorder' => $c['reference.recorder'],
		'collection.cache' => $c['collection.cache'],
		'storage.post.listeners.username' => $userNameListener,
		'storage.post.listeners.watch_topic' => $watchTopicListener,
		'listeners.notification' => $c['listeners.notification'],
		'storage.post.listeners.moderation_logging' => $c['storage.post.listeners.moderation_logging'],
		'listener.recentchanges' => $c['listener.recentchanges'],
		'listener.editcount' => $c['listener.editcount'],
		'storage.post_board_history.indexes.primary' => $c['storage.post_board_history.indexes.primary'],
	];
	return new ObjectManager(
		$c['storage.post.mapper'],
		$c['storage.post.backend'],
		$c['db.factory'],
		$indexes,
		$listeners
	);
};

$c['storage.post_topic_history.backend'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowPostRevisionTopicHistoryStorage' );
};

$c['storage.post_topic_history.indexes.topic_lookup'] = static function ( $c ) {
	return new PostRevisionTopicHistoryIndex(
		$c['flowcache'],
		$c['storage.post_topic_history.backend'],
		$c['storage.post.mapper'],
		'flow_revision:topic_history:post:v2',
		[ 'topic_root_id' ],
		[
			'limit' => FLOW_BOARD_TOPIC_HISTORY_POST_INDEX_LIMIT,
			'sort' => 'rev_id',
			'order' => 'DESC',
			// Why does topic history have a shallow compactor, but not board history?
			'shallow' => $c['storage.post.indexes.primary'],
			'create' => static function ( array $row ) {
				// only create new indexes for new topics, so it has to be
				// of type 'post' and have no parent post & revision
				if ( $row['rev_type'] !== 'post' ) {
					return false;
				}
				return $row['tree_parent_id'] === null && $row['rev_parent_id'] === null;
			},
		]
	);
};

$c['storage.post_topic_history'] = static function ( $c ) {
	$indexes = [
		$c['storage.post_topic_history.indexes.topic_lookup'],
	];
	return new ObjectLocator(
		$c['storage.post.mapper'],
		$c['storage.post_topic_history.backend'],
		$c['db.factory'],
		$indexes
	);
};

$c['storage.manager_list'] = static function ( $c ) {
	return [
		\Flow\Model\Workflow::class => 'storage.workflow',
		'Workflow' => 'storage.workflow',

		\Flow\Model\PostRevision::class => 'storage.post',
		'PostRevision' => 'storage.post',
		'post' => 'storage.post',

		\Flow\Model\PostSummary::class => 'storage.post_summary',
		'PostSummary' => 'storage.post_summary',
		'post-summary' => 'storage.post_summary',

		\Flow\Model\TopicListEntry::class => 'storage.topic_list',
		'TopicListEntry' => 'storage.topic_list',

		\Flow\Model\Header::class => 'storage.header',
		'Header' => 'storage.header',
		'header' => 'storage.header',

		'PostRevisionBoardHistoryEntry' => 'storage.post_board_history',

		'PostSummaryBoardHistoryEntry' => 'storage.post_summary_board_history',

		'PostRevisionTopicHistoryEntry' => 'storage.post_topic_history',

		\Flow\Model\WikiReference::class => 'storage.wiki_reference',
		'WikiReference' => 'storage.wiki_reference',

		\Flow\Model\URLReference::class => 'storage.url_reference',
		'URLReference' => 'storage.url_reference',
	];
};
$c['storage'] = static function ( $c ) {
	return new \Flow\Data\ManagerGroup(
		$c,
		$c['storage.manager_list']
	);
};
$c['loader.root_post'] = static function ( $c ) {
	return new \Flow\Repository\RootPostLoader(
		$c['storage'],
		$c['repository.tree']
	);
};

// Queue of callbacks to run by DeferredUpdates, but only
// on successful commit
$c['deferred_queue'] = static function ( $c ) {
	return new SplQueue;
};

$c['factory.loader.workflow'] = static function ( $c ) {
	$blockFactory = new Flow\BlockFactory(
		$c['storage'],
		$c['loader.root_post']
	);
	$submissionHandler = new Flow\SubmissionHandler(
		$c['storage'],
		$c['db.factory'],
		$c['deferred_queue']
	);
	return new Flow\WorkflowLoaderFactory(
		$c['storage'],
		$blockFactory,
		$submissionHandler
	);
};
// Initialized in Flow\Hooks to facilitate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['occupation_controller'] = Flow\Hooks::getOccupationController();

$c['controller.opt_in'] = static function ( $c ) {
	$archiveNameHelper = new Flow\Import\ArchiveNameHelper();
	return new Flow\Import\OptInController(
		$c['occupation_controller'],
		$c['controller.notification'],
		$archiveNameHelper,
		$c['db.factory'],
		$c['default_logger'],
		$c['occupation_controller']->getTalkpageManager()

	);
};

$c['controller.notification'] = static function ( $c ) {
	return MediaWikiServices::getInstance()->getService( 'FlowNotificationsController' );
};

// Initialized in Flow\Hooks to faciliate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['controller.abusefilter'] = Flow\Hooks::getAbuseFilter();

$c['controller.spamfilter'] = static function ( $c ) {
	global $wgMaxArticleSize;

	// wgMaxArticleSize is in kilobytes,
	// whereas this really is characters (it uses
	// mb_strlen), so it's not the exact same limit.
	$maxCharCount = $wgMaxArticleSize * 1024;

	$contentLengthFilter = new Flow\SpamFilter\ContentLengthFilter( $maxCharCount );

	return new Flow\SpamFilter\Controller(
		$contentLengthFilter,
		new Flow\SpamFilter\SpamRegex,
		new Flow\SpamFilter\RateLimits,
		new Flow\SpamFilter\SpamBlacklist,
		$c['controller.abusefilter'],
		new Flow\SpamFilter\ConfirmEdit
	);
};

$c['query.categoryviewer'] = static function ( $c ) {
	return new Flow\Formatter\CategoryViewerQuery(
		$c['storage']
	);
};
$c['formatter.categoryviewer'] = static function ( $c ) {
	return new Flow\Formatter\CategoryViewerFormatter(
		$c['permissions']
	);
};
$c['query.singlepost'] = static function ( $c ) {
	return new Flow\Formatter\SinglePostQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.checkuser'] = static function ( $c ) {
	return new Flow\Formatter\CheckUserQuery(
		$c['storage'],
		$c['repository.tree']
	);
};

$c['formatter.irclineurl'] = static function ( $c ) {
	return new Flow\Formatter\IRCLineUrlFormatter(
		$c['permissions'],
		$c['formatter.revision.factory']->create()
	);
};

$c['formatter.checkuser'] = static function ( $c ) {
	return new Flow\Formatter\CheckUserFormatter(
		$c['permissions'],
		$c['formatter.revision.factory']->create()
	);
};
$c['formatter.revisionview'] = static function ( $c ) {
	return new Flow\Formatter\RevisionViewFormatter(
		$c['url_generator'],
		$c['formatter.revision.factory']->create()
	);
};
$c['formatter.revision.diff.view'] = static function ( $c ) {
	return new Flow\Formatter\RevisionDiffViewFormatter(
		$c['formatter.revisionview'],
		$c['url_generator']
	);
};
$c['query.topiclist'] = static function ( $c ) {
	return new Flow\Formatter\TopicListQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions'],
		$c['watched_items']
	);
};
$c['query.topic.history'] = static function ( $c ) {
	return new Flow\Formatter\TopicHistoryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};
$c['query.post.history'] = static function ( $c ) {
	return new Flow\Formatter\PostHistoryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};
$c['query.changeslist'] = static function ( $c ) {
	$query = new Flow\Formatter\ChangesListQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
	$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
	$query->setExtendWatchlist( $userOptionsLookup->getOption( $c['user'], 'extendwatchlist' ) );

	return $query;
};
$c['query.postsummary'] = static function ( $c ) {
	return new Flow\Formatter\PostSummaryQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.header.view'] = static function ( $c ) {
	return new Flow\Formatter\HeaderViewQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions']
	);
};
$c['query.post.view'] = static function ( $c ) {
	return new Flow\Formatter\PostViewQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions']
	);
};
$c['query.postsummary.view'] = static function ( $c ) {
	return new Flow\Formatter\PostSummaryViewQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions']
	);
};
$c['formatter.changeslist'] = static function ( $c ) {
	return new Flow\Formatter\ChangesListFormatter(
		$c['permissions'],
		$c['formatter.revision.factory']->create()
	);
};

$c['query.contributions'] = static function ( $c ) {
	return new Flow\Formatter\ContributionsQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['db.factory'],
		$c['flow_actions'],
		MediaWikiServices::getInstance()->getUserIdentityLookup()
	);
};
$c['formatter.contributions'] = static function ( $c ) {
	return new Flow\Formatter\ContributionsFormatter(
		$c['permissions'],
		$c['formatter.revision.factory']->create()
	);
};
$c['formatter.contributions.feeditem'] = static function ( $c ) {
	return new Flow\Formatter\FeedItemFormatter(
		$c['permissions'],
		$c['formatter.revision.factory']->create()
	);
};
$c['query.board.history'] = static function ( $c ) {
	return new Flow\Formatter\BoardHistoryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};

$c['formatter.revision.factory'] = static function ( $c ) {
	global $wgFlowMaxThreadingDepth;

	return new Flow\Formatter\RevisionFormatterFactory(
		$c['permissions'],
		$c['templating'],
		$c['url_generator'],
		$c['repository.username'],
		$wgFlowMaxThreadingDepth
	);
};
$c['formatter.topiclist'] = static function ( $c ) {
	return new Flow\Formatter\TopicListFormatter(
		$c['url_generator'],
		$c['formatter.revision.factory']->create()
	);
};
$c['formatter.topiclist.toc'] = static function ( $c ) {
	return new Flow\Formatter\TocTopicListFormatter(
		$c['templating']
	);
};
$c['formatter.topic'] = static function ( $c ) {
	return new Flow\Formatter\TopicFormatter(
		$c['url_generator'],
		$c['formatter.revision.factory']->create()
	);
};

$c['search.index.iterators.header'] = static function ( $c ) {
	return new \Flow\Search\Iterators\HeaderIterator( $c['db.factory'] );
};
$c['search.index.iterators.topic'] = static function ( $c ) {
	return new \Flow\Search\Iterators\TopicIterator( $c['db.factory'], $c['loader.root_post'] );
};

$c['storage.wiki_reference'] = static function ( $c ) {
	$wikiReferenceMapper = BasicObjectMapper::model(
		\Flow\Model\WikiReference::class
	);
	$wikiReferenceBackend = new BasicDbStorage(
		$c['db.factory'],
		'flow_wiki_ref',
		[
			'ref_src_wiki',
			'ref_src_namespace',
			'ref_src_title',
			'ref_src_object_id',
			'ref_type',
			'ref_target_namespace',
			'ref_target_title'
		]
	);
	$wikiReferenceSourceLookupIndex = new TopKIndex(
		$c['flowcache'],
		$wikiReferenceBackend,
		$wikiReferenceMapper,
		'flow_ref:wiki:by-source:v3',
		[
			'ref_src_wiki',
			'ref_src_namespace',
			'ref_src_title',
		],
		[
			'order' => 'ASC',
			'sort' => 'ref_src_object_id',
		]
	);
	$wikiReferenceRevisionLookupIndex = new TopKIndex(
		$c['flowcache'],
		$wikiReferenceBackend,
		$wikiReferenceMapper,
		'flow_ref:wiki:by-revision:v3',
		[
			'ref_src_wiki',
			'ref_src_object_type',
			'ref_src_object_id',
		],
		[
			'order' => 'ASC',
			'sort' => [ 'ref_target_namespace', 'ref_target_title' ],
		]
	);
	$indexes = [
		$wikiReferenceSourceLookupIndex,
		$wikiReferenceRevisionLookupIndex,
	];
	return new ObjectManager(
		$wikiReferenceMapper,
		$wikiReferenceBackend,
		$c['db.factory'],
		$indexes,
		[]
	);
};

$c['storage.url_reference'] = static function ( $c ) {
	$urlReferenceMapper = BasicObjectMapper::model(
		\Flow\Model\URLReference::class
	);
	$urlReferenceBackend = new BasicDbStorage(
		// factory and table
		$c['db.factory'],
		'flow_ext_ref',
		[
			'ref_src_wiki',
			'ref_src_namespace',
			'ref_src_title',
			'ref_src_object_id',
			'ref_type',
			'ref_target',
		]
	);
	$urlReferenceSourceLookupIndex = new TopKIndex(
		$c['flowcache'],
		$urlReferenceBackend,
		$urlReferenceMapper,
		'flow_ref:url:by-source:v3',
		[
			'ref_src_wiki',
			'ref_src_namespace',
			'ref_src_title',
		],
		[
			'order' => 'ASC',
			'sort' => 'ref_src_object_id',
		]
	);
	$urlReferenceRevisionLookupIndex = new TopKIndex(
		$c['flowcache'],
		$urlReferenceBackend,
		$urlReferenceMapper,
		'flow_ref:url:by-revision:v3',
		[
			'ref_src_wiki',
			'ref_src_object_type',
			'ref_src_object_id',
		],
		[
			'order' => 'ASC',
			'sort' => [ 'ref_target' ],
		]
	);
	$indexes = [
		$urlReferenceSourceLookupIndex,
		$urlReferenceRevisionLookupIndex,
	];
	return new ObjectManager(
		$urlReferenceMapper,
		$urlReferenceBackend,
		$c['db.factory'],
		$indexes,
		[]
	);
};

$c['reference.updater.links-tables'] = static function ( $c ) {
	return new Flow\LinksTableUpdater( $c['storage'] );
};

$c['reference.clarifier'] = static function ( $c ) {
	return new Flow\ReferenceClarifier( $c['storage'], $c['url_generator'] );
};

$c['reference.extractor'] = static function ( $c ) {
	$default = [
		new Flow\Parsoid\Extractor\ImageExtractor,
		new Flow\Parsoid\Extractor\PlaceholderExtractor,
		new Flow\Parsoid\Extractor\WikiLinkExtractor,
		new Flow\Parsoid\Extractor\ExtLinkExtractor,
		new Flow\Parsoid\Extractor\TransclusionExtractor,
	];
	$extractors = [
		'header' => $default,
		'post-summary' => $default,
		'post' => $default,
	];
	// In addition to the defaults header and summaries collect
	// the related categories.
	$extractors['header'][] = $extractors['post-summary'][] = new Flow\Parsoid\Extractor\CategoryExtractor;

	return new Flow\Parsoid\ReferenceExtractor( $extractors );
};

$c['reference.recorder'] = static function ( $c ) {
	return new Flow\Data\Listener\ReferenceRecorder(
		$c['reference.extractor'],
		$c['reference.updater.links-tables'],
		$c['storage'],
		$c['repository.tree'],
		$c['deferred_queue']
	);
};

$c['user_merger'] = static function ( $c ) {
	return new Flow\Data\Utils\UserMerger(
		$c['db.factory'],
		$c['storage']
	);
};

$c['importer'] = static function ( $c ) {
	$importer = new Flow\Import\Importer(
		$c['storage'],
		$c['factory.loader.workflow'],
		$c['db.factory'],
		$c['deferred_queue'],
		$c['occupation_controller']
	);

	$importer->addPostprocessor( new Flow\Import\Postprocessor\SpecialLogTopic(
		$c['occupation_controller']->getTalkpageManager()
	) );

	return $importer;
};

$c['listener.editcount'] = static function ( $c ) {
	return new \Flow\Data\Listener\EditCountListener( $c['flow_actions'] );
};

$c['formatter.undoedit'] = static function ( $c ) {
	return new Flow\Formatter\RevisionUndoViewFormatter(
		$c['formatter.revisionview']
	);
};

$c['board_mover'] = static function ( $c ) {
	return new Flow\BoardMover(
		$c['db.factory'],
		$c['storage'],
		$c['occupation_controller']->getTalkpageManager()
	);
};

$c['default_logger'] = static function () {
	return MediaWikiServices::getInstance()->getService( 'FlowDefaultLogger' );
};

return $c;
