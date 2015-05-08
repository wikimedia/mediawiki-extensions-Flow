/**
 * Flow topic list widget
 *
 * @extends OO.ui.MenuSelectWidget
 * @constructor
 * @param {mw.flow.dm.System} system
 * @param {Object} [config]
 *
 */
mw.flow.ui.TopicMenuSelectWidget = function mwFlowUiTopicMenuSelectWidget( system, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.ui.TopicMenuSelectWidget.super.call( this, config );

	// Properties
	this.system = system;
	this.board = this.system.getBoard();
	this.topics = {};

	// Events
	this.board.connect( this, {
		add: 'addTopics',
		remove: 'removeTopics'
	} );

	// Initialize
	this.addTopics( this.board.getItems() );
	this.toggle( true );
};

/* Initialization */

OO.inheritClass( mw.flow.ui.TopicMenuSelectWidget, OO.ui.MenuSelectWidget );

/* Methods */

mw.flow.ui.TopicMenuSelectWidget.prototype.addTopics = function ( items, index ) {
	var widget = this;
	// TODO rewrite as a loop
	this.addItems(
		$.map(
			items,
			function ( item ) {
				var optionWidget = new OO.ui.MenuOptionWidget( {
					data: item,
					label: item.getRawContent()
				} );
				widget.topics[item.getId()] = optionWidget;
				return optionWidget;
			}
		),
		index
	);
};

mw.flow.ui.TopicMenuSelectWidget.prototype.removeTopics = function ( items ) {
	var widget = this;
	// TODO rewrite as a loop
	this.removeItems( $.map( items, function ( item ) {
		var optionWidget = widget.topics[item.getId()];
		delete widget.topics[item.getId()];
		return optionWidget;
	} ) );
};
