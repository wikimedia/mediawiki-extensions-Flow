( function ( $ ) {
	/**
	 * Flow ToC widget
	 *
	 * @extends OO.ui.Widget
	 * @constructor
	 * @param {mw.flow.dm.Board} board Board model
	 * @param {Object} [config]
	 *
	 */
	mw.flow.ui.ToCWidget = function mwFlowUiToCWidget( board, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.ToCWidget.super.call( this, config );

		this.board = board;
		this.originalButtonLabel = mw.msg( 'flow-board-header-browse-topics-link' );

		this.button = new OO.ui.ButtonWidget( {
			framed: false,
			icon: 'stripeToC',
			label: this.originalButtonLabel,
			classes: [ 'flow-ui-tocWidget-button' ]
		} );
		this.topicSelect = new mw.flow.ui.TopicMenuSelectWidget( this.board, $.extend( {
			classes: [ 'flow-ui-tocWidget-menu' ],
			tocPostLimit: config.tocPostLimit
		}, config ) );

		this.$wrapper = $( '<div>' )
			.addClass( 'flow-ui-tocWidget-wrapper' );

		// Events
		this.button.connect( this, { click: 'onButtonClick' } );
		this.topicSelect.connect( this, { topic: 'onTopicSelectChoose' } );

		// Initialize
		this.$element
			.addClass( 'flow-ui-tocWidget' )
			.append(
				this.button.$element,
				this.$wrapper.append( this.topicSelect.$element )
			);
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.ToCWidget, OO.ui.Widget );

	/* Methods */

	/**
	 * Resize the widget and its components
	 */
	mw.flow.ui.ToCWidget.prototype.resize = function ( width ) {
		this.$wrapper.css( {
			width: width
		} );
		this.topicSelect.resize( width );
	};

	/**
	 * Respond to button click
	 */
	mw.flow.ui.ToCWidget.prototype.onButtonClick = function () {
		this.topicSelect.toggle();
	};

	/**
	 * Respond to topic select choose event
	 * @param {string} topicId Topic id
	 */
	mw.flow.ui.ToCWidget.prototype.onTopicSelectChoose = function ( topicId ) {
		var $topic = $( '#flow-topic-' + topicId );

		// TODO: Ideally, we should be able to do this by checking whether the
		// topic is a stub or not. Right now that's not possible because when we
		// scroll, the topics do not unstub themselves, so we can't trust that.
		if ( $topic.length > 0 ) {
			// Scroll down to the topic
			$( 'html, body' ).animate( {
				scrollTop: $( '#flow-topic-' + topicId ).offset().top + 'px'
			}, 'fast' );
		} else {
			// TODO: Widgetize board, topic and post so we can do this
			// through OOUI rather than callbacks from the current system
			this.emit( 'loadTopic', topicId );
		}
	};

	/**
	 * Update the ToC selection
	 *
	 * @param {string} topicId Topic Id
	 */
	mw.flow.ui.ToCWidget.prototype.updateSelection = function ( topicId ) {
		var item = this.board.getItemById( topicId ),
			label = item ? item.getContent() : this.originalButtonLabel;

		this.topicSelect.selectItemByData( item );
		this.updateLabel( label );
	};

	/**
	 * Update the button label. If no label is given, the button will
	 * retain its original label.
	 *
	 * @param {string} [label] New label
	 */
	mw.flow.ui.ToCWidget.prototype.updateLabel = function ( label ) {
		this.button.setLabel( label || this.originalButtonLabel );
	};
}( jQuery ) );
