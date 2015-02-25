<?php

namespace Flow\Import;

use Iterator;

interface IImportPost extends IRevisionableObject {
	/**
	 * @return Iterator<IImportPost>
	 */
	function getReplies();
}
