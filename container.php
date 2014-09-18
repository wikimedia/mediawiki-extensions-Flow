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
$c['memcache'] = function( $c ) {
	global $wgFlowUseMemcache, $wgMemc;

	if ( $wgFlowUseMemcache ) {
		return $wgMemc;
	} else {
		return new \HashBagOStuff();
	}
};
$c['cache.version'] = $GLOBALS['wgFlowCacheVersion'];

// Flow config
$c['flow_actions'] = function( $c ) {
	global $wgFlowActions;
	return new Flow\FlowActions( $wgFlowActions );
};

// Always returns the correct database for flow storage
$c['db.factory'] = function( $c ) {
	global $wgFlowDefaultWikiDb, $wgFlowCluster;
	return new Flow\DbFactory( $wgFlowDefaultWikiDb, $wgFlowCluster );
};

// Database Access Layer external from main implementation
$c['repository.tree'] = function( $c ) {
	global $wgFlowCacheTime;
	return new Flow\Repository\TreeRepository(
		$c['db.factory'],
		$c['memcache.buffered']
	);
};

$c['url_generator'] = function( $c ) {
	return new Flow\UrlGenerator();
};
// listener is attached to storage.workflow, it
// notifies the url generator about all loaded workflows.
$c['listener.url_generator'] = function( $c ) {
	return new Flow\Data\Listener\UrlGenerationListener(
		$c['url_generator']
	);
};

$c['watched_items'] = function( $c ) {
	return new Flow\WatchedTopicItems(
		$c['user'],
		wfGetDB( DB_SLAVE, 'watchlist' )
	);
};

$c['link_batch'] = function() {
	return new LinkBatch;
};

$c['redlinker'] = function( $c ) {
	return new Flow\Parsoid\Fixer\Redlinker( $c['link_batch'] );
};

$c['bad_image_remover'] = function( $c ) {
	return new Flow\Parsoid\Fixer\BadImageRemover( 'wfIsBadImage' );
};

$c['content_fixer'] = function( $c ) {
	return new Flow\Parsoid\ContentFixer(
		$c['redlinker'],
		$c['bad_image_remover']
	);
};

$c['permissions'] = function( $c ) {
	return new Flow\RevisionActionPermissions( $c['flow_actions'], $c['user'] );
};

$c['lightncandy.template_dir'] = __DIR__ . '/handlebars';
$c['lightncandy'] = function( $c ) {
	global $wgFlowServerCompileTemplates;

	return new Flow\TemplateHelper(
		$c['lightncandy.template_dir'],
		$wgFlowServerCompileTemplates
	);
};

$c['templating'] = function( $c ) {
	return new Flow\Templating(
		$c['repository.username'],
		$c['url_generator'],
		$c['output'],
		$c['content_fixer'],
		$c['permissions']
	);
};

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

