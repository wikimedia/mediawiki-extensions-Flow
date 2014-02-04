<?php

namespace Flow\Model;

/**
 * LocalBufferedCache saves all data that has been requested in an internal
 * cache (in memory, per request). This provides the opportunity of (trying to)
 * be smart about what results we fetch.
 * The class extends the default AbstractRevisionable to make sure not all
 * revisions are loaded unless we really need them. It could very well be that
 * perhaps 5 recent revisions have already been loaded in other parts of the
 * code, and we only need the 3rd most recent, in which case we shouldn't
 * try to fetch all of them.
 */
abstract class LocalCacheAbstractRevisionable extends AbstractRevisionable {
	/**
	 * Returns all revisions.
	 *
	 * @return AbstractRevision
	 */
	public function getAllRevisions() {
		// if we have not yet loaded everything, just clear what we have and
		// fetch from cache
		if ( $this->loaded() ) {
			$this->revisions = array();
		}

		return parent::getAllRevisions();
	}

	/**
	 * Returns the revision with the given id.
	 *
	 * @param UUID $uuid
	 * @return AbstractRevision|null null if there is no such revision
	 */
	public function getRevision( UUID $uuid ) {
		// check if fetching last already res
		if ( isset( $this->revisions[$uuid->getHex() ] ) ) {
			return $this->revisions[$uuid->getHex() ];
		}

		/*
		 * The strategy here is to avoid having to call getAllRevisions(), which
		 * is most likely to have to load (fresh) data that is not yet in
		 * LocalBufferedCache's internal cache.
		 * To do so, we'll build the $this->revisions array by hand. Starting at
		 * the most recent revision and going up 1 revision at a time, checking
		 * if it is already in LocalBufferedCache's cache.
		 * If, however, we can't find the requested revisions (or one of the
		 * revisions on our way to the requested revision) in the internal cache
		 * of LocalBufferedCache, we'll just bail and load all revisions after
		 * all: if we do have to fetch data, might as well do it all in 1 go!
		 */
		while ( !$this->loaded() ) {
			// fetch current oldest revision
			$oldest = $this->getOldestLoaded();

			// fetch that one's preceeding revision id
			$previousId = $oldest->getPrevRevisionId();

			// check if it's in local storage already
			if ( $this->getStorage()->got( $previousId ) ) {
				$revision = $this->getStorage()->get( $previousId );

				// add this revision to revisions array
				array_unshift( $this->revisions, $revision );

				// stop iterating if we've found the one we wanted
				if ( $previousId->getHex() === $uuid->getHex() ) {
					break;
				}
			} else {
				// revision not found in local storage: load all revisions
				$this->getAllRevisions();
				break;
			}
		}

		if ( !isset( $this->revisions[$uuid->getHex()] ) ) {
			return null;
		}

		return $this->revisions[$uuid->getHex()];
	}

	/**
	 * Returns the most recent revision.
	 *
	 * @return AbstractRevision
	 */
	public function getLastRevision() {
		// if $revisions is not empty, it will always have the last revision,
		// at the end of the array
		if ( $this->revisions ) {
			return end( $this->revisions );
		}

		$attributes = array( $this->getIdColumn() => $this->uuid );
		$options = array( 'sort' => 'rev_id', 'limit' => 1, 'order' => 'DESC' );

		if ( $this->storage->found( $attributes, $options ) ) {
			// if last revision is already known in local cache, fetch it
			return $this->find( $attributes, $options );

		} else {
			// otherwise, might as well fetch all previous revisions while we're at
			// it - saves roundtrips to cache/db
			unset( $options['limit'] );
			$this->revisions = $this->storage->find( $attributes, $options );
			return end( $this->revisions );
		}
	}

	/**
	 * Given a certain revision, returns the next revision.
	 *
	 * @param AbstractRevision $revision
	 * @return AbstractRevision|null null if there is no next revision
	 */
	public function getNextRevision( AbstractRevision $revision ) {
		// make sure the given revision is loaded
		$this->getRevision( $revision->getRevisionId() );

		// find requested id, based on given revision
		$ids = array_keys( $this->revisions );
		$current = array_search( $revision->getRevisionId()->getHex(), $ids );
		$next = $current + 1;

		if ( $next >= count( $ids ) ) {
			return null;
		}

		return $this->getRevision( UUID::create( $ids[$next] ) );
	}


	/**
	 * Returns true if all revisions have been loaded into $this->revisions.
	 *
	 * @return bool
	 */
	public function loaded() {
		$first = reset( $this->revisions );
		return $first && $first->getPrevRevisionId() === null;
	}

	/**
	 * Returns the oldest revision that has already been fetched via this class.
	 *
	 * @return AbstractRevision
	 */
	public function getOldestLoaded() {
		if ( !$this->revisions ) {
			return $this->getLastRevision();
		}

		return reset( $this->revisions );
	}
}
