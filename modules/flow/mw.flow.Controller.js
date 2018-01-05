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
	this.flowBoardComponent = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $board );

	this.api = new mw.flow.dm.APIHandler( this.viewModel.getPageTitle().getPrefixedDb() );
	this.fetchPromise = null;
};
OO.initClass( mw.flow.Controller );

mw.flow.Controller.prototype.initialize = function () {
	// Get the initial JSON blob
	// Create and populate the topics from the blob
};
//
// /**
//  * Create new topic objects from a topiclist api response and add them to the board model
//  *
//  * @private
//  * @param {Object} topiclist API data for topiclist
//  */
// mw.flow.Controller.static._createTopicsFromTopiclist = function ( topiclist ) {
// 	var i, len, topicId, revisionId,
// 		topics = [];
//
// 	for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
// 		topicId = topiclist.roots[ i ];
// 		revisionId = topiclist.posts[ topicId ] && topiclist.posts[ topicId ][ 0 ];
// 		topics.push(
// 			new mw.flow.dm.Topic(
// 				topicId,
// 				topiclist.revisions[ revisionId ]
// 			)
// 		);
// 	}
//
// 	this.board.addItems( topics );
// };
