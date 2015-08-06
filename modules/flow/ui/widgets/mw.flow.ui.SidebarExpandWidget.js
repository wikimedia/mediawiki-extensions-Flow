( function ( $ ) {
	/**
	 * Flow sidebar expand widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration object
	 * @cfg {boolean} [collapsed=false] Start as collapsed
	 */
	mw.flow.ui.SidebarExpandWidget = function mwFlowUiSidebarExpandWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.SidebarExpandWidget.parent.call( this, config );

		this.button = new OO.ui.ButtonWidget( {
			framed: false
		} );

		this.toggleCollapsed( !!config.collapsed );

		// Events
		this.button.connect( this, { click: 'onButtonClick' } );

		this.$element
			.addClass( 'flow-ui-sidebarExpandWidget' )
			.append( this.button.$element );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.SidebarExpandWidget, OO.ui.Widget );

	mw.flow.ui.SidebarExpandWidget.prototype.onButtonClick = function () {
		this.toggleCollapsed();
	};

	/**
	 * Toggle collapsed state
	 *
	 * @param {boolean} collapse Widget is collapsed
	 */
	mw.flow.ui.SidebarExpandWidget.prototype.toggleCollapsed = function ( collapse ) {
		var action;

		collapse = collapse !== undefined ? collapse : !this.collapsed;

		if ( this.collapsed !== collapse ) {
			this.collapsed = collapse;
			action = this.collapsed ? 'expand' : 'collapse';

			this.$element.toggleClass( 'flow-ui-sidebarExpandWidget-collapsed', this.collapsed );

			this.button.setIcon( 'topic-' + action );
			this.button.setTitle( mw.msg( 'flow-sidebar-' + action ) );

			this.emit( 'toggle', this.collapsed );
		}
	};

	/**
	 * Get the collapsed state of the widget
	 *
	 * @return {boolean} collapse Widget is collapsed
	 */
	mw.flow.ui.SidebarExpandWidget.prototype.isCollapsed = function () {
		return this.collapsed;
	};
}( jQuery ) );
