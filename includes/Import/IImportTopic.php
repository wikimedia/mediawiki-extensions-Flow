<?php

namespace Flow\Import;

use Iterator;

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
