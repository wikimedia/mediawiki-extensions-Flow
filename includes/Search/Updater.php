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
			// @todo: I know the types already, we don't need to fetch their collection to fetch their updater... this is just ugly code that should be refactored ;)
			$collection = $revision->getCollection();
			$updater = $collection->getUpdater();

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

		$count = 0;
		foreach ( $revisions as $type => $revs ) {
			foreach ( $revs as $rev ) {
				var_dump($rev->getContent());
			}
			exit;
			$documents = $this->buildDocumentsForRevisions( $revs, $flags );
			$this->sendDocuments( $type, $documents, $shardTimeout );
			$count += count( $documents );
		}

		return $count;
	}

	protected function sendDocuments( $type, $documents, $shardTimeout ) {
		if ( count( $documents ) === 0 ) {
			return;
		}

		$profiler = new ProfileSection( __METHOD__ );

//		try {
			// addDocuments (notice plural) is the bulk api
			$bulk = new \Elastica\Bulk( Connection::getClient() );
			if ( $shardTimeout ) {
				$bulk->setShardTimeout( $shardTimeout );
			}

			$type = Connection::getRevisionType( wfWikiId(), $type );
			$bulk->setType( $type );
			$bulk->addDocuments( $documents );
			$bulk->send();
//		} catch ( \Exception $e ) {
			// ignore exceptions for now
			// @todo: do I need to address exceptions?
//		}
	}
}
