<?php

use Flow\Serializer\Type\RevisionType;

function main() {
	$c = \Flow\Container::getContainer();

	$serializer = $c['serializer.factory']->create( 'revision', new RevisionType )->getSerializer();
	$formatter = $c['formatter.revision'];
	$row = $c['query.post.view']->getSingleViewResult( 's0qke8d5ou6vzdr6' );

	/*
	$res = $serializer->transform( $row );
	array_walk_recursive( $res, function( &$data ) {
		if ( $data instanceof \Message ) {
			$data = $data->text();
		}
	} );
	var_dump( $res );
	die();
	*/

	for ( $i = 0; $i < 3; ++$i ) {
		$rounds[] = number_format( 1000 * bench( $serializer, $row ), 2 );
		//$rounds[] = number_format( 1000 * bench2( $formatter, $row ), 2 );
	}
	// toss the first result due to JIT
	array_shift( $rounds );

	$avg = array_sum( $rounds ) / count( $rounds );
	var_dump( $rounds );
	var_dump( $avg );
}

function bench( $serializer, $row, $times = 50 ) {
	$start = microtime( true );
	for ( $i = $times; $i > 0 ; --$i ) {
		$serializer->transform( $row );
	}
	return ( microtime( true ) - $start ) / $times;
}

function bench2( $formatter , $row, $times = 50 ) {
	$ctx = \RequestContext::getMain();
	$start = microtime( true );
	for ( $i = $times; $i > 0 ; --$i ) {
		$formatter->formatApi( $row, $ctx );
	}
	return ( microtime( true ) - $start ) / $times;
}

require_once __DIR__ . '/../../../../maintenance/commandLine.inc';
main();
