<?php

namespace Flow\Import\SourceStore;

use Flow\Import\IImportHeader;
use Flow\Import\IImportObject;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Flow\Import\IObjectRevision;
use Flow\Import\IRevisionableObject;
use Flow\Model\UserTuple;
use Flow\Model\UUID;
use MediaWiki\User\User;
use MediaWiki\Utils\MWTimestamp;
use Wikimedia\IPUtils;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Timestamp\TimestampException;

/**
 * Unlike other source stores, this doesn't really "store" anything. This just
 * does a lookup for certain types of objects to the database to figure out if
 * they have already been imported.
 *
 * This is less versatile than other source stores (you can't just throw
 * anything at it, it's tied to a specific schema and throwing new objects at it
 * will prompt changes in here) but it's more reliable (if the source store is
 * lost, it can use the "result" of a previous import)
 */
class FlowRevisionsDb implements SourceStoreInterface {
	/**
	 * @var IReadableDatabase
	 */
	protected $dbr;

	public function __construct( IReadableDatabase $dbr ) {
		$this->dbr = $dbr;
	}

	public function setAssociation( UUID $objectId, $importSourceKey ) {
		return '';
	}

	public function getImportedId( IImportObject $object ) {
		if ( $object instanceof IImportHeader ) {
			$conds = [ 'rev_type' => 'header' ];
		} elseif ( $object instanceof IImportSummary ) {
			$conds = [ 'rev_type' => 'post-summary' ];
		} elseif ( $object instanceof IImportTopic ) {
			$conds = [ 'rev_type' => 'post', 'tree_parent_id' => null ];
		} elseif ( $object instanceof IImportPost ) {
			$conds = [ 'rev_type' => 'post', $this->dbr->expr( 'tree_parent_id', '!=', null ) ];
		} else {
			throw new Exception( 'Import object of type ' . get_class( $object ) . ' not supported.' );
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
	 * @throws \Wikimedia\Rdbms\DBUnexpectedError
	 * @throws \Flow\Exception\FlowException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	protected function getCollectionId( $timestamp, $author, array $conds = [] ) {
		$range = $this->getUUIDRange( new MWTimestamp( $timestamp ) );
		$tuple = $this->getUserTuple( $author );

		// flow_revision will LEFT JOIN against flow_tree_revision, meaning that
		// we'll also have info about the parent; or it can just be ignored if
		// there is no parent
		$rev_type_id = $this->dbr->newSelectQueryBuilder()
			->select( 'rev_type_id' )
			->from( 'flow_revision' )
			->leftJoin( 'flow_tree_revision', null, 'tree_rev_descendant_id = rev_type_id' )
			->where( [
				$this->dbr->expr( 'rev_type_id', '>=', $range[0]->getBinary() ),
				$this->dbr->expr( 'rev_type_id', '<', $range[1]->getBinary() ),
			] )
			->andWhere( $tuple->toArray( 'rev_user_' ) )
			->andWhere( $conds )
			->caller( __METHOD__ )
			->fetchField();

		if ( $rev_type_id === false ) {
			return false;
		}

		return UUID::create( $rev_type_id );
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
		if ( IPUtils::isIPAddress( $name ) ) {
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
	 * @throws TimestampException
	 */
	protected function getUUIDRange( MWTimestamp $timestamp ) {
		return [
			UUID::getComparisonUUID( (int)$timestamp->getTimestamp( TS_UNIX ) ),
			UUID::getComparisonUUID( (int)$timestamp->getTimestamp( TS_UNIX ) + 1 ),
		];
	}
}
