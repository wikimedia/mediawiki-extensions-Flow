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
	abstract public function getId( AbstractRevision $revision );

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
		$this->storage = Container::get( 'storage' )->getStorage( $this->getRevisionClass() );
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
		// create bogus object to access getId()
		$object = new static( UUID::create() );
		$uuid = $object->getId( $revision );
		return static::newFromId( $uuid );
	}

	/**
	 * Instantiate a new object based off of a revision's UUID.
	 *
	 * @param UUID $uuid
	 * @return AbstractRevisionable
	 */
	public static function newFromRevisionId( UUID $uuid ) {
		// create bogus object to access getStorage()
		$object = new static( UUID::create() );
		$revision = $object->getStorage()->get( $uuid );
		return static::newFromRevision( $revision );
	}

	/**
	 * @return \Flow\Data\ObjectManager
	 */
	public function getStorage() {
		return $this->storage;
	}

	/**
	 * Returns all revisions.
	 *
	 * @return AbstractRevision
	 */
	public function getAllRevisions() {
		if ( !$this->revisions ) {
			$this->revisions = $this->storage->find(
				array( $this->getIdColumn() => $this->uuid ),
				array( 'sort' => 'rev_id', 'order' => 'DESC' )
			);

			if ( !$this->revisions ) {
				throw new InvalidDataException( 'Revisions for ' . $this->uuid . ' could not be found', 'invalid-revision-id' );
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

		if ( !isset( $this->revisions[$uuid->toHex()] ) ) {
			return null;
		}

		// find requested id, based on given revision
		return $this->revisions[$uuid->toHex()];
	}

	/**
	 * Returns the oldest revision.
	 *
	 * @return AbstractRevision
	 */
	public function getFirstRevision() {
		$revisions = (array) $this->getAllRevisions();
		return array_shift( $revisions );
	}

	/**
	 * Returns the most recent revision.
	 *
	 * @return AbstractRevision
	 */
	public function getLastRevision() {
		$revisions = (array) $this->getAllRevisions();
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
		$current = array_search( $revision->getRevisionId()->getHex(), $ids );
		$next = $current + 1;

		if ( $next >= count( $ids ) ) {
			return null;
		}

		return $this->getRevision( UUID::create( $ids[$next] ) );
	}

	/**
	 * Pass-through to call latest revision's methods.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return $this->getLastRevision()->{$name}( $arguments );
	}

	/**
	 * Pass-through to get latest revision's properties.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		return $this->getLastRevision()->{$name};
	}

	/**
	 * Pass-through to set latest revision's properties.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function __set( $name, $value ) {
		return $this->getLastRevision()->{$name} = $value;
	}
}
