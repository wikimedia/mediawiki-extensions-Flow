<?php

namespace Flow\Parsoid;

use DOMDocument;
use Flow\Model\PostRevision;
use Title;

interface Fixer {
	/**
	 * @param DOMDocument $dom
	 * @param Title $title
	 * @return string
	 */
	public function apply( DOMDocument $dom, Title $title );

	/**
	 * @param PostRevision $post
	 * @param array $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function recursive( PostRevision $post, $result );

	/**
	 * Returns the end-result of the recursive function, allowing the
	 * implementing class to process that.
	 *
	 * @param mixed $result
	 */
	public function resolve( $result );
}
