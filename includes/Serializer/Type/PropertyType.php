<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use Flow\Serializer\SerializerPriority;
use Flow\Serializer\SerializerTypeInterface;
use Flow\Serializer\Transformer\PropertyPathTransformer;
use Closure;

/**
 *
 */
class PropertyType implements SerializerTypeInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getParentType( array $options ) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array(
			'path' => false,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		if ( isset( $options['path'] ) ) {
			$path = $options['path'];
		} else {
			$path = $builder->getName();
		}
		if ( $path instanceof Closure ) {
			$builder->addTransformer(
				$path,
				SerializerPriority::PRE_CHILDREN
			);
		} elseif ( $path !== false ) {
			$builder->addTransformer(
				new PropertyPathTransformer( $path ),
				SerializerPriority::PRE_CHILDREN
			);
		}
	}
}
