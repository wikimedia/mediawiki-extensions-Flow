( function ( $ ) {
	/**
	 * Flow initialization system
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
	 * @cfg {string} [defaultSort] The current default sort order for topic list
	 */
	mw.flow.dm.System = function mwFlowDmSystem( config ) {

		config = config || {};

		// Mixin constructor
		OO.EventEmitter.call( this );

		this.pageTitle = config.pageTitle || mw.Title.newFromText( mw.config.get( 'wgPageName' ) );
		this.tocPostsLimit = config.tocPostsLimit || 10;
		this.renderedTopics = config.renderedTopics || 0;

		this.boardId = config.boardId || 'flow-generated-board';
		this.offsetId = config.offsetId || null;

		// Initialize board
		this.board = new mw.flow.dm.Board( {
			id: this.boardId,
			pageTitle: this.getPageTitle(),
			isDeleted: mw.config.get( 'wgArticleId' ) === 0,
			defaultSort: config.defaultSort
		} );

		this.board.connect( this, { reset: 'resetBoard' } );
	};

	/* Setup */

	OO.initClass( mw.flow.dm.System );
	OO.mixinClass( mw.flow.dm.System, OO.EventEmitter );

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

	/**
	 * Populate the board by querying the Api
	 *
	 * @param {string} [sortBy] A sort option, either 'newest' or 'updated' or unset
	 * @return {jQuery.Promise} Promise that is resolved when the
	 *  board is populated
	 */
	mw.flow.dm.System.prototype.populateBoardFromApi = function ( sortBy ) {
		var system = this,
			apiParams = {
				action: 'flow',
				submodule: 'view-topiclist',
				page: this.getPageTitle().getPrefixedDb(),
				vtloffset: 0,
				vtllimit: this.getToCPostsLimit()
			},
			requests = [];

		if ( sortBy ) {
			apiParams.vtlsortby = sortBy;
			apiParams.vtlsavesortby = 1;
		}

		requests = [
			( new mw.Api() ).get( apiParams )
				.then( function ( data ) {
					var result = data.flow[ 'view-topiclist' ].result;
					system.populateBoardTopicsFromJson( result.topiclist );
					// HACK: This return value should go away. It is only
					// here so that we can initialize the board with
					// handlebars while we migrate things to ooui
					return result;
				} ),
			( new mw.Api() ).get( {
				action: 'flow',
				submodule: 'view-header',
				page: system.getPageTitle().getPrefixedDb()
			} )
				.then( function ( result ) {
					var headerData = OO.getProp( result.flow, 'view-header', 'result', 'header' );
					system.populateBoardDescriptionFromJson( headerData );
				} )
		];

		return $.when.apply( $, requests )
			// HACK: This should go away, see hack comment above
			.then( function ( resultTopicList ) {
				return resultTopicList;
			} );

	};

	/**
	 * Set the board description according to the header data sent from
	 * the API.
	 *
	 * @param {Object} headerData API object for the board header
	 */
	mw.flow.dm.System.prototype.populateBoardDescriptionFromJson = function ( headerData ) {
		if ( headerData.revision ) {
			this.getBoard().updateDescription( headerData.revision );
		}
	};

	/**
	 * Add topics to the board from a JSON object.
	 * This is populating the actual board data from an object sent
	 * either directly or through the API method.
	 *
	 * @param {Object} topiclist API object for the board and topic list
	 * @param {number} [index] The position to enter the items in.
	 * @fires populate
	 */
	mw.flow.dm.System.prototype.populateBoardTopicsFromJson = function ( topiclist, index ) {
		var i, len, topicId, revisionData, topic,
			topicTitlesById = {},
			updateTimestampsByTopicId = {},
			topics = [];

		for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
			// The content of the topic is its first post
			topicId = topiclist.roots[ i ];
			revisionData = mw.flow.dm.Topic.static.getTopicRevisionFromApi( topiclist, topicId );

			// Check if topic exists
			topic = this.getBoard().getItemById( topicId );
			if ( !topic ) {
				// Add items
				topic = new mw.flow.dm.Topic( topicId, revisionData );
				topics.push( topic );
			} else {
				// Update topic
				topic.populate( revisionData );
			}

			// HACK: While we use both systems (new ooui and old flow-event system)
			// We need to make sure that the old system is updated too
			topicTitlesById[ topicId ] = topic.getContent();
			updateTimestampsByTopicId[ topicId ] = topic.getLastUpdate();
		}
		// Add to board
		this.getBoard().addItems( topics, index );

		// Both of these should be safe to update with extend, since only the latest data should be needed.
		this.emit( 'populate', {
			topicTitlesById: topicTitlesById,
			updateTimestampsByTopicId: updateTimestampsByTopicId
		} );
	};

	/**
	 * Reset the board per the new sorting
	 *
	 * @param {string} [sortBy] Sorting option 'newest' or 'updated'
	 * @return {jQuery.Promise} Promise that resolves with the api result
	 * @fires resetBoardStart
	 * @fires resetBoardEnd
	 */
	mw.flow.dm.System.prototype.resetBoard = function ( sortBy ) {
		var system = this;

		this.emit( 'resetBoardStart' );

		return this.populateBoardFromApi( sortBy )
			.then( function ( result ) {
				// HACK: This parameter should go away. It is only
				// here so that we can initialize the board with
				// handlebars while we migrate things to ooui
				system.emit( 'resetBoardEnd', result );
				return result;
			} );
	};

	/**
	 * Get the page title
	 *
	 * @return {mw.Title} Page title
	 */
	mw.flow.dm.System.prototype.getPageTitle = function () {
		return this.pageTitle;
	};

	/**
	 * Get ToC post limit
	 *
	 * @return {number} ToC post limit
	 */
	mw.flow.dm.System.prototype.getToCPostsLimit = function () {
		return this.tocPostsLimit;
	};

	/**
	 * Get the board
	 *
	 * @return {mw.flow.dm.Board} Associated board
	 */
	mw.flow.dm.System.prototype.getBoard = function () {
		return this.board;
	};

	/**
	 * Get the number of topics that are rendered in each pagination change.
	 * When we scroll down or load a topic, this is the number that defines the
	 * batch of topics to be loaded and rendered.
	 *
	 * @return {number} Rendered topics
	 */
	mw.flow.dm.System.prototype.getRenderedTopics = function () {
		return this.renderedTopics;
	};
}( jQuery ) );
