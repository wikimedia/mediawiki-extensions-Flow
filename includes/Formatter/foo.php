<?php

$x = array( 'foo', 'bar', 'baz', 'bang' );
$idx = array_search( 'bang', $x );
var_dump( count( $x ) );
var_dump( $idx );
