<?php

namespace Flow\Model;

use Flow\Container;
use Flow\Exception\InvalidDataException;

abstract class AbstractCollection {
	/**
	 * Id of the collection object.
	 *
	 * @var UUID
	 */
	protected $uuid;

	/**
	 * @var \Flow\Data\ObjectManager[]
	 */
	protected $storage;

	/**
	 * Array of revisions for this object.
	 *
	 * @var AbstractRevision[]
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
	 * Returns the id of the workflow this collection is associated with.
	 *
	 * @return UUID
	 */
	abstract public function getWorkflowId();

	/**
	 * Use the static methods to load an object from a given revision.
	 *
	 * @see AbstractCollection::newFromId
	 * @see AbstractCollection::newFromRevision
	 * @see AbstractCollection::newFromRevisionId
	 *
	 * @param UUID $uuid
	 */
	protected function __construct( UUID $uuid ) {
		$this->uuid = $uuid ;
	}

	/**
	 * Instantiate a new object based on its id.
	 *
	 * @param UUID $uuid
	 * @return AbstractCollection
	 */
	public static function newFromId( UUID $uuid ) {
		return new static( $uuid );
	}

	/**
	 * Instantiate a new object based off of an AbstractRevision object.
	 *
	 * @param AbstractRevision $revision
	 * @return AbstractCollection
	 */
	public static function newFromRevision( AbstractRevision $revision ) {
		return static::newFromId( $revision->getCollectionId() );
	}

	/**
	 * @return UUID
	 */
	public function getId() {
		return $this->uuid;
	}

	/**
	 * @param $class[optional] Storage class - defaults to getRevisionClass()
	 * @return \Flow\Data\ObjectManager
	 */
	public function getStorage( $class = null ) {
		if ( !$class ) {
			$class = $this->getRevisionClass();
		}

		if ( !$this->storage[$class] ) {
			$this->storage[$class] = Container::get( 'storage' )->getStorage( $class );
		}

		return $this->storage[$class];
	}

	/**
	 * Returns all revisions.
	 *
	 * @return array Array of AbstractRevision
	 * @throws InvalidDataException When no revisions can be found
	 */
	public function getAllRevisions() {
		if ( !$this->revisions ) {
			$revisions = $this->getStorage()->find(
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
		return array_pop( $revisions );
	}

	/**
	 * Returns the most recent revision.
	 *
	 * @return AbstractRevision
	 */
	public function getLastRevision() {
		$revisions = $this->getAllRevisions();
		return array_shift( $revisions );
	}

	/**
	 * Given a certain revision, returns the previous revision.
	 *
	 * @param AbstractRevision $revision
	 * @return AbstractRevision|null null if there is no previous revision
	 */
	public function getPrevRevision( AbstractRevision $revision ) {
		$previousRevisionId = $revision->getPrevRevisionId();
		if ( !$previousRevisionId ) {
			return null;
		}

		return $this->getRevision( $previousRevisionId );
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
		$next = $current - 1;

		if ( $next < 0 ) {
			return null;
		}

		return $this->getRevision( UUID::create( $ids[$next] ) );
	}

	/**
	 * Returns the Title object this revision is associated with.
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->getWorkflow()->getArticleTitle();
	}

	/**
	 * Returns the workflow object this collection is associated with.
	 *
	 * @return Workflow
	 * @throws InvalidDataException
	 */
	public function getWorkflow() {
		$uuid = $this->getWorkflowId();

		/** @var Workflow $workflow */
		$workflow = $this->getStorage( 'Flow\\Model\\Workflow' )->get( $uuid );
		if ( !$workflow ) {
			throw new InvalidDataException( 'Invalid workflow: ' . $uuid->getAlphadecimal(), 'invalid-workflow' );
		}

		return $workflow;
	}
}
