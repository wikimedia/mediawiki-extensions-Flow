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
	 * @param PostRevision $post
	 * @return bool Return true when the provided post should be
	 *  handled with self::recursive
	 */
	public function isRecursive( PostRevision $post );

	/**
	 * @param DOMNode $node
	 */
	public function recursive( DOMNode $node );

	/**
	 * Run any post-recursive cleanups.
	 */
	public function resolve();
}
