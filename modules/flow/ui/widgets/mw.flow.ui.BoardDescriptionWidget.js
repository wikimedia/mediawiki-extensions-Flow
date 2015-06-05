( function ( $ ) {
	/**
	 * Flow board description widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {mw.flow.dm.BoardDescription} model Board description model
	 * @param {Object} [config]
	 *
	 */
	mw.flow.ui.BoardDescriptionWidget = function mwFlowUiBoardDescriptionWidget( model, $container, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.BoardDescriptionWidget.super.call( this, config );

		// TODO: Figure out a better way to work with having an existing content
		// on the page. How do we work with the data-parsoid information (do we
		// need it?) and if we fetch information from an existing element on the
		// page, do we update the model? Do we replace this.$element directly
		// or just transfer the content, like is done below?
		this.model = model;
		this.$content = $container && $container.clone().html();

		// Initialize
		this.$element
			.append( this.$content )
			.addClass( 'flow-ui-boardDescriptionWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.BoardDescriptionWidget, OO.ui.Widget );
}( jQuery ) );
