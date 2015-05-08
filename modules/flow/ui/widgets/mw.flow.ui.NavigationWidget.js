/**
 * Flow navigation widget
 *
 * @extends OO.ui.Widget
 * @constructor
 * @param {mw.flow.dm.System} system
 * @param {Object} [config]
 *
 */
mw.flow.ui.NavigationWidget = function mwFlowUiNavigationWidget( system, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.ui.NavigationWidget.super.call( this, config );

	this.system = system;

	this.tocWidget = new mw.flow.ui.ToCWidget( this.system, {
		classes: [ 'flow-ui-navigationWidget-tocWidget' ]
	} );
	this.latestTopicsWidgets = new mw.flow.ui.LatestTopicsWidget( this.system );

	// Events
	// this.onWindowScrollCallback = OO.ui.debounce( this.onWindowScroll, 100 );
	$( window ).on( 'scroll', this.onWindowScroll.bind( this ) );

	this.$element
		.append(
			this.tocWidget.$element,
			this.latestTopicsWidgets.$element
		)
		.addClass( 'flow-ui-navigationWidget' );
};

/* Initialization */

OO.inheritClass( mw.flow.ui.NavigationWidget, OO.ui.Widget );

/* Methods */

/**
 * Initialize the widget
 */
mw.flow.ui.NavigationWidget.prototype.initialize = function () {
	this.fixedTop = this.$element.offset().top;
};

/**
 * Respond to window scroll
 *
 */
mw.flow.ui.NavigationWidget.prototype.onWindowScroll = function () {
	var x = $( window ).width() / 2,
		y = this.$element.height() + 100, // Widget height + some threshhold
		// Find the topic we're on
		// This works in desktop but doesn't work on mobile.
		// see: https://developer.mozilla.org/en-US/docs/Web/API/Document/elementFromPoint#Browser_compatibility
		// TODO: Find a better way to figure out which topic we're on
		element = document.elementFromPoint( x, y ),
		$topic = $( element ).closest( '.flow-topic' ),
		topicId = $topic.data( 'flowId' ),
		topic = this.system.getBoard().getItemById( topicId );

	// Fix the widget to the top when we scroll down below its original
	// location
	this.$element.toggleClass(
		'flow-ui-navigationWidget-affixed',
		$( window ).scrollTop() > this.fixedTop
	);

	// Update the toc selection
	this.tocWidget.updateSelection( topic ? topic.getId() : undefined );
};
