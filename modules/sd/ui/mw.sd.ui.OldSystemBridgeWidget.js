( function ( $ ) {
	/**
	 * Old system controller for StructuredDiscussions operations
	 * @class
	 *
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	mw.sd.ui.OldSystemBridgeWidget = function ( $component, config ) {
		config = config || {};

		this.$component = $component;
		this.$board = null;
		this.flowBoard = null;

		// TODO: Utilize a different initialization when we are in edit summary mode
		this.$board = this.$component.find( '.flow-board' );

		// flowBoard emits 'loadmore' and 'refreshTopic'
		this.flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( this.$board );

		// First initialization for the entire component
		mw.flow.initComponent( this.$component );
	};

	/* Inheritance */
	OO.inheritClass( mw.sd.ui.OldSystemBridgeWidget, OO.ui.Widget );

	/**
	 * Reinitialize the board based on a new structure from the template
	 *
	 * @param {jQuery[]} $templateRenderArray A jQuery collection array that
	 *  is the result of the template rendering
	 */
	mw.sd.ui.OldSystemBridgeWidget.prototype.reinitializeFromTemplate = function ( $templateRenderArray ) {
		this.$board = $( $templateRenderArray[ 1 ] );
		this.flowBoard.reinitializeContainer( $templateRenderArray );
	};
}( jQuery ) );
