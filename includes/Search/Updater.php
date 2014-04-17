<?php

namespace Flow\Search;

use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use ProfileSection;

class Updater {
	/**
	 * @param AbstractRevision[] $revisions
	 * @param array $flags
	 * @return \Elastica\Document[]
	 */
	protected function buildDocumentsForRevisions( array $revisions, $flags ) {
		// @todo: flags?

		$documents = array();
		foreach ( $revisions as $revision ) {
			// @todo: document are being split up per index type anyways, why am I even accepting them mangled together, only to split them up again later?

			$collection = $revision->getCollection();
			$updater = $collection->getUpdater(); // @todo: should be able to come up with something more graceful that this ;)

			if ( $updater ) {
				try {
					$documents[] = $updater->buildDocument( $revision, $flags );
				} catch ( FlowException $e ) {
					// just ignore revisions that fail to build document...
				}
			}

			// @todo: some more magic - see Cirrus' Updater.php (based on flags, apparently - figure out what they're about)
		}

		return $documents;
	}

	public function updateRevisions( $revisions, $shardTimeout, $clientSideTimeout, $flags ) {
		$profiler = new ProfileSection( __METHOD__ );

		if ( $clientSideTimeout !== null ) {
			Connection::setTimeout( $clientSideTimeout );
		}

//		OtherIndexJob::queueIfRequired( $this->pagesToTitles( $pages ), true ); // @todo: what's this?

		$allDocuments = array();
		foreach ( $this->buildDocumentsForRevisions( $revisions, $flags ) as $document ) {
			// @todo: I have no idea what this suffix is about...
//			$suffix = Connection::getIndexSuffixForNamespace( $document->get( 'namespace' ) );
			$suffix = $document->has( 'title' ) ? Connection::TOPIC_INDEX_TYPE : Connection::HEADER_INDEX_TYPE; // @todo: lame way to detect topic/header, will improve once I figure out what this suffix is about

			$allDocuments[$suffix][] = $document;
		}

		$count = 0;
		foreach( $allDocuments as $indexType => $documents ) {
			$this->sendDocuments( $indexType, $documents, $shardTimeout );
			$count += count( $documents );
		}

		return $count;
	}

	protected function sendDocuments( $indexType, $documents, $shardTimeout ) {
		if ( count( $documents ) === 0 ) {
			return;
		}

		$profiler = new ProfileSection( __METHOD__ );

//		try {
			$revisionType = Connection::getRevisionType( wfWikiId(), $indexType ); // @todo: what's this? does it matter?

			// addDocuments (notice plural) is the bulk api
			$bulk = new \Elastica\Bulk( Connection::getClient() );
			if ( $shardTimeout ) {
				$bulk->setShardTimeout( $shardTimeout );
			}

			$bulk->setType( $revisionType ); // seriously, what's this about?
			$bulk->addDocuments( $documents );
			$bulk->send();
//		} catch ( \Exception $e ) {
			// ignore exceptions for now
			// @todo: do I need to address exceptions?
//		}
	}
}
