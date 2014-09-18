<?php

namespace Flow\Search;

use Flow\Collection\PostSummaryCollection;
use Flow\DbFactory;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Repository\RootPostLoader;
use ProfileSection;
use ResultWrapper;
use Sanitizer;

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
	 * A topic workflow is updated with a workflow_last_update_timestamp for
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
		$workflows = $this->getWorkflows( $conditions, $options );
		return $this->getRoots( $workflows );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildDocument( /* PostRevision */ $revision ) {
		/** @var PostRevision $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// get timestamp from the most recent revision
		$updateTimestamp = $revision->getCollection()->getWorkflow()->getLastModifiedObj();
		// timestamp for initial topic post
		$creationTimestamp = $revision->getCollectionId()->getTimestampObj();

		// get content from all child posts in a [post id => [data]] array
		$revisions = $this->getRevisionsData( $revision );

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
				'timestamp' => $creationTimestamp->getTimestamp( TS_ISO_8601 ),
				'update_timestamp' => $updateTimestamp->getTimestamp( TS_ISO_8601 ),
				'revisions' => $revisions,
			)
		);

		return $doc;
	}

	/**
	 * @param array $conditions
	 * @param array $options
	 * @return bool|ResultWrapper
	 */
	public function getWorkflows( array $conditions = array(), array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		return $dbr->select(
			array( 'flow_workflow' ),
			// for root post (topic title), workflow_id is the same as its rev_type_id
			array( 'workflow_id', 'workflow_last_update_timestamp' ),
			array(
				'workflow_type' => 'topic'
			) + $conditions,
			__METHOD__,
			array(
				'ORDER BY' => 'workflow_last_update_timestamp ASC',
			) + $options
		);
	}

	/**
	 * @param ResultWrapper $workflows
	 * @return PostRevision[]
	 */
	public function getRoots( ResultWrapper $workflows ) {
		$roots = array();
		foreach ( $workflows as $row ) {
			$roots[$row->workflow_id] = UUID::create( $row->workflow_id );
		}

		// we need to fetch all data via rootloader because we'll want children
		// to be populated
		return $this->rootPostLoader->getMulti( $roots );
	}

	/**
	 * Recursively get the data for all children. This will add the revision's
	 * content to the results array, with the post ID as key.
	 *
	 * @param PostRevision|PostSummary $revision
	 * @return array
	 */
	public function getRevisionsData( /* PostRevision|PostSummary */ $revision ) {
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

		$alpha = $revision->getCollectionId()->getAlphadecimal();
		$data[$alpha] = array(
			'id' => $alpha,
			'text' => trim( Sanitizer::stripAllTags( $revision->getContent( $format ) ) ),
			'source_text' => $revision->getContent( 'wikitext' ), // for insource: searches
			'moderation_state' => $revision->getModerationState(),
			'timestamp' => $revision->getCollectionId()->getTimestamp( TS_ISO_8601 ),
			'update_timestamp' => $revision->getRevisionId()->getTimestamp( TS_ISO_8601 ),
			'type' => $type,
		);

		// get data from all child posts too
		foreach ( $revision->getChildren() as $child ) {
			$data += $this->getRevisionsData( $child );
		}

		return $data;
	}
}
