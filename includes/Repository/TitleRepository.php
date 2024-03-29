<?php

namespace Flow\Repository;

use MediaWiki\Title\Title;

/**
 * Abstraction for calling stateful methods of the title class. Allows
 * replacing them in unit tests.
 */
class TitleRepository {
	public function exists( Title $title ) {
		return $title->exists();
	}
}
