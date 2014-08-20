<?php

namespace Flow\Serializer;

/**
 * Serializer types are a composable method of defining a serializer.
 */
interface SerializerTypeInterface {
	/**
	 * @return array
	 */
	function getDefaultOptions();

	/**
	 * @param array $options
	 * @return SerializerTypeInterface|string
	 */
	function getParentType( array $options );

	/**
	 * @param SerializerBuilder $builder
	 * @param array $options
	 */
	function buildSerializer( SerializerBuilder $builder, array $options );
}
