<?php

$c = new Flow\Container;

// MediaWiki
if ( defined( 'RUN_MAINTENANCE_IF_MAIN' ) ) {
	$c['user'] = new User;
} else {
	$c['user'] = isset( $GLOBALS['wgUser'] ) ? $GLOBALS['wgUser'] : new User;
}
$c['output'] = $GLOBALS['wgOut'];
$c['request'] = $GLOBALS['wgRequest'];
if ( $GLOBALS['wgFlowUseMemcache'] ) {
	$c['memcache'] = $GLOBALS['wgMemc'];
} else {
	$c['memcache'] = new \HashBagOStuff;
}
$c['cache.version'] = $GLOBALS['wgFlowCacheVersion'];

// Flow config
$c['flow_actions'] = $c->share( function( $c ) {
	global $wgFlowActions;
	return new Flow\FlowActions( $wgFlowActions );
} );

// Always returns the correct database for flow storage
$c['db.factory'] = $c->share( function( $c ) {
	global $wgFlowDefaultWikiDb, $wgFlowCluster;
	return new Flow\DbFactory( $wgFlowDefaultWikiDb, $wgFlowCluster );
} );

// Database Access Layer external from main implementation
$c['repository.tree'] = $c->share( function( $c ) {
	global $wgFlowCacheTime;
	return new Flow\Repository\TreeRepository(
		$c['db.factory'],
		$c['memcache.buffered']
	);
} );

$c['url_generator'] = $c->share( function( $c ) {
	return new Flow\UrlGenerator(
		$c['occupation_controller']
	);
} );
// listener is attached to storage.workflow, it
// notifies the url generator about all loaded workflows.
$c['listener.url_generator'] = $c->share( function( $c ) {
	return new Flow\Data\Listener\UrlGenerationListener(
		$c['url_generator']
	);
} );

$c['watched_items'] = $c->share( function( $c ) {
	return new Flow\WatchedTopicItems(
		$c['user'],
		wfGetDB( DB_SLAVE, 'watchlist' )
	);
} );

$c['link_batch'] = $c->share( function() {
	return new LinkBatch;
} );

$c['redlinker'] = $c->share( function( $c ) {
	return new Flow\Parsoid\Fixer\Redlinker( $c['link_batch'] );
} );

$c['bad_image_remover'] = $c->share( function( $c ) {
	return new Flow\Parsoid\Fixer\BadImageRemover( 'wfIsBadImage' );
} );

$c['content_fixer'] = $c->share( function( $c ) {
	return new Flow\Parsoid\ContentFixer(
		$c['redlinker'],
		$c['bad_image_remover']
	);
} );

$c['permissions'] = $c->share( function( $c ) {
	return new Flow\RevisionActionPermissions( $c['flow_actions'], $c['user'] );
} );

$c['lightncandy'] = $c->share( function( $c ) {
	global $wgFlowServerCompileTemplates;

	return new Flow\TemplateHelper(
		__DIR__ . '/handlebars',
		$wgFlowServerCompileTemplates
	);
} );

$c['templating'] = $c->share( function( $c ) {
	return new Flow\Templating(
		$c['repository.username'],
		$c['url_generator'],
		$c['output'],
		$c['content_fixer'],
		$c['permissions']
	);
} );

// New Storage Impl
use Flow\Data\BufferedCache;
use Flow\Data\Mapper\BasicObjectMapper;
use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Data\Storage\BasicDbStorage;
use Flow\Data\Storage\TopicListStorage;
use Flow\Data\Storage\TopicListLastUpdatedStorage;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Data\Storage\PostSummaryRevisionStorage;
use Flow\Data\Storage\TopicHistoryStorage;
use Flow\Data\Index\UniqueFeatureIndex;
use Flow\Data\Index\TopKIndex;
use Flow\Data\Index\TopicHistoryIndex;
use Flow\Data\Storage\BoardHistoryStorage;
use Flow\Data\Index\BoardHistoryIndex;
use Flow\Data\ObjectManager;
use Flow\Data\ObjectLocator;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;

