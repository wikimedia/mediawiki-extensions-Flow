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

} ( jQuery ) );
