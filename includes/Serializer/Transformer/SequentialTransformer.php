<?php

namespace Flow\Serializer\Transformer;

use Closure;

/**
 * Transform a single data point with an ordered sequence
 * of transformations.
 */
class SequentialTransformer extends AbstractTransformer {
	/**
	 * @param array $data
	 * @return array
	 */
	public function transform( $data ) {
		foreach ( $this->transformers as $transformer ) {
			if ( $transformer instanceof Closure ) {
				$data = $transformer( $data );
			} else {
				$data = $transformer->transform( $data );
			}
		}
		return $data;
	}
}
