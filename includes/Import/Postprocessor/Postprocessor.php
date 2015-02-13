<?php

namespace Flow\Import\Postprocessor;

use Flow\Model\UUID;
use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\PageImportState;
use Flow\Import\TopicImportState;

interface Postprocessor {
	/**
	 * Called after the successfull commit of a header. This is
	 * currently called regardless of if any new content was imported.
	 *
	 * @param PageImportState $state
	 * @param IImportHeader $header
	 */
	function afterHeaderImported( PageImportState $state, IImportHeader $header );

	/**
	 * Called after the import of a single post. This has not yet been
	 * commited, and serves to inform the postprocessor about topic
	 * import progress. Only posts that have not been previously
	 * imported are reported here.
	 *
	 * @param TopicImportState $state
	 * @param IImportPost $post
	 * @param UUID $newPostId
	 */
	function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId );

	/**
	 * Called after the successful commit of a topic to the database.
	 * This may or may not have imported any actual posts, it is
	 * called on all topics run through the process regardless.
	 *
	 * @param TopicImportState $state
	 * @param IImportPost $post
	 */
	function afterTopicImported( TopicImportState $state, IImportTopic $topic );

	/**
	 * Callled when there has been an error in the import process.
	 * Any information the postprocessor has received since the last
	 * commit operation should be discarded as it will not be written
	 * to permenant storage.
	 */
	function importAborted();
}
