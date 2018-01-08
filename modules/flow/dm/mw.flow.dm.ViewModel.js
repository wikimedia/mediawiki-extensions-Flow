( function ( $ ) {
	/**
	 * Flow frontend view model
	 *
	 * @class
	 * @mixins OO.EventEmitter
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {mw.Title} [pageTitle] Board page title. Defaults to current page title
	 * @cfg {number} [tocPostsLimit=10] Post count for the table of contents. This is also
	 *  used as the number of posts to fetch when more posts are requested for the table
	 *  of contents.
	 * @cfg {number} [renderedTopics=tocPostsLimit] Number of visible posts on the page. This is also
	 *  used as the number of posts to fetch when more posts are requested on scrolling
	 *  the page. Defaults to the tocPostsLimit
	 * @cfg {string} [defaultSort='newest'] The current default sort order for topic list
	 */
	mw.flow.dm.ViewModel = function mwFlowDmViewModel( config ) {
		var self = this;
		config = config || {};

		// Mixin constructor
		OO.EventEmitter.call( this );

		this.pageTitle = config.pageTitle || mw.Title.newFromText( mw.config.get( 'wgPageName' ) );
		this.tocPostsLimit = config.tocPostsLimit || 10;
		this.renderedTopics = config.renderedTopics || 0;

		this.boardId = config.boardId || 'flow-generated-board';
		this.offsetId = config.offsetId || null;
		this.sort = config.defaultSort || 'newest';

		// Initialize board
		this.board = new mw.flow.dm.Board( {
			id: this.boardId,
			pageTitle: this.getPageTitle(),
			isDeleted: mw.config.get( 'wgArticleId' ) === 0
		} );
		this.board.connect( this, {
			reset: 'resetBoard',
		} );

		this.moreTopicsExistInApi = false;
	};

	/* Setup */

	OO.initClass( mw.flow.dm.ViewModel );
	OO.mixinClass( mw.flow.dm.ViewModel, OO.EventEmitter );

	/* Events */

	/**
	 * Board topics are populated
	 *
	 * @event populate
	 * @param {Object} Topic titles by id { 'topicId': 'topic title', ... }
	 */

	/**
	 * Board reset procedure started
	 * @todo This shouldn't be needed when we work with full widgets. Right now
	 * this is a hack between the 'old' system the new data model.
	 *
	 * @event resetBoardStart
	 */

	/**
	 * Board reset procedure ended
	 * @todo This shouldn't be needed when we work with full widgets. Right now
	 * this is a hack between the 'old' system the new data model.
	 *
	 * @event resetBoardEnd
	 */

	/* Methods */

	mw.flow.dm.ViewModel.prototype.getTopics = function () {
		return this.board.getItems();
	};

	mw.flow.dm.ViewModel.prototype.getTopicIDs = function () {
		return this.getTopics().map( function ( model ) {
			return model.getId();
		} );
	};

	/**
	 * Add topics from the topiclist API response
	 *
	 * @return {string[]} Added Topic IDs
	 */
	mw.flow.dm.ViewModel.prototype.addTopicsFromTopiclistAPI = function ( topiclist ) {
		var i, len, topicId, revisionId, topic,
			ids = [],
			topics = [];

		for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
			topicId = topiclist.roots[ i ];
			ids.push( topicId );
			revisionId = topiclist.posts[ topicId ] && topiclist.posts[ topicId ][ 0 ];
			topic = this.getBoard().getItemById( topicId );

			if ( !topic ) {
				// Create new topic
				topic = new mw.flow.dm.Topic(
						topicId,
						topiclist.revisions[ revisionId ]
					)
				);
				topics.push( topic );
			} else {
				// Update the data
				topic.populate( topiclist.revisions[ revisionId ] );
			}

			// Update topic posts
			// TODO: This should be done directly in viewmodel rather than in static method in the post
			posts = mw.flow.dm.Post.static.createTopicReplyTree(
				topiclist, topic.getReplyIds()
			);
			topic.addItems( posts );
		}

		// Add topics to the board
		this.board.addItems( topics );

		return ids;
	};

	/**
	 * Get board sort order, 'newest' or 'updated'
	 *
	 * @return {string} Board sort order
	 */
	mw.flow.dm.ViewModel.prototype.getSortOrder = function () {
		return this.sort;
	};

	/**
	 * Set board sort order, 'newest' or 'updated'
	 *
	 * @param {string} order Board sort order
	 * @fires sortOrderChange
	 */
	mw.flow.dm.ViewModel.prototype.setSortOrder = function ( order ) {
		if ( this.sort !== order ) {
			this.sort = order;
			this.emit( 'sortOrderChange', order );
		}
	};
	/**
	 * Get the state of whether there are more topics to fetch from the
	 * topiclist API.
	 *
	 * @return {boolean} There are more topics in the API
	 */
	mw.flow.dm.ViewModel.prototype.hasMoreTopicsInApi = function () {
		return this.moreTopicsExistInApi;
	};

	/**
	 * Set the state of whether there are more topics to fetch from the
	 * topiclist API.
	 *
	 * @param {boolean} moreTopicsExistInApi There are more topics in the API
	 */
	mw.flow.dm.ViewModel.prototype.toggleMoreTopicsInApi = function ( moreTopicsExistInApi ) {
		moreTopicsExistInApi = moreTopicsExistInApi === undefined ?
			!this.moreTopicsExistInApi : !!moreTopicsExistInApi;

		if ( this.moreTopicsExistInApi !== moreTopicsExistInApi ) {
			this.moreTopicsExistInApi = moreTopicsExistInApi;
		}
	};
    //
	// // TODO: This should be merged with mw.flow.dm.APIHandler.getTopicList.
	// /**
	//  * Populate the board by querying the Api
	//  *
	//  * @param {string} [sortBy] A sort option, either 'newest' or 'updated' or unset
	//  * @return {jQuery.Promise} Promise that is resolved when the
	//  *  board is populated
	//  */
	// mw.flow.dm.ViewModel.prototype.populateBoardFromApi = function ( sortBy ) {
	// 	var viewModel = this,
	// 		apiParams = {
	// 			action: 'flow',
	// 			submodule: 'view-topiclist',
	// 			page: this.getPageTitle().getPrefixedDb(),
	// 			vtloffset: 0,
	// 			vtllimit: this.getToCPostsLimit()
	// 		},
	// 		requests = [];
    //
	// 	if ( sortBy ) {
	// 		apiParams.vtlsortby = sortBy;
	// 		apiParams.vtlsavesortby = 1;
	// 	}
    //
	// 	requests = [
	// 		( new mw.Api() ).get( apiParams )
	// 			.then( function ( data ) {
	// 				var result = data.flow[ 'view-topiclist' ].result;
	// 				system.populateBoardTopicsFromJson( result.topiclist );
	// 				// HACK: This return value should go away. It is only
	// 				// here so that we can initialize the board with
	// 				// handlebars while we migrate things to ooui
	// 				return result;
	// 			} ),
	// 		( new mw.Api() ).get( {
	// 			action: 'flow',
	// 			submodule: 'view-header',
	// 			page: system.getPageTitle().getPrefixedDb()
	// 		} )
	// 			.then( function ( result ) {
	// 				var headerData = OO.getProp( result.flow, 'view-header', 'result', 'header' );
	// 				system.populateBoardDescriptionFromJson( headerData );
	// 			} )
	// 	];
    //
	// 	return $.when.apply( $, requests )
	// 		// HACK: This should go away, see hack comment above
	// 		.then( function ( resultTopicList ) {
	// 			return resultTopicList;
	// 		} );
    //
	// };

	/**
	 * Set the board description according to the header data sent from
	 * the API.
	 *
	 * @param {Object} revisionData Revision data for the description
	 */
	mw.flow.dm.ViewModel.prototype.updateDescriptionFromAPI = function ( revisionData ) {
		this.getBoard().updateDescription( revisionData );
	};

	// /**
	//  * Add topics to the board from a JSON object.
	//  * This is populating the actual board data from an object sent
	//  * either directly or through the API method.
	//  *
	//  * @param {Object} topiclist API object for the board and topic list
	//  * @param {number} [index] The position to enter the items in.
	//  * @fires populate
	//  */
	// mw.flow.dm.ViewModel.prototype.populateBoardTopicsFromJson = function ( topiclist, index ) {
	// 	var i, len, topicId, revisionData, topic, posts, type,
	// 		topicTitlesById = {},
	// 		updateTimestampsByTopicId = {},
	// 		topics = [];
    //
	// 	if ( !$.isPlainObject( topiclist ) ) {
	// 		return;
	// 	}
	// 	topiclist.roots = topiclist.roots || [];
	// 	type = topiclist.type;
    //
	// 	for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
	// 		// The content of the topic is its first post
	// 		topicId = topiclist.roots[ i ];
	// 		revisionData = mw.flow.dm.Topic.static.getTopicRevisionFromApi( topiclist, topicId );
    //
	// 		// Check if topic exists
	// 		topic = this.getBoard().getItemById( topicId );
	// 		if ( !topic ) {
	// 			// Add items
	// 			topic = new mw.flow.dm.Topic( topicId, revisionData );
	// 			topics.push( topic );
	// 		} else {
	// 			// Update topic
	// 			topic.populate( revisionData );
	// 		}
    //
	// 		// Populate posts
	// 		posts = mw.flow.dm.Post.static.createTopicReplyTree( topiclist, topic.getReplyIds() );
	// 		topic.addItems( posts );
    //
	// 		// HACK: While we use both systems (new ooui and old flow-event system)
	// 		// We need to make sure that the old system is updated too
	// 		topicTitlesById[ topicId ] = topic.getContent();
	// 		updateTimestampsByTopicId[ topicId ] = topic.getLastUpdate();
	// 	}
    //
	// 	// FIXME: checking against two different values can result in a false positive
	// 	// We have to remember how many topics we requested and check against that number,
	// 	// or use a query-continue-like thing
	// 	this.moreTopicsExistInApi = (
	// 		(
	// 			topiclist.type === 'topiclist' &&
	// 			topiclist.roots.length !== this.renderedTopics &&
	// 			topiclist.roots.length !== this.tocPostLimit
	// 		)
	// 	);
	// 	// Add to board
	// 	this.getBoard().addItems( topics, index );
    //
	// 	// Both of these should be safe to update with extend, since only the latest data should be needed.
	// 	this.emit( 'populate', {
	// 		topicTitlesById: topicTitlesById,
	// 		updateTimestampsByTopicId: updateTimestampsByTopicId
	// 	} );
	// };

	/**
	 * Reset the board per the new sorting
	 *
	 * @param {string} [sortBy] Sorting option 'newest' or 'updated'
	 * @return {jQuery.Promise} Promise that resolves with the api result
	 * @fires resetBoardStart
	 * @fires resetBoardEnd
	 */
	mw.flow.dm.ViewModel.prototype.resetBoard = function ( sortBy ) {
		var viewModel = this;

		this.emit( 'resetBoardStart' );

		return this.resetBoardDataFromApi( sortBy )
			.then( function ( result ) {
				// HACK: This parameter should go away. It is only
				// here so that we can initialize the board with
				// handlebars while we migrate things to ooui
				viewModel.emit( 'resetBoardEnd', result );
				return result;
			} );
	};

	/**
	 * Get the page title
	 *
	 * @return {mw.Title} Page title
	 */
	mw.flow.dm.ViewModel.prototype.getPageTitle = function () {
		return this.pageTitle;
	};

	/**
	 * Get ToC post limit
	 *
	 * @return {number} ToC post limit
	 */
	mw.flow.dm.ViewModel.prototype.getToCPostsLimit = function () {
		return this.tocPostsLimit;
	};

	/**
	 * Get the board
	 *
	 * @return {mw.flow.dm.Board} Associated board
	 */
	mw.flow.dm.ViewModel.prototype.getBoard = function () {
		return this.board;
	};

	/**
	 * Get the number of topics that are rendered in each pagination change.
	 * When we scroll down or load a topic, this is the number that defines the
	 * batch of topics to be loaded and rendered.
	 *
	 * This does not affect the number of topics loaded when the sort order changes.
	 *
	 * @return {number} Rendered topics
	 */
	mw.flow.dm.ViewModel.prototype.getRenderedTopics = function () {
		return this.renderedTopics;
	};
}( jQuery ) );
