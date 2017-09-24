<?php

namespace Flow\Import;

interface IImportSource {
	/**
	 * @return Iterator<IImportTopic>
	 */
	function getTopics();

	/**
	 * @return IImportHeader
	 * @throws ImportException
	 */
	function getHeader();
}

interface IImportObject {
	/**
	 * Returns an opaque string that uniquely identifies this object.
	 * Should uniquely identify this particular object every time it is imported.
	 *
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
	 * @return string The name of the user who created this revision.
	 */
	function getAuthor();
}

interface IImportPost extends IRevisionableObject {
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

	/**
	 * @return string The subtype to use when logging topic imports
	 *  to Special:Log.  It will appear in the log as "import/$logType"
	 */
	function getLogType();

	/**
	 * @return string[string] A k/v map of strings containing additional
	 *  parameters to be stored with the log about importing this topic.
	 */
	function getLogParameters();
}

interface IImportHeader extends IRevisionableObject {
}

interface IImportSummary extends IRevisionableObject {
}
