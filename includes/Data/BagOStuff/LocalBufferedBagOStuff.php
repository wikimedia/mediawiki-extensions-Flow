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

	protected function doGet( $key, $flags = 0 ) {
		$value = parent::doGet( $key, $flags );
		$this->bag[$key] = array( $value, 0 );
		return $value;
	}

	public function getMulti( array $keys, $flags = 0 ) {
		$values = parent::getMulti( $keys );

		foreach ( $values as $key => $value ) {
			$this->bag[$key] = array( $value, 0 );
		}

		return $values;
	}

	protected function clearLocal() {
		// contrary to BufferedBagOStuff, don't clear $this->bag
		//
		// TODO: Reconsider this, or at least bound $this->bag somehow?  This
		// *might* be acceptable for web requests, but $this->bag growing
		// unbounded can easily cause problems for batch.
		$this->buffer = array();
		$this->committed = array();
	}
}
