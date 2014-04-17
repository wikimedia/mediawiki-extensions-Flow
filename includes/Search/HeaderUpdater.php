<?php

namespace Flow\Search;

use Flow\Model\Header;
use ProfileSection;

class HeaderUpdater extends RevisionUpdater {
	/**
	 * @param Header $revisions
	 * @param array $flags
	 * @return \Elastica\Document
	 */
	public function buildDocument( /* Header */ $revision, $flags ) {
		/** @var Header $revision */

		$profiler = new ProfileSection( __METHOD__ );

		// @todo: flags?

		// get article title associated with this revision
		$title = $revision->getCollection()->getWorkflow()->getArticleTitle();

		return new \Elastica\Document(
			$revision->getCollectionId(),
			array(
				// @todo: index namespace? probably - have yet to figure that out ;)
				'namespace' => $title->getNamespace(),
				'namespace_text' => $title->getPageLanguage()->getFormattedNsText( $title->getNamespace() ),
				// headers have no title
				'timestamp' => wfTimestamp( TS_ISO_8601, $revision->getRevisionId()->getTimestampObj() ),
				// @todo: I assume we will have to use Templating::getContent() here, to make sure we get don't return suppressed content
				'content' => $revision->getContent( /* @todo: what format? */ ), // @todo: I guess we can/should strip html tags?
			)
		);
	}
}
