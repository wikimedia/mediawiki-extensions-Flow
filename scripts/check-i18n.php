<?php

$en = json_decode( file_get_contents( __DIR__ . '/../i18n/en.json' ), true );
if ( json_last_error() != JSON_ERROR_NONE ) {
	echo "Failed decoding english i18n: ", json_last_error_msg(), "\n";
	exit( 1 );
}

$qqq = json_decode( file_get_contents( __DIR__ . '/../i18n/qqq.json' ), true );
if ( json_last_error() !== JSON_ERROR_NONE ) {
	echo "Failed decoding qqq i18n: ", json_last_error_msg(), "\n";
	exit( 1 );
}

$missing = array_diff( array_keys( $en ), array_keys( $qqq ) );
if ( $missing ) {
	echo "i18n messages missing qqq:\n\t", implode( "\n\t", $missing ), "\n";
	exit( 1 );
}

$extra = array_diff( array_keys( $qqq ), array_keys( $en ) );
if ( $extra ) {
	echo "Extra qqq messages:\n\t", implode( "\n\t", $extra ), "\n";
	exit( 1 );
}
