/**
 * Flow initialization
 *
 * @param {Object} [config] Configuration options
 * @cfg {string} [id] Board Id
 * @cfg {mw.Title} [pageTitle] Current page title
 * @cfg {string} [highlight] An Id of a highlighted post, if exists
 * @cfg {number} [tocPostsLimit] Post count for the table of contents. This is also
 *  used as the number of posts to fetch when more posts are requested for the table
 *  of contents. Defaults to 10
 * @cfg {number} [visibleItems] Number of visible posts on the page. This is also
 *  used as the number of posts to fetch when more posts are requested on scrolling
 *  the page. Defaults to the tocPostsLimit
 */
mw.flow.dm.System = function MwFlowDmSystem( config ) {

	config = config || {};

	this.pageTitle =  config.pageTitle || mw.Title.newFromText( mw.config.get( 'wgPageName' ) );
	this.tocPostsLimit = config.tocPostsLimit || 10;
//	this.highlight = config.highlight;

	this.boardId = config.boardId || 'flow-generated';
	this.offsetId = config.offsetId || null;

	// Initialize API queue
	this.topicQueue = new mw.flow.dm.APIResultsQueue( {
		limit: this.getToCPostLimit(),
		threshhold: 5
	} );
	this.topicQueue.addProvider(
		new mw.flow.dm.APITopicsProvider(
			this.getPageTitle().getPrefixedDb(),
			mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' ) + '/api.php',
			{
				fetchLimit: this.getToCPostLimit()
			}
		)
	);

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


mw.flow.dm.System.prototype.populateBoardFromApi = function () {
	var system = this;

	return ( new mw.Api() ).get( {
		action: 'flow',
		submodule: 'view-topiclist',
		page: this.getPageTitle().getPrefixedDb(),
		vtloffset: 0,
		vtllimit: this.getToCPostLimit()
	} )
		.then( function ( data ) {
			system.populateBoardFromJson(
				OO.getProp( data.flow, 'view-topiclist', 'result', 'topiclist' )
			);
			return ( new mw.Api() ).get( {
				action: 'flow',
				submodule: 'view-header',
				page: system.getPageTitle().getPrefixedDb()
			} );
		} );
//		.then( function ( result ) {
//			var headerData = OO.getProp( data.flow, 'view-header', 'result', 'header' );
//			// TODO: Add MW Posts and then add the header as a post
//			header = new mw.flow.dm.Post( headerData );
//			system.getBoard().setDescription( header );
//		} );
};

/* Methods */

/*
mw.flow.dm.System.prototype.populateBoardFromDom = function ( $container ) {
	this.$container = $container || $( '.flow-component' );

	$container = $container || $( '.flow-component' );

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
		// We probably don't need this for a Flow Board, as there is
		// only one and it always exists where it should, even when it's
		// empty.

		// Generate an ID for this component
		boardId = 'flow-generated-' + mw.flow.totalInstanceCount++;
		$container.data( 'flowId', boardId );
	}
};*/

mw.flow.dm.System.prototype.populateBoardFromJson = function ( topiclist ) {
	var i, len, postId, topic,
		topics = [],
		roots = topiclist.roots;

	for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
		postId = topiclist.posts[ topiclist.roots[i] ];
		topic = new mw.flow.dm.Topic( topiclist.roots[i], topiclist.revisions[ postId ] );
		topic.unStub();
		topics.push( topic );
	}
	// Add to board
	this.getBoard().addItems( topics );

	// Set offset Id
	this.setOffsetId( roots[ roots.length - 1 ] );
};

mw.flow.dm.System.prototype.getPageTitle = function () {
	return this.pageTitle;
};

mw.flow.dm.System.prototype.getToCPostLimit = function () {
	return this.tocPostsLimit;
};

mw.flow.dm.System.prototype.getTopicQueue = function () {
	return this.topicQueue;
};

mw.flow.dm.System.prototype.getBoard = function () {
	return this.board;
};

mw.flow.dm.System.prototype.getOffsetId = function () {
	return this.offsetId;
};
mw.flow.dm.System.prototype.setOffsetId = function ( offsetId ) {
	this.offsetId = offsetId;
};
