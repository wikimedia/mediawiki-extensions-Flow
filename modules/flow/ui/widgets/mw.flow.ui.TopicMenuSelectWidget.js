( function ( $ ) {
	/**
	 * Flow topic list widget
	 *
	 * @extends OO.ui.MenuSelectWidget
	 * @constructor
	 * @param {mw.flow.dm.Board} Board model
	 * @param {Object} [config]
	 * @cfg {number} [tocPostLimit=50] The number of topics in the ToC per API request
	 */
	mw.flow.ui.TopicMenuSelectWidget = function mwFlowUiTopicMenuSelectWidget( model, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.TopicMenuSelectWidget.super.call( this, config );

		// Properties
		this.board = model;
		this.topics = {};
		this.tocPostLimit = config.tocPostLimit || 50;

		// Flags for infinite scroll
		// Mark whether the process of loading is undergoing so we won't trigger it multiple times at once
		this.loadingMoreTopics = false;
		// Mark whether there are no more topics available so we can stop triggering infinite scroll
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

		this.addTopics( this.board.getItems() );

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

	mw.flow.ui.TopicMenuSelectWidget.prototype.destroy = function () {
		this.board.disconnect( this );
	};

	/**
	 * Respond to model topic content change and update the ToC content
	 *
	 * @param {string} topicId Topic Id
	 * @param {string} content Content
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.onTopicContentChange = function ( topic, content ) {
		var topicWidget = this.topics[ topic.getId() ];

		if ( topicWidget ) {
			topicWidget.setLabel( content );
		}
	};

	/**
	 * Respond to scrolling of the menu. If we are close to the
	 * bottom, call for more topics.
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.onMenuScroll = function () {
		var scrollTop, isNearBottom, height;

		// Do nothing if we're already fetching topics
		// or if there are no more topics to fetch
		if ( this.loadingMoreTopics || this.noMoreTopics ) {
			return true;
		}

		// Calculate scroll
		height = this.$element.height();
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
		var topic = item.getData(),
			topicId = topic && item.getData().getId();

		// TODO: Check the position of the topic (its index in the list of topics)
		if ( topicId ) {
			this.emit( 'topic', topicId, this.board.getItemIndex( topic ) );
		}
	};

	/**
	 * Get more topics from the queue
	 *
	 * @return {jQuery.Promise} Promise that is resolved when all
	 *  available topics in the response have been added to the
	 *  flow.dm.Board
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.getMoreTopics = function () {
		var widget = this,
			sortOrder = this.board.getSortOrder();

		// TODO: Add a spinner animation
		this.loadingMoreTopics = true;
		return this.api.getTopicList(
			sortOrder,
			{
				offset: sortOrder === 'newest' ?
					this.board.getOffsetId() :
					this.board.getOffset(),
				toconly: true
			} )
			.then( function ( topiclist ) {
				return mw.flow.dm.Topic.static.extractTopicsFromAPI( topiclist );
			} )
			.then( function ( topics ) {
				if ( topics.length < widget.tocPostLimit ) {
					// We received less than the post limit, which
					// means that there are no more topics available
					widget.noMoreTopics = true;
				}

				// Remove the 'more topics' option
				widget.removeItems( [ widget.loadingMoreOptionWidget ] );

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
			optionWidget = this.items[ items[ i ].getId() ];
			if ( !optionWidget ) {
				optionWidget = new OO.ui.MenuOptionWidget( {
					data: items[ i ],
					label: items[ i ].getContent()
				} );
			}
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
		this.topics = {};
	};

	/**
	 * Remove topics from the ToC list
	 *
	 * @param {mw.flow.dm.Topic[]} items Topic data items to remove
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.removeTopics = function ( items ) {
		var i, len, itemId, optionWidget,
			widgets = [];

		for ( i = 0, len = items.length; i < len; i++ ) {
			itemId = items[i].getId();
			optionWidget = this.topics[ itemId ];
			widgets.push( optionWidget );
		}

		this.removeItems( widgets );

		// Move the 'load more' to the end
		if ( !this.noMoreTopics ) {
			this.addItems( [ this.loadingMoreOptionWidget ] );
		}
	};

	/**
	 * Extend addItems to also add to the topic reference item
	 * @inheritdoc
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.addItems = function ( items, index ) {
		var i, len;

		for ( i = 0, len = items.length; i < len; i++ ) {
			if ( items[i].getData() ) {
				this.topics[ items[i].getData().getId() ] = items[i];
			}
		}

		// Parent call
		mw.flow.ui.TopicMenuSelectWidget.super.prototype.addItems.call( this, items, index );
	};

	/**
	 * Extend removeItems to also remove to the topic reference item
	 * @inheritdoc
	 */
	mw.flow.ui.TopicMenuSelectWidget.prototype.removeItems = function ( items ) {
		var i, len;

		for ( i = 0, len = items.length; i < len; i++ ) {
			if ( items[ i ].getData() ) {
				delete this.topics[ items[ i ].getData().getId() ];
			}
		}

		// Parent call
		mw.flow.ui.TopicMenuSelectWidget.super.prototype.removeItems.call( this, items );
	};

}( jQuery ) );
