/**
 * A controller for Flow frontend
 *
 * @class
 *
 * @constructor
 * @param {Object} [config] Configuration options
 */
mw.flow.Controller = function MwFlowController( viewModel, config ) {
	this.viewModel = viewModel;
	this.oldSystem = new mw.flow.OldSystemController( viewModel );

	this.api = new mw.flow.dm.APIHandler( this.viewModel.getPageTitle().getPrefixedDb() );
	this.fetchPromise = null;
};
OO.initClass( mw.flow.Controller );

/**
 * Initialize the controller and data models
 *
 * @param  {[type]} blob Initial data blob
 * @return {[type]}      [description]
 */
mw.flow.Controller.prototype.initializeBoard = function ( blob ) {
	var controller = this;

	// TODO: Separate the initialization for a board/topic
	// vs editing summary/description

	this.setPending( true );
	return this.resetBoardDataFromBlob( blob.blocks, blob.toc )
		// Continue after board is filled
		.then( function () {
debugger;
			controller.setPending( false );
		} );
};

mw.flow.Controller.prototype.setPending = function ( isPending ) {
	this.oldSystem.setPending( isPending );
};

/**
 * Set sort order for the board
 *
 * @param {string} [sort='newest'] Sort order; defaults in the model to 'newest'
 */
mw.flow.Controller.prototype.setSortOrder = function ( sort ) {
	this.viewModel.setSortOrder( sort );
	// Update the old system
	this.oldSystem.update();
};

/**
 * Reload the board based on a new sort order for the board
 *
 * @param {string} [sort='newest'] Sort order; defaults in the model to 'newest'
 */
mw.flow.Controller.prototype.resortBoard = function ( sort ) {
	var controller = this;

	this.setSortOrder( sort );

	// Re-render the board with the new sorting
	this.setPending( true );
	return this.resetBoardDataFromApi( this.viewModel.getSortOrder() )
		.then( function ( result ) {
			// Render the board
			controller.oldSystem.renderBoard( result );
		} )
		.always( this.setPending.bind( this, false ) );
};

/**
 * Repopulate the board by the given object
 *
 * @return {jQuery.Promise} Promise that is resolved when the
 *  board is populated
 */
mw.flow.Controller.prototype.resetBoardDataFromBlob = function ( blocks, toc ) {
	var deferred = $.Deferred();
	// this.viewModel.resetBoard();

	// Populate the rendered topics or topic (if we are in a single-topic view)
	this.viewModel.addTopicsFromTopiclistAPI(
		blocks.topiclist || blocks.topic
	);
	this.oldSystem.update();

	// Populate header
	this.viewModel.updateDescriptionFromAPI( blocks.header || {} );

	// Populate ToC
	if ( toc ) {
		// These boards are only filled up with general information
		// but are not being rendered
		this.viewModel.addTopicsFromTopiclistAPI( toc );
	}

	// Mock promise so we can have the same interface between resetting
	// the board through given data or through API call
	deferred.resolve();
	return deferred.promise();
};

/**
 * Populate the board by querying the Api
 *
 * @return {jQuery.Promise} Promise that is resolved when the
 *  board is populated
 */
mw.flow.Controller.prototype.resetBoardDataFromApi = function () {
	var controller = this,
		requests = [];

	// this.viewModel.resetBoard();

	requests = [
		this.api.getTopicList(
			this.viewModel.getSortOrder(),
			{ offset: 0 }
		)
			.then( function ( topiclist ) {
				controller.viewModel.addTopicsFromTopiclistAPI( topiclist );
				controller.oldSystem.update();

				// HACK: This return value should go away. It is only
				// here so that we can initialize the board with
				// handlebars while we migrate things to ooui
				return topiclist;
			} ),
		this.api.getDescription()
			.then( this.viewModel.updateDescriptionFromAPI.bind( this.viewModel ) )
	];

	// TODO: Update the TOC

	return $.when.apply( $, requests )
		// HACK: This should go away, see hack comment above
		.then( function ( resultTopicList ) {
			return resultTopicList;
		} );

};

/**
 * Fetch more topics from the topiclist API, if more topics exist.
 * The method also preserves the state of whether more topics exist
 * to know whether to send a request to the API at all.
 *
 * @return {jQuery.Promise} Promise that is resolved with whether
 *  more topics exist or not.
 */
mw.flow.Controller.prototype.fetchMoreTopics = function () {
	var controller = this,
		sortOrder = this.viewModel.getSortOrder();

	if ( this.fetchPromise ) {
		return this.fetchPromise;
	}

	if ( !this.moreTopicsExistInApi ) {
		return $.Deferred().resolve( false ).promise();
	}

	this.fetchPromise = this.api.getTopicList(
		sortOrder,
		{
			offset: sortOrder === 'newest' ?
				this.board.getOffsetId() :
				this.board.getOffset(),
			toconly: true
		} )
		.then( function ( topicList ) {
			controller.viewModel.addTopicsFromTopiclistAPI( topicList );
			// viewModel.toggleMoreTopicsInApi( ... );
			controller.oldSystem.update();
		} )
		.always( function () {
			viewModel.fetchPromise = null;
		} );
	return this.fetchPromise;
};
