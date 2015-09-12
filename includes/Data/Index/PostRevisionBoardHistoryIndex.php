<?php

namespace Flow\Data\Index;

use Flow\Model\AbstractRevision;

class PostRevisionBoardHistoryIndex extends BoardHistoryIndex {
	protected function findTopicId( AbstractRevision $post ) {
		return $post->getRootPost()->getPostId();
	}
}
