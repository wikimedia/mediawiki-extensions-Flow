<?php

namespace Flow\Data\BagOStuff;

/**
 * Handles duplicate requests for the same data by keeping them in memory for
 * the rest of this request.
 */
class LocalBufferedBagOStuff extends BufferedBagOStuff {
	/**
	 * Returns true if the data is in own "storage" already, or false if it will
	 * need to be fetched from external cache.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has( $key ) {
		return array_key_exists( $key, $this->bag );
	}

	/**
	 * @param string $key
	 * @param null $casToken
	 * @return bool|mixed
	 */
	public function get( $key, &$casToken = null ) {
		$value = parent::get( $key, $casToken );
		$this->bag[$key] = array( $value, 0 );
		return $value;
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	public function getMulti( array $keys ) {
		$values = parent::getMulti( $keys );

		foreach ( $values as $key => $value ) {
			$this->bag[$key] = array( $value, 0 );
		}

		return $values;
	}

	protected function clearLocal() {
		// contrary to BufferedBagOStuff, don't clear $this->bag
		$this->buffer = array();
		$this->committed = array();
	}
}
