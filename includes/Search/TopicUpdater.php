<?php

namespace Flow\Search;

use Flow\Collection\PostCollection;
use Flow\Container;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use ProfileSection;
use MWTimestamp;

class TopicUpdater extends Updater {
	/**
	 * {@inheritDoc}
	 */
	public function getType() {
		return Connection::TOPIC_TYPE_NAME;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRevisions( array $conditions = array(), array $options = array() ) {
		$dbFactory = Container::get( 'db.factory' );
		$dbr = $dbFactory->getDB( DB_SLAVE );

		// because root posts (= topic titles) don't change when there's a reply
		// we have to query for all revisions (titles + replies) to find all
		// topics that match $conditions (which may have WHERE clauses to find
		// stuff more recent than a certain revision id)
		// @todo: after https://gerrit.wikimedia.org/r/#/c/124644/, we can
		// probably optimize this, since change date will be part of the root
		$rows = $dbr->select(
			array(
				'flow_revision', // revisions to find
				'flow_tree_revision', // resolve to post id
				'flow_tree_node', // resolve to root post (topic title)
				'flow_workflow', // resolve to workflow, to test if in correct wiki/namespace
			),
			array( 'rev_type_id' ),
			$conditions,
			__METHOD__,
			array(
				'ORDER BY' => 'rev_id ASC',
				'GROUP BY' => 'rev_type_id',
			) + $options,
			array(
				'flow_tree_revision' => array(
					'INNER JOIN',
					array( 'tree_rev_id = rev_id' )
				),
				'flow_tree_node' => array(
					'INNER JOIN',
					array(
						'tree_descendant_id = tree_rev_descendant_id',
						// the one with max tree_depth will be root,
						// which will have the matching workflow id
					)
				),
				'flow_workflow' => array(
					'INNER JOIN',
					array( 'workflow_id = tree_ancestor_id' )
				),
			)
		);

		// although we had to query for replies, we only care about the roots
		$roots = array();
		foreach ( $rows as $row ) {
			$collection = PostCollection::newFromId( UUID::create( $row->rev_type_id ) );

			// root post id == workflow id
			$rootId = $collection->getWorkflowId();
			$roots[$rootId->getAlphadecimal()] = $rootId;
		}

		// we need to fetch all data via rootloader because we'll want children
		// to be populated
		$rootPostLoader = Container::get( 'loader.root_post' );
		return $rootPostLoader->getMulti( $roots );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildDocument( /* PostRevision */ $revision, $flags ) {
		/** @var PostRevision $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// @todo: flags?

		$idData = $revision->registerRecursive( array( $this, 'getRevisionData' ), array(), 'search-data' );
		$idTimestamp = $revision->registerRecursive( array( $this, 'getMostRecentTimestamp' ), $revision->getRevisionId()->getTimestampObj(), 'search-timestamp' );

		// get timestamp from the most recent (child) revision
		// @todo: after https://gerrit.wikimedia.org/r/#/c/124644/, we can
		// probably optimize this, since change date will be part of the root
		$timestamp = $revision->getRecursiveResult( $idTimestamp );

		// get content from all child posts in a [post id => [data]] array
		$revisions = $revision->getRecursiveResult( $idData );

		// @todo: summary content

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

		$doc = new \Elastica\Document(
			$revision->getCollectionId()->getAlphadecimal(),
			array(
				'namespace' => $title->getNamespace(),
				'namespace_text' => $title->getPageLanguage()->getFormattedNsText( $title->getNamespace() ),
				'pageid' => $title->getArticleID(),
				'title' => $title->getText(),
				'timestamp' => wfTimestamp( TS_ISO_8601, $timestamp ),
				'revisions' => array_values( $revisions ),
			)
		);

		return $doc;
	}

	/**
	 * Callback function that will recursively be executed on all children of
	 * the revision it was registered on. This will add the revision's content
	 * to the results array, with the post ID as key.
	 *
	 * @param PostRevision $revision
	 * @param array $result
	 * @return array
	 */
	public function getRevisionData( PostRevision $revision, array $result ) {
		// @todo: I assume we will have to use Templating::getContent() here, to make sure we get don't return suppressed content
		// @todo: or will we save raw data and only filter that out when returning, based on the searching user's permissions?
		// @todo: not sure about format to index data in - for now chosing the storage format
		$format = $revision->isFormatted() ? $revision->getContentFormat() : 'wikitext';
		$content = $revision->getContent( $format );
		$content = trim( $content );

		$timestamp = $revision->getRevisionId()->getTimestampObj();

		$result[$revision->getCollectionId()->getAlphadecimal()] = array(
			'id' => $revision->getCollectionId()->getAlphadecimal(),
			'text' => $content,
			'moderation-state' => $revision->getModerationState(),
			'timestamp' => wfTimestamp( TS_ISO_8601, $timestamp ),
		);

		return array( $result, true );
	}

	/**
	 * Callback function that will recursively be executed on all children of
	 * the revision it was registered on. This will return the most recent
	 * timestamp for any child revision.
	 *
	 * @param PostRevision $revision
	 * @param MWTimestamp $result
	 * @return array
	 */
	public function getMostRecentTimestamp( PostRevision $revision, MWTimestamp $result ) {
		$timestamp = $revision->getRevisionId()->getTimestampObj();
		$diff = $timestamp->diff( $result );

		// invert will be 1 if the diff is a negative time period from
		// $timestamp to $result, which means that the new $timestamp is more
		// recent than our current $result
		if ( $diff->invert ) {
			$result = $timestamp;
		}

		return array( $result, true );
	}
}
