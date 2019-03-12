<?php

namespace Flow\Import;

use Iterator;

interface IImportSource {
	/**
	 * @return Iterator<IImportTopic>
	 */
	public function getTopics();

	/**
	 * @return IImportHeader
	 * @throws ImportException
	 */
	public function getHeader();
}

interface IImportObject {
	/**
	 * Returns an opaque string that uniquely identifies this object.
	 * Should uniquely identify this particular object every time it is imported.
	 *
	 * @return string
	 */
	public function getObjectKey();
}

interface IRevisionableObject extends IImportObject {
	/**
	 * @return Iterator<IObjectRevision>
	 */
	public function getRevisions();
}

interface IObjectRevision extends IImportObject {
	/**
	 * @return string Wikitext
	 */
	public function getText();

	/**
	 * @return string Timestamp compatible with wfTimestamp()
	 */
	public function getTimestamp();

	/**
	 * @return string The name of the user who created this revision.
	 */
	public function getAuthor();
}

interface IImportPost extends IRevisionableObject {
	/**
	 * @return Iterator<IImportPost>
	 */
	public function getReplies();
}

interface IImportTopic extends IImportPost {
	/**
	 * @return IImportSummary|null The summary, if any, for a topic
	 */
	public function getTopicSummary();

	/**
	 * @return string The subtype to use when logging topic imports
	 *  to Special:Log.  It will appear in the log as "import/$logType"
	 */
	public function getLogType();

	/**
	 * @return string[] A k/v map of strings containing additional
	 *  parameters to be stored with the log about importing this topic.
	 */
	public function getLogParameters();
}

interface IImportHeader extends IRevisionableObject {
}

interface IImportSummary extends IRevisionableObject {
}
