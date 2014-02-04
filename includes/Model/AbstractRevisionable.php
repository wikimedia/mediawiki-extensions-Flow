<?php

namespace Flow\Model;

use Flow\Container;
use Flow\Exception\InvalidDataException;

abstract class AbstractRevisionable {
	/**
	 * Id of the revisionable object.
	 *
	 * @var UUID
	 */
	protected $uuid;

	/**
	 * @var \Flow\Data\ObjectManager
	 */
	protected $storage;

	/**
	 * Array of revisions for this object.
	 *
	 * @var array
	 */
	protected $revisions = array();

	/**
	 * Returns the revision class name for this specific object (e.g. Header,
	 * PostRevision)
	 *
	 * @return string
	 */
	abstract public function getRevisionClass();

	/**
	 * Returns the DB column that holds the revision hierarchy, where all
	 * revisions are mapped to a shared object id.
	 * E.g. a post can have multiple revisions, all of which have their own id;
	 * but they're identifiable as revisions of the same post because they share
	 * a common postId (in tree_rev_descendant_id)
	 *
	 * @return string
	 */
	abstract public function getIdColumn();

	/**
	 * Returns the object's UUID, given an AbstractRevision (not to be confused
	 * with that single revision's UUID!)
	 *
	 * @param AbstractRevision $revision
	 * @return UUID
	 */
	abstract protected static function getIdFromRevision( AbstractRevision $revision );

	/**
	 * Use the static methods to load an object from a given revision.
	 *
	 * @see AbstractRevisionable::newFromId
	 * @see AbstractRevisionable::newFromRevision
	 * @see AbstractRevisionable::newFromRevisionId
	 *
	 * @param AbstractRevision[optional] $revision
	 */
	protected function __construct( UUID $uuid ) {
		$this->uuid = $uuid ;
	}

	/**
	 * Instantiate a new object based on its id.
	 *
	 * @param UUID $uuid
	 * @return AbstractRevisionable
	 */
	public static function newFromId( UUID $uuid ) {
		return new static( $uuid );
	}

	/**
	 * Instantiate a new object based off of an AbstractRevision object.
	 *
	 * @param AbstractRevision $revision
	 * @return AbstractRevisionable
	 */
	public static function newFromRevision( AbstractRevision $revision ) {
		$uuid = static::getIdFromRevision( $revision );
		return static::newFromId( $uuid );
	}

	/**
	 * @return UUID
	 */
	public function getId() {
		return $this->uuid;
	}

	/**
	 * @return \Flow\Data\ObjectManager
	 */
	public function getStorage() {
		if ( !$this->storage ) {
			$this->storage = Container::get( 'storage' )->getStorage( $this->getRevisionClass() );
		}

		return $this->storage;
	}

	/**
	 * Returns all revisions.
	 *
	 * @return array Array of AbstractRevision
	 */
	public function getAllRevisions() {
		if ( !$this->revisions ) {
			$revisions = $this->storage->find(
				array( $this->getIdColumn() => $this->uuid ),
				array( 'sort' => 'rev_id', 'order' => 'DESC' )
			);

			if ( !$revisions ) {
				throw new InvalidDataException( 'Revisions for ' . $this->uuid . ' could not be found', 'invalid-revision-id' );
			}

			foreach ( $revisions as $revision ) {
				$this->revisions[$revision->getRevisionId()->getAlphadecimal()] = $revision;
			}
		}

		return $this->revisions;
	}

	/**
	 * Returns the revision with the given id.
	 *
	 * @param UUID $uuid
	 * @return AbstractRevision|null null if there is no such revision
	 */
	public function getRevision( UUID $uuid ) {
		// make sure all revisions have been loaded
		$this->getAllRevisions();

		if ( !isset( $this->revisions[$uuid->getAlphadecimal()] ) ) {
			return null;
		}

		// find requested id, based on given revision
		return $this->revisions[$uuid->getAlphadecimal()];
	}

	/**
	 * Returns the oldest revision.
	 *
	 * @return AbstractRevision
	 */
	public function getFirstRevision() {
		$revisions = $this->getAllRevisions();
		return array_shift( $revisions );
	}

	/**
	 * Returns the most recent revision.
	 *
	 * @return AbstractRevision
	 */
	public function getLastRevision() {
		$revisions = $this->getAllRevisions();
		return array_pop( $revisions );
	}

	/**
	 * Given a certain revision, returns the previous revision.
	 *
	 * @param AbstractRevision $revision
	 * @return AbstractRevision|null null if there is no previous revision
	 */
	public function getPreviousRevision( AbstractRevision $revision ) {
		return $this->getRevision( $revision->getPrevRevisionId() );
	}

	/**
	 * Given a certain revision, returns the next revision.
	 *
	 * @param AbstractRevision $revision
	 * @return AbstractRevision|null null if there is no next revision
	 */
	public function getNextRevision( AbstractRevision $revision ) {
		// make sure all revisions have been loaded
		$this->getAllRevisions();

		// find requested id, based on given revision
		$ids = array_keys( $this->revisions );
		$current = array_search( $revision->getRevisionId()->getAlphadecimal(), $ids );
		$next = $current + 1;

		if ( $next >= count( $ids ) ) {
			return null;
		}

		return $this->getRevision( UUID::create( $ids[$next] ) );
	}
}
