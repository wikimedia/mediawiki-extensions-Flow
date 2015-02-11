<?php

namespace Flow\Import\Postprocessor;

use Flow\Model\UUID;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\TopicImportState;

interface Postprocessor {
	function afterTopicImported( TopicImportState $state, IImportTopic $topic );
	function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId );
	function afterTalkpageImported();
	function talkpageImportAborted();
}
