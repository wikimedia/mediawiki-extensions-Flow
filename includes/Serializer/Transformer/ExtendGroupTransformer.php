<?php

namespace Flow\Serializer\Transformer;

use Closure;

/**
 * Extends a single data point with additional
 * data from each provided transformer
 */
class ExtendGroupTransformer extends AbstractTransformer {
	/**
	 * {@inheritDoc}
	 */
	public function transform( $data ) {
		foreach ( $this->transformers as $name => $transformer ) {
			if ( $transformer instanceof Closure ) {
				$data[$name] = $transformer( $data );
			} else {
				$data[$name] = $transformer->transform( $data );
			}
		}
		return $data;
	}
}
