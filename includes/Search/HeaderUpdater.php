<?php

namespace Flow\Search;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\Header;
use Flow\Model\UUID;
use ProfileSection;
use Sanitizer;

class HeaderUpdater extends Updater {
	/**
	 * {@inheritDoc}
	 */
	public function getType() {
		return Connection::HEADER_TYPE_NAME;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRevisions( array $conditions = array(), array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		// get the current (=most recent, =max) revision id for all headers
		$rows = $dbr->select(
			array( 'flow_revision', 'flow_workflow' ),
			array( 'rev_id' => 'MAX(rev_id)' ),
			$conditions,
			__METHOD__,
			array(
				'ORDER BY' => 'rev_id ASC',
				'GROUP BY' => 'rev_type_id',
			) + $options,
			array(
				'flow_workflow' => array(
					'INNER JOIN',
					array( 'workflow_id = rev_type_id' , 'rev_type' => 'header' )
				),
			)
		);

		$uuids = array();
		foreach ( $rows as $row ) {
			$uuids[] = UUID::create( $row->rev_id );
		}

		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );
		return $storage->getStorage( 'Header' )->getMulti( $uuids );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildDocument( /* Header */ $revision ) {
		/** @var Header $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

		// make sure we don't parse text that isn't meant to be parsed (e.g.
		// topic titles are never meant to be parsed from wikitext to html)
		$format = $revision->isFormatted() ? 'html' : 'wikitext';

		$creationTimestamp = $revision->getCollectionId()->getTimestampObj();
		$updateTimestamp = $this->getUpdateTimestamp( $revision );

		// for consistency with topics, headers will also get "revisions",
		// although there's always only 1 revision per document (unlike topics,
		// which may have multiple sub-posts)
		return new \Elastica\Document(
			$revision->getCollectionId()->getAlphadecimal(),
			array(
				'namespace' => $title->getNamespace(),
				'namespace_text' => $title->getPageLanguage()->getFormattedNsText( $title->getNamespace() ),
				'pageid' => $title->getArticleID(),
				'title' => $title->getText(),
				'timestamp' => wfTimestamp( TS_ISO_8601, $creationTimestamp ),
				'update_timestamp' => wfTimestamp( TS_ISO_8601, $updateTimestamp ),
				'revisions' => array(
					'id' => $revision->getCollectionId()->getAlphadecimal(),
					'text' => trim( Sanitizer::stripAllTags( $revision->getContent( $format ) ) ),
					'source_text' => $revision->getContent( 'wikitext' ), // for insource: searches
					'moderation_state' => $revision->getModerationState(), // headers can't (currently) be moderated, so should always be MODERATED_NONE
					'timestamp' => wfTimestamp( TS_ISO_8601, $creationTimestamp ),
					'update_timestamp' => wfTimestamp( TS_ISO_8601, $updateTimestamp ),
					'type' => $revision->getRevisionType(),
				)
			)
		);
	}
}
