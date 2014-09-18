<?php

namespace Flow\Search;

use Flow\Container;
use Flow\Data\RevisionStorage;
use Flow\DbFactory;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use ProfileSection;
use ResultWrapper;

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
	 * @return ResultWrapper
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

	public function updateRevisions( array $revisions, $shardTimeout, $clientSideTimeout ) {
		$profiler = new ProfileSection( __METHOD__ );

		if ( $clientSideTimeout !== null ) {
			Connection::getSingleton()->setTimeout2( $clientSideTimeout );
		}

		$documents = $this->buildDocumentsForRevisions( $revisions );
		$this->sendDocuments( $documents, $shardTimeout );

		return count( $documents );
	}

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
}
