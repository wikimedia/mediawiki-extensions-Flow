<?php

namespace Flow\Import;

use Iterator;
use User;

interface IImportSource {
	/**
	 * @return Iterator<IImportTopic>
	 */
	function getTopics();

	/**
	 * @todo We probably need headers too eventually
	 *
	 * function getHeader();
	 */
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

	/**
	 * @return Iterator<IImportPost>
	 */
	function getReplies();
}

interface IImportTopic extends IImportPost {
	/**
	 * Gets the summary, if any, for a topic.
	 *
	 * @return IImportSummary|null
	 */
	function getTopicSummary();
}

interface IImportSummary {

	/**
	 * @return string WikiText
	 */
	function getText();

	/**
	 * @return User The user who created this summary.
	 */
	function getAuthor();
}

class ImportException extends \MWException {
}
