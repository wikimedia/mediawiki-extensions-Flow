<?php

namespace Flow\Serializer;

/**
 * Primary outwards facing code for serialization package.
 *
 * Usage:
 *
 *   $factory = new SerializerFactory( $types );
 *   $serializer = $factory->create( 'revision', new RevisionType )->getSerializer();
 *   ...
 *   $data = $serializer->transform( $row );
 */
class SerializerFactory {
	/**
	 * @var SerializerTypeInterface[]
	 */
	protected $types;

	/**
	 * @param SerializerTypeInterface[]
	 */
	public function __construct( array $types ) {
		$this->types = $types;
	}

	/**
	 * @param SerializerTypeInterface|string $type
	 * @return SerializerTypeInterface
	 * @throws Exception
	 */
	public function resolveType( $type ) {
		if ( $type instanceof SerializerTypeInterface ) {
			return $type;
		}
		if ( isset( $this->types[$type] ) ) {
			return $this->types[$type];
		}
		throw new \Exception( "Expected SerializerTypeInterface, got $type" );
	}

	/**
	 * @var string $name
	 * @var SerializerTypeInterface|string|null $type
	 * @var array $options
	 * @return SerialierBuilder
	 */
	public function create( $name, $type = null, array $options = array() ) {
		if ( $type === null ) {
			$type = 'text';
		}
		$types = array();
		while( $type !== null ) {
			$type = $this->resolveType( $type );
			$types[] = $type;
			$options += $type->getDefaultOptions();
			$type = $type->getParentType( $options );
		}

		$builder = new SerializerBuilder( $this, $name, $options );
		$options = $builder->getOptions();
		// Run the types from the first parent down through the children
		foreach ( array_reverse( $types ) as $type ) {
			$type->buildSerializer( $builder, $options );
		}

		return $builder;
	}
}
