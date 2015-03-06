<?php

namespace Flow\Parsoid;

use DOMNode;
use Flow\Model\PostRevision;
use Title;

interface Fixer {
	/**
	 * @param DOMNode $node
	 * @param Title $title
	 */
	public function apply( DOMNode $node, Title $title );

	/**
	 * @return string
	 */
	public function getXPath();
}
