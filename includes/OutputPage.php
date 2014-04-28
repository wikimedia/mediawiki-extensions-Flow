<?php

namespace Flow;

class OutputPage extends \OutputPage {
	public $appendToOutput;

	protected function appendCallback( $callback ) {
		$this->appendToOutput[] = $callback;
	}

	protected function finalizeAndSend() {

	}

}
