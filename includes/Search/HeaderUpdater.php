<?php

namespace Flow\Search;

use Flow\Model\Header;
use ProfileSection;

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

		// @todo: I need to fetch the latest revisions for every rev_type_id

		$rows = $dbr->select(
			array( 'flow_revision', 'flow_workflow' ),
			array( '*' ),
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

		return $this->loadRevisionsFromRow( $rows, 'Header' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildDocument( /* Header */ $revision ) {
		/** @var Header $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

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
				'timestamp' => wfTimestamp( TS_ISO_8601, $revision->getRevisionId()->getTimestampObj() ),
				'revisions' => array(
					'id' => $revision->getCollectionId()->getAlphadecimal(),
					// @todo: I assume we will have to use Templating::getContent() here, to make sure we don't return suppressed content
					'text' => $revision->getContent( /* @todo: what format? */ ), // @todo: I guess we can/should strip html tags?
					'moderation-state' => $revision->getModerationState(), // headers can't (currently) be moderated, so should always be MODERATED_NONE
					'timestamp' => wfTimestamp( TS_ISO_8601, $revision->getRevisionId()->getTimestampObj() ),
				)
			)
		);
	}
}
