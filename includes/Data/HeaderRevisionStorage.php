<?php

namespace Flow\Data;

class HeaderRevisionStorage extends RevisionStorage {
	protected function getRevType() {
		return 'header';
	}
}
