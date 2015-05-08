( function ( $ ) {
	/**
	 * Flow navigation widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {mw.flow.dm.Board} board Board model
	 * @param {Object} [config]
	 * @cfg {number} [tocPostLimit=50] The number of topics in the ToC per API request
	 * @cfg {string} [defaultSort='newest'] The current default topic sort order
	 */
	mw.flow.ui.NavigationWidget = function mwFlowUiNavigationWidget( board, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.NavigationWidget.super.call( this, config );

		this.board = board;

		this.tocWidget = new mw.flow.ui.ToCWidget( this.board, {
			classes: [ 'flow-ui-navigationWidget-tocWidget' ],
			tocPostLimit: config.tocPostLimit
		} );

		this.reorderTopicsWidget = new mw.flow.ui.ReorderTopicsWidget( this.board, config );

		// Events
		$( window ).on( 'scroll resize', this.onWindowScroll.bind( this ) );
		this.tocWidget.connect( this, { loadTopic: 'onToCWidgetLoadTopic' } );
		this.reorderTopicsWidget.connect( this, { reorder: 'onReorderTopicsWidgetReorder' } );

		// Initialize
		this.$element
			.append(
				this.tocWidget.$element,
				this.reorderTopicsWidget.$element
			)
			.addClass( 'flow-ui-navigationWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.NavigationWidget, OO.ui.Widget );

	/* Methods */

	/**
	 * Propagate the scrollto event so the old code can
	 * work on it.
	 *
	 * @param {string} topicId Topic id
	 * @fires loadTopic
	 */
	mw.flow.ui.NavigationWidget.prototype.onToCWidgetLoadTopic = function ( topicId ) {
		this.emit( 'loadTopic', topicId );
	};

	/**
	 * Propagate the reorder event from the reorderTopicsWidget
	 * so the old code can be updated
	 *
	 * @param {string} order New order
	 * @fires reorderTopics
	 */
	mw.flow.ui.NavigationWidget.prototype.onReorderTopicsWidgetReorder = function ( order ) {
		this.emit( 'reorderTopics', order );
	};

	/**
	 * Initialize the widget.
	 */
	mw.flow.ui.NavigationWidget.prototype.initialize = function () {
		this.resize();
	};

	/**
	 * Resize the widget to fit the board
	 */
	mw.flow.ui.NavigationWidget.prototype.resize = function () {
		var width = this.$element.parent().width();

		this.$element.css( {
			width: width
		} );

		this.tocWidget.resize( width );
	};

	/**
	 * Respond to window scroll
	 */
	mw.flow.ui.NavigationWidget.prototype.onWindowScroll = function () {
		var scrollTop, isScrolledDown,
			x = $( window ).width() / 2,
			y = this.$element.height() + 20, // Widget height + some threshhold
			// Find the topic we're on
			// This works in desktop but doesn't work on mobile.
			// see: https://developer.mozilla.org/en-US/docs/Web/API/Document/elementFromPoint#Browser_compatibility
			// TODO: Find a better way to figure out which topic we're on
			element = document.elementFromPoint( x, y ),
			$topic = $( element ).closest( '.flow-topic' ),
			topicId = $topic.data( 'flowId' ),
			topic = this.board.getItemById( topicId );

		// HACK: Quit if the widget is unattached. This happens when we are
		// waiting to rebuild the board when reordering the topics
		// This should not be needed when the board is wigdetized
		if ( this.$element.parent().length === 0 ) {
			return;
		}

		scrollTop = $( window ).scrollTop();
		isScrolledDown = scrollTop > this.$element.parent().offset().top;

		// Fix the widget to the top when we scroll down below its original
		// location
		this.$element.toggleClass(
			'flow-ui-navigationWidget-affixed',
			isScrolledDown
		);

		this.reorderTopicsWidget.toggle( !isScrolledDown );

		// Update the toc selection
		this.tocWidget.updateSelection( topic ? topic.getId() : undefined );
	};
}( jQuery ) );
