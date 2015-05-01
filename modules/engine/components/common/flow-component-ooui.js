/*!
 * Contains flow-menu functionality.
 */

( function ( $, mw ) {
	/**
	 * Initializes and handles the ooui components
	 *
	 * @this FlowComponentOoui
	 * @constructor
	 */
	mw.flow.ooui = function FlowComponentOoui( config ) {
		config = config || {};

		this.widgets = {};

		// New topic save button
		this.widgets['flow-newtopic-save'] = OO.ui.infuse( 'flow-newtopic-save' );
		this.widgets['flow-newtopic-save'].connect( this, { click: this.onNewTopicSaveButtonClick } );
	};
	OO.initClass( mw.flow.ooui );

	mw.flow.ooui.prototype.onNewTopicSaveButtonClick = function () {
		console.log( 'onNewTopicSaveButtonClick' );
		console.log( this.widgets['flow-newtopic-save'].getData() );
		return false;
	};

}( jQuery, mediaWiki ) );
