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
		var returnTo, labelHtml,
			isAnon = mw.user.isAnon();

		config = config || {};

		// Parent constructor
		mw.flow.ui.AnonWarningWidget.parent.call( this, config );

		this.label = new OO.ui.LabelWidget();

		if ( isAnon ) {
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
			.addClass( 'flow-ui-anonWarningWidget' )
			.toggleClass( 'flow-ui-anonWarningWidget-active', isAnon );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.AnonWarningWidget, OO.ui.Widget );

}( jQuery ) );
