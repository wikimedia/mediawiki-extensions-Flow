<?php

namespace Flow\Import\Postprocessor;

use Flow\Model\UUID;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;

interface Postprocessor {
	function afterTopicImported( IImportTopic $topic, UUID $newTopicId );
	function afterPostImported( IImportPost $post, UUID $topicId, UUID $newPostId );
	function afterTalkpageImported();
	function talkpageImportAborted();
}
