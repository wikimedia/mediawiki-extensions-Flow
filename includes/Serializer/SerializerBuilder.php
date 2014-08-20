<?php

namespace Flow\Serializer;

/**
 * Adds two levels of nested builders to the SerializerConfigBuilder.
 *
 * children:  Child builders are added with SerializerBuilder::add and recieve
 *            the input transformation data.  This is the primary method for transforming
 *            input data into output data.
 *
 * extend: Extended builders as added with SerilizerBuilder::extend and receive
 * 		   the output of the child transformation as their input. The is primarily
 * 		   used for adding properties that depend on the output of the children.
 */
class SerializerBuilder extends SerializerConfigBuilder {
	/**
	 * @var SerializerFactory $factory
	 */
	protected $factory;


	/**
	 * @var SerializerBuilder[]
	 */
	protected $children = array();

	/**
	 * @var SerializerBuilder[]
	 */
	protected $extend = array();

	/**
	 * @param SerializerFactory $factory
	 * @param string $name
	 * @param array $options
	 */
	public function __construct( SerializerFactory $factory, $name, array $options = array() ) {
		parent::__construct( $name, $options );
		$this->factory = $factory;
	}

	/**
	 * @param string $name
	 * @return SerializerBuilder
	 */
	public function get( $name ) {
		if ( isset( $this->children[$name] ) ) {
			return $this->children[$name];
		}
		if ( isset( $this->extend[$name] ) ) {
			return $this->extend[$name];
		}
		throw new \Exception( "Could not find child $name" );
	}

	/**
	 * @param string $name
	 * @return SerializerBuilder
	 */
	public function remove( $name ) {
		unset( $this->children[$name], $this->extend[$name] );

		return $this;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function has( $name ) {
		return isset( $this->children[$name] )
			|| isset( $this->extend[$name] );
	}

	/**
	 * @param SerializerBuilder|string|null $child
	 * @param SerializerTypeInterface|string|null $type
	 * @param array $options
	 * @return SerializerBuilder
	 */
	public function add( $child, $type = null, array $options = array() ) {
		if ( $child instanceof self ) {
			$this->children[$child->getName()] = $child;
			return $this;
		}

		if ( !is_string( $child ) ) {
			throw new \Exception( 'Expected string or SerializerBuilder' );
		}
		if ( $type !== null && !is_string( $type ) && !$type instanceof SerializerTypeInterface ) {
			throw new \Exception( 'Expected null, string, or SerializerTypeInterface' );
		}

		$this->children[$child] = $this->createBuilder( $child, $type, $options );

		return $this;
	}

	/**
	 * @param SerializerBuilder|string|null $child
	 * @param SerializerTypeInterface|string|null $type
	 * @param array $options
	 * @return SerializerBuilder
	 */
	public function extend( $child, $type = null, array $options = array() ) {
		if ( $child instanceof self ) {
			$this->extend[$child->getName()] = $child;
			return;
		}

		if ( !is_string( $child ) ) {
			throw new \Exception( 'Expected string or SerializerBuilder' );
		}
		if ( $type !== null && !is_string( $type ) && !$type instanceof SerializerTypeInterface ) {
			throw new \Exception( 'Expected null, string, or SerializerTypeInterface' );
		}

		$this->extend[$child] = $this->createBuilder( $child, $type, $options );

		return $this;
	}

	/**
	 * @return TransformerInterface
	 */
	public function getSerializer() {
		$transformers = clone $this->transformers;
		if ( $this->children ) {
			$children = array();
			foreach ( $this->children as $name => $child ) {
				$children[$name] = $child->getSerializer();
			}
			$transformers->insert(
				new Transformer\GroupTransformer( $children ),
				SerializerPriority::CHILDREN
			);
		}
		if ( $this->extend ) {
			$extend = array();
			foreach ( $this->extend as $name => $child ) {
				$extend[$name] = $child->getSerializer();
			}
			$transformers->insert(
				new Transformer\ExtendGroupTransformer( $extend ),
				SerializerPriority::EXTEND
			);
		}
		return new Transformer\SequentialTransformer( $transformers->toArray() );
	}

	/**
	 * @return SerializerBuilder
	 */
	public function createBuilder( $name, $type = null, array $options = array() ) {
		return $this->factory->create( $name, $type, $options );
	}
}
