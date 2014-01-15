<?php

namespace Flow\Import;

interface ImportSource {
	/**
	 * @return Iterator
	 */
	function getTopics();


	/**
	 * Gets the summary, if any, for a topic.
	 * @return ImportSummary
	 */
	function getTopicSummary( ImportTopic $topic );


	/**
	 * Gets the posts for a topic
	 * @param  ImportTopic $topic
	 * @return Iterator
	 */
	function getTopicPosts( ImportTopic $topic );


	/**
	 * Gets the replies to a post
	 * @param  ImportPost $post
	 * @return Iterator
	 */
	function getPostReplies( ImportPost $post );
}

interface ImportTopic {
	function getSubject();
	function getCreatedTimestamp();
	function getModifiedTimestamp();
	function getCreator();
}

interface ImportPost {
	function getAuthor();
	function getCreatedTimestamp();
	function getModifiedTimestamp();
	function getText();
}

interface ImportSummary {
	function getText();
	function getUser();
}