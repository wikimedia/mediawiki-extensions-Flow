<?php

namespace Flow\Parsoid;

use Flow\Model\PostRevision;
use Title;

interface ContentFixer {
	/**
	 * @param string $content
	 * @param Title $title
	 * @return string
	 */
	public function apply( $content, Title $title );

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
