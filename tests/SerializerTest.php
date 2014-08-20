<?php

namespace Flow\Tests;

use Flow\Serializer\SerializerConfigBuilder;
use Flow\Serializer\SerializerFactory;
use Flow\Serializer\SerializerPriority;
use Flow\Serializer\Transformer\SequentialTransformer;
use Flow\Serializer\Type\PropertyType;

class SerializerTest extends \MediaWikiTestCase {

	public function configTransformationOrderProvider() {
		$tests = array(
			array(
				'Something something pre-ordered',
				// expected result
				'foobarbazzzz',
				// commands
				array(
					'foo' => SerializerPriority::PRE_CHILDREN,
					'bar' => SerializerPriority::CHILDREN,
					'baz' => SerializerPriority::CHILDREN,
					'zzz' => SerializerPriority::LAST,
				),
			),

			array(
				'Something something random order',
				// expected result
				'foobarbazzzz',
				// commands
				array(
					'zzz' => SerializerPriority::LAST,
					'bar' => SerializerPriority::CHILDREN,
					'foo' => SerializerPriority::PRE_CHILDREN,
					'baz' => SerializerPriority::CHILDREN,
				),
			),
		);

		return $tests;
	}


	/**
	 * @dataProvider configTransformationOrderProvider
	 */
	public function testConfigTransformationOrder( $message, $expect, $commands ) {
		$builder = new SerializerConfigBuilder( 'unittest' );
		$gen = function( $x ) {
			return function( $data ) use ( $x ) { return $data .= $x; };
		};
		foreach ( $commands as $val => $priority ) {
			$builder->addTransformer( $gen( $val ), $priority );
		}

		$serializer = new SequentialTransformer( $builder->getTransformers() );
		$this->assertEquals( $expect, $serializer->transform( '' ), $message );
	}

	/**
	 * @dataProvider configTransformationOrderProvider
	 */
	public function testBuilderTransformationOrder( $message, $expect, $commands ) {
		$factory = new SerializerFactory( array( 'text' => new PropertyType ) );
		$builder = $factory->create( 'unittest' );
		$gen = function( $x ) {
			return function( $data ) use ( $x ) { return $data .= $x; };
		};
		foreach ( $commands as $val => $priority ) {
			$builder->addTransformer( $gen( $val ), $priority );
		}
		$this->assertEquals( $expect, $builder->getSerializer()->transform( '' ), $message );
	}

}
