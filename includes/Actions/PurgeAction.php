<?php

namespace Flow\Actions;

use BagOStuff;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\Pager\Pager;
use Flow\Formatter\TopicListQuery;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use HashBagOStuff;

/**
 * Extends the core Purge action to additionally purge flow specific cache
 * keys related to the requested title.
 */
class PurgeAction extends \PurgeAction {
	/**
	 * @var BagOStuff
	 */
	protected $realMemcache;

	/**
	 * @var BagOStuff
	 */
	protected $hashBag;

	/**
	 * {@inheritDoc}
	 */
	public function onSubmit( $data ) {
		if ( !parent::onSubmit( array() ) ) {
			return false;
		}

		// Replace $c['memcache'] with a hash bag o stuff.  This will look to the
		// application layer like an empty cache, and as such it will populate this
		// empty cache with all the cache keys required to reload this page.
		// We then extract the complete list of keys updated from this hash bag o stuff
		// and delete them from the real memcache.
		$container = Container::getContainer();
		$container->extend( 'memcache', function( $memcache, $c ) {
			$c['memcache.purge_backup'] = $memcache;
			return new HashBagOStuff;
		} );
		$this->hashBag = $container['memcache'];
		$this->realMemcache = $container['memcache.purge_backup'];

		/** @var WorkflowLoaderFactory $loader */
		$loader = $container['factory.loader.workflow'];
		$workflow = $loader
			->createWorkflowLoader( $this->page->getTitle() )
			->getWorkflow();

		switch( $workflow->getType() ) {
		case 'discussion':
			$this->fetchDiscussion( $workflow );
			break;

		case 'topic':
			$this->fetchTopics( array( $workflow->getId()->getAlphadecimal() => $workflow->getId() ) );
			break;

		default:
			throw new \MWException( 'Unknown workflow type: ' . $workflow->getType() );
		}

		// delete all the keys we just visited
		$this->purgeCache();

		return true;
	}

	/**
	 * Load the topics from the requested discussion.  Does not return anything, the goal
	 * here is to populate $this->hashBag.
	 *
	 * @param Workflow $workflow
	 */
	protected function fetchDiscussion( Workflow $workflow ) {
		$results = array();
		$pagers = array();
		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );

		// 'newest' sort order
		$pagers[] = new Pager(
			$storage->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $workflow->getId() ),
			array( 'pager-limit' => 499 )
		);

		// 'updated' sort order
		$pagers[] = new Pager(
			$storage->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $workflow->getId() ),
			array(
				'pager-limit' => 499,
				'sort' => 'workflow_last_update_timestamp',
				'order' => 'desc',
			)
		);

		foreach ( $pagers as $pager ) {
			foreach ( $pager->getPage()->getResults() as $entry ) {
				// use array key to de-duplicate
				$results[$entry->getId()->getAlphadecimal()] = $entry->getId();
			}
		}

		$this->fetchTopics( $results );

		// purge the board history
		$storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 499 )
		);
	}


	/**
	 * Load the requested topics.  Does not return anything, the goal
	 * here is to populate $this->hashBag.
	 *
	 * @param UUID[] $results
	 */
	protected function fetchTopics( array $results ) {
		// purge the revisions that make up the topic
		/** @var TopicListQuery $query */
		$query = Container::get( 'query.topiclist' );
		$query->getResults( $results );

		// Purge the history
		$queries = array();
		foreach ( $results as $id ) {
			$queries[] = array( 'topic_root_id' => $id );
		}
		Container::get( 'storage' )->findMulti(
			'TopicHistoryEntry',
			$queries,
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 499 )
		);
	}

	/**
	 * Purge all keys written to $this->hashBag that match our cache prefix key.
	 */
	protected function purgeCache() {
		$prefix = $this->cacheKeyPrefix();
		$reflProp = new \ReflectionProperty( $this->hashBag, 'bag' );
		$reflProp->setAccessible( true );
		// Loop through all the cache keys we just populated and find the ones
		// that contain our prefix.
		$keys = array_filter(
			array_keys( $reflProp->getValue( $this->hashBag ) ),
			function( $key ) use( $prefix ) {
				if ( strpos( $key, $prefix ) === 0 ) {
					return true;
				} else {
					return false;
				}
			}
		);

		foreach ( $keys as $key ) {
			$this->realMemcache->delete( $key );
		}

		return true;
	}

	/**
	 * @return string The id of the database being cached
	 */
	protected function cacheKeyPrefix() {
		global $wgFlowDefaultWikiDb;
		if ( $wgFlowDefaultWikiDb === false ) {
			return wfWikiId();
		} else {
			return $wgFlowDefaultWikiDb;
		}
	}
}
