( function ( $ ) {
QUnit.module( 'ext.flow: Handlebars helpers', {
	setup: function() {
		var stub = this.sandbox.stub( mw.mantle.template, 'get' ),
			stubUser;

		stub.withArgs( 'foo.handlebars' ).returns ( {
			render: function( data ) {
				return data && data.val ? '<div>Magic.</div>' : 'Stubbed.';
			}
		} );
		this.handlebarsProto = mw.flow.FlowHandlebars.prototype;
		this.handlebarsProto._qunit_helper_test = function( a, b ) {
			return a + b;
		};

		// Stub user
		stubUser = this.sandbox.stub( mw.user, 'isAnon' );
		stubUser.onCall( 0 ).returns( true );
		stubUser.onCall( 1 ).returns( false );
		this.opts = {
			fn: function() {
				return 'ok';
			},
			inverse: function() {
				return 'nope';
			}
		};
	}
} );

QUnit.test( 'Handlebars.prototype.processTemplate', 1, function( assert ) {
	assert.strictEqual( this.handlebarsProto.processTemplate( 'foo', { val: 'Hello' } ),
		'<div>Magic.</div>', 'Getting a template works.' );
} );

QUnit.test( 'Handlebars.prototype.processTemplateGetFragment', 1, function( assert ) {
	assert.strictEqual( this.handlebarsProto.processTemplateGetFragment( 'foo', { val: 'Hello' } ).childNodes.length,
		1, 'Return a fragment with the div child node' );
} );

QUnit.test( 'Handlebars.prototype.getTemplate', 2, function( assert ) {
	assert.strictEqual( this.handlebarsProto.getTemplate( 'foo' )(), 'Stubbed.', 'Getting a template works.' );
	assert.strictEqual( this.handlebarsProto.getTemplate( 'foo' )(), 'Stubbed.', 'Getting a template from cache works.' );
} );

// Helpers
QUnit.test( 'Handlebars.prototype.callHelper', 1, function( assert ) {
	assert.strictEqual( this.handlebarsProto.callHelper( '_qunit_helper_test', 1, 2 ),
		3, 'Check the helper was called.' );
} );

QUnit.test( 'Handlebars.prototype.eachPost', 3, function( assert ) {
	var ctx = {
		posts: {
			1: [ 300 ],
			// Purposely points to a missing revision to deal with edge case
			2: [ 500 ]
		},
		revisions: {
			300: { content: 'a' }
		}
	};

	assert.deepEqual( this.handlebarsProto.eachPost( ctx, 1, {} ), { content: 'a' }, 'Matches given id.' );
	assert.deepEqual( this.handlebarsProto.eachPost( ctx, 1, this.opts ), 'ok', 'Runs fn when given.' );
	assert.deepEqual( this.handlebarsProto.eachPost( ctx, 2, {} ), { content: null }, 'Missing revision id.' );
} );

QUnit.test( 'Handlebars.prototype.ifEquals', 2, function( assert ) {
	assert.strictEqual( this.handlebarsProto.ifEquals( 'foo', 'bar', this.opts ), 'nope', 'not equal' );
	assert.strictEqual( this.handlebarsProto.ifEquals( 'foo', 'foo', this.opts ), 'ok', 'equal' );
} );

QUnit.test( 'Handlebars.prototype.ifCond', 4, function() {
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( true, 'or', false, this.opts ), 'ok', 'true || false' );
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( true, 'or', true, this.opts ), 'ok', 'true || true' );
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( false, 'or', false, this.opts ), 'nope', 'false || false' );
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( false, 'monkeypunch', this.opts ), '', 'Unknown operator' );
} );

QUnit.test( 'Handlebars.prototype.ifAnonymous', 2, function() {
	strictEqual( this.handlebarsProto.ifAnonymous( this.opts ), 'ok', 'User should be anonymous first time.' );
	strictEqual( this.handlebarsProto.ifAnonymous( this.opts ), 'nope', 'User should be logged in on second call.' );
} );

QUnit.test( 'Handlebars.prototype.concat', 2, function() {
	strictEqual( this.handlebarsProto.concat( 'a', 'b', 'c', this.opts ), 'abc', 'Check concat working fine.' );
	strictEqual( this.handlebarsProto.concat( this.opts ), '', 'Without arguments.' );
} );

Array.prototype.slice.call( ['a', 'b', 'c', {}], 0, -1 ).join( '' )

QUnit.test( 'Handlebars.prototype.progressiveEnhancement', 1, function() {
	var opts = $.extend( { hash: { insertionType: 'insert', target: 'abc', sectionId: 'def' } }, this.opts );

	strictEqual(
		this.handlebarsProto.progressiveEnhancement( opts ).string,
		'<scr' + 'ipt' +
			' type="text/x-handlebars-template-progressive-enhancement"' +
			' data-target="' + opts.hash.target +'"' +
			' data-type="' + opts.hash.insertionType + '"' +
			' id="' + opts.hash.sectionId + '">' +
			'ok' +
			'</scr' + 'ipt>',
		'Should output exact replica of script tag.'
	);
} );

} ( jQuery ) );
