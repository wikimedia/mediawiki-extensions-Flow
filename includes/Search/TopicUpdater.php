<?php

namespace Flow\Search;

use Flow\Data\RootPostLoader;
use Flow\DbFactory;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use ProfileSection;
use MWTimestamp;

class TopicUpdater extends Updater {
	/**
	 * @var RootPostLoader
	 */
	protected $rootPostLoader;

	/**
	 * @param DbFactory $dbFactory
	 * @param RootPostLoader $rootPostLoader
	 */
	public function __construct( DbFactory $dbFactory, RootPostLoader $rootPostLoader ) {
		$this->rootPostLoader = $rootPostLoader;
		parent::__construct( $dbFactory );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType() {
		return Connection::TOPIC_TYPE_NAME;
	}

	/**
	 * We'll be querying the workflow table instead of the revisions table.
	 * Because it's possible to request only a couple of revisions (in between
	 * certain ids), we'll need to override the parent buildQueryConditions
	 * method to also work on the workflow table.
	 * A topic workflow is updates with a workflow_last_update_timestamp for
	 * every change made in the topic. Our UUIDs are sequential & time-based,
	 * so we can just query for workflows with a timestamp higher than the
	 * timestamp derived from the starting UUID and lower than the end UUID.
	 *
	 * {@inheritDoc}
	 */
	public function buildQueryConditions( UUID $fromId = null, UUID $toId = null, $namespace = null ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$conditions = array();

		// only find entries in a given range
		if ( $fromId !== null ) {
			$conditions[] = 'workflow_last_update_timestamp > ' . $dbr->addQuotes( $fromId->getTimestamp() );
		}
		if ( $toId !== null ) {
			$conditions[] = 'workflow_last_update_timestamp <= ' . $dbr->addQuotes( $toId->getTimestamp() );
		}

		// find only within requested wiki/namespace
		$conditions['workflow_wiki'] = wfWikiId();
		if ( $namespace !== null ) {
			$conditions['workflow_namespace'] = $namespace;
		}

		return $conditions;
	}

	/**
	 * Instead of querying for revisions (which is what we actually need), we'll
	 * just query the workflow table, which will save us some complicated joins.
	 * The workflow_id for a topic title (aka root post) is the same as its
	 * revision is, so we can pass that to the root post loader and *poof*, we
	 * have our revisions!
	 *
	 * {@inheritDoc}
	 */
	public function getRevisions( array $conditions = array(), array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$rows = $dbr->select(
			array( 'flow_workflow' ),
			// for root post (topic title), workflow_id it's the same as its rev_type_id
			array( 'workflow_id' ),
			array(
				'workflow_type' => 'topic'
			) + $conditions,
			__METHOD__,
			array(
				'ORDER BY' => 'workflow_id ASC',
			) + $options
		);

		$roots = array();
		foreach ( $rows as $row ) {
			$roots[$row->workflow_id] = UUID::create( $row->workflow_id );
		}

		// we need to fetch all data via rootloader because we'll want children
		// to be populated
		return $this->rootPostLoader->getMulti( $roots );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildDocument( /* PostRevision */ $revision ) {
		/** @var PostRevision $revision */

		$profiler = new ProfileSection( __METHOD__ );

		$idData = $revision->registerRecursive( array( $this, 'getRevisionData' ), array(), 'search-data' );
		$idTimestamp = $revision->registerRecursive( array( $this, 'getMostRecentTimestamp' ), $revision->getRevisionId()->getTimestampObj(), 'search-timestamp' );

		// get content from all child posts in a [post id => [data]] array
		$revisions = $revision->getRecursiveResult( $idData );

		// get timestamp from the most recent (child) revision
		// (we could get it from workflow's workflow_last_update_timestamp, but
		// then we'd have to fetch Workflow object again and we already have to
		// iterate recursively over all children anyway...)
		$timestamp = $revision->getRecursiveResult( $idTimestamp );

		// @todo: summary content

		// get board title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getOwnerTitle();

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
		// @todo: I assume we will have to use Templating::getContent() here, to make sure we don't return suppressed content
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
