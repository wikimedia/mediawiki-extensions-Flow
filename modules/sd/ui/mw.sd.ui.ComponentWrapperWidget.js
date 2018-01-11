( function ( $ ) {
	/**
	 * Wrapper widget for StructuredDiscussion UI
	 * @class
	 *
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	mw.sd.ui.ComponentWrapperWidget = function ( viewModel, controller, config ) {
		config = config || {}:

		// Parent method
		// Note that we send $element as the flow-component, which will be set
		// as this.$element
		mw.sd.ui.ComponentWrapperWidget.parent.call( this, config );

		this.model = viewModel;
		this.controller = controller;

		this.$overlay = config.$overlay || this.$element;
		// TODO: The widget should be the only thing managing the factory and window manager
		// and the sub-widgets (including all dialogs) should use this internally.
		// At the moment, there are still dialogs that use the global mw.flow.windowManager
		// and mw.flow.windowFactory, and those should be removed when the entire widget
		// set is managed from this wrapper widget
		this.windowFactory = config.windowFactory || new OO.Factory();
		this.windowManager = config.windowManager || new OO.ui.WindowManager( {
			factory: this.windowFactory
		} );
		this.$overlay.append( this.windowManager.$element );

		this.bridgeWidget = new mw.sd.ui.OldSystemBridgeWidget( this.$element );
		this.processWidgetsOnBoard();

		this.model.connect( this, {
			reinitializeFromTemplate: 'onModelReinitializeFromTemplate'
		} );

		this.$element
			.addClass( 'mw-sd-ui-componentWrapperWidget' );
	};

	/**
	 * Process the widgets that are attached to the board after it is initialized
	 */
	mw.sd.ui.ComponentWrapperWidget.prototype.processWidgetsOnBoard = function () {
		// Use this.$element.find( ... ) so that we always do this to the updated
		// DOM even after the sub-piece that is the FlowBoard was reinitialized

		// - Attach the navigation widget
		// - Replace reply forms
		// - Set up the 'new topic' widget
		// - stop pending
	};

	/**
	 * Respond to reinitializeFromTemplate event, where the board was rebuilt from
	 * a template and we are now reinserting and replacing the board DOM
	 *
	 * @param  {[type]} $templateRenderArray Template array result
	 */
	mw.sd.ui.ComponentWrapperWidget.prototype.onModelReinitializeFromTemplate = function ( $templateRenderArray ) {
		this.bridgeWidget.reinitializeFromTemplate( $templateRenderArray );
		this.processWidgetsOnBoard();
	};

	/**
	 * Set the pending state of the component
	 *
	 * @param {boolean} isPending State is pending
	 */
	mw.sd.ui.ComponentWrapperWidget.prototype.setPending = function ( isPending ) {
		this.$element.toggleClass( 'mw-sd-ui-pending', isPending );
	};
}( jQuery ) );
