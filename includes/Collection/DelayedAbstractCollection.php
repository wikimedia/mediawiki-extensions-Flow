<?php

namespace Flow\Collection;

use Flow\Container;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;

/**
 * While the collection classes make it easy to fetch data, they still don't
 * make fetching it cheaper. LocalCacheAbstractCollection will do some effort
 * there (by preloading all previous revision of that collection, if it has to
 * resort to fetching from DB/cache). That may not always be the most efficient
 * way to get data: sometime we only just need 1 revision and fetching all of
 * them is just overkill.
 *
 * Instead, this class will save the requested revision ids and instead return
 * stub objects that serve as a pass-through for the requested revisions.
 * Once there is any kind of *interaction* with any of those stub objects, all
 * the requested revisions will be resolved at once.
 */
abstract class DelayedAbstractCollection extends LocalCacheAbstractCollection {
	// @todo: ensure there's only 1 collection object per uuid (similar to UUID cache)

	/**
	 * Array of queries to be executed.
	 *
	 * @var array
	 */
	protected $queries = array();

	/**
	 * @var Batchloader|null
	 */
	protected $loader;

	/**
	 * Will be set to true if we've scheduled to query for all revisions, in
	 * which case doing an additional query to fetch a specific revision becomes
	 * pointless.
	 *
	 * @var bool
	 */
	protected $scheduledAll = false;

	/**
	 * {@inheritDoc}
	 */
	public function getLastRevision() {
		if ( !$this->scheduledAll ) {
			$this->addDelayedFind(
				array( $this->getIdColumn() => $this->uuid ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
		}

		// the UUID for the last revision is not known until the query has been
		// resolved, so let's toss in a Closure that can be executed once the
		// query has been resolved and we can figure out the UUID
		// NOTE: we could actually just use ( $this ) instead of passing in
		// $collection as an argument, but that's only supported since PHP 5.4
		$uuid = function ( DelayedAbstractCollection $collection ) {
			$revisions = $collection->getLoadedRevisions();
			reset( $revisions );
			return UUID::create( key( $revisions ) );
		};

		return new RevisionStub( $this, $uuid );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAllRevisions() {
		// we may have already requested specific revisions, but apparently we
		// still need to fetch all anyway, so clear out those other queries
		foreach ( $this->queries as $query ) {
			list( $attributes, $options ) = $query;
			$this->getLoader()->removeFind( $this->getRevisionClass(), $attributes, $options );
		}

		$this->addDelayedFind(
			array( $this->getIdColumn() => $this->uuid ),
			array( 'sort' => 'rev_id', 'order' => 'DESC' )
		);

		$this->scheduledAll = true;

		// create a bogus "array" (-like object) that will only resolve the
		// query when the data is actually accessed
		$stub = new RevisionArrayStub();
		$stub->init( $this );
		return $stub;
	}

	/**
	 * Returns the revision with the given id.
	 *
	 * @param UUID $uuid
	 * @return AbstractRevision|null null if there is no such revision
	 */
	public function getRevision( UUID $uuid ) {
		if ( !$this->scheduledAll ) {
			$this->addDelayedFind(
				array( 'rev_id' => $uuid )
			);
		}

		return new RevisionStub( $this, $uuid );
	}

	/**
	 * Returns the currently loaded revisions for this collection.
	 *
	 * @return array
	 */
	public function getLoadedRevisions() {
		return $this->revisions;
	}

	/**
	 * @return Batchloader
	 */
	protected function getLoader() {
		if ( !$this->loader ) {
			$this->loader = Container::get( 'collection.batchloader' );
		}

		return $this->loader;
	}

	/**
	 * Add a delayed find query to the batchloader.
	 *
	 * @param array $attributes
	 * @param array $options
	 */
	protected function addDelayedFind( array $attributes, array $options = array() ) {
		$this->queries[] = array( $attributes, $options );
		$this->getLoader()->addFind( $this->getRevisionClass(), $this->getStorage(), $attributes, $options );
	}

	/**
	 * Resolves all scheduled queries & populates the found revisions into this
	 * object.
	 */
	public function resolveQueries() {
		if ( $this->queries ) {
			// we really need the object now, we're trying to access it via
			// RevisionStub - make batchloader execute all queries & gather the
			// results
			foreach ( $this->queries as $query ) {
				list( $attributes, $options ) = $query;
				$revisions = $this->getLoader()->getResult( $this->getRevisionClass(), $attributes, $options );

				foreach ( $revisions as $revision ) {
					$this->revisions[$revision->getRevisionId()->getAlphadecimal()] = $revision;
				}

				// sort revisions so have the most recent revision first
				krsort( $this->revisions );
			}

			$this->queries = array();
		}
	}

	/**
	 * This method will be called from the stub revision object, once we attempt
	 * to access the requested data for real. At this point, we need to resolve
	 * all scheduled queries and return the real AbstractRevision object.
	 *
	 * @param UUID $uuid
	 * @return AbstractRevision
	 * @throws \Flow\Exception\InvalidDataException
	 */
	public function getRevisionObject( UUID $uuid ) {
		$this->resolveQueries();

		if ( !isset( $this->revisions[$uuid->getAlphadecimal()] ) ) {
			throw new InvalidDataException( 'Unknown revision '. $uuid->getAlphadecimal(), 'fail-load-data' );
		}

		return $this->revisions[$uuid->getAlphadecimal()];
	}
}
