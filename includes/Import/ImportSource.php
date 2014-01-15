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
	 * @return IImportHeader
	 */
	function getHeader();
}

interface IImportObject {
	/**
	 * Returns an opaque string that uniquely identifies this object.
	 * Should uniquely identify this particular object every time it is imported.
	 * @return string
	 */
	function getObjectKey();
}

interface IRevisionableObject extends IImportObject {
	/**
	 * @return Iterator<IObjectRevision>
	 */
	function getRevisions();
}

interface IObjectRevision extends IImportObject {
	/**
	 * @return string Wikitext
	 */
	function getText();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	function getTimestamp();

	/**
	 * @return User The user who created this summary.
	 */
	function getAuthor();
}

interface IImportPost extends IRevisionableObject {
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

interface IImportHeader extends IRevisionableObject {

}

interface IImportSummary extends IRevisionableObject {

}

class ImportException extends \MWException {
}
