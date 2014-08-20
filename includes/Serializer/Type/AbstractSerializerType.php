<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerTypeInterface;

/**
 * Abstract type reduces boilerplate for implementing simple types.
 */
abstract class AbstractSerializerTypeInterface implements SerializerTypeInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getParentType( array $options ) {
		return 'text';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array();
	}
}
