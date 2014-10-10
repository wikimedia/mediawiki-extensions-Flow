<?php

require_once __DIR__ . '/../../../maintenance/commandLine.inc';

class SlowUuid extends \Flow\Model\UUID {
	public function getTimestampObj() {
		if ( $this->timestamp === null ) {
			if ( $this->alphadecimalValue ) {
				$bits = wfBaseConvert( $this->alphadecimalValue, 36, 2, 88 );
			} else {
				$bits = wfBaseConvert( $this->getHex(), 16, 2, 88 );
			}
			$msTimestamp = wfBaseConvert( substr( $bits, 0, 46 ), 2, 10 );
			$this->timestamp = new MWTimestamp( intval( $msTimestamp / 1000 ) );
		}
		return clone $this->timestamp;
	}
}

function benchmark( $num, $class ) {
	$ids = array();
	for ( ; $num > 0; $num = $num - 10 ) {
		// ids take awhile to create, so clone a few
		$ids[] = $new = $class::create();
		for( $i = 0; $i < 9; ++$i ) {
			$ids[] = clone $new;
		}
	}
	$start = microtime( true );
	foreach ( $ids as $id ) {
		$id->getTimestampObj();
	}

	return microtime( true ) - $start;
}

function main() {
	$time = benchmark( 1000, 'Flow\Model\UUID' );
	// no conversion factor because $time is in s, and /1000 rounds makes ms
	echo "Current implementation took ", $time, "ms per conversion\n";

	$time = benchmark( 1000, 'SlowUUID' );
	echo "Original implementationTook ", $time, "ms per conversion\n";
}

for( $i = 0; $i < 10; ++$i ) {
	main();
	echo "---\n";
}