$c['memcache.buffered'] = $c->share( function( $c ) {
	global $wgFlowCacheTime;

	// This is the real buffered cached that will allow transactional-like cache
	$bufferedCache = new Flow\Data\BagOStuff\LocalBufferedBagOStuff( $c['memcache'] );
	// This is Flow's wrapper around it, to have a fixed cache expiry time
	return new BufferedCache( $bufferedCache, $wgFlowCacheTime );
} );
// Batched username loader
$c['repository.username'] = $c->share( function( $c ) {
	return new Flow\Repository\UserNameBatch( new Flow\Repository\UserName\TwoStepUserNameQuery( $c['db.factory'] ) );
} );
$c['collection.cache'] = $c->share( function( $c ) {
	return new Flow\Collection\CollectionCache();
} );
// Individual workflow instances
$c['storage.workflow'] = $c->share( function( $c ) {
	$primaryKey = array( 'workflow_id' );
	$cache = $c['memcache.buffered'];
	$mapper = CachingObjectMapper::model( 'Flow\\Model\\Workflow', $primaryKey );
	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_workflow',
		// pk
		$primaryKey
	);
	$pk = new UniqueFeatureIndex( $cache, $storage, 'flow_workflow:v2:pk', $primaryKey );
	$indexes = array(
		$pk,
		// This is actually a unique index, but it wants the shallow functionality.
		new TopKIndex(
			$cache, $storage, 'flow_workflow:title:v2:',
			array( 'workflow_wiki', 'workflow_namespace', 'workflow_title_text', 'workflow_type' ),
			array( 'shallow' => $pk, 'limit' => 1, 'sort' => 'workflow_id' )
		),
	);
	$lifecycle = array(
		new Flow\Data\Listener\UserNameListener(
			$c['repository.username'],
			array( 'workflow_user_id' => 'workflow_user_wiki' )
		),
		new Flow\Data\Listener\WorkflowTopicListListener( $c['storage.topic_list'], $c['topic_list.last_updated.index'] ),
		$c['listener.occupation'],
		$c['listener.url_generator']
	);

	return new ObjectManager( $mapper, $storage, $indexes, $lifecycle );
} );

$c['listener.occupation'] = $c->share( function( $c ) {
	global $wgFlowDefaultWorkflow;

	return new Flow\Data\Listener\OccupationListener(
		$c['occupation_controller'],
		$c['deferred_queue'],
		$wgFlowDefaultWorkflow
	);
} );

$c['storage.board_history.backing'] = $c->share( function( $c ) {
	return new BoardHistoryStorage( $c['db.factory'] );
} );

$c['storage.board_history.index'] = $c->share( function( $c ) {
	return new BoardHistoryIndex( $c['memcache.buffered'], $c['storage.board_history.backing'], 'flow_revision:topic_list_history',
		array( 'topic_list_id' ),
		array(
			'limit' => 500,
			'sort' => 'rev_id',
			'order' => 'DESC'
	) );
} );

$c['storage.board_history'] = $c->share( function( $c ) {
	$mapper = new BasicObjectMapper(
		function( $rev ) use( $c ) {
			if ( $rev instanceof PostRevision ) {
				return $c['storage.post.mapper']->toStorageRow( $rev );
			} elseif ( $rev instanceof Header ) {
				return $c['storage.header.mapper']->toStorageRow( $rev );
			} elseif ( $rev instanceof PostSummary ) {
				return $c['storage.post.summary.mapper']->toStorageRow( $rev );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid class for board history entry: ' . get_class( $rev ), 'fail-load-data' );
			}
		},
		function( array $row, $obj = null ) use( $c ) {
			if ( $row['rev_type'] === 'header' ) {
				return $c['storage.header.mapper']->fromStorageRow( $row, $obj );
			} elseif ( $row['rev_type'] === 'post' ) {
				return $c['storage.post.mapper']->fromStorageRow( $row, $obj );
			} elseif ( $row['rev_type'] === 'post-summary' ) {
				return $c['storage.post.summary.mapper']->fromStorageRow( $row, $obj );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid rev_type for board history entry: ' . $row['rev_type'], 'fail-load-data' );
			}
		}
	);

	$indexes = array(
		$c['storage.board_history.index'],
	);
	return new ObjectLocator( $mapper, $c['storage.board_history.backing'], $indexes );
} );

