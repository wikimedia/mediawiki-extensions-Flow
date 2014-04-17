<?php

namespace Flow\Search;

use Flow\Container;
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
		$dbFactory = Container::get( 'db.factory' );
		$dbr = $dbFactory->getDB( DB_SLAVE );

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

		return $this->loadRevisions( $rows, 'Header' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildDocument( /* Header */ $revision, $flags ) {
		/** @var Header $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// @todo: flags?

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

		return new \Elastica\Document(
			$revision->getCollectionId()->getAlphadecimal(),
			array(
				'namespace' => $title->getNamespace(),
				'namespace_text' => $title->getPageLanguage()->getFormattedNsText( $title->getNamespace() ),
				'pageid' => $title->getArticleID(),
				'title' => $title->getText(),
				// headers have no title
				'timestamp' => wfTimestamp( TS_ISO_8601, $revision->getRevisionId()->getTimestampObj() ),
				// @todo: I assume we will have to use Templating::getContent() here, to make sure we get don't return suppressed content
				'text' => $revision->getContent( /* @todo: what format? */ ), // @todo: I guess we can/should strip html tags?
			)
		);
	}
}
