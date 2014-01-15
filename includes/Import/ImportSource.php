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

interface IImportObject {
	/**
	 * Returns an opaque string that uniquely identifies this object.
	 * Should uniquely identify this particular object every time it is imported.
	 * @return string
	 */
	function getObjectKey();
}

interface IImportPost extends IImportObject {
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
	 * @return IImportSummary|null The summary, if any, for a topic
	 */
	function getTopicSummary();
}

interface IImportSummary extends IImportObject {
	/**
	 * @return User The user who created this summary.
	 */
	function getAuthor();

	/**
	 * @return string WikiText
	 */
	function getText();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	function getCreatedTimestamp();
}

class ImportException extends \MWException {
}
