<?php

namespace Flow\Import\Postprocessor;

use Article;
use Threads; // LiquidThreads

use Flow\Model\UUID;
use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\PageImportState;
use Flow\Import\TopicImportState;

/**
 * Makes sure LiquidThreads syncs everything relevant after the move.
 */
class LqtMoveUpdater implements Postprocessor {
	public function afterHeaderImported( PageImportState $state, IImportHeader $header ) {
		// not a thing to do, yet
	}

	public function afterPageArchived( $archiveTitle ) {
		// LiquidThreads has its own move handler in its
		// onArticleMoveComplete, but it only updates the first 500 posts
		// immediately.  The rest go to the job queue, but we need them updated
		// immediately.
		Threads::synchroniseArticleData(
			new Article( $archiveTitle, 0 ),
			false /* no limit */
		);
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId ) {
		// not a thing to do, yet
	}

	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		// not a thing to do, yet
	}

	public function importAborted() {
		// not a thing to do, yet
	}
}
