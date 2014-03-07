<?php
namespace Flow\Data;

class PostSummaryRevisionStorage extends RevisionStorage {
	protected function getRevType() {
		return 'post-summary';
	}
}
