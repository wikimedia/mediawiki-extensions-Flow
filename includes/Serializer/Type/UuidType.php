<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use Flow\Model\UUID;

/**
 *
 */
class UuidType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array(
			'timestamp' => false,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		if ( $options['timestamp'] ) {
			$builder->addTransformer( function( $data ) use ( $options ) {
				return $data instanceof UUID
					? $data->getTimestampObj()->getTimestamp( $options['timestamp'] )
					: null;
			} );
		} else {
			$builder->addTransformer( function( $data ) {
				return $data instanceof UUID ? $data->getAlphadecimal() : null;
			} );
		}
	}
}