// Arbitrary bit of revisioned wiki-text attached to a workflow
$c['storage.header.lifecycle-handlers'] = $c->share( function( $c ) {
	return array(
		$c['listener.recent_changes'],
		$c['storage.board_history.index'],
		new Flow\Data\Listener\UserNameListener(
			$c['repository.username'],
			array(
				'rev_user_id' => 'rev_user_wiki',
				'rev_mod_user_id' => 'rev_mod_user_wiki',
				'rev_edit_user_id' => 'rev_edit_user_wiki'
			)
		),
		$c['reference.recorder'],
	);
} );
$c['storage.header.mapper'] = $c->share( function( $c ) {
	return CachingObjectMapper::model( 'Flow\\Model\\Header', array( 'rev_id' ) );
} );
$c['storage.header'] = $c->share( function( $c ) {
	global $wgFlowExternalStore;

	$cache = $c['memcache.buffered'];
	$storage = new HeaderRevisionStorage( $c['db.factory'], $wgFlowExternalStore );

	$pk = new UniqueFeatureIndex(
		$cache, $storage,
		'flow_header:v2:pk', array( 'rev_id' )
	);
	$workflowIndexOptions = array(
		'sort' => 'rev_id',
		'order' => 'DESC',
		'shallow' => $pk,
		'create' => function( array $row ) {
			return $row['rev_parent_id'] === null;
		},
	);
	$indexes = array(
		$pk,
		new TopKIndex(
			$cache, $storage,
			'flow_header:workflow', array( 'rev_type_id' ),
			array( 'limit' => 100 ) + $workflowIndexOptions
		),
	);

	return new ObjectManager( $c['storage.header.mapper'], $storage, $indexes, $c['storage.header.lifecycle-handlers'] );
} );

$c['storage.post.summary.mapper'] = $c->share( function( $c ) {
	return CachingObjectMapper::model( 'Flow\\Model\\PostSummary', array( 'rev_id' ) );
} );
$c['listener.recent_changes'] = $c->share( function( $c ) {
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
} );
$c['storage.post.summary.lifecycle-handlers'] = $c->share( function( $c ) {
	return array(
		$c['storage.board_history.index'],
		$c['listener.recent_changes'],
		new Flow\Data\Listener\UserNameListener(
			$c['repository.username'],
			array(
				'rev_user_id' => 'rev_user_wiki',
				'rev_mod_user_id' => 'rev_mod_user_wiki',
				'rev_edit_user_id' => 'rev_edit_user_wiki'
			)
		),
		// topic history -- to keep a history by topic we have to know what topic every post
		// belongs to, not just its parent. TopicHistoryIndex is a slight tweak to TopKIndex
		// using TreeRepository for extra information and stuffing it into topic_root while indexing
		$c['storage.topic_history.index'],
	);
} );

$c['storage.post.summary'] = $c->share( function( $c ) {
	global $wgFlowExternalStore;

	$cache = $c['memcache.buffered'];
	$storage = new PostSummaryRevisionStorage( $c['db.factory'], $wgFlowExternalStore );
	$pk = new UniqueFeatureIndex(
		$cache, $storage,
		'flow_post_summary:v2:pk', array( 'rev_id' )
	);
	$workflowIndexOptions = array(
		'sort' => 'rev_id',
		'order' => 'DESC',
		'shallow' => $pk,
		'create' => function( array $row ) {
			return $row['rev_parent_id'] === null;
		},
	);
	$indexes = array(
		$pk,
		new TopKIndex(
			$cache, $storage,
			'flow_post_summary:workflow', array( 'rev_type_id' ),
			array( 'limit' => 100 ) + $workflowIndexOptions
		),
	);

	return new ObjectManager( $c['storage.post.summary.mapper'], $storage, $indexes, $c['storage.post.summary.lifecycle-handlers'] );
} );

$c['topic_list.last_updated.index'] = $c->share( function( $c ) {
	$primaryKey = array( 'topic_list_id', 'topic_id' );
	$cache = $c['memcache.buffered'];
	return new TopKIndex(
		$cache, new TopicListLastUpdatedStorage(
			// factory and table
			$c['db.factory'], 'flow_topic_list',
			// pk
			$primaryKey
		),
		'flow_topic_list_last_updated:list', array( 'topic_list_id' ),
		array(
			'sort' => 'workflow_last_update_timestamp',
			'order' => 'desc'
		)
	);
} );

