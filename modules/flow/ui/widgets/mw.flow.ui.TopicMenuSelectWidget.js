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
	this.tocPostLimit = config.tocPostLimit || 50;

	this.moreTopicsOptionWidget = new OO.ui.MenuOptionWidget( {
		data: 'more',
		label: 'More topics',
		icon: 'search',
		classes: [ 'flow-ui-topicMenuSelectWidget-more' ]
	} );

	// Initialize ToC API queue
	this.tocQueue = new mw.flow.dm.APIResultsQueue( {
		limit: this.tocPostLimit,
		threshhold: 5
	} );
	this.tocProvider = new mw.flow.dm.APITopicsProvider(
		this.system.getPageTitle().getPrefixedDb(),
		mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' ) + '/api.php',
		{
			fetchLimit: this.tocPostLimit,
			toconly: true
		}
	);
	this.tocQueue.addProvider( this.tocProvider );

	// Events
	this.connect( this, { choose: 'onTopicChoose' } );
	this.board.connect( this, {
		add: 'addTopics',
		remove: 'removeTopics',
		clear: 'clearTopics'
	} );

	// Initialize
	// PROBLEM: Since we're initializing from the board,there is
	// no way of knowing on initialization whether the 'get more topics'
	// is necessary (if there are any more topics to take) or not.
	// The only way to actually know is to kickstart the queue and have
	// it fetch x topics. If it is depleted, we know that the initial
	// items are enough and there is no need for 'get more topics' option
	// For the moment, we just add that option regardless as a start.
	this.addTopics( this.board.getItems() );
};

/* Initialization */

OO.inheritClass( mw.flow.ui.TopicMenuSelectWidget, OO.ui.MenuSelectWidget );

/* Methods */

mw.flow.ui.TopicMenuSelectWidget.prototype.onTopicChoose = function ( item ) {
	var topicId;

	if ( item.getData() === 'more' ) {
		// Get more topics
		this.getMoreTopics();
	} else {
		topicId = item.getData().getId();
		this.emit( 'topic', topicId );
	}

};
/**
 * Update the topic provider offset id for the next
 * queue call.
 */
mw.flow.ui.TopicMenuSelectWidget.prototype.updateProviderOffsetId = function () {
	var topics = this.board.getItems();

	this.tocProvider.setOffsetId(
		topics.length > 0 ?
			topics[ topics.length - 1 ].getId() :
			null
	);
};

/**
 * Get more topics from the queue
 *
 * @return {jQuery.Promise} Promise that is resolved when all
 *  available topics in the response have been added to the
 *  flow.dm.Board
 */
mw.flow.ui.TopicMenuSelectWidget.prototype.getMoreTopics = function () {
	var widget = this;
	if ( !this.tocProvider.isDepleted() ) {
		// If there are no more topics, this is a noop
		return this.tocQueue.get()
			.then( function ( data ) {
				var topic, postId,
					topics = [],
					result = data[ 0 ];

				for ( postId in result ) {
					topic = new mw.flow.dm.Topic( postId, result[ postId ] );
					topics.push( topic );
				}
				// Add to board
				widget.board.addItems( topics );
			} );
	} else {
		// Remove 'get more topics' option
		this.removeItems( [ this.moreTopicsOptionWidget ] );
	}
};

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

	// Move the 'get more' option to the end
	if ( !this.tocProvider.isDepleted() ) {
		this.addItems( [ this.moreTopicsOptionWidget ] );
	}
	this.updateProviderOffsetId();
};

mw.flow.ui.TopicMenuSelectWidget.prototype.clearTopics = function () {
	this.clearItems();
	this.updateProviderOffsetId();
};

mw.flow.ui.TopicMenuSelectWidget.prototype.removeTopics = function ( items ) {
	var widget = this;
	// TODO rewrite as a loop
	this.removeItems( $.map( items, function ( item ) {
		var optionWidget = widget.topics[item.getId()];
		delete widget.topics[item.getId()];
		return optionWidget;
	} ) );
	this.updateProviderOffsetId();
};
