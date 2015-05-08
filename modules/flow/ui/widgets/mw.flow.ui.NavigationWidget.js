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
		classes: [ 'flow-ui-navigationWidget-tocWidget' ],
		tocPostLimit: config.tocPostLimit
	} );

	// TODO: rename to 'sortTopicsWidget'
	this.latestTopicsWidgets = new mw.flow.ui.LatestTopicsWidget( this.system );

	// Events
	// this.onWindowScrollCallback = OO.ui.debounce( this.onWindowScroll, 100 );
	$( window ).on( 'scroll', this.onWindowScroll.bind( this ) );
	$( window ).on( 'resize', this.onWindowScroll.bind( this ) );

	this.tocWidget.connect( this, { loadTopic: 'onToCWidgetLoadTopic' } );

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
 * Propogate the scrollto event so the old code can
 * work on it.
 *
 * @param {string} topicId Topic id
 * @fires loadTopic
 */
mw.flow.ui.NavigationWidget.prototype.onToCWidgetLoadTopic = function ( topicId ) {
	this.emit( 'loadTopic', topicId );
};

mw.flow.ui.NavigationWidget.prototype.resize = function () {
	this.$element.css( {
		width: this.$element.parent().width()
	} );
};

/**
 * Respond to window scroll
 */
mw.flow.ui.NavigationWidget.prototype.onWindowScroll = function () {
	var x = $( window ).width() / 2,
		y = this.$element.height() + 100, // Widget height + some threshhold
		threshhold = 1000,
		scrollTop = $( window ).scrollTop(),
		isScrolledDown = scrollTop > this.$element.parent().offset().top,
		// Find the topic we're on
		// This works in desktop but doesn't work on mobile.
		// see: https://developer.mozilla.org/en-US/docs/Web/API/Document/elementFromPoint#Browser_compatibility
		// TODO: Find a better way to figure out which topic we're on
		element = document.elementFromPoint( x, y ),
		$topic = $( element ).closest( '.flow-topic' ),
		topicId = $topic.data( 'flowId' ),
		topic = this.system.getBoard().getItemById( topicId );

	// Check if we are at the bottom of the page
	// if ( scrollTop >= $( document ).height() - threshhold ) {
	// 	Get more topics into the ToC
	// 	TODO: Once the entire behavior is moved from original flow to ooui
	// 	widgets, this should also load more topic widgets into the page
	// 	this.system.getToCTopics();
	// 	TODO: Check if we need to get more topics based on the number of
	// 	topics that are rendered
	// 	this.ToCWidget.getMoreTopics();
	// }

	// Fix the widget to the top when we scroll down below its original
	// location
	this.$element.toggleClass(
		'flow-ui-navigationWidget-affixed',
		isScrolledDown
	);

	if ( isScrolledDown ) {
		this.resize();
		this.latestTopicsWidgets.toggle( !isScrolledDown );
	}

	// Update the toc selection
	this.tocWidget.updateSelection( topic ? topic.getId() : undefined );
};
