<?php
namespace Flow\Data;

/**
 * Generic storage implementation for PostSummary instances
 */
class PostSummaryRevisionStorage extends RevisionStorage {
	protected function getRevType() {
		return 'post-summary';
	}
}
