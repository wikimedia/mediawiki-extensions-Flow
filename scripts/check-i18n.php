<?php

$repo = realpath( __DIR__ . '/..' );

include __DIR__ . "/../Flow.i18n.php";

$missing = array_diff( array_keys( $messages['en'] ), array_keys( $messages['qqq'] ) );
if ( $missing ) {
	echo "Missing i18n messages:\n\t", implode( "\n\t", $missing ), "\n";
	exit( 1 );
}

$extra = array_diff( array_keys( $messages['qqq'] ), array_keys( $messages['en'] ) );
if ( $extra ) {
	echo "Extra qqq messages:\n\t", implode( "\n\t", $extra ), "\n";
	exit( 1 );
}
