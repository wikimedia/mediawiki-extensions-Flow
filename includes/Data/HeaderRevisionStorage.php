<?php

namespace Flow\Data;

/**
 * Generic storage implementation for Header revision instances
 */
class HeaderRevisionStorage extends RevisionStorage {
	protected function getRevType() {
		return 'header';
	}
}
