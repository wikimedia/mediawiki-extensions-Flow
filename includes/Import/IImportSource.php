<?php

namespace Flow\Import;

use Iterator;

interface IImportSource {
	/**
	 * @return Iterator<IImportTopic>
	 */
	function getTopics();

	/**
	 * @return IImportHeader|null
	 */
	function getHeader();
}
