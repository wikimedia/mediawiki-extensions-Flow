<?php

namespace Flow\Import\Postprocessing;

use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\LiquidThreadsApi\ImportPost;
use Flow\Import\LiquidThreadsApi\ImportTopic;
use Flow\Model\UUID;
use Title;
use WatchedItem;
use WikiPage;
use WikitextContent;

class LqtRedirector implements Postprocessor {
	public function afterTopicImported( IImportTopic $topic, UUID $newTopicId ) {
		if ( $topic instanceof ImportTopic /* LQT */ ) {
			$this->doRedirect( $topic->getTitle(), $newTopicId );
		}
	}

	function afterPostImported( IImportPost $post, UUID $topicId, UUID $newPostId ) {
		if ( $post instanceof ImportPost /* LQT */ ) {
			$this->doRedirect( $post->getTitle(), $topicId, $newPostId );
		}
	}

	protected function doRedirect( Title $fromTitle, UUID $toTopic, UUID $toPost = null ) {
		$redirectTarget = Title::makeTitleSafe( NS_TOPIC, $toTopic->getAlphaDecimal() );

		if ( $toPost ) {
			$redirectTarget->setFragment( $toPost->getAlphaDecimal() );
		}

		// XXX: Not sure which user this is done by?
		$newContent = new WikiTextContent( "#REDIRECT [[$redirectTarget]]" );
		$page = WikiPage::factory( $fromTitle );
		$summary = wfMessage( 'flow-lqt-redirect-reason' )->plain();
		$page->doEditContent( $newContent, $summary, EDIT_FORCE_BOT );

		WatchedItem::duplicateEntries( $fromTitle, $redirectTarget );
	}
}
