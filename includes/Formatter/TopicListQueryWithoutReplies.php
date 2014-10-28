<?php

namespace Flow\Formatter;

// Allows getting a list of topics without including the replies.
// This both avoids accessing the tree repository at all, and helps save
// bandwidth.
class TopicListQueryWithoutReplies extends TopicListQuery {
	protected function collectPostIds( array $topicIds) {
		return $topicIds;
	}
}
