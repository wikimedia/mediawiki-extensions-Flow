<?php

namespace Flow\Import;

use Iterator;

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
	 * @return string The name of the user who created this summary.
	 */
	function getAuthor();
}