$c['memcache.buffered'] = function( $c ) {
	global $wgFlowCacheTime;

	// This is the real buffered cached that will allow transactional-like cache
	$bufferedCache = new Flow\Data\BagOStuff\LocalBufferedBagOStuff( $c['memcache'] );
	// This is Flow's wrapper around it, to have a fixed cache expiry time
	return new BufferedCache( $bufferedCache, $wgFlowCacheTime );
};
// Batched username loader
$c['repository.username.query'] = function( $c ) {
	return new Flow\Repository\UserName\TwoStepUserNameQuery(
		$c['db.factory']
	);
};
$c['repository.username'] = function( $c ) {
	return new Flow\Repository\UserNameBatch(
		$c['repository.username.query']
	);
};
$c['collection.cache'] = function( $c ) {
	return new Flow\Collection\CollectionCache();
};
// Individual workflow instances
$c['storage.workflow.class'] = 'Flow\Model\Workflow';
$c['storage.workflow.table'] = 'flow_workflow';
$c['storage.workflow.primary_key'] = array( 'workflow_id' );
$c['storage.workflow.backend'] = function( $c ) {
	return new BasicDbStorage(
		$c['db.factory'],
		$c['storage.workflow.table'],
		$c['storage.workflow.primary_key']
	);
};
$c['storage.workflow.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.workflow.class'],
		$c['storage.workflow.primary_key']
	);
};
$c['storage.workflow.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.buffered'],
		$c['storage.workflow.backend'],
		'flow_workflow:v2:pk',
		$c['storage.workflow.primary_key']
	);
};
$c['storage.workflow.indexes.title_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.workflow.backend'],
		'flow_workflow:title:v2:',
		array( 'workflow_wiki', 'workflow_namespace', 'workflow_title_text', 'workflow_type' ),
		array(
			'shallow' => $c['storage.workflow.indexes.primary'],
			'limit' => 1,
			'sort' => 'workflow_id'
		)
	);
};
$c['storage.workflow.indexes'] = function( $c ) {
	return array(
		$c['storage.workflow.indexes.primary'],
		$c['storage.workflow.indexes.title_lookup']
	);
};
$c['storage.workflow.listeners.topiclist'] = function( $c ) {
	return new Flow\Data\Listener\WorkflowTopicListListener(
		$c['storage.topic_list'],
		$c['storage.topic_list.indexes.last_updated']
	);
};
$c['storage.workflow.listeners'] = function( $c ) {
	return array(
		$c['listener.occupation'],
		$c['listener.url_generator'],
		$c['storage.workflow.listeners.topiclist'],
	);
};
$c['storage.workflow'] = function( $c ) {
	return new ObjectManager(
		$c['storage.workflow.mapper'],
		$c['storage.workflow.backend'],
		$c['storage.workflow.indexes'],
		$c['storage.workflow.listeners']
	);
};
$c['listener.recentchanges'] = function( $c ) {
	global $wgContLang;
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
$c['listener.occupation'] = function( $c ) {
	global $wgFlowDefaultWorkflow;

	return new Flow\Data\Listener\OccupationListener(
		$c['occupation_controller'],
		$c['deferred_queue'],
		$wgFlowDefaultWorkflow
	);
};

$c['storage.board_history.backend'] = function( $c ) {
	return new BoardHistoryStorage( $c['db.factory'] );
};
$c['storage.board_history.indexes.primary'] = function( $c ) {
	return new BoardHistoryIndex(
		$c['memcache.buffered'],
		// backend storage
		$c['storage.board_history.backend'],
		// key prefix
		'flow_revision:topic_list_history',
		// primary key
		array( 'topic_list_id' ),
		// index options
		array(
			'limit' => 500,
			'sort' => 'rev_id',
			'order' => 'DESC'
		),
		$c['storage.topic_list']
	);
};
$c['storage.board_history.mapper'] = function( $c ) {
	return new BasicObjectMapper(
		function( $rev ) use( $c ) {
			if ( $rev instanceof PostRevision ) {
				return $c['storage.post.mapper']->toStorageRow( $rev );
			} elseif ( $rev instanceof Header ) {
				return $c['storage.header.mapper']->toStorageRow( $rev );
			} elseif ( $rev instanceof PostSummary ) {
				return $c['storage.post_summary.mapper']->toStorageRow( $rev );
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
				return $c['storage.post_summary.mapper']->fromStorageRow( $row, $obj );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid rev_type for board history entry: ' . $row['rev_type'], 'fail-load-data' );
			}
		}
	);
};
$c['storage.board_history.indexes'] = function( $c ) {
	return array( $c['storage.board_history.indexes.primary'] );
};
$c['storage.board_history'] = function( $c ) {
	return new ObjectLocator(
		$c['storage.board_history.mapper'],
		$c['storage.board_history.backend'],
		$c['storage.board_history.indexes']
	);
};

$c['storage.header.listeners.username'] = function( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		array(
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki'
		)
	);
};
$c['storage.header.listeners'] = function( $c ) {
	return array(
		$c['reference.recorder'],
		$c['storage.board_history.indexes.primary'],
		$c['storage.header.listeners.username'],
		$c['listener.recentchanges'],
		$c['listener.editcount'],
	);
};
$c['storage.header.primary_key'] = array( 'rev_id' );
$c['storage.header.mapper'] = function( $c ) {
	return CachingObjectMapper::model( 'Flow\\Model\\Header', array( 'rev_id' ) );
};
$c['storage.header.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new HeaderRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore
	);

};
$c['storage.header.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.buffered'],
		$c['storage.header.backend'],
		'flow_header:v2:pk',
		$c['storage.header.primary_key']
	);
};
$c['storage.header.indexes.topic_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.header.backend'],
		'flow_header:workflow',
		array( 'rev_type_id' ),
		array(
			'limit' => 100,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.header.indexes.primary'],
			'create' => function( array $row ) {
				return $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.header.indexes'] = function( $c ) {
	return array(
		$c['storage.header.indexes.primary'],
		$c['storage.header.indexes.topic_lookup']
	);
};
$c['storage.header'] = function( $c ) {
	return new ObjectManager(
		$c['storage.header.mapper'],
		$c['storage.header.backend'],
		$c['storage.header.indexes'],
		$c['storage.header.listeners']
	);
};

$c['storage.post_summary.class'] = 'Flow\Model\PostSummary';
$c['storage.post_summary.primary_key'] = array( 'rev_id' );
$c['storage.post_summary.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.post_summary.class'],
		$c['storage.post_summary.primary_key']
	);
};
$c['storage.post_summary.listeners.username'] = function( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		array(
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki'
		)
	);
};
$c['storage.post_summary.listeners'] = function( $c ) {
	return array(
		$c['listener.recentchanges'],
		$c['storage.post_summary.listeners.username'],
		$c['storage.board_history.indexes.primary'],
		$c['listener.editcount'],
		// topic history -- to keep a history by topic we have to know what topic every post
		// belongs to, not just its parent. TopicHistoryIndex is a slight tweak to TopKIndex
		// using TreeRepository for extra information and stuffing it into topic_root while indexing
		$c['storage.topic_history.indexes.primary'],
		$c['reference.recorder'],
	);
};
$c['storage.post_summary.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new PostSummaryRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore
	);
};
$c['storage.post_summary.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.buffered'],
		$c['storage.post_summary.backend'],
		'flow_post_summary:v2:pk',
		$c['storage.post_summary.primary_key']
	);
};
$c['storage.post_summary.indexes.topic_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.post_summary.backend'],
		'flow_post_summary:workflow',
		array( 'rev_type_id' ),
		array(
			'limit' => 100,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.post_summary.indexes.primary'],
			'create' => function( array $row ) {
				return $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.post_summary.indexes'] = function( $c ) {
	return array(
		$c['storage.post_summary.indexes.primary'],
		$c['storage.post_summary.indexes.topic_lookup']
	);
};
$c['storage.post_summary'] = function( $c ) {
	return new ObjectManager(
		$c['storage.post_summary.mapper'],
		$c['storage.post_summary.backend'],
		$c['storage.post_summary.indexes'],
		$c['storage.post_summary.listeners']
	);
};

$c['storage.topic_list.class'] = 'Flow\Model\TopicListEntry';
$c['storage.topic_list.table'] = 'flow_topic_list';
$c['storage.topic_list.primary_key'] = array( 'topic_list_id', 'topic_id' );
$c['storage.topic_list.indexes.last_updated.backend'] = function( $c ) {
	return new TopicListLastUpdatedStorage(
		$c['db.factory'],
		$c['storage.topic_list.table'],
		$c['storage.topic_list.primary_key']
	);
};
$c['storage.topic_list.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.topic_list.class'],
		$c['storage.topic_list.primary_key']
	);
};
$c['storage.topic_list.backend'] = function( $c ) {
	return new TopicListStorage(
		// factory and table
		$c['db.factory'],
		$c['storage.topic_list.table'],
		$c['storage.topic_list.primary_key']
	);
};
// Lookup from topic_id to its owning board id
$c['storage.topic_list.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.buffered'],
		$c['storage.topic_list.backend'],
		'flow_topic_list:topic',
		array( 'topic_id' )
	);
};
// Lookup from board to contained topics
$c['storage.topic_list.indexes.reverse_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.topic_list.backend'],
		'flow_topic_list:list',
		array( 'topic_list_id' ),
		array( 'sort' => 'topic_id' )
	);
};
$c['storage.topic_list.indexes.last_updated'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.topic_list.indexes.last_updated.backend'],
		'flow_topic_list_last_updated:list',
		array( 'topic_list_id' ),
		array(
			'sort' => 'workflow_last_update_timestamp',
			'order' => 'desc'
		)
	);
};
$c['storage.topic_list.indexes'] = function( $c ) {
	return array(
		$c['storage.topic_list.indexes.primary'],
		$c['storage.topic_list.indexes.reverse_lookup'],
		$c['storage.topic_list.indexes.last_updated'],
	);
};
$c['storage.topic_list'] = function( $c ) {
	return new ObjectManager(
		$c['storage.topic_list.mapper'],
		$c['storage.topic_list.backend'],
		$c['storage.topic_list.indexes']
	);
};
$c['storage.post.class'] = 'Flow\Model\PostRevision';
$c['storage.post.primary_key'] = array( 'rev_id' );
$c['storage.post.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.post.class'],
		$c['storage.post.primary_key']
	);
};
$c['storage.post.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new PostRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore,
		$c['repository.tree']
	);
};
$c['storage.post.listeners.moderation_logging'] = function( $c ) {
	return new Flow\Data\Listener\ModerationLoggingListener(
		$c['logger.moderation']
	);
};
$c['storage.post.listeners.username'] = function( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		array(
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki',
			'tree_orig_user_id' => 'tree_orig_user_wiki'
		)
	);
};
$c['storage.post.listeners.watch_topic'] = function( $c ) {
	// Auto-subscribe users to the topic after performing specific actions
	return new Flow\Data\Listener\ImmediateWatchTopicListener(
		$c['watched_items']
	);
};
$c['storage.post.listeners.notification'] = function( $c ) {
	// Defer notifications triggering till end of request so we could get
	// article_id in the case of a new topic, this will need support of
	// adding deferred update when running deferred update
	return new Flow\Data\Listener\DeferredInsertLifecycleHandler(
		$c['deferred_queue'],
		new Flow\Data\Listener\NotificationListener(
			$c['controller.notification']
		)
	);
};
$c['storage.post.listeners'] = function( $c ) {
	return array(
		$c['reference.recorder'],
		$c['collection.cache'],
		$c['storage.post.listeners.username'],
		$c['storage.post.listeners.watch_topic'],
		$c['storage.post.listeners.notification'],
		$c['storage.post.listeners.moderation_logging'],
		$c['listener.recentchanges'],
		$c['listener.editcount'],
		// topic history -- to keep a history by topic we have to know what topic every post
		// belongs to, not just its parent. TopicHistoryIndex is a slight tweak to TopKIndex
		// using TreeRepository for extra information and stuffing it into topic_root while indexing
		$c['storage.board_history.indexes.primary'],
		$c['storage.topic_history.indexes.primary'],
	);
};
$c['storage.post.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.buffered'],
		$c['storage.post.backend'],
		'flow_revision:v4:pk',
		$c['storage.post.primary_key']
	);
};
// Each bucket holds a list of revisions in a single post
$c['storage.post.indexes.post_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.post.backend'],
		'flow_revision:descendant',
		array( 'rev_type_id' ),
		array(
			'limit' => 100,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.post.indexes.primary'],
			'create' => function( array $row ) {
				// return true to create instead of merge index
				return $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.post.indexes'] = function( $c ) {
	return array(
		$c['storage.post.indexes.primary'],
		$c['storage.post.indexes.post_lookup'],
	);
};
$c['storage.post'] = function( $c ) {
	return new ObjectManager(
		$c['storage.post.mapper'],
		$c['storage.post.backend'],
		$c['storage.post.indexes'],
		$c['storage.post.listeners']
	);
};
$c['storage.topic_history.primary_key'] = array( 'rev_id' );
$c['storage.topic_history.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new TopicHistoryStorage(
		new PostRevisionStorage( $c['db.factory'], $wgFlowExternalStore, $c['repository.tree'] ),
		new PostSummaryRevisionStorage( $c['db.factory'], $wgFlowExternalStore )
	);
};
$c['storage.topic_history.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.buffered'],
		$c['storage.topic_history.backend'],
		'flow_revision:v4:pk',
		$c['storage.topic_history.primary_key']
	);
};
$c['storage.topic_history.indexes.topic_lookup'] = function( $c ) {
	return new TopicHistoryIndex(
		$c['memcache.buffered'],
		$c['storage.topic_history.backend'],
		$c['repository.tree'],
		'flow_revision:topic',
		array( 'topic_root_id' ),
		array(
			'limit' => 500,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.topic_history.indexes.primary'],
			'create' => function( array $row ) {
				// only create new indexes for post revisions
				if ( $row['rev_type'] !== 'post' ) {
					return false;
				}
				// if the post has no parent and the revision has no parent
				// then this is a brand new topic title
				return $row['tree_parent_id'] === null && $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.topic_history.indexes'] = function( $c ) {
	return array(
		$c['storage.topic_history.indexes.primary'],
		$c['storage.topic_history.indexes.topic_lookup'],
	);
};
$c['storage.topic_history.mapper'] = function( $c ) {
	return new BasicObjectMapper(
		function( $rev ) use( $c ) {
			if ( $rev instanceof PostRevision ) {
				return $c['storage.post.mapper']->toStorageRow( $rev );
			} elseif ( $rev instanceof PostSummary ) {
				return $c['storage.post_summary.mapper']->toStorageRow( $rev );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid class for board history entry: ' . get_class( $rev ), 'fail-load-data' );
			}
		},
		function( array $row, $obj = null ) use( $c ) {
			if ( $row['rev_type'] === 'post' ) {
				return $c['storage.post.mapper']->fromStorageRow( $row, $obj );
			} elseif ( $row['rev_type'] === 'post-summary' ) {
				return $c['storage.post_summary.mapper']->fromStorageRow( $row, $obj );
			} else {
				throw new \Flow\Exception\InvalidDataException( 'Invalid rev_type for board history entry: ' . $row['rev_type'], 'fail-load-data' );
			}
		}
	);
};
$c['storage.topic_history'] = function( $c ) {
	return new ObjectLocator(
		$c['storage.topic_history.mapper'],
		$c['storage.topic_history.backend'],
		$c['storage.topic_history.indexes']
	);
};
$c['storage.manager_list'] = function( $c ) {
	return array(
		'Flow\\Model\\Workflow' => 'storage.workflow',
		'Workflow' => 'storage.workflow',

		'Flow\\Model\\PostRevision' => 'storage.post',
		'PostRevision' => 'storage.post',
		'post' => 'storage.post',

		'Flow\\Model\\PostSummary' => 'storage.post_summary',
		'PostSummary' => 'storage.post_summary',
		'post-summary' => 'storage.post_summary',

		'Flow\\Model\\TopicListEntry' => 'storage.topic_list',
		'TopicListEntry' => 'storage.topic_list',

		'Flow\\Model\\Header' => 'storage.header',
		'Header' => 'storage.header',
		'header' => 'storage.header',

		'BoardHistoryEntry' => 'storage.board_history',

		'TopicHistoryEntry' => 'storage.topic_history',

		'Flow\\Model\\WikiReference' => 'storage.wiki_reference',
		'WikiReference' => 'storage.wiki_reference',

		'Flow\\Model\\URLReference' => 'storage.url_reference',
		'URLReference' => 'storage.url_reference',
	);
};
$c['storage'] = function( $c ) {
	return new \Flow\Data\ManagerGroup(
		$c,
		$c['storage.manager_list']
	);
};
$c['loader.root_post'] = function( $c ) {
	return new \Flow\Repository\RootPostLoader(
		$c['storage'],
		$c['repository.tree']
	);
};

// Queue of callbacks to run by DeferredUpdates, but only
// on successfull commit
$c['deferred_queue'] = function( $c ) {
	return new SplQueue;
};

$c['submission_handler'] = function( $c ) {
	return new Flow\SubmissionHandler(
		$c['storage'],
		$c['db.factory'],
		$c['memcache.buffered'],
		$c['deferred_queue']
	);
};
$c['factory.block'] = function( $c ) {
	return new Flow\BlockFactory(
		$c['storage'],
		$c['loader.root_post']
	);
};
$c['factory.loader.workflow'] = function( $c ) {
	global $wgFlowDefaultWorkflow;

	return new Flow\WorkflowLoaderFactory(
		$c['storage'],
		$c['factory.block'],
		$c['submission_handler'],
		$wgFlowDefaultWorkflow
	);
};
// Initialized in FlowHooks to faciliate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['occupation_controller'] = FlowHooks::getOccupationController();

$c['controller.notification'] = function( $c ) {
	global $wgContLang;
	return new Flow\NotificationController( $wgContLang );
};

// Initialized in FlowHooks to faciliate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['controller.abusefilter'] = FlowHooks::getAbuseFilter();

$c['controller.spamregex'] = function( $c ) {
	return new Flow\SpamFilter\SpamRegex;
};

$c['controller.spamblacklist'] = function( $c ) {
	return new Flow\SpamFilter\SpamBlacklist;
};

$c['controller.confirmedit'] = function( $c ) {
	return new Flow\SpamFilter\ConfirmEdit;
};

$c['controller.contentlength'] = function( $c ) {
	return new Flow\SpamFilter\ContentLengthFilter;
};

$c['controller.spamfilter'] = function( $c ) {
	return new Flow\SpamFilter\Controller(
		$c['controller.spamregex'],
		$c['controller.spamblacklist'],
		$c['controller.abusefilter'],
		$c['controller.confirmedit'],
		$c['controller.contentlength']
	);
};

$c['query.categoryviewer'] = function( $c ) {
	return new Flow\Formatter\CategoryViewerQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['formatter.categoryviewer'] = function( $c ) {
	return new Flow\Formatter\CategoryViewerFormatter(
		$c['permissions']
	);
};
$c['query.singlepost'] = function( $c ) {
	return new Flow\Formatter\SinglePostQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.checkuser'] = function( $c ) {
	return new Flow\Formatter\CheckUserQuery(
		$c['storage'],
		$c['repository.tree']
	);
};

$c['formatter.irclineurl'] = function( $c ) {
	return new Flow\Formatter\IRCLineUrlFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};

$c['formatter.checkuser'] = function( $c ) {
	return new Flow\Formatter\CheckUserFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};
$c['formatter.revisionview'] = function( $c ) {
	return new Flow\Formatter\RevisionViewFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
};
$c['formatter.revision.diff.view'] = function( $c ) {
	return new Flow\Formatter\RevisionDiffViewFormatter(
		$c['formatter.revisionview'],
		$c['url_generator']
	);
};
$c['query.topiclist'] = function( $c ) {
	return new Flow\Formatter\TopicListQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions'],
		$c['watched_items']
	);
};
$c['query.topic.history'] = function( $c ) {
	return new Flow\Formatter\TopicHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.post.history'] = function( $c ) {
	return new Flow\Formatter\PostHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.recentchanges'] = function( $c ) {
	$query = new Flow\Formatter\RecentChangesQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
	$query->setExtendWatchlist( $c['user']->getOption( 'extendwatchlist' ) );

	return $query;
};
$c['query.postsummary'] = function( $c ) {
	return new Flow\Formatter\PostSummaryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};
$c['query.header.view'] = function( $c ) {
	return new Flow\Formatter\HeaderViewQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.post.view'] = function( $c ) {
	return new Flow\Formatter\PostViewQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.postsummary.view'] = function( $c ) {
	return new Flow\Formatter\PostSummaryViewQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['formatter.recentchanges'] = function( $c ) {
	return new Flow\Formatter\RecentChanges(
		$c['permissions'],
		$c['formatter.revision']
	);
};

$c['query.contributions'] = function( $c ) {
	return new Flow\Formatter\ContributionsQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['memcache'],
		$c['db.factory']
	);
};
$c['formatter.contributions'] = function( $c ) {
	return new Flow\Formatter\Contributions(
		$c['permissions'],
		$c['formatter.revision']
	);
};
$c['formatter.contributions.feeditem'] = function( $c ) {
	return new Flow\Formatter\FeedItemFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};
$c['query.board-history'] = function( $c ) {
	return new Flow\Formatter\BoardHistoryQuery(
		$c['storage'],
		$c['repository.tree']
	);
};

// The RevisionFormatter holds internal state like
// contentType of output and if it should include history
// properties.  To prevent different code using the formatter
// from causing problems return a new RevisionFormatter every
// time it is requested.
$c['formatter.revision'] = $c->factory( function( $c ) {
	global $wgFlowMaxThreadingDepth;

	return new Flow\Formatter\RevisionFormatter(
		$c['permissions'],
		$c['templating'],
		$c['repository.username'],
		$wgFlowMaxThreadingDepth
	);
} );
$c['formatter.topiclist'] = function( $c ) {
	return new Flow\Formatter\TopicListFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
};
$c['formatter.topiclist.toc'] = function ( $c ) {
	return new Flow\Formatter\TocTopicListFormatter(
		$c['templating']
	);
};
$c['formatter.topic'] = function( $c ) {
	return new Flow\Formatter\TopicFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
};
$c['query.search'] = function( $c ) {
	return new Flow\Formatter\SearchQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['searchindex.updaters'] = function( $c ) {
	return array(
		'topic' => new \Flow\Search\TopicUpdater( $c['db.factory'], $c['loader.root_post'] ),
		'header' => new \Flow\Search\HeaderUpdater( $c['db.factory'] )
	);
};

$c['logger.moderation'] = function( $c ) {
	return new Flow\Log\ModerationLogger(
		$c['flow_actions']
	);
};

$c['storage.wiki_reference.class'] = 'Flow\Model\WikiReference';
$c['storage.wiki_reference.table'] = 'flow_wiki_ref';
$c['storage.wiki_reference.primary_key'] = array(
	'ref_src_namespace',
	'ref_src_title',
	'ref_src_object_id',
	'ref_type',
	'ref_target_namespace', 'ref_target_title'
);
$c['storage.wiki_reference.mapper'] = function( $c ) {
	return Flow\Data\Mapper\BasicObjectMapper::model(
		$c['storage.wiki_reference.class']
	);
};
$c['storage.wiki_reference.backend'] = function( $c ) {
	return new BasicDbStorage(
		$c['db.factory'],
		$c['storage.wiki_reference.table'],
		$c['storage.wiki_reference.primary_key']
	);
};
$c['storage.wiki_reference.indexes.source_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.wiki_reference.backend'],
		'flow_ref:wiki:by-source',
		array(
			'ref_src_namespace',
			'ref_src_title',
		),
		array(
			'order' => 'ASC',
			'sort' => 'ref_src_object_id',
		)
	);
};
$c['storage.wiki_reference.indexes.revision_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.wiki_reference.backend'],
		'flow_ref:wiki:by-revision:v2',
		array(
			'ref_src_object_type',
			'ref_src_object_id',
		),
		array(
			'order' => 'ASC',
			'sort' => array( 'ref_target_namespace', 'ref_target_title' ),
		)
	);
};
$c['storage.wiki_reference.indexes'] = function( $c ) {
	return array(
		$c['storage.wiki_reference.indexes.source_lookup'],
		$c['storage.wiki_reference.indexes.revision_lookup'],
	);
};
$c['storage.wiki_reference'] = function( $c ) {
	return new ObjectManager(
		$c['storage.wiki_reference.mapper'],
		$c['storage.wiki_reference.backend'],
		$c['storage.wiki_reference.indexes'],
		array()
	);
};
$c['storage.url_reference.class'] = 'Flow\Model\URLReference';
$c['storage.url_reference.table'] = 'flow_ext_ref';
$c['storage.url_reference.primary_key'] = array(
	'ref_src_namespace',
	'ref_src_title',
	'ref_src_object_id',
	'ref_type',
	'ref_target'
);
$c['storage.url_reference.mapper'] = function( $c ) {
	return Flow\Data\Mapper\BasicObjectMapper::model(
		$c['storage.url_reference.class']
	);
};
$c['storage.url_reference.backend'] = function( $c ) {
	return new BasicDbStorage(
		// factory and table
		$c['db.factory'],
		$c['storage.url_reference.table'],
		$c['storage.url_reference.primary_key']
	);
};

