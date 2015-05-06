/**
 * Flow Board
 *
 * @constructor
 *
 * @extends mw.flow.dm.Item
 * @mixins mw.flow.dm.List
 *
 * @param {Object} data API data to build board with
 * @param {string} data.id Board Id
 * @param {mw.Title} data.pageTitle Current page title
 * @param {string} [data.highlight] An Id of a highlighted post, if exists
 * @param {Object} [config] Configuration options
 * @cfg {number} [tocPostsLimit] Post count for the table of contents. This is also
 *  used as the number of posts to fetch when more posts are requested for the table
 *  of contents. Defaults to 10
 * @cfg {number} [visibleItems] Number of visible posts on the page. This is also
 *  used as the number of posts to fetch when more posts are requested on scrolling
 *  the page. Defaults to the tocPostsLimit
 */
mw.flow.dm.Board = function mwFlowDmBoard( data, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.dm.Board.super.call( this, config );

	// Mixing constructor
	mw.flow.dm.List.call( this, config );

	// TODO: Fill this stuff in properly
	this.id = data.id;
	this.pageTitle = data.pageTitle;
	this.highlight = data.highlight || null;

	// Configuration options
	this.tocPostsLimit = config.tocPostsLimit || 10;
	this.visibleItems = config.visibleItems || this.tocPostsLimit;

	// Set up the API queue
	this.queue = new mw.flow.dm.APIResultsQueue( {
		limit: this.tocPostsLimit,
		threshhold: 5
	} );
	this.queue.addProvider(
		new mw.flow.dm.APITopicsProvider(
			this.getPageTitle().getPrefixedDb(),
			{
				fetchLimit: this.tocPostsLimit
			}
		)
	);
};

/* Initialization */

OO.inheritClass( mw.flow.dm.Board, mw.flow.dm.Item );
OO.mixinClass( mw.flow.dm.Board, mw.flow.dm.List );

/**
 * Initialize the board and fetch topics
 *
 * TODO: We should at some point replace this to first go over the
 * available DOM and get the initial topics from there, saving an
 * API call.
 *
 * @return {jQuery.Promise} Promise that resolves when the board finished initializing
 */
mw.flow.dm.Board.prototype.initialize = function () {
	return this.fetchTopics();
};
/**
 * Fetch topics from the API queue
 *
 * @return {jQuery.Promise} Promise that resolves when the topics finished
 *  populating
 */
mw.flow.dm.Board.prototype.fetchTopics = function ( numVisibleTopics ) {
	numVisibleTopics = numVisibleTopics === undefined ? this.tocPostsLimit : numVisibleTopics;

	return this.queue.get()
		.then( this.addTopicsFromApi.bind( this, numVisibleTopics ) );
};

/**
 * Add topics from API response
 *
 * @param {number} numVisibleTopics Number of visible topics; those topics will be
 *  fetched so we have all the information to display. The rest will only receive
 *  basic information that we need to display in the ToC, and they will be considered
 *  'stubs' until their information is fetched as well.
 * @param {Object} result API result
 * @return {jQuery.Promise} Promise that resolves when all visible topics have finished
 * fetching the data they require to be displayed.
 */
mw.flow.dm.Board.prototype.addTopicsFromApi = function ( numVisibleTopics, result ) {
	var topicId, topic,
		count = 0,
		promises = [],
		topics = [],
		topicList = result[ 0 ];

	for ( topicId in topicList ) {
		topic = new mw.flow.dm.Topic( topicId, topicList[topicId] );
		if ( count < numVisibleTopics ) {
			// Get full information for the topic, since
			// it is visible
			promises.push( topic.fetchInformation() );
		}
		count++;
		topics.push( topic );
	}
	// Add topics
	this.addItems( topics );

	return $.when.apply( this, promises );
};

/**
 * Get board UUID
 * @return {string} UUID
 */
mw.flow.dm.Board.prototype.getId = function () {
	return this.id;
};

/**
 * Get page title
 * @return {mw.Title} Page title
 */
mw.flow.dm.Board.prototype.getPageTitle = function () {
	return this.pageTitle;
};

/**
 * Get the maximum post limit to fetch for the ToC
 *
 * @return {number} Fetching limit
 */
mw.flow.dm.Board.prototype.getToCPostLimit = function () {
	return this.tocPostsLimit;
};
