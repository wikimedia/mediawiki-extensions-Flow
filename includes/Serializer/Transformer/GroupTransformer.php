<?php

namespace Flow\Serializer\Transformer;

use Closure;

/**
 * Transformers a single data point into many points,
 * one for each provided transformer
 */
class GroupTransformer extends AbstractTransformer {
	/**
	 * {@inheritDoc}
	 */
	public function transform( $data ) {
		$result = array();
		foreach ( $this->transformers as $name => $transformer ) {
			if ( $transformer instanceof Closure ) {
				$result[$name] = $transformer( $data );
			} else {
				$result[$name] = $transformer->transform( $data );
			}
		}
		return $result;
	}
}
