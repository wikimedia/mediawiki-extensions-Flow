( function ( $ ) {
QUnit.module( 'ext.flow: Flow board' );

QUnit.test( 'Check Flow is running', 1, function() {
	strictEqual( 1, 1, 'Test to see if Flow has a qunit test.' );
} );

QUnit.test( 'Check board hash matching', 3, function() {
	var $container = $( '<div>' ).data( 'flow-component', 'board' ),
		board = mw.flow.initComponent( $container ),
		someId = '#flow-post-rxq84rnl3jmv5m0q';

	strictEqual(
		null,
		board.matchHash( '#nothing' ),
		'Unmatched hash returns null'
	);
	deepEqual(
		[ someId , someId, undefined ],
		board.matchHash( someId ),
		'Just the hash returns the hash and undefined'
	);
	deepEqual(
		[ someId + '/newer', someId, 'newer' ],
		board.matchHash( someId + '/newer' ),
		'Hash followed by /newer identifies the provided `newer` option'
	);
} );

} ( jQuery ) );
