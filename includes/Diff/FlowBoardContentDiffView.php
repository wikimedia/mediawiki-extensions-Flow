<?php

namespace Flow\Diff;

use DifferenceEngine;
use MediaWiki\Content\Content;

/**
 * Provides a mechanism for handling diffs of Flow posts without throwing exceptions.
 */
class FlowBoardContentDiffView extends DifferenceEngine {

	/** @inheritDoc */
	public function getDiffBody() {
		return false;
	}

	/** @inheritDoc */
	public function generateContentDiffBody( Content $old, Content $new ) {
		return false;
	}

}