// List of topic workflows and their owning discussion workflow
// TODO: This could use similar to ShallowCompactor to
// get the objects directly instead of just returning ids.
// Would also need object mapper adjustments to return array
// of two objects.
$c['storage.topic_list'] = $c->share( function( $c ) {
	$primaryKey = array( 'topic_list_id', 'topic_id' );
	$cache = $c['memcache.buffered'];
	$mapper = CachingObjectMapper::model( 'Flow\\Model\\TopicListEntry', $primaryKey );
	$storage = new TopicListStorage(
		// factory and table
		$c['db.factory'], 'flow_topic_list',
		// pk
		$primaryKey
	);
	$indexes = array(
		new TopKIndex(
			$cache, $storage,
			'flow_topic_list:list', array( 'topic_list_id' ),
			array( 'sort' => 'topic_id' )
		),
		$c['topic_list.last_updated.index'],
		new UniqueFeatureIndex(
			$cache, $storage,
			'flow_topic_list:topic', array( 'topic_id' )
		),
	);

	return new ObjectManager( $mapper, $storage, $indexes );
} );
// Individual post within a topic workflow
$c['storage.post.lifecycle-handlers'] = $c->share( function( $c ) {
	$handlers = array(
		new Flow\Log\PostModerationLogger( $c['logger'] ),
		$c['listener.recent_changes'],
		$c['storage.board_history.index'],
		new Flow\Data\Listener\UserNameListener(
			$c['repository.username'],
			array(
				'rev_user_id' => 'rev_user_wiki',
				'rev_mod_user_id' => 'rev_mod_user_wiki',
				'rev_edit_user_id' => 'rev_edit_user_wiki',
				'tree_orig_user_id' => 'tree_orig_user_wiki'
			)
		),
		// Auto-subscribe users to the topic after performing specific actions
		new Flow\Data\Listener\ImmediateWatchTopicListener( $c['watched_items'] ),
		$c['collection.cache'],
		// topic history -- to keep a history by topic we have to know what topic every post
		// belongs to, not just its parent. TopicHistoryIndex is a slight tweak to TopKIndex
		// using TreeRepository for extra information and stuffing it into topic_root while indexing
		$c['storage.topic_history.index'],
		$c['reference.recorder'],
		// Defer notifications triggering till end of request so we could get
		// article_id in the case of a new topic, this will need support of
		// adding deferred update when running deferred update
		new Flow\Data\Listener\DeferredInsertLifecycleHandler(
			$c['deferred_queue'],
			new Flow\Data\Listener\NotificationListener( $c['controller.notification'] )
		)
	);

	return $handlers;
} );
$c['storage.post.mapper'] = $c->share( function( $c ) {
	return CachingObjectMapper::model( 'Flow\\Model\\PostRevision', array( 'rev_id' ) );
} );

$c['storage.post'] = $c->share( function( $c ) {
	global $wgFlowExternalStore;
	$cache = $c['memcache.buffered'];
	$treeRepo = $c['repository.tree'];
	$storage = new PostRevisionStorage( $c['db.factory'], $wgFlowExternalStore, $treeRepo );
	$pk = new UniqueFeatureIndex( $cache, $storage, 'flow_revision:v4:pk', array( 'rev_id' ) );
	$indexes = array(
		$pk,
		// revision history
		new TopKIndex( $cache, $storage, 'flow_revision:descendant',
			array( 'rev_type_id' ),
			array(
				'limit' => 100,
				'sort' => 'rev_id',
				'order' => 'DESC',
				'shallow' => $pk,
				'create' => function( array $row ) {
					// return true to create instead of merge index
					return $row['rev_parent_id'] === null;
				},
		) ),
	);

	return new ObjectManager( $c['storage.post.mapper'], $storage, $indexes, $c['storage.post.lifecycle-handlers'] );
} );

