( function ( $ ) {
QUnit.module( 'ext.flow: Handlebars helpers', {
	setup: function() {
		var stub = this.sandbox.stub( mw.mantle.template, 'get' );
		stub.withArgs( 'foo.handlebars' ).returns ( {
			render: function( data ) {
				return data && data.val ? '<div>Magic.</div>' : 'Stubbed.';
			}
		} );
		this.handlebarsProto = mw.flow.FlowHandlebars.prototype;
		this.handlebarsProto._qunit_helper_test = function( a, b ) {
			return a + b;
		};

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

} ( jQuery ) );
