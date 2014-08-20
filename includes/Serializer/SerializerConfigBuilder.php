<?php

namespace Flow\Serializer;

use Closure;

/**
 * Configuration for a serializer.  Holds name, options and
 * a prioritized list of transformations to perform.
 */
class SerializerConfigBuilder {
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var PriorityQueue<Closure|TransformerInterface>
	 */
	protected $transformers;

	/**
	 * @param string $name
	 * @param array $options
	 */
	public function __construct( $name, array $options = array() ) {
		$this->name = $name;
		$this->options = $options;
		$this->transformers = new PriorityQueue;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return Closure|TransformerInterface[]
	 */
	public function getTransformers() {
		return $this->transformers->toArray();
	}

	/**
	 * @var Closure|TransformerInterace $transformer
	 * @var integer $priority Higher numbers are run first
	 * @return self
	 */
	public function addTransformer( $transformer, $priority = SerializerPriority::STANDARD ) {
		if (
			!$transformer instanceof Closure &&
			!$transformer instanceof TransformerInterface
		) {
			throw new \Exception( 'Expected TransformerInterface or Closure' );
		}

		$this->transformers->insert( $transformer, $priority );

		return $this;
	}
}