$c['storage.url_reference.indexes.revision_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.url_reference.backend'],
		'flow_ref:url:by-source',
		array(
			'ref_src_namespace',
			'ref_src_title',
		),
		array(
			'order' => 'ASC',
			'sort' => 'ref_src_object_id',
		)
	);
};
$c['storage.url_reference.indexes.source_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.buffered'],
		$c['storage.url_reference.backend'],
		'flow_ref:url:by-revision:v2',
		array(
			'ref_src_object_type',
			'ref_src_object_id',
		),
		array(
			'order' => 'ASC',
			'sort' => array( 'ref_target' ),
		)
	);
};
$c['storage.url_reference.indexes'] = function( $c ) {
	return array(
		$c['storage.url_reference.indexes.source_lookup'],
		$c['storage.url_reference.indexes.revision_lookup'],
	);
};
$c['storage.url_reference'] = function( $c ) {
	return new ObjectManager(
		$c['storage.url_reference.mapper'],
		$c['storage.url_reference.backend'],
		$c['storage.url_reference.indexes'],
		array()
	);
};

$c['reference.updater.links-tables'] = function( $c ) {
	return new Flow\LinksTableUpdater( $c['storage'] );
};

$c['reference.clarifier'] = function( $c ) {
	return new Flow\ReferenceClarifier( $c['storage'], $c['url_generator'] );
};

