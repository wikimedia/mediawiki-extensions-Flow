<?php

namespace Flow\Actions;

use IContextSource;
use Flow\Container;
use Flow\Data\Pager\Pager;
use HashBagOStuff;
use Html;
use Page;

class PurgeAction extends \PurgeAction {
	protected $realMemcache;
	protected $hashBag;

	public function onSubmit( $data ) {
		global $wgMemc;

		if ( !parent::onSubmit( array() ) ) {
			return false;
		}

		// Swap in a hash bagostuff to force the backend to repopulate
		// it with cache keys
		$this->realMemcache = $wgMemc;
		$this->hashBag = new HashBagOStuff;
		$wgMemc = $this->hashBag;

		$workflow = Container::get( 'factory.loader.workflow' )
			->createWorkflowLoader( $this->page->getTitle() )
			->getWorkflow();

		switch( $workflow->getType() ) {
		case 'discussion':
			$this->fetchDiscussion( $workflow );
			break;

		case 'topic':
			$this->fetchTopics( array( $workflow ) );
			break;

		default:
			throw new \MWException( 'Unknown workflow type: ' . $workflow->getType() );
		}

		// delete all the keys we just visited
		$this->purgeCache();

		// Swap the real memcached back into place
		$wgMemc = $this->realMemcache;

		return true;
	}

	protected function fetchDiscussion( Workflow $workflow ) {
		$pager = new Pager(
			Container::get( 'storage' )->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $workflow->getId() ),
			array( 'pager-limit' => 499 )
		);

		return $this->fetchTopics( $pager->getPage()->getResults() );
	}

	protected function fetchTopics( array $results ) {
		Container::get( 'query.topiclist' )->getResults( $results );
	}

	protected function purgeCache() {
		$prefix = $this->cacheKeyPrefix();
		$reflProp = new \ReflectionProperty( $this->hashBag, 'bag' );
		$reflProp->setAccessible( true );
		// Loop through all the cache keys we just populated and find the ones
		// that contain our prefix.
		$keys = array_filter(
			array_keys( $reflProp->getValue( $this->hashBag ) ),
			function( $key ) use( $prefix ) {
				if ( 0 === strpos( $key, $prefix ) ) {
					return true;
				} else {
					return false;
				}
			}
		);

		foreach ( $keys as $key ) {
			$this->realMemc->delete( $key );
		}

		return true;
	}

	protected function cacheKeyPrefix() {
		global $wgFlowDefaultWikiDb;
		if ( $wgFlowDefaultWikiDb === false ) {
			return wfWikiId();
		} else {
			return $wgFlowDefaultWikiDb;
		}
	}
}
