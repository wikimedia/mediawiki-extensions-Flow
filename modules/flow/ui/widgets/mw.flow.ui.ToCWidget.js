/**
 * Flow ToC widget
 *
 * @extends OO.ui.Widget
 * @constructor
 * @param {mw.flow.dm.System} system
 * @param {Object} [config]
 *
 */
mw.flow.ui.ToCWidget = function mwFlowUiToCWidget( system, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.ui.ToCWidget.super.call( this, config );

	this.system = system;

	this.originalButtonLabel = mw.msg( 'flow-board-header-browse-topics-link' );

	this.button = new OO.ui.ButtonWidget( {
		framed: false,
		icon: 'stripeToC',
		label: this.originalButtonLabel,
		classes: [ 'flow-ui-tocWidget-button' ]
	} );
	this.topicSelect = new mw.flow.ui.TopicMenuSelectWidget( this.system, {
		classes: [ 'flow-ui-tocWidget-menu' ]
	} );

	// Events
	this.button.connect( this, { click: 'onButtonClick' } );
	this.topicSelect.connect( this, { choose: 'onTopicSelectChoose' } );

	// Initialize
	this.$element
		.addClass( 'flow-ui-tocWidget' )
		.append(
			this.button.$element,
			this.topicSelect.$element
		);
};

/* Initialization */

OO.inheritClass( mw.flow.ui.ToCWidget, OO.ui.Widget );

/* Methods */

/**
 * Respond to button click
 */
mw.flow.ui.ToCWidget.prototype.onButtonClick = function () {
	this.topicSelect.toggle();
};

/**
 * Respond to topic select choose event
 * @param {OO.ui.MenuOptionWidget} item Chosen item
 */
mw.flow.ui.ToCWidget.prototype.onTopicSelectChoose = function ( item ) {
	var topicId = item.getData().getId(),
		$topic = $( '#flow-topic-' + topicId ),
		flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );

	if ( $topic.length > 0 ) {
		// Scroll down to the topic
		$( 'html, body' ).animate( {
			scrollTop: $( '#flow-topic-' + topicId ).offset().top + 'px'
		}, 'fast' );
	} else {
debugger;
		// TODO: Widgetize board, topic and post so we can do this
		// through OOUI rather than callbacks from the current system
		flowBoard.jumpToTopic( topicId );
	}
};

/**
 * Update the ToC selection
 *
 * @param {string} topicId Topic Id
 */
mw.flow.ui.ToCWidget.prototype.updateSelection = function ( topicId ) {
	var item = this.system.getBoard().getItemById( topicId ),
		label = item ? item.getRawContent() : this.originalButtonLabel;

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
