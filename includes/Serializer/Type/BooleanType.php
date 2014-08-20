<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;

/**
 *
 */
class BooleanType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$builder->addTransformer( function( $data ) {
			return (bool)$data;
		} );
	}
}
