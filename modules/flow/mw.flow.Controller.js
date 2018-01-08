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

mw.flow.Controller.prototype.initialize = function ( blob ) {
	var controller = this;

	this.oldSystem.setPending( true );
	if ( dataBlob && dataBlob.blocks ) {
		promise = this.controller.resetBoardDataFromBlob(
			dataBlob.blocks, dataBlob.toc
		);
	} else {
		promise = this.controller.resetBoardDataFromApi();
	}

	// TODO: Continue after board is filled
	// When we have a proper widget delegation, we can use
	// promise.then( ... ) to toggle the 'pending' state on
	// the board instead of having to rely on 'resetBoardStart'
	// and 'resetBoardEnd' events
	// see mw.flow.Initializer line 281
	promise
		.then( function ( result ) {
			// Render with TemplateEngine
			// ...
		} )
		.always( function () {
			controller.oldSystem.setPending( false );
		} );
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

	// Actually sort

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
		// TODO: Make this actually work
		// this.viewModel.populateBoardTopicsFromJson( toc );
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
			.then( function ( result ) {
				controller.viewModel.addTopicsFromTopiclistAPI( result.topicList );
				controller.oldSystem.update();

				// HACK: This return value should go away. It is only
				// here so that we can initialize the board with
				// handlebars while we migrate things to ooui
				return result;
			} ),
		this.api.getDescription()
			.then( this.viewModel.updateDescriptionFromAPI.bind( this.viewModel ) )
	];

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
