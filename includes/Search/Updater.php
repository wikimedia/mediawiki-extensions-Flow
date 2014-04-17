<?php

namespace Flow\Search;

use Flow\Container;
use Flow\Data\RevisionStorage;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use ProfileSection;
use ResultWrapper;

abstract class Updater {
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
	 * @param array $flags
	 * @return \Elastica\Document
	 */
	abstract public function buildDocument( /* AbstractRevision */ $revision, $flags );

	/**
	 * Turns DB data into revision objects.
	 *
	 * @param ResultWrapper $rows
	 * @param string $revisionClass Class of revision object to build: PostRevision|Header
	 * @return array
	 */
	protected function loadRevisions( ResultWrapper $rows, $revisionClass ) {
		$revisions = array();
		foreach ( $rows as $row ) {
			$revisions[UUID::create( $row->rev_id )->getAlphadecimal()] = (array) $row;
		}

		// get content in external storage
		$revisions = RevisionStorage::mergeExternalContent( array( $revisions ) );
		$revisions = reset( $revisions );

		// we have all required data to build revision
		$storage = Container::get( 'storage' );
		$mapper = $storage->getStorage( $revisionClass )->getMapper();
		$revisions = array_map( array( $mapper, 'fromStorageRow' ), $revisions );

		return $revisions;
	}

	/**
	 * @param AbstractRevision[] $revisions
	 * @param array $flags
	 * @return \Elastica\Document[]
	 */
	protected function buildDocumentsForRevisions( array $revisions, $flags ) {
		// @todo: flags?

		$documents = array();
		foreach ( $revisions as $revision ) {
			try {
				$documents[] = $this->buildDocument( $revision, $flags );
			} catch ( FlowException $e ) {
				// just ignore revisions that fail to build document...
			}

			// @todo: some more magic - see Cirrus' Updater.php (based on flags, apparently - figure out what they're about)
		}

		return $documents;
	}

	public function updateRevisions( array $revisions, $shardTimeout, $clientSideTimeout, $flags ) {
		$profiler = new ProfileSection( __METHOD__ );

		if ( $clientSideTimeout !== null ) {
			Connection::setTimeout( $clientSideTimeout );
		}

//		OtherIndexJob::queueIfRequired( $this->pagesToTitles( $pages ), true ); // @todo: what's this?

		$documents = $this->buildDocumentsForRevisions( $revisions, $flags );
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
			$bulk = new \Elastica\Bulk( Connection::getClient() );
			if ( $shardTimeout ) {
				$bulk->setShardTimeout( $shardTimeout );
			}

			// @todo: figure out where I'll get $type from

			$type = Connection::getRevisionType( wfWikiId(), $this->getType() );
			$bulk->setType( $type );
			$bulk->addDocuments( $documents );
			$bulk->send();
		} catch ( \Exception $e ) {
			// ignore exceptions for now
			// @todo: do I need to address exceptions?
		}
	}
}