$c['storage.topic_history.index'] = $c->share( function( $c ) {
	$cache = $c['memcache.buffered'];
	$pk = new UniqueFeatureIndex( $cache, $c['storage.topic_history.backing'], 'flow_revision:v4:pk', array( 'rev_id' ) );
	return new TopicHistoryIndex( $cache, $c['storage.topic_history.backing'], $c['repository.tree'], 'flow_revision:topic',
		array( 'topic_root_id' ),
		array(
			'limit' => 500,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $pk,
			'create' => function( array $row ) {
				// only create new indexes for post revisions
				if ( $row['rev_type'] !== 'post' ) {
					return false;
				}
				// if the post has no parent and the revision has no parent
				// then this is a brand new topic title
				return $row['tree_parent_id'] === null && $row['rev_parent_id'] === null;
			},
	) );
} );

$c['storage.topic_history.backing'] = $c->share( function( $c ) {
	global $wgFlowExternalStore;
	return new TopicHistoryStorage(
		new PostRevisionStorage( $c['db.factory'], $wgFlowExternalStore, $c['repository.tree'] ),
		new PostSummaryRevisionStorage( $c['db.factory'], $wgFlowExternalStore )
	);
} );

$c['storage.topic_history'] = $c->share( function( $c ) {
	$mapper = new BasicObjectMapper(
		function( $rev ) use( $c ) {
			if ( $rev instanceof PostRevision ) {
				return $c['storage.post.mapper']->toStorageRow( $rev );
			} elseif ( $rev instanceof PostSummary ) {
				return $c['storage.post.summary.mapper']->toStorageRow( $rev );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid class for board history entry: ' . get_class( $rev ), 'fail-load-data' );
			}
		},
		function( array $row, $obj = null ) use( $c ) {
			if ( $row['rev_type'] === 'post' ) {
				return $c['storage.post.mapper']->fromStorageRow( $row, $obj );
			} elseif ( $row['rev_type'] === 'post-summary' ) {
				return $c['storage.post.summary.mapper']->fromStorageRow( $row, $obj );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid rev_type for board history entry: ' . $row['rev_type'], 'fail-load-data' );
			}
		}
	);

	$indexes = array(
		$c['storage.topic_history.index'],
	);
	return new ObjectLocator( $mapper, $c['storage.topic_history.backing'], $indexes );
} );


$c['storage'] = $c->share( function( $c ) {
	return new \Flow\Data\ManagerGroup(
		$c,
		array(
			'Flow\\Model\\Workflow' => 'storage.workflow',
			'Workflow' => 'storage.workflow',

			'Flow\\Model\\PostRevision' => 'storage.post',
			'PostRevision' => 'storage.post',

			'Flow\\Model\\PostSummary' => 'storage.post.summary',
			'PostSummary' => 'storage.post.summary',

			'Flow\\Model\\TopicListEntry' => 'storage.topic_list',
			'TopicListEntry' => 'storage.topic_list',

			'Flow\\Model\\Header' => 'storage.header',
			'Header' => 'storage.header',

			'BoardHistoryEntry' => 'storage.board_history',

			'TopicHistoryEntry' => 'storage.topic_history',

			'Flow\\Model\\WikiReference' => 'storage.reference.wiki',
			'WikiReference' => 'storage.reference.wiki',

			'Flow\\Model\\URLReference' => 'storage.reference.url',
			'URLReference' => 'storage.reference.url',
		)
	);
} );
$c['loader.root_post'] = $c->share( function( $c ) {
	return new \Flow\Repository\RootPostLoader(
		$c['storage'],
		$c['repository.tree']
	);
} );

// Queue of callbacks to run by DeferredUpdates, but only
// on successfull commit
$c['deferred_queue'] = $c->share( function( $c ) {
	return new SplQueue;
} );

$c['submission_handler'] = $c->share( function( $c ) {
	return new Flow\SubmissionHandler(
		$c['storage'],
		$c['db.factory'],
		$c['memcache.buffered'],
		$c['deferred_queue']
	);
} );
$c['factory.block'] = $c->share( function( $c ) {
	return new Flow\BlockFactory(
		$c['storage'],
		$c['loader.root_post']
	);
} );
$c['factory.loader.workflow'] = $c->share( function( $c ) {
	global $wgFlowDefaultWorkflow;

	return new Flow\WorkflowLoaderFactory(
		$c['storage'],
		$c['factory.block'],
		$c['submission_handler'],
		$wgFlowDefaultWorkflow
	);
} );
// Initialized in FlowHooks to faciliate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['occupation_controller'] = FlowHooks::getOccupationController();

