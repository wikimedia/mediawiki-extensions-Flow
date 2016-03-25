( function ( $ ) {
	// Should be refined later to handle different scenarios (block/protect/etc.) explicitly.
	/**
	 * Flow error widget for when the user can not edit/post/etc.
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {boolean} [isProbablyEditable=true] Whether the user probably has the right to
	 *   edit this page.  If true, they may be able to post.  If false, they can not.
	 *   For performance reasons to avoid pre-computing with 100% accuracy.
	 */
	mw.flow.ui.CanNotEditWidget = function mwFlowUiCanNotEditWidget( config ) {
		var labelHtml, isProbablyEditable;

		config = config || {};

		if ( config.isProbablyEditable !== undefined ) {
			isProbablyEditable = config.isProbablyEditable;
		} else {
			isProbablyEditable = true;
		}

		// Parent constructor
		mw.flow.ui.CanNotEditWidget.parent.call( this, config );

		this.label = new OO.ui.LabelWidget();

		if ( !isProbablyEditable ) {
			// 'blocked' is never triggered by the quick check, so that is not
			// mentioned in the message.  So it's mainly 'protected', but could also be
			// lack of 'createtalk', etc.
			labelHtml = mw.message( 'flow-error-can-not-edit' ).parse();
			this.label.setLabel( $( $.parseHTML( labelHtml ) ) );
		}

		// Initialize
		this.$element
			.append(
				this.label.$element
			)
			.addClass( 'flow-ui-canNotEditWidget' )
			.toggleClass( 'flow-ui-canNotEditWidget-active', !isProbablyEditable );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.CanNotEditWidget, OO.ui.Widget );

}( jQuery ) );
