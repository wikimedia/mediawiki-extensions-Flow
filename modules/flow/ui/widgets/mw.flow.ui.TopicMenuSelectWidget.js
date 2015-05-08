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

		this.loadingMoreOptionWidget = new OO.ui.MenuOptionWidget( {
			data: null,
			label: mw.msg( 'flow-load-more' ),
			classes: [ 'flow-ui-topicMenuSelectWidget-loadmore' ]
		} );

		// Events
		this.connect( this, { choose: 'onTopicChoose' } );
		this.$element.on( 'scroll', this.onMenuScroll.bind( this ) );
		this.board.connect( this, {
			add: 'addTopics',
			remove: 'removeTopics',
			clear: 'clearTopics',
			topicContentChange: 'onTopicContentChange'
		} );

		// Initialize
		this.$element.addClass( 'flow-ui-topicMenuSelectWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.TopicMenuSelectWidget, OO.ui.MenuSelectWidget );

	/* Methods */

	/**
	 * Resize the widget to fit the board
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.resize = function ( width ) {
		this.$element.css( {
			width: width || this.$element.parent().width()
		} );
	};

	/**
	 * Respond to model topic content change and update the ToC content
	 *
	 * @param {string} topicId Topic Id
	 * @param {string} content Content
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.onTopicContentChange = function ( topicId, content ) {
		var topicModel = this.board.getItemById( topicId ),
			topicWidget = this.getItemFromData( topicModel );

		if ( topicWidget ) {
			topicWidget.setLabel( content );
		}
	};

	/**
	 * Respond to scrolling of the menu. If we are close to the
	 * bottom, call for more topics.
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.onMenuScroll = function () {
		var scrollTop, isNearBottom,
			height = this.$element.height();

		// Do nothing if we're already fetching topics
		// or if there are no more topics to fetch
		if ( this.loadingMoreTopics || this.noMoreTopics ) {
			return true;
		}

		// Calculate scroll
		scrollTop = this.$element.scrollTop();
		isNearBottom = ( scrollTop > height - 100 );

		if ( isNearBottom ) {
			this.getMoreTopics();
		}
	};

	/**
	 * Respond to topic choose
	 *
	 * @param {OO.ui.MenuOptionWidget} item Chosen menu item
	 * @fires topic
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.onTopicChoose = function ( item ) {
		var topicId;

		if ( item.getData() ) {
			topicId = item.getData().getId();
			this.emit( 'topic', topicId );
		}
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
	 * Get the last offset for the API's offsetId
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.getOffset = function () {
		var topics = this.board.getItems();

		return topics.length > 0 ?
			topics[ topics.length - 1 ].getTimestamp() :
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
		return this.api.getTopicList(
			this.board.getSortOrder(),
			this.board.getSortOrder() === 'updated' ?
				this.getOffsetId() :
				this.getOffset(),
				true
			)
			.then( function ( topiclist ) {
				var i, len, topic, topicId, revisionData,
					topics = [];

				// Remove the 'more topics' option
				widget.removeItems( [ widget.loadingMoreOptionWidget ] );

				// Connect the information to the topic
				for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
					topicId = topiclist.roots[ i ];
					revisionData = mw.flow.dm.Topic.static.getTopicRevisionFromApi( topiclist, topicId );

					topic = new mw.flow.dm.Topic( topicId, revisionData );
					topics.push( topic );
				}

				return topics;
			} )
			.then( function ( topics ) {
				if ( topics.length < widget.tocPostLimit ) {
					// We received less than the post limit, which
					// means that there are no more topics available
					widget.noMoreTopics = true;
				}

				// Add to board
				widget.board.addItems( topics );

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
			optionWidget = this.getItemFromData( items[ i ] );
			if ( !optionWidget ) {
				optionWidget = new OO.ui.MenuOptionWidget( {
					data: items[ i ],
					label: items[ i ].getContent()
				} );
			}

			this.topics[ items[ i ].getId() ] = optionWidget;
			widgets.push( optionWidget );
		}

		this.addItems( widgets, index );

		// Move the 'load more' to the end
		if ( !this.noMoreTopics ) {
			this.addItems( [ this.loadingMoreOptionWidget ] );
		}
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

		// Move the 'load more' to the end
		if ( !this.noMoreTopics ) {
			this.addItems( [ this.loadingMoreOptionWidget ] );
		}
	};
}( jQuery ) );
