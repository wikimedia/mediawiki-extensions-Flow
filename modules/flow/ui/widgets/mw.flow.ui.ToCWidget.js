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