$c['controller.notification'] = $c->share( function( $c ) {
	global $wgContLang;
	return new Flow\NotificationController( $wgContLang );
} );

// Initialized in FlowHooks to faciliate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['controller.abusefilter'] = FlowHooks::getAbuseFilter();

$c['controller.spamregex'] = $c->share( function( $c ) {
	return new Flow\SpamFilter\SpamRegex;
} );

$c['controller.spamblacklist'] = $c->share( function( $c ) {
	return new Flow\SpamFilter\SpamBlacklist;
} );

$c['controller.confirmedit'] = $c->share( function( $c ) {
	return new Flow\SpamFilter\ConfirmEdit;
} );

$c['controller.contentlength'] = $c->share( function( $c ) {
	return new Flow\SpamFilter\ContentLengthFilter;
} );

$c['controller.spamfilter'] = $c->share( function( $c ) {
	return new Flow\SpamFilter\Controller(
		$c['controller.spamregex'],
		$c['controller.spamblacklist'],
		$c['controller.abusefilter'],
		$c['controller.confirmedit'],
		$c['controller.contentlength']
	);
} );

$c['query.singlepost'] = $c->share( function( $c ) {
	return new Flow\Formatter\SinglePostQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['query.checkuser'] = $c->share( function( $c ) {
	return new Flow\Formatter\CheckUserQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );

$c['formatter.irclineurl'] = $c->share( function( $c ) {
	return new Flow\Formatter\IRCLineUrlFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
} );

$c['formatter.checkuser'] = $c->share( function( $c ) {
	return new Flow\Formatter\CheckUserFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
} );
$c['formatter.revisionview'] = $c->share( function( $c ) {
	return new Flow\Formatter\RevisionViewFormatter(
		$c['url_generator'],
		$c['formatter.revision'],
		$c['templating']
	);
} );
$c['formatter.revision.diff.view'] = $c->share( function( $c ) {
	return new Flow\Formatter\RevisionDiffViewFormatter(
		$c['formatter.revisionview']
	);
} );
$c['query.topiclist'] = $c->share( function( $c ) {
	return new Flow\Formatter\TopicListQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions'],
		$c['watched_items']
	);
} );
$c['query.topic.history'] = $c->share( function( $c ) {
	return new Flow\Formatter\TopicHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['query.post.history'] = $c->share( function( $c ) {
	return new Flow\Formatter\PostHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['query.recentchanges'] = $c->share( function( $c ) {
	$query = new Flow\Formatter\RecentChangesQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
	$query->setExtendWatchlist( $c['user']->getOption( 'extendwatchlist' ) );

	return $query;
} );
$c['query.postsummary'] = $c->share( function( $c ) {
	return new Flow\Formatter\PostSummaryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
} );
$c['query.header.view'] = $c->share( function( $c ) {
	return new Flow\Formatter\HeaderViewQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['query.post.view'] = $c->share( function( $c ) {
	return new Flow\Formatter\PostViewQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['query.postsummary.view'] = $c->share( function( $c ) {
	return new Flow\Formatter\PostSummaryViewQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['formatter.recentchanges'] = $c->share( function( $c ) {
	return new Flow\Formatter\RecentChanges(
		$c['permissions'],
		$c['formatter.revision']
	);
} );

$c['query.contributions'] = $c->share( function( $c ) {
	return new Flow\Formatter\ContributionsQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['memcache'],
		$c['db.factory']
	);
} );
$c['formatter.contributions'] = $c->share( function( $c ) {
	return new Flow\Formatter\Contributions(
		$c['permissions'],
		$c['formatter.revision']
	);
} );
$c['query.board-history'] = $c->share( function( $c ) {
	return new Flow\Formatter\BoardHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
// The RevisionFormatter holds internal state like
// contentType of output and if it should include history
// properties.  To prevent different code using the formatter
// from causing problems return a new RevisionFormatter every
// time it is requested.
$c['formatter.revision'] = function( $c ) {
	global $wgFlowMaxThreadingDepth;

	return new Flow\Formatter\RevisionFormatter(
		$c['permissions'],
		$c['templating'],
		$c['repository.username'],
		$wgFlowMaxThreadingDepth
	);
};
$c['formatter.topiclist'] = $c->share( function( $c ) {
	return new Flow\Formatter\TopicListFormatter(
		$c['url_generator'],
		$c['formatter.revision'],
		$c['templating']
	);
} );
$c['formatter.topic'] = $c->share( function( $c ) {
	return new Flow\Formatter\TopicFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
} );
$c['logger'] = $c->share( function( $c ) {
	return new Flow\Log\Logger(
		$c['flow_actions'],
		$c['user']
	);
} );

$c['reference.extractor'] = $c->share( function( $c ) {
	return new Flow\Parsoid\ReferenceExtractor(
		array(
			new Flow\Parsoid\Extractor\ImageExtractor,
			new Flow\Parsoid\Extractor\PlaceholderExtractor,
			new Flow\Parsoid\Extractor\WikiLinkExtractor,
			new Flow\Parsoid\Extractor\ExtLinkExtractor,
			new Flow\Parsoid\Extractor\TransclusionExtractor,
		)
	);
} );

$c['storage.reference.wiki'] = $c->share( function( $c ) {
	$mapper = Flow\Data\Mapper\BasicObjectMapper::model( 'Flow\Model\WikiReference' );

	$cache = $c['memcache.buffered'];

	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_wiki_ref',
		// pk
		array(
			'ref_src_namespace',
			'ref_src_title',
			'ref_src_object_id',
			'ref_type',
			'ref_target_namespace', 'ref_target_title'
		)
	);

	$indexes = array(
		new TopKIndex(
			$cache,
			$storage,
			'flow_ref:wiki:by-source',
			array(
				'ref_src_namespace',
				'ref_src_title',
			),
			array(
				'order' => 'ASC',
				'sort' => 'ref_src_object_id',
			)
		),
		new TopKIndex(
			$cache,
			$storage,
			'flow_ref:wiki:by-revision:v2',
			array(
				'ref_src_object_type',
				'ref_src_object_id',
			),
			array(
				'order' => 'ASC',
				'sort' => array( 'ref_target_namespace', 'ref_target_title' ),
			)
		),
	);

	$handlers = array();

	return new ObjectManager( $mapper, $storage, $indexes, $handlers );
} );

// TODO duplicated
$c['storage.reference.url'] = $c->share( function( $c ) {
	$mapper = Flow\Data\Mapper\BasicObjectMapper::model( 'Flow\Model\URLReference' );

	$cache = $c['memcache.buffered'];

	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_ext_ref',
		// pk
		array(
			'ref_src_namespace',
			'ref_src_title',
			'ref_src_object_id',
			'ref_type',
			'ref_target'
		)
	);

	$indexes = array(
		new TopKIndex(
			$cache,
			$storage,
			'flow_ref:url:by-source',
			array(
				'ref_src_namespace',
				'ref_src_title',
			),
			array(
				'order' => 'ASC',
				'sort' => 'ref_src_object_id',
			)
		),
		new TopKIndex(
			$cache,
			$storage,
			'flow_ref:url:by-revision:v2',
			array(
				'ref_src_object_type',
				'ref_src_object_id',
			),
			array(
				'order' => 'ASC',
				'sort' => array( 'ref_target' ),
			)
		),
	);

	$handlers = array(); // TODO make a handler to insert into *links tables

	return new ObjectManager( $mapper, $storage, $indexes, $handlers );
} );

$c['reference.updater.links-tables'] = $c->share( function( $c ) {
	return new Flow\LinksTableUpdater( $c['storage'] );
} );

$c['reference.clarifier'] = $c->share( function( $c ) {
	return new Flow\ReferenceClarifier( $c['storage'], $c['url_generator'] );
} );

$c['reference.recorder'] = $c->share( function( $c ) {
	return new Flow\Data\Listener\ReferenceRecorder(
			$c['reference.extractor'],
			$c['reference.updater.links-tables'],
			$c['storage']
		);
} );

$c['importer'] = $c->share( function( $c ) {
	return new Flow\Import\Importer(
		$c['storage'],
		$c['factory.loader.workflow']
	);
} );

return $c;
