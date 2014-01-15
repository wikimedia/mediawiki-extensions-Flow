<?php

namespace Flow\Import;

interface ImportSource {
	/**
	 * @return Iterator<ImportTopic>
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
	 * @return Iterator<ImportPost>
	 */
	function getTopicPosts( ImportTopic $topic );


	/**
	 * Gets the replies to a post
	 * @param  ImportPost $post
	 * @return Iterator<ImportPost>
	 */
	function getPostReplies( ImportPost $post );
}

interface ImportTopic {
	/**
	 * @return string
	 */
	function getSubject();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	function getCreatedTimestamp();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	function getModifiedTimestamp();

	/**
	 * @return User
	 */
	function getCreator();
}

interface ImportPost {

	/**
	 * @return User User to whom this post is attributed
	 */
	function getAuthor();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	function getCreatedTimestamp();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	function getModifiedTimestamp();

	/**
	 * @return string
	 */
	function getText();
}

interface ImportSummary {

	/**
	 * @return string
	 */
	function getText();

	/**
	 * @return User The user who created this summary.
	 */
	function getUser();
}