<?php

namespace Flow\Search;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\Header;
use Flow\Model\UUID;
use Sanitizer;

class HeaderUpdater extends Updater {
	/**
	 * {@inheritDoc}
	 */
	public function getTypeName() {
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

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

		$format = 'html';

		$creationTimestamp = $revision->getCollectionId()->getTimestampObj();
		$updateTimestamp = $revision->getRevisionId()->getTimestampObj();

		$revisions = array();
		if ( $this->permissions->isAllowed( $revision, 'view' ) ) {
			$revisions[] = array(
				'id' => $revision->getCollectionId()->getAlphadecimal(),
				'text' => trim( Sanitizer::stripAllTags( $revision->getContent( $format ) ) ),
				'source_text' => $revision->getContent( 'wikitext' ), // for insource: searches
				'moderation_state' => $revision->getModerationState(), // headers can't (currently) be moderated, so should always be MODERATED_NONE
				'timestamp' => $creationTimestamp->getTimestamp( TS_ISO_8601 ),
				'update_timestamp' => $updateTimestamp->getTimestamp( TS_ISO_8601 ),
				'type' => $revision->getRevisionType(),
			);
		}

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
				'timestamp' => $creationTimestamp->getTimestamp( TS_ISO_8601 ),
				'update_timestamp' => $updateTimestamp->getTimestamp( TS_ISO_8601 ),
				'revisions' => $revisions,
			)
		);
	}
}
