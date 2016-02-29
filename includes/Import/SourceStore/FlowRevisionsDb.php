<?php

namespace Flow\Import\SourceStore;

use DatabaseBase;
use IP;
use Flow\Import\IImportHeader;
use Flow\Import\IImportObject;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Flow\Import\IObjectRevision;
use Flow\Import\IRevisionableObject;
use Flow\Model\UserTuple;
use Flow\Model\UUID;
use MWTimestamp;
use User;

/**
 * Unlike other source stores, this doesn't really "store" anything. This just
 * does a lookup for certain types of objects to the database to figure out if
 * they have already been imported.
 *
 * This is less versatile than other source stores (you can't just throw
 * anything at it, it's tied to a specific schema and throwing new objects at it
 * will prompt changes in here) but it's more reliable (if the source store it
 * lost, it can use the "result" of a previous import)
 */
class FlowRevisionsDb implements SourceStoreInterface {
	/**
	 * @var DatabaseBase
	 */
	protected $dbr;

	/**
	 * @param DatabaseBase $dbr
	 */
	public function __construct( DatabaseBase $dbr ) {
		$this->dbr = $dbr;
	}

	public function setAssociation( UUID $objectId, $importSourceKey ) {
		return '';
	}

	public function getImportedId( IImportObject $object ) {
		if ( $object instanceof IImportHeader ) {
			$conds = array( 'rev_type' => 'header' );
		} elseif ( $object instanceof IImportSummary ) {
			$conds = array( 'rev_type' => 'post-summary' );
		} elseif ( $object instanceof IImportTopic ) {
			$conds = array( 'rev_type' => 'post', 'rev_parent_id' => null );
		} elseif ( $object instanceof IImportPost ) {
			$conds = array( 'rev_type' => 'post', 'rev_parent_id IS NOT NULL' );
		} else {
			throw new Exception( 'Import object of type ' . get_class( $object ) . ' not summported.' );
		}

		$revision = $this->getObjectRevision( $object );
		return $this->getCollectionId( $revision->getTimestamp(), $revision->getAuthor(), $conds );
	}

	public function save() {
	}

	public function rollback() {
	}

	/**
	 * @param string $timestamp
	 * @param string $author
	 * @param array $conds
	 * @return bool|UUID
	 * @throws Exception
	 * @throws \DBUnexpectedError
	 * @throws \Flow\Exception\FlowException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	protected function getCollectionId( $timestamp, $author, array $conds = array() ) {
		$range = $this->getUUIDRange( new MWTimestamp( $timestamp ) );
		$tuple = $this->getUserTuple( $author );

		$field = $this->dbr->selectField(
			array( 'flow_revision' ),
			array( 'rev_type_id' ),
			array(
				'rev_type_id >= ' . $this->dbr->addQuotes( $range[0]->getBinary() ),
				'rev_type_id < ' . $this->dbr->addQuotes( $range[1]->getBinary() ),
			) + $tuple->toArray( 'rev_' ) + $conds,
			__METHOD__
		);

		return $field !== false ? UUID::create( $field ) : false;
	}

	/**
	 * @param IRevisionableObject $object
	 * @return IObjectRevision
	 */
	protected function getObjectRevision( IRevisionableObject $object ) {
		$revisions = $object->getRevisions();
		$revisions->rewind();
		return $revisions->current();
	}

	/**
	 * @param string $name
	 * @return UserTuple
	 * @throws Exception
	 */
	protected function getUserTuple( $name ) {
		$user = $this->getUser( $name );
		if ( $user === false ) {
			throw new Exception( 'Invalid author: ' . $name );
		}
		return UserTuple::newFromUser( $user );
	}

	/**
	 * @param string $name
	 * @return bool|User
	 */
	protected function getUser( $name ) {
		if ( IP::isIPAddress( $name ) ) {
			return User::newFromName( $name, false );
		}

		return User::newFromName( $name );
	}

	/**
	 * Gets the min <= ? < max boundaries for a UUID that has a given
	 * timestamp. Returns an array where [0] = min & [1] is max.
	 *
	 * @param MWTimestamp $timestamp
	 * @return UUID[] [min, max]
	 * @throws \TimestampException
	 */
	protected function getUUIDRange( MWTimestamp $timestamp ) {
		return array(
			UUID::getComparisonUUID( (int) $timestamp->getTimestamp( TS_UNIX ) ),
			UUID::getComparisonUUID( (int) $timestamp->getTimestamp( TS_UNIX ) + 1 ),
		);
	}
}