$c['reference.extractor'] = function( $c ) {
	$default = array(
		new Flow\Parsoid\Extractor\ImageExtractor,
		new Flow\Parsoid\Extractor\PlaceholderExtractor,
		new Flow\Parsoid\Extractor\WikiLinkExtractor,
		new Flow\Parsoid\Extractor\ExtLinkExtractor,
		new Flow\Parsoid\Extractor\TransclusionExtractor,
	);
	$extractors = array(
		'header' => $default,
		'post-summary' => $default,
		'post' => $default,
	);
	// In addition to the defaults header and summaries collect
	// the related categories.
	$extractors['header'][] = $extractors['post-summary'][] = new Flow\Parsoid\Extractor\CategoryExtractor;

	return new Flow\Parsoid\ReferenceExtractor( $extractors );
};

$c['reference.recorder'] = function( $c ) {
	return new Flow\Data\Listener\ReferenceRecorder(
		$c['reference.extractor'],
		$c['reference.updater.links-tables'],
		$c['storage'],
		$c['repository.tree']
	);
};

$c['user_merger'] = function( $c ) {
	return new Flow\Data\Utils\UserMerger(
		$c['db.factory'],
		$c['storage']
	);
};

$c['importer'] = function( $c ) {
	$importer = new Flow\Import\Importer(
		$c['storage'],
		$c['factory.loader.workflow'],
		$c['memcache.buffered'],
		$c['db.factory'],
		$c['deferred_queue']
	);

	$importer->addPostprocessor( new Flow\Import\Postprocessor\SpecialLogTopic(
		$c['occupation_controller']->getTalkpageManager()
	) );

	return $importer;
};

$c['listener.editcount'] = function( $c ) {
	return new \Flow\Data\Listener\EditCountListener( $c['flow_actions'] );
};

return $c;
