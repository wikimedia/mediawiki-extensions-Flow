<?php

$c = new Flow\Container;

// MediaWiki
$c['user'] = $GLOBALS['wgUser'];
$c['output'] = $GLOBALS['wgOut'];
$c['request'] = $GLOBALS['wgRequest'];
//$c['memcache'] = new \HashBagOStuff;
$c['memcache'] = $GLOBALS['wgMemc'];

// Flow config
$c['flow_actions'] = $c->share( function( $c ) {
	global $wgFlowActions;
	return new Flow\FlowActions( $wgFlowActions );
} );

// Always returns the correct database for flow storage
$c['db.factory'] = $c->share( function( $c ) {
	global $wgFlowDefaultWikiDb;
	return new Flow\DbFactory( $wgFlowDefaultWikiDb );
} );

// Database Access Layer external from main implementation
$c['repository.tree'] = $c->share( function( $c ) {
	return new Flow\Repository\TreeRepository(
		$c['db.factory'],
		$c['memcache']
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
$c['templating.namespaces'] = array(
	'flow' => __DIR__ . '/templates',
);
$c['templating.global_variables'] = $c->share( function( $c ) {
	global $wgFlowTokenSalt;

	return array(
		'user' => $c['user'],
		'editToken' => $c['user']->getEditToken( $wgFlowTokenSalt ),
	);
} );
$c['templating'] = $c->share( function( $c ) {
	return new Flow\Templating(
		$c['url_generator'],
		$c['output'],
		$c['templating.namespaces'],
		$c['templating.global_variables']
	);
} );

// New Storage Impl
use Flow\Data\BufferedCache;
use Flow\Data\LocalBufferedCache;
use Flow\Data\BasicObjectMapper;
use Flow\Data\BasicDbStorage;
use Flow\Data\PostRevisionStorage;
use Flow\Data\HeaderRevisionStorage;
use Flow\Data\UniqueFeatureIndex;
use Flow\Data\TopKIndex;
use Flow\Data\TopicHistoryIndex;
use Flow\Data\BoardHistoryStorage;
use Flow\Data\BoardHistoryIndex;
use Flow\Data\ObjectMapper;
use Flow\Data\ObjectManager;
use Flow\Data\ObjectLocator;
use Flow\Model\Header;
use Flow\Model\PostRevision;

// TODO: this is still pass-thru untill it gets hooked up to the begin/commit
//       transaction.  Easiest will be to explicitly start/end the transaction
//       in the entry point ?
$c['memcache.buffered'] = $c->share( function( $c ) {
	return new LocalBufferedCache( $c['memcache'] );
} );
// Per wiki workflow definitions (types of workflows)
$c['storage.definition'] = $c->share( function( $c ) {
	$cache = $c['memcache.buffered'];
	$mapper = BasicObjectMapper::model( 'Flow\\Model\\Definition' );
	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_definition',
		// pk
		array( 'definition_id' )
	);
	$indexes = array(
		new UniqueFeatureIndex( $cache, $storage, 'flow_definition:pk', array( 'definition_id' ) ),
		new UniqueFeatureIndex( $cache, $storage, 'flow_definition:name', array( 'definition_wiki', 'definition_name' ) ),
	);

	return new ObjectManager( $mapper, $storage, $indexes );
} );
// Individual workflow instances
$c['storage.workflow'] = $c->share( function( $c ) {
	$cache = $c['memcache.buffered'];
	$mapper = BasicObjectMapper::model( 'Flow\\Model\\Workflow' );
	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_workflow',
		// pk
		array( 'workflow_id' )
	);
	$pk = new UniqueFeatureIndex( $cache, $storage, 'flow_workflow:pk', array( 'workflow_id' ) );
	$indexes = array(
		$pk,
		// This is actually a unique index, but it wants the shallow functionality.
		new TopKIndex(
			$cache, $storage, 'flow_workflow:title',
			array( 'workflow_wiki', 'workflow_namespace', 'workflow_title_text', 'workflow_definition_id' ),
			array( 'shallow' => $pk, 'limit' => 1, 'sort' => 'workflow_id' )
		),
	);
	//ch /valulds t not ready yet
	$lifecycle = array(); // $c['storage.user_subs.user_index'] );

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
		function( $rev ) {
			return $rev->toStorageRow( $rev );
		},
		function( array $row, $obj = null ) {
			if ( $row['rev_type'] === 'header' ) {
				return Header::fromStorageRow( $row, $obj );
			} elseif ( $row['rev_type'] === 'post' ) {
				return PostRevision::fromStorageRow( $row, $obj );
			} else {
				throw new \MWException( 'Invalid rev_type for board history entry: ' . $row['rev_type'] );
			}
		}
	);

	$indexes = array(
		$c['storage.board_history.index'],
	);
	return new ObjectLocator( $mapper, $c['storage.board_history.backing'], $indexes );
} );

