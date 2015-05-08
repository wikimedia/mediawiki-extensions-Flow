( function ( $ ) {
	/**
	 * Flow topic list widget
	 *
	 * @extends OO.ui.MenuSelectWidget
	 * @constructor
	 * @param {mw.flow.dm.Board} Board model
	 * @param {Object} [config]
	 */
	mw.flow.ui.TopicMenuSelectWidget = function mwFlowUiTopicMenuSelectWidget( model, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.TopicMenuSelectWidget.super.call( this, config );

		// Properties
		this.board = model;
		this.topics = {};
		this.tocPostLimit = config.tocPostLimit || 50;

		this.loadingMoreTopics = false;
		this.noMoreTopics = false;
		this.api = new mw.flow.dm.APIHandler( this.board.getPageTitle().getPrefixedDb() );

		// Events
		this.connect( this, { choose: 'onTopicChoose' } );
		this.board.connect( this, {
			add: 'addTopics',
			remove: 'removeTopics',
			clear: 'clearTopics'
		} );
		this.$element.on( 'scroll', this.onMenuScroll.bind( this ) );
		this.$element.on( 'resize', this.resize.bind( this ) );

		// TODO: Aggregate events for 'change' on mw.flow.dm.Topic
		// and change display of individual topic titles accordingly
		// This will be relevant when we have topic widgets that allow
		// for editing; If a user edits a topic -- either its text or
		// any of its parameters, like suppresses or hides it -- the
		// ToC widget will have to respond accordingly.

		// Initialize
		this.$element.addClass( 'flow-ui-TopicMenuSelectWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.TopicMenuSelectWidget, OO.ui.MenuSelectWidget );

	/* Methods */

	/**
	 * Resize the widget to fit the board
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.resize = function () {
		this.$element.css( {
			width: this.$element.parent().width()
		} );
	};

	/**
	 * Respond to scrolling of the menu. If we are close to the
	 * bottom, call for more topics.
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.onMenuScroll = function () {
		var scrollTop, isNearBottom;

		// Do nothing if we're already fetching topics
		// or if there are no more topics to fetch
		if ( this.loadingMoreTopics || this.noMoreTopics ) {
			return;
		}

		// Calculate scroll
		scrollTop = this.$element.scrollTop();
		isNearBottom = scrollTop > this.$element.height() - 200;

		if ( isNearBottom ) {
			this.getMoreTopics();
		}
	};

	mw.flow.ui.TopicMenuSelectWidget.prototype.onTopicChoose = function ( item ) {
		var topicId;

		topicId = item.getData().getId();
		this.emit( 'topic', topicId );
	};

	/**
	 * Get the last offset for the API's offsetId
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.getOffsetId = function () {
		var topics = this.board.getItems();

		return topics.length > 0 ?
			topics[ topics.length - 1 ].getId() :
			null;
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

		// TODO: Add a spinner animation

		this.loadingMoreTopics = true;
		return this.api.getTopicList( this.getOffsetId() )
			.then( function ( topiclist ) {
				var i, len, topic, topicId, revisionData,
					topics = [];

				// Connect the information to the topic
				for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
					topicId = topiclist.roots[ i ];
					revisionData = mw.flow.dm.Topic.static.getTopicRevisionFromApi( topiclist, topicId );

					topic = new mw.flow.dm.Topic( topicId, revisionData );
					topics.push( topic );
				}

				// Add to board
				widget.board.addItems( topics );

				return topiclist.roots.length;
			} )
			.then( function ( numTopics ) {
				if ( numTopics < widget.tocPostLimit ) {
					// We received less than the post limit, which
					// means that there are no more topics available
					widget.noMoreTopics = true;
				}
			} )
			.then( function () {
				widget.loadingMoreTopics = false;
			} );
	};

	/**
	 * Add topics to the ToC list
	 *
	 * @param {mw.flow.dm.Topic[]} items Topic data items
	 * @param {number} index Location to add
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.addTopics = function ( items, index ) {
		var i, len, optionWidget,
			widgets = [];

		for ( i = 0, len = items.length; i < len; i++ ) {
			optionWidget = new OO.ui.MenuOptionWidget( {
				data: items[ i ],
				label: items[ i ].getContent()
			} );
			this.topics[ items[ i ].getId() ] = optionWidget;
			widgets.push( optionWidget );
		}

		this.addItems( widgets, index );
	};

	/**
	 * Clear all topics from the ToC list
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.clearTopics = function () {
		this.clearItems();
	};

	/**
	 * Remove topics from the ToC list
	 *
	 * @param {mw.flow.dm.Topic[]} items Topic data items to remove
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.removeTopics = function ( items ) {
		var i, len, itemId, optionWidget,
			widgets = [];
		// TODO rewrite as a loop

		for ( i = 0, len = items.length; i < len; i++ ) {
			itemId = items[i].getId();
			optionWidget = this.topics[ itemId ];
			delete this.topics[ itemId ];
			widgets.push( optionWidget );
		}

		this.removeItems( widgets );
	};
}( jQuery ) );
