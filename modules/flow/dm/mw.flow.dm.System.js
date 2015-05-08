/**
 * Flow initialization system
 *
 * @param {Object} [config] Configuration options
 * @cfg {string} [id] Board Id
 * @cfg {mw.Title} [pageTitle] Current page title
 * @cfg {number} [tocPostsLimit] Post count for the table of contents. This is also
 *  used as the number of posts to fetch when more posts are requested for the table
 *  of contents. Defaults to 10
 * @cfg {number} [visibleItems] Number of visible posts on the page. This is also
 *  used as the number of posts to fetch when more posts are requested on scrolling
 *  the page. Defaults to the tocPostsLimit
 */
mw.flow.dm.System = function MwFlowDmSystem( config ) {

	config = config || {};

	// Mixin constructor
	OO.EventEmitter.call( this );

	this.pageTitle =  config.pageTitle || mw.Title.newFromText( mw.config.get( 'wgPageName' ) );
	this.tocPostsLimit = config.tocPostsLimit || 10;
	this.renderedTopics = config.renderedTopics || 0;

	this.boardId = config.boardId || 'flow-generated';
	this.offsetId = config.offsetId || null;

	// Initialize board
	this.board = new mw.flow.dm.Board( {
		id: this.boardId,
		pageTitle: this.getPageTitle(),
		isDeleted: mw.config.get( 'wgArticleId' ) === 0
	} );
};

/* Setup */

OO.initClass( mw.flow.dm.System );
OO.mixinClass( mw.flow.dm.System, OO.EventEmitter );

/* Methods */

/**
 * Populate the board by querying the Api
 *
 * @param {string} [sortBy] A sort option, either 'newest' or 'updated' or unused
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
			vtllimit: this.getToCPostLimit()
		};

	if ( sortBy ) {
		apiParams.vtlsortby = sortBy;
		apiParams.vtlsavesortby = 1;
	}

	return ( new mw.Api() ).get( apiParams )
		.then( function ( data ) {
			var result = data.flow[ 'view-topiclist' ].result;
			system.populateBoardTopicsFromJson( result.topiclist );
			// HACK: This return value should go away. It is only
			// here so that we can initialize the board with
			// handlebars while we migrate things to ooui
			return result;
			// return ( new mw.Api() ).get( {
			// 	action: 'flow',
			// 	submodule: 'view-header',
			// 	page: system.getPageTitle().getPrefixedDb()
			// } );
		} );
	// TODO: Add MW Posts and then add the header as a post
	/*	.then( function ( result ) {
			var headerData = OO.getProp( data.flow, 'view-header', 'result', 'header' );
			header = new mw.flow.dm.Post( headerData );
			system.getBoard().setDescription( header );
		} );
	*/
};

/**
 * Populate the board by fetching data from the Dom
 *
 * @param {jQuery} [$container] The container for the flow component
 */
mw.flow.dm.System.prototype.populateBoardFromDom = function ( $container ) {
	var boardId;

	this.$container = $container || $( '.flow-component' );

	// TODO: Migrate the logic (this should be in the ui widget
	// but the logic of whether we need to see the newer posts
	// or only the highlighted post should probably be here)

	// if ( uri.query.fromnotif ) {
	// 	_flowHighlightPost( $container, uid, 'newer' );
	// } else {
	// 	_flowHighlightPost( $container, uid );
	// }
	boardId = $container.data( 'flowId' );
	if ( !boardId ) {
		// Generate an ID for this component
		boardId = 'flow-generated-board';
		$container.data( 'flowId', boardId );
	}
};

/**
 * Add topics to the board from a JSON object.
 * This is populating the actual board data from an object sent
 * either directly or through one of the DOM or API fetching methods.
 *
 * @param {Object} topiclist API object for the board and topic list
 */
mw.flow.dm.System.prototype.populateBoardTopicsFromJson = function ( topiclist ) {
	var i, len, postId, topic,
		topicTitlesById = {},
		count = 0,
		topics = [];

	for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
		// The content of the topic is its first post
		postId = topiclist.posts[ topiclist.roots[i] ];
		topic = new mw.flow.dm.Topic( topiclist.roots[i], topiclist.revisions[ postId ] );
		// TODO: We need a better way to calculate and set up which
		// topics are visible on the page (and are 'unstub')
		// This will be easier when there are topic widgets that
		// are rendered and can, on their side, set the status of unstub.
		if ( count < this.renderedTopics ) {
			topic.unStub();
		}
		count++;
		topics.push( topic );

		// HACK: While we use both systems (new ooui and old flow-event system)
		// We need to make sure that the old system is updated too
		topicTitlesById[ topiclist.roots[i] ] = topic.getRawContent();
	}
	// Add to board
	this.getBoard().addItems( topics );
	this.emit( 'populate', topicTitlesById );
};

/**
 * Reset the board per the new sorting
 *
 * @param {string} sortBy Sorting option 'newest' or 'updated'
 * @return {jQuery.Promise} Promise that resolves with the api result
 * @fires resetBoardStart
 * @fires resetBoardEnd
 */
mw.flow.dm.System.prototype.resetBoard = function ( sortBy ) {
	var system = this;

	sortBy = sortBy || 'newest';

	this.emit( 'resetBoardStart' );

	this.getBoard().clearItems();
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
 * @return {string} Page title
 */
mw.flow.dm.System.prototype.getPageTitle = function () {
	return this.pageTitle;
};

/**
 * Get ToC post limit
 *
 * @return {number} ToC post limit
 */
mw.flow.dm.System.prototype.getToCPostLimit = function () {
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