// Arbitrary bit of revisioned wiki-text attached to a workflow
$c['storage.header'] = $c->share( function( $c ) {
	global $wgFlowExternalStore, $wgContLang;

	$cache = $c['memcache.buffered'];
	$mapper = BasicObjectMapper::model( 'Flow\\Model\\Header' );
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
			'flow_header:workflow', array( 'header_workflow_id' ),
			array( 'limit' => 100 ) + $workflowIndexOptions
		),
		new TopKIndex(
			$cache, $storage,
			'flow_header:latest', array( 'header_workflow_id' ),
			array( 'limit'  => 1 ) + $workflowIndexOptions
		),
	);

	$handlers = array(
		new Flow\Data\HeaderRecentChanges( $c['storage'], $wgContLang ),
		$c['storage.board_history.index'],
	);

	return new ObjectManager( $mapper, $storage, $indexes, $handlers );
} );

// List of topic workflows and their owning discussion workflow
// TODO: This could use similar to ShallowCompactor to
// get the objects directly instead of just returning ids.
// Would also need object mapper adjustments to return array
// of two objects.
$c['storage.topic_list'] = $c->share( function( $c ) {
	$cache = $c['memcache.buffered'];
	$mapper = BasicObjectMapper::model( 'Flow\\Model\\TopicListEntry' );
	$storage = new BasicDbStorage(
		// factory and table
		$c['db.factory'], 'flow_topic_list',
		// pk
		array( 'topic_list_id', 'topic_id' )
	);
	$indexes = array(
		new TopKIndex(
			$cache, $storage,
			'flow_topic_list:list', array( 'topic_list_id' ),
			array( 'sort' => 'topic_id' )
		),
		new UniqueFeatureIndex(
			$cache, $storage,
			'flow_topic_list:topic', array( 'topic_id' )
		),
	);

	return new ObjectManager( $mapper, $storage, $indexes );
} );
// Individual post within a topic workflow
$c['storage.post'] = $c->share( function( $c ) {
	global $wgFlowExternalStore, $wgContLang;
	$cache = $c['memcache.buffered'];
	$treeRepo = $c['repository.tree'];
	$mapper = BasicObjectMapper::model( 'Flow\\Model\\PostRevision' );
	$storage = new PostRevisionStorage( $c['db.factory'], $wgFlowExternalStore, $treeRepo );
	$pk = new UniqueFeatureIndex( $cache, $storage, 'flow_revision:v3:pk', array( 'rev_id' ) );
	$indexes = array(
		$pk,
		// revision history
		new TopKIndex( $cache, $storage, 'flow_revision:descendant',
			array( 'tree_rev_descendant_id' ),
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
		// most recent revision
		new TopKIndex( $cache, $storage, 'flow_revision:latest_post',
			array( 'tree_rev_descendant_id' ),
			array(
				'limit' => 1,
				'sort' => 'rev_id',
				'order' => 'DESC',
				'shallow' => $pk,
				'create' => function( array $row ) {
					return $row['rev_parent_id'] === null;
				},
		) ),
		// topic history -- to keep a history by topic we have to know what topic every post
		// belongs to, not just its parent. TopicHistoryIndex is a slight tweak to TopKIndex
		// using TreeRepository for extra information and stuffing it into topic_root while indexing
		new TopicHistoryIndex( $cache, $storage, $c['repository.tree'], 'flow_revision:topic',
			array( 'topic_root' ),
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
		) ),
	);

	$handlers = array(
		new Flow\Data\PostRevisionRecentChanges( $c['storage'], $c['repository.tree'], $wgContLang ),
		$c['storage.board_history.index'],
	);

	return new ObjectManager( $mapper, $storage, $indexes, $handlers );
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

			'Flow\\Model\\TopicListEntry' => 'storage.topic_list',
			'TopicListEntry' => 'storage.topic_list',

			'Flow\\Model\\Header' => 'storage.header',
			'Header' => 'storage.header',

			'BoardHistoryEntry' => 'storage.board_history',
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
		$c['storage'],
		$c['loader.root_post'],
		$c['controller.notification']
	);
} );

$c['occupation_controller'] = $c->share( function( $c ) {
	global $wgFlowOccupyPages, $wgFlowOccupyNamespaces;
	return new Flow\TalkpageManager( $wgFlowOccupyNamespaces, $wgFlowOccupyPages );
} );

$c['controller.notification'] = $c->share( function( $c ) {
	return new Flow\NotificationController;
} );

$c['recentchanges.formatter'] = $c->share( function( $c ) {
	global $wgLang;
	return new Flow\RecentChanges\Formatter(
		$c['storage'],
		$c['factory.loader.workflow'],
		$c['flow_actions'],
		$c['templating'],
		$wgLang
	);
} );

$c['logger'] = $c->share( function( $c ) {
	return new Flow\Log\Logger(
		$c['flow_actions'],
		$c['url_generator'],
		$c['user']
	);
} );

return $c;
