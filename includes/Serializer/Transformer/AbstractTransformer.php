<?php

namespace Flow\Serializer\Transformer;

use Flow\Serializer\TransformerInterface;
use Closure;

/**
 * Transform a single data point with an ordered sequence
 * of transformations.
 */
abstract class AbstractTransformer implements TransformerInterface {
	/**
	 * @var Closure|TransformerInterface[]
	 */
	protected $transformers;

	/**
	 * @param Closure|TransformerInterface[] $transformers
	 */
	public function __construct( array $transformers ) {
		$this->transformers = $transformers;
	}
}
