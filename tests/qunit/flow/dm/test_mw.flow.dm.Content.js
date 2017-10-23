QUnit.module( 'ext.flow.dm mw.flow.dm.Content' );

/* Tests */

QUnit.test( 'Stores different content representations (formats)', function ( assert ) {
	var content = new mw.flow.dm.Content( {
		content: 'content in default format (wikitext, for instance)',
		format: 'wikitext',
		html: 'content in html format',
		plaintext: 'content in plaintext format',
		someNewFormat: 'content in some new format'
	} );

	assert.equal( content.get( 'html' ), 'content in html format' );
	assert.equal( content.get( 'wikitext' ), 'content in default format (wikitext, for instance)' );
	assert.equal( content.get(), 'content in default format (wikitext, for instance)' );
	assert.equal( content.get( 'unknown format' ), null );

	assert.expect( 4 );
} );

QUnit.test( 'Behaves when empty', function ( assert ) {
	var content = new mw.flow.dm.Content();

	assert.equal( content.get(), null );
	assert.equal( content.get( 'whatever format' ), null );

	assert.expect( 2 );
} );
