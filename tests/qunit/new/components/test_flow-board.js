( function ( $ ) {
QUnit.module( 'ext.flow: Flow board' );

QUnit.test( 'Check Flow is running', 1, function() {
	strictEqual( 1, 1, 'Test to see if Flow has a qunit test.' );
} );

QUnit.module( 'ext.flow: FlowBoardComponent', {
	setup: function() {
		this.$el = $( '<div class="flow-component" data-flow-component="board">' );
		this.component = mw.flow.initComponent( this.$el );
		this.UI = this.component.constructor.UI;
	}
} );

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.preview', 6, function( assert ) {
	var $container = this.$el,
		$form = $( '<form>' ).appendTo( $container ),
		$input = $( '<input value="HEADING">' ).appendTo( $form ),
		$textarea = $( '<textarea data-flow-preview-template="flow_post">text</textarea>' ).appendTo( $form ),
		$btn = $( '<button name="preview">' ).
			appendTo( $form ),
		info = {
			$target: $textarea,
			status: 'done'
		},
		data = {
			'flow-parsoid-utils': {
				format: 'html',
				content: 'hello'
			}
		};

	this.UI.events.apiHandlers.preview.call( $btn, info, data );

	// check all is well.
	assert.strictEqual( $container.find( '.flow-preview-warning' ).length, 1, 'There is a preview warning.' );
	assert.strictEqual( $textarea.hasClass( 'flow-preview-target-hidden' ), true, 'Textarea is hidden.' );
	assert.strictEqual( $input.hasClass( 'flow-preview-target-hidden' ), true, 'Input is hidden.' );

	// now cancel the form
	this.UI.events.interactiveHandlers.cancelForm.call( $btn, new $.Event() );
	assert.strictEqual( $container.find( '.flow-preview-warning' ).length, 0, 'There is no preview warning.' );
	assert.strictEqual( $textarea.hasClass( 'flow-preview-target-hidden' ), false, 'Textarea is no longer hidden.' );
	assert.strictEqual( $input.hasClass( 'flow-preview-target-hidden' ), false, 'Input is no longer hidden.' );
} );

} ( jQuery ) );
