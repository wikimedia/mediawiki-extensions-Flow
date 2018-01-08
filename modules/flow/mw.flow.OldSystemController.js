/**
 * A controller for communicating with the "old" Flow frontend
 *
 * @class
 *
 * @constructor
 * @param {Object} [config] Configuration options
 */
mw.flow.OldSystemController = function MwFlowOldSystemController( viewModel, config ) {
	this.viewModel = viewModel;
	this.boardComponent = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );
	this.$component = $( '.flow-component' );
};

/**
 * Update the flowBoardComponent to reflect the view model state
 */
mw.flow.OldSystemController.prototype.update = function () {
	var topicTitlesById = {},
		timestampsByTopicId = {},
		orderedTopicIds = [];

	// TODO: Assess whether this is appropriate for boards that are very long
	// and for users who scroll through the infinite-scroll bringing more and
	// more topics to the memory

	// Collect the data
	this.viewModel.getTopics().forEach( function ( topicModel ) {
		topicTitlesById[ topicModel.getId() ] = topicModel.getContent();
		timestampsByTopicId[ topicModel.getId() ] = topicModel.getLastUpdate();
		orderedTopicIds.push( topicModel.getId() );
	} );

	// Update the data
	this.boardComponent.topicTitlesById = topicTitlesById;
	this.boardComponent.updateTimestampsByTopicId = timestampsByTopicId;
	this.boardComponent.orderedTopicIds = orderedTopicIds;
	this.boardComponent.topicIdSort = this.viewModel.getSortOrder();
	// Update sort
	this.boardComponent.sortTopicIds();
};

mw.flow.OldSystemController.prototype.setPending = function ( isPending ) {
	this.getjQueryComponent().toggleClass( 'flow-api-inprogress', isPending );
};

// TODO: These shouldn't be necessary
mw.flow.OldSystemController.prototype.getBoardComponent = function () {
	return this.boardComponent;
};

mw.flow.OldSystemController.prototype.getjQueryComponent = function () {
	return this.$component;
};
