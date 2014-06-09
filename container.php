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
		$c['memcache'],
		$wgFlowCacheTime
	);
} );

// Templating
// (this implementation is mostly useless actually)
$c['url_generator'] = $c->share( function( $c ) {
	return new Flow\UrlGenerator(
		$c['storage.workflow'],
		$c['occupation_controller']
	);
} );

$c['link_batch'] = $c->share( function() {
	return new LinkBatch;
} );

$c['redlinker'] = $c->share( function( $c ) {
	return new Flow\Parsoid\Redlinker( $c['link_batch'] );
} );

$c['bad_image_remover'] = $c->share( function( $c ) {
	return new Flow\Parsoid\BadImageRemover();
} );

$c['content_fixer'] = $c->share( function( $c ) {
	return new Flow\Parsoid\Controller(
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
$c['templating.namespaces'] = array(
	'flow' => __DIR__ . '/templates',
);

$c['templating.global_variables'] = $c->share( function( $c ) {
	global $wgFlowMaxThreadingDepth;

	$user = $c['user'];
	return array(
		'user' => $user,
		'editToken' => $user->getEditToken(),
		'maxThreadingDepth' => $wgFlowMaxThreadingDepth,
		'permissions' => $c['permissions'],
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
use Flow\Data\LocalBufferedCache;
use Flow\Data\BasicObjectMapper;
use Flow\Data\CachingObjectMapper;
use Flow\Data\BasicDbStorage;
use Flow\Data\TopicListStorage;
use Flow\Data\TopicListLastUpdatedStorage;
use Flow\Data\PostRevisionStorage;
use Flow\Data\HeaderRevisionStorage;
use Flow\Data\PostSummaryRevisionStorage;
use Flow\Data\TopicHistoryStorage;
use Flow\Data\UniqueFeatureIndex;
use Flow\Data\TopKIndex;
use Flow\Data\TopicHistoryIndex;
use Flow\Data\BoardHistoryStorage;
use Flow\Data\BoardHistoryIndex;
use Flow\Data\ObjectManager;
use Flow\Data\ObjectLocator;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;

$c['memcache.buffered'] = $c->share( function( $c ) {
	global $wgFlowCacheTime;
	return new LocalBufferedCache( $c['memcache'], $wgFlowCacheTime );
} );
// Batched username loader
$c['repository.username'] = $c->share( function( $c ) {
	return new Flow\Data\UserNameBatch( new Flow\Data\TwoStepUserNameQuery( $c['db.factory'] ) );
} );
$c['collection.cache'] = $c->share( function( $c ) {
	return new Flow\Collection\CollectionCache();
} );
// Per wiki workflow definitions (types of workflows)
$c['storage.definition'] = $c->share( function( $c ) {
	$primaryKey = array( 'definition_id' );
	$cache = $c['memcache.buffered'];
	$mapper = CachingObjectMapper::model( 'Flow\\Model\\Definition', $primaryKey );
	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_definition',
		// pk
		$primaryKey
	);
	$indexes = array(
		new UniqueFeatureIndex( $cache, $storage, 'flow_definition:pk', $primaryKey ),
		new UniqueFeatureIndex( $cache, $storage, 'flow_definition:name', array( 'definition_wiki', 'definition_name' ) ),
	);

	return new ObjectManager( $mapper, $storage, $indexes );
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
	$pk = new UniqueFeatureIndex( $cache, $storage, 'flow_workflow:pk', $primaryKey );
	$indexes = array(
		$pk,
		// This is actually a unique index, but it wants the shallow functionality.
		new TopKIndex(
			$cache, $storage, 'flow_workflow:title',
			array( 'workflow_wiki', 'workflow_namespace', 'workflow_title_text', 'workflow_definition_id' ),
			array( 'shallow' => $pk, 'limit' => 1, 'sort' => 'workflow_id' )
		),
	);
	$lifecycle = array(
		new Flow\Data\UserNameListener(
			$c['repository.username'],
			array( 'workflow_user_id' => 'workflow_user_wiki' )
		),
		new Flow\Data\WorkflowTopicListListener( $c['storage.topic_list'], $c['topic_list.last_updated.index'] )
		// $c['storage.user_subs.user_index']
	);

	return new ObjectManager( $mapper, $storage, $indexes, $lifecycle );
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
	$cache = $c['memcache.buffered'];
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
	global $wgContLang;
	return array(
		new Flow\Data\HeaderRecentChanges(
			$c['flow_actions'],
			$c['repository.username'],
			$c['storage'],
			$wgContLang
		),
		$c['storage.board_history.index'],
		new Flow\Data\UserNameListener(
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

$c['storage.post.summary.lifecycle-handlers'] = $c->share( function( $c ) {
	global $wgContLang;

	return array(
		$c['storage.board_history.index'],
		new Flow\Data\PostSummaryRecentChanges(
			$c['flow_actions'],
			$c['repository.username'],
			$c['storage'],
			$wgContLang
		),
		new Flow\Data\UserNameListener(
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
	global $wgContLang;
	return array(
		new Flow\Log\PostModerationLogger( $c['logger'] ),
		new Flow\Data\PostRevisionRecentChanges( $c['flow_actions'], $c['repository.username'], $c['storage'], $c['repository.tree'], $wgContLang ),
		$c['storage.board_history.index'],
		new Flow\Data\UserNameListener(
			$c['repository.username'],
			array(
				'rev_user_id' => 'rev_user_wiki',
				'rev_mod_user_id' => 'rev_mod_user_wiki',
				'rev_edit_user_id' => 'rev_edit_user_wiki',
				'tree_orig_user_id' => 'tree_orig_user_wiki'
			)
		),
		$c['collection.cache'],
		// topic history -- to keep a history by topic we have to know what topic every post
		// belongs to, not just its parent. TopicHistoryIndex is a slight tweak to TopKIndex
		// using TreeRepository for extra information and stuffing it into topic_root while indexing
		$c['storage.topic_history.index'],
		$c['reference.recorder'],
	);
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
				// if the post has no parent and the revision has no parent
				// then this is a brand new topic title
				return $row['tree_parent_id'] === null
					&& $row['rev_parent_id'] === null;
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
	$cache = $c['memcache.buffered'];
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


// Storage implementation for user subscriptions, separate from storage.user_subs so it
// can be used in storage.user_subs.user_index as well.
$c['storage.user_subs.backing'] = $c->share( function( $c ) {
	return new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_user_sub',
		// pk
		array( 'subscrip_user_id', 'subscrip_workflow_id' )
	);
} );
// Needs to be separate from storage.user_subs so it can be attached to Workflow updates
// Limits users to 2000 subscriptions
// TODO: Can't use TopKIndex, needs to be custom. so it stores the right data
// TODO: Storage wont work either, it
$c['storage.user_subs.user_index'] = $c->share( function( $c ) {
	$cache = $c['memcache.buffered'];
	$storage = $c['storage.user_subs.backing'];
	return new TopKIndex(
		$cache, $storage, 'flow_user_sub:user', array( 'subscrip_user_id' ),
		array( 'limit' => 2000, 'sort' => 'subscrip_last_updated' )
	);
} );
// User subscriptions are triggered by updates on workflow objects.
$c['storage.user_subs'] = $c->share( function( $c ) {
	$cache = $c['memcache.buffered'];
	$mapper = BasicObjectMapper::model( 'Flow\\Model\\UserSubscription' );
	$storage = $c['storage.user_subs.backing'];
	$indexes = array(
		// no reason to index workflow_id, it subscription updates need to happen
		// in a background job anyways.
		$c['storage.user_subs.user_index']
	);
	return new ObjectLocator( $mapper, $storage, $indexes );
} );
$c['storage'] = $c->share( function( $c ) {
	return new \Flow\Data\ManagerGroup(
		$c,
		array(
			'Flow\\Model\\Definition' => 'storage.definition',
			'Definition' => 'storage.definition',

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
	return new \Flow\Data\RootPostLoader(
		$c['storage'],
		$c['repository.tree']
	);
} );

$c['factory.loader.workflow'] = $c->share( function( $c ) {
	return new Flow\WorkflowLoaderFactory(
		$c['db.factory'],
		$c['memcache.buffered'],
		$c['storage'],
		$c['loader.root_post'],
		$c['controller.notification']
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

$c['controller.spamfilter'] = $c->share( function( $c ) {
	return new Flow\SpamFilter\Controller(
		$c['controller.spamregex'],
		$c['controller.spamblacklist'],
		$c['controller.abusefilter'],
		$c['controller.confirmedit']
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
	return new Flow\Formatter\CheckUser(
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
		$c['repository.tree']
	);
} );
$c['query.topic.history'] = $c->share( function( $c ) {
	return new Flow\Formatter\TopicHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
} );
$c['query.recentchanges'] = $c->share( function( $c ) {
	return new Flow\Formatter\RecentChangesQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
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
$c['formatter.revision'] = $c->share( function( $c ) {
	return new Flow\Formatter\RevisionFormatter(
		$c['permissions'],
		$c['templating'],
		$c['repository.username']
	);
} );
$c['formatter.topiclist'] = $c->share( function( $c ) {
	return new Flow\Formatter\TopicListFormatter(
		$c['url_generator'],
		$c['formatter.revision']
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
			'//*[starts-with(@typeof, "mw:Image")]' =>
				function( $element ) {
					$imgNode = $element->getElementsByTagName( 'img' )->item( 0 );
					$data = FormatJson::decode( $imgNode->getAttribute( 'data-parsoid' ), true );
					$imageName = $data['sa']['resource'];

					return array(
						'refType' => 'file',
						'targetType' => 'wiki',
						'target' => $imageName,
					);
				},
			'//a[@rel="mw:WikiLink"][not(@typeof)]' =>
				function( $element ) {
					$parsoidData = FormatJson::decode( $element->getAttribute( 'data-parsoid' ), true );
					$linkTarget = $parsoidData['sa']['href'];

					return array(
						'refType' => 'link',
						'targetType' => 'wiki',
						'target' => $linkTarget,
					);
				},
			'//a[@rel="mw:ExtLink"]' =>
				function( $element ) {
					$href = urldecode( $element->getAttribute( 'href' ) );

					return array(
						'refType' => 'link',
						'targetType' => 'url',
						'target' => $href,
					);
				},
			'//*[@typeof="mw:Transclusion"]' =>
				function( $element ) {
					$data = json_decode( $element->getAttribute( 'data-mw' ) );
					$templateTarget = Title::newFromText( $data->parts[0]->template->target->wt, NS_TEMPLATE );

					if ( !$templateTarget ) {
						return null;
					}

					return array(
						'refType' => 'template',
						'targetType' => 'wiki',
						'target' => $templateTarget->getPrefixedText(),
					);;
				},
		)
	);
} );

$c['storage.reference.wiki'] = $c->share( function( $c ) {
	$mapper = Flow\Data\BasicObjectMapper::model( 'Flow\Model\WikiReference' );

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
	$mapper = Flow\Data\BasicObjectMapper::model( 'Flow\Model\URLReference' );

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
	return new Flow\Data\ReferenceRecorder(
			$c['reference.extractor'],
			$c['reference.updater.links-tables'],
			$c['storage']
		);
} );

return $c;
