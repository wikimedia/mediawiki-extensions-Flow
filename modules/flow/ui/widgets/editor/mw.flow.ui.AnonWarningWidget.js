( function ( $ ) {
	/**
	 * Flow anonymous editor warning widget.
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.AnonWarningWidget = function mwFlowUiEditorControlsWidget( config ) {
		var returnTo, labelHtml;

		config = config || {};

		// Parent constructor
		mw.flow.ui.AnonWarningWidget.parent.call( this, config );

		this.label = new OO.ui.LabelWidget();

		if ( mw.user.isAnon() ) {
			returnTo = {
				returntoquery: encodeURIComponent( window.location.search ),
				returnto: mw.config.get( 'wgPageName' )
			};
			labelHtml = mw.message( 'flow-anon-warning' )
				.params( [
					mw.util.getUrl( 'Special:Userlogin', returnTo ),
					mw.util.getUrl( 'Special:Userlogin/signup', returnTo )
				] )
				.parse();
			this.label.setLabel( $( $.parseHTML( labelHtml ) ) );
		}

		// Initialize
		this.$element
			.append(
				this.label.$element
			)
			.addClass( 'flow-ui-anonWarningWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.AnonWarningWidget, OO.ui.Widget );

}( jQuery ) );
