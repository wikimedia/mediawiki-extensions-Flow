<?php

namespace Flow\Search;

use Flow\Model\AbstractRevision;

abstract class RevisionUpdater {
	/**
	 * @param AbstractRevision $revision
	 * @param array $flags
	 * @return \Elastica\Document
	 */
	abstract public function buildDocument( /* AbstractRevision */ $revision, $flags );
}
