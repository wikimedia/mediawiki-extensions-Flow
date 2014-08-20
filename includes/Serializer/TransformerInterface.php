<?php

namespace Flow\Serializer;

/**
 * Basic unit of transformation
 */
interface TransformerInterface {
	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function transform( $value );
}
