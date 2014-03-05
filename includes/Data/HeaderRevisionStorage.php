<?php

namespace Flow\Data;

use Flow\Exception\DataModelException;

class HeaderRevisionStorage extends RevisionStorage {
	protected function getRevType() {
		return 'header';
	}
}
