<?php

namespace Flow\Search;

use Flow\DbFactory;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use MWExceptionHandler;

abstract class Updater {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

	/**
	 * @param DbFactory $dbFactory
	 * @param RevisionActionPermissions $permissions
	 */
	public function __construct( DbFactory $dbFactory, RevisionActionPermissions $permissions ) {
		$this->dbFactory = $dbFactory;
		$this->permissions = $permissions;
	}

	/**
	 * @return string One of the Connection::*_TYPE_NAME constants
	 */
	abstract public function getTypeName();

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
				wfWarn( __METHOD__ . ': Failed to build document for ' . $revision->getRevisionId()->getAlphadecimal() . ': ' . $e->getMessage());
				MWExceptionHandler::logException( $e );
			}
		}

		return $documents;
	}

	/**
	 * @param AbstractRevision[] $revisions
	 * @param string|null $shardTimeout Timeout in Elasticsearch time format (1m, 15s, ...)
	 * @param int|null $clientSideTimeout
	 * @return int
	 */
	public function updateRevisions( array $revisions, $shardTimeout = null, $clientSideTimeout = null ) {
		if ( $clientSideTimeout !== null ) {
			Connection::getSingleton()->setTimeout2( $clientSideTimeout );
		}

		$documents = $this->buildDocumentsForRevisions( $revisions );
		$this->sendDocuments( $documents, $shardTimeout );

		return count( $documents );
	}

	/**
	 * @param \Elastica\Document[] $documents
	 * @param string|null $shardTimeout Timeout in Elasticsearch time format (1m, 15s, ...)
	 */
	protected function sendDocuments( array $documents, $shardTimeout = null ) {
		if ( count( $documents ) === 0 ) {
			return;
		}

		try {
			// addDocuments (notice plural) is the bulk api
			$bulk = new \Elastica\Bulk( Connection::getSingleton()->getClient2() );
			if ( $shardTimeout !== null ) {
				$bulk->setShardTimeout( $shardTimeout );
			}

			$index = Connection::getFlowIndex( wfWikiId() );
			$type = $index->getType( $this->getTypeName() );
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
