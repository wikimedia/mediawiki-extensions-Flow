<?php

namespace Flow\Serializer\Transformer;

use Flow\Serializer\TransformerInterface;

/**
 * Traverses a provided path against a source object or array.
 *
 * Example:
 *
 * 	path: foo.bar.baz
 * 	result: $data->getFoo()->bar['baz']
 *
 * This class will fetch nested data from objects, prefering getters, then properties,
 * and arrays. Unfound data is returned as null.
 */
class PropertyPathTransformer implements TransformerInterface {
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @param string $path
	 */
	public function __construct( $path ) {
		$this->path = explode( '.', $path );
	}

	/**
	 * {@inheritDoc}
	 */
	public function transform( $data ) {
		$origData = $data;
		foreach ( $this->path as $piece ) {
			if ( is_object( $data ) ) {
				$method = 'get' . ucfirst( $piece );
				if ( method_exists( $data, $method ) ) {
					$data = $data->$method();
				} elseif ( property_exists( $data, $piece ) ) {
					$data = $data->$piece;
				} else {
					return null;
				}
			} elseif ( is_array( $data ) ) {
				if ( !isset( $data[$piece] ) ) {
					return null;
				}
				$data = $data[$piece];
			} elseif ( $data === null ) {
				return null;
			} else {
				throw new \Exception( "Invalid property path element `$piece` from `" . implode( '.', $this->path ) .'`' );
			}
		}

		return $data;
	}
}
