<?php

namespace Flow\Search;

use Flow\Model\PostRevision;
use ProfileSection;
use MWTimestamp;

class TopicUpdater extends RevisionUpdater {
	/**
	 * @param PostRevision $revision
	 * @param array $flags
	 * @return \Elastica\Document
	 */
	public function buildDocument( /* PostRevision */ $revision, $flags ) {
		/** @var PostRevision $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// @todo: flags?

		$idContent = $revision->registerRecursive( array( $this, 'getRevisionContent' ), array(), 'search-content' );
		$idTimestamp = $revision->registerRecursive( array( $this, 'getMostRecentTimestamp' ), $revision->getRevisionId()->getTimestampObj(), 'search-timestamp' );

		// get timestamp from the most recent (child) revision
		// @todo: I think we may soon (or already?) have this denormalized on topic level, for sorting-by-recent-activity purposes
		$timestamp = $revision->getRecursiveResult( $idTimestamp );

		// get content from all child posts in a [post id => post content] array
		$content = $revision->getRecursiveResult( $idContent );

		// exclude topic title from content (it'll be indexed separately as "title")
		unset( $content[$revision->getCollectionId()->getAlphadecimal()] );

		// glue content from all child posts together
		$content = implode( ' ', $content );
		$content = trim( $content );

		// @todo: summary content

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

		$doc = new \Elastica\Document(
			$revision->getCollectionId(),
			array(
				'namespace' => $title->getNamespace(),
				'namespace_text' => $title->getPageLanguage()->getFormattedNsText( $title->getNamespace() ),
				'pageid' => $title->getArticleID(),
				'title' => $title->getText(),
				'topic' => $revision->getContent( 'wikitext' ), // topic title doesn't contain parseable text
				'timestamp' => wfTimestamp( TS_ISO_8601, $timestamp ), // @todo: this should probably be the most recent timestamp, not the
				'content' => $content, // @todo: I guess we can/should strip html tags?
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
	public function getRevisionContent( PostRevision $revision, array $result ) {
		// @todo: I assume we will have to use Templating::getContent() here, to make sure we get don't return suppressed content
		$result[$revision->getCollectionId()->getAlphadecimal()] = $revision->getContent( /* @todo: what format? */ );

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
