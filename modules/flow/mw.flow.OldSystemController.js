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
	this.template = mw.flow.TemplateEngine;
	this.$component = $( '.flow-component' );
	this.setBoardDom( $( '.flow-board' ) );
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
	this.boardComponent.sortTopicIds( this.boardComponent );
};

mw.flow.OldSystemController.prototype.setPending = function ( isPending ) {
	this.getjQueryComponent().toggleClass( 'flow-api-inprogress', isPending );
};

mw.flow.OldSystemController.prototype.setBoardDom = function ( $dom ) {
	this.$board = $dom;
}
/**
 * Render the board based on given data
 *
 * @param {Object} data API data for topiclist
 */
mw.flow.OldSystemController.prototype.renderBoard = function ( data ) {
	var $rendered,
		self = this;

	// populateBoardFromApi uses the larger TOC limit so the TOC can
	// be fully populated on re-sort.  To avoid two requests
	// (TOC and full topics) with different limits, we do a single
	// full-topic request with that limit.
	//
	// However, this is inconsistent with the number of topics
	// we show at page load.
	//
	// This could be addressed by either showing the larger number of
	// topics on page load, doing two separate requests (might still be
	// faster considering the backend doesn't have to get full data for
	// many topics), or filtering the topic list on render.
	//
	// The latter (filter on render) could be done when the topic- and
	// board-widget are operational using some sort of computed subset
	// data model.
	$rendered = $(
		this.template.processTemplateGetFragment(
			'flow_block_loop',
			{ blocks: data }
		)
	).children();
	// Run this on a short timeout so that the other board handler in FlowBoardComponentLoadMoreFeatureMixin can run
	// TODO: Using a timeout doesn't seem like the right way to do this.
	setTimeout( function () {
		var boardEl = $rendered[ 1 ];

		// Since we've replaced the entire board, we need to reinitialize
		// it. This also takes away the original navWidget, so we need to
		// make sure it's reinitialized too
		self.boardComponent.reinitializeContainer( $rendered );
		self.setBoardDom( $( boardEl ) );

		// TODO: This should go in the controller
		// self.replaceReplyForms( self.$board );
		// self.setupNewTopicWidget( $( 'form.flow-newtopic-form' ) );
		// $( '.flow-board-navigation' ).append( self.navWidget.$element );

		self.$component.removeClass( 'flow-api-inprogress' );
	}, 50 );
};

// TODO: These shouldn't be necessary
mw.flow.OldSystemController.prototype.getBoardComponent = function () {
	return this.boardComponent;
};

mw.flow.OldSystemController.prototype.getjQueryComponent = function () {
	return this.$component;
};
