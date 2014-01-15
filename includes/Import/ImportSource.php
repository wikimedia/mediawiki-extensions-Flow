<?php

namespace Flow\Import;

interface IImportSource {
	/**
	 * @return Iterator<IImportTopic>
	 */
	function getTopics();


	/**
	 * Gets the summary, if any, for a topic.
	 * @return IImportSummary
	 */
	function getTopicSummary( IImportTopic $topic );


	/**
	 * Gets the posts for a topic
	 * @param  IImportTopic $topic
	 * @return Iterator<IImportPost>
	 */
	function getTopicPosts( IImportTopic $topic );


	/**
	 * Gets the replies to a post
	 * @param  IImportPost $post
	 * @return Iterator<IImportPost>
	 */
	function getPostReplies( IImportPost $post );
}

interface IImportTopic {
	/**
	 * @return string Plain text
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

interface IImportPost {

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
	 * @return string WikiText
	 */
	function getText();
}

interface IImportSummary {

	/**
	 * @return string WikiText
	 */
	function getText();

	/**
	 * @return User The user who created this summary.
	 */
	function getUser();
}

class ImportException extends \MWException {

}
