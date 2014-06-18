( function ( $ ) {

QUnit.module( 'ext.flow: Flow board', {
	setup: function() {
		this.$container = $( '<form><div class="flow-content-preview"></div></form>' ).appendTo( '#qunit-fixture' );
	}
} );

QUnit.test( 'Preview disappears when cancel form.', 1, function( assert ) {
	mw.flow.FlowBoardComponent.UI.events.interactiveHandlers.cancelForm.call( this.$container, new $.Event( 'click' ) );
	assert.strictEqual( this.$container.find( '.flow-content-preview' ).is( ":visible" ), false,
		'The preview is hidden when we hit cancel.' );
} );


} ( jQuery ) );
