<?php

namespace Flow\Search;

use Flow\Data\RevisionStorage;
use Flow\DbFactory;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use MWTimestamp;
use ProfileSection;

abstract class Updater {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @param DbFactory $dbFactory
	 */
	public function __construct( DbFactory $dbFactory ) {
		$this->dbFactory = $dbFactory;
	}

	/**
	 * @return string
	 */
	abstract public function getType();

	/**
	 * @param array $conditions
	 * @param array $options
	 * @return AbstractRevision[]
	 */
	abstract public function getRevisions( array $conditions = array(), array $options = array() );

	/**
	 * @param AbstractRevision $revision
	 * @return \Elastica\Document
	 */
	abstract public function buildDocument( /* AbstractRevision */ $revision );

	/**
	 * @param UUID|null $fromId
	 * @param UUID|null $toId
	 * @param int|null $namespace
	 * @return array
	 */
	public function buildQueryConditions( UUID $fromId = null, UUID $toId = null, $namespace = null ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$conditions = array();

		// only find entries in a given range
		if ( $fromId !== null ) {
			$conditions[] = 'rev_id >= ' . $dbr->addQuotes( $fromId->getBinary() );
		}
		if ( $toId !== null ) {
			$conditions[] = 'rev_id <= ' . $dbr->addQuotes( $toId->getBinary() );
		}

		// find only within requested wiki/namespace
		$conditions['workflow_wiki'] = wfWikiId();
		if ( $namespace !== null ) {
			$conditions['workflow_namespace'] = $namespace;
		}

		return $conditions;
	}

	/**
	 * @param AbstractRevision[] $revisions
	 * @return \Elastica\Document[]
	 */
	protected function buildDocumentsForRevisions( array $revisions ) {
		$documents = array();
		foreach ( $revisions as $revision ) {
			try {
				$documents[] = $this->buildDocument( $revision );
			} catch ( FlowException $e ) {
				// just ignore revisions that fail to build document...
			}
		}

		return $documents;
	}

	/**
	 * @param AbstractRevision[] $revisions
	 * @param int $shardTimeout
	 * @param int $clientSideTimeout
	 * @return int
	 */
	public function updateRevisions( array $revisions, $shardTimeout, $clientSideTimeout ) {
		$profiler = new ProfileSection( __METHOD__ );

		if ( $clientSideTimeout !== null ) {
			Connection::getSingleton()->setTimeout2( $clientSideTimeout );
		}

		$documents = $this->buildDocumentsForRevisions( $revisions );
		$this->sendDocuments( $documents, $shardTimeout );

		return count( $documents );
	}

	/**
	 * @param \Elastica\Document[] $documents
	 * @param int $shardTimeout
	 */
	protected function sendDocuments( $documents, $shardTimeout ) {
		if ( count( $documents ) === 0 ) {
			return;
		}

		$profiler = new ProfileSection( __METHOD__ );

		try {
			// addDocuments (notice plural) is the bulk api
			$bulk = new \Elastica\Bulk( Connection::getSingleton()->getClient2() );
			if ( $shardTimeout ) {
				$bulk->setShardTimeout( $shardTimeout );
			}

			$type = Connection::getRevisionType( wfWikiId(), $this->getType() );
			$bulk->setType( $type );
			$bulk->addDocuments( $documents );
			$bulk->send();
		} catch ( \Exception $e ) {
			$documentIds = array_map( function( $doc ) {
				return $doc->getId();
			}, $documents );
			wfWarn( __METHOD__ . ': Failed updating documents (' . implode( ',', $documentIds ) . '): ' . $e->getMessage() );
		}
	}

	/**
	 * @param AbstractRevision $revision
	 * @return MWTimestamp
	 */
	public function getUpdateTimestamp( AbstractRevision $revision ) {
		if ( !$revision instanceof PostRevision ) {
			return $revision->getRevisionId()->getTimestampObj();
		}

		$idTimestamp = $revision->registerRecursive( array( $this, 'getMostRecentTimestamp' ), $revision->getRevisionId()->getTimestampObj(), 'search-timestamp' );

		// get timestamp from the most recent (child) revision
		// (we could get it from workflow's workflow_last_update_timestamp, but
		// then we'd have to fetch Workflow object again and we already have to
		// iterate recursively over all children anyway...)
		return $revision->getRecursiveResult( $idTimestamp );
	}

	/**
	 * Callback function that will recursively be executed on all children of
	 * the revision it was registered on. This will return the most recent
	 * timestamp for any child revision.
	 *
	 * @param PostRevision $revision
	 * @param MWTimestamp $result
	 * @return array
	 */
	protected function getMostRecentTimestamp( PostRevision $revision, MWTimestamp $result ) {
		$timestamp = $revision->getRevisionId()->getTimestampObj();
		$diff = $timestamp->diff( $result );

		// invert will be 1 if the diff is a negative time period from
		// $timestamp to $result, which means that the new $timestamp is more
		// recent than our current $result
		if ( $diff->invert ) {
			$result = $timestamp;
		}

		return array( $result, true );
	}
}
