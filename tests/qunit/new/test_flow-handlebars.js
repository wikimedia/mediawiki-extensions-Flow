( function ( $ ) {
QUnit.module( 'ext.flow: Handlebars helpers', {
	setup: function() {
		this.handlebarsProto = mw.flow.FlowHandlebars.prototype;

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
