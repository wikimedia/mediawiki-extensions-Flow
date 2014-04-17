<?php

namespace Flow\Search;

use Flow\Model\AbstractRevision;

abstract class Updater {
	/**
	 * @param AbstractRevision $revision
	 * @param array $flags
	 * @return \Elastica\Document
	 */
	abstract protected function buildDocumentForRevision( /* AbstractRevision */ $revision, $flags );

	/**
	 * @param AbstractRevision[] $revisions
	 * @param array $flags
	 * @return \Elastica\Document[]
	 */
	protected function buildDocumentsForRevisions( $revisions, $flags ) {
		// @todo: flags?

		$documents = array();
		foreach ( $revisions as $revision ) {
			$documents[] = $this->buildDocumentForRevision( $revision, $flags );

			// @todo: some more magic - see Cirrus' Updater.php (based on flags, apparently - figure out what they're about)
		}

		return $documents;
	}

	public function updateRevisions( $revisions, $shardTimeout, $clientSideTimeout, $flags ) {
		$profiler = new ProfileSection( __METHOD__ );

		if ( $clientSideTimeout !== null ) {
			Connection::setTimeout( $clientSideTimeout );
		}

		OtherIndexJob::queueIfRequired( $this->pagesToTitles( $pages ), true );

		// @todo: other
		$allDocuments = array_fill_keys( Connection::getAllIndexTypes(), array() );
		foreach ( $this->buildDocumentsForPages( $pages, $flags ) as $document ) {
			$suffix = Connection::getIndexSuffixForNamespace( $document->get( 'namespace' ) );
			$allDocuments[$suffix][] = $document;
		}
		$count = 0;
		foreach( $allDocuments as $indexType => $documents ) {
			$this->sendDocuments( $indexType, $documents, $shardTimeout );
			$count += count( $documents );
		}

		return $count;
	}
}
