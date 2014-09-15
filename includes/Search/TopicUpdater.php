<?php

namespace Flow\Search;

use Flow\Collection\PostSummaryCollection;
use Flow\Data\RootPostLoader;
use Flow\DbFactory;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use ProfileSection;
use Sanitizer;
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
			$conditions[] = 'workflow_last_update_timestamp >= ' . $dbr->addQuotes( $fromId->getTimestamp() );
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

		// find summary for this topic & add it as revision
		$summaryCollection = PostSummaryCollection::newFromId( $revision->getCollectionId() );
		try {
			/** @var PostSummary $summaryRevision */
			$summaryRevision = $summaryCollection->getLastRevision();
			$data = $this->getRevisionData( $summaryRevision, array() );
			$revisions[] = current( $data[0] );
		} catch ( \Exception $e ) {
			// no summary - that's ok!
		}

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
				'revisions' => $revisions,
			)
		);

		return $doc;
	}

	/**
	 * Callback function that will recursively be executed on all children of
	 * the revision it was registered on. This will add the revision's content
	 * to the results array, with the post ID as key.
	 *
	 * @param PostRevision|PostSummary $revision
	 * @param array $result
	 * @return array
	 */
	public function getRevisionData( /* PostRevision|PostSummary */ $revision, array $result ) {
		// make sure we don't parse text that isn't meant to be parsed (e.g.
		// topic titles are never meant to be parsed from wikitext to html)
		$format = $revision->isFormatted() ? 'html' : 'wikitext';

		// store type of revision so we can also search for very specific types
		// (e.g. titles only)
		// possible values will be:
		// * title
		// * post
		// * post-summary
		$type = $revision->getRevisionType();
		if ( method_exists( $revision, 'isTopicTitle' ) && $revision->isTopicTitle() ) {
			$type = 'title';
		}

		$result[] = array(
			'id' => $revision->getCollectionId()->getAlphadecimal(),
			'text' => trim( Sanitizer::stripAllTags( $revision->getContent( $format ) ) ),
			'source_text' => $revision->getContent( 'wikitext' ), // for insource: searches
			'moderation_state' => $revision->getModerationState(),
			'timestamp' => wfTimestamp( TS_ISO_8601, $revision->getRevisionId()->getTimestampObj() ),
			'type' => $type,
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
