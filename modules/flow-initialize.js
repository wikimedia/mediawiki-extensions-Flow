/*!
 * Runs Flow code, using methods in FlowUI.
 */

( function ( $ ) {
	// Pretend we got some data and run with it
	/*
	 * Now do stuff
	 * @todo not like this
	 */
	$( document ).ready( function () {
		var flowBoard, dmBoard,
			$component = $( '.flow-component' );

		// HACK: If there is no component, we are not on a flow
		// board at all, and there's no need to load anything.
		// This is especially true for tests, though we should
		// fix this by telling ResourceLoader to not load
		// flow-initialize at all on tests.
		if ( $component.length === 0 ) {
			return;
		}

		// HACK: Bridge between the new oouified description and the old system. We have to
		// do this before we're calling 'initComponent' so that initComponent will consider
		// the apiHandler actions we are reattaching
		$( '.flow-ui-boardDescriptionWidget' ).addClass( 'flow-board-header-detail-view' );
		$( '.flow-ui-boardDescriptionWidget-editButton' ).addClass( 'flow-board-header-nav' );
		$( '.flow-ui-boardDescriptionWidget-editButton a' )
			.data( 'flow-api-handler', 'activateEditHeader' )
			.data( 'flow-api-target', '< .flow-board-header' )
			.data( 'flow-interactive-handler', 'apiRequest' );
		$( '.flow-ui-boardDescriptionWidget-content' ).addClass( 'flow-board-header-content' );

		mw.flow.initComponent( $component );
		flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );

		// Load data model
		mw.flow.system = new mw.flow.dm.System( {
			pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			tocPostsLimit: 50,
			renderedTopics: $( '.flow-topic' ).length,
			boardId: $component.data( 'flow-id' ),
			defaultSort: $( '.flow-board' ).data( 'flow-sortby' )
		} );

		dmBoard = mw.flow.system.getBoard();
		dmBoard.on( 'add', function ( newItems ) {
			var i, len, item, itemId;

			// Items can be unstubbed anywhere, but they should only be added at the
			// end (due to TOC infinite scroll or initial population).
			for ( i = 0, len = newItems.length; i < len; i++ ) {
				item = newItems[i];
				itemId = item.getId();

				flowBoard.orderedTopicIds.push( itemId );
				flowBoard.topicTitlesById[ itemId ] = item.getContent();
				flowBoard.updateTimestampsByTopicId[ itemId ] = item.getLastUpdate();
			}
			flowBoard.sortTopicIds( flowBoard );
		} );

		// E.g. on topic re-order, before re-population.
		dmBoard.on( 'clear', function () {
			flowBoard.orderedTopicIds = [];
			flowBoard.topicTitlesById = {};
		} );
		// We shouldn't have to worry about 'remove', since by the time we have filtering,
		// orderedTopicIds should be gone.

		// Initialize the old system to accept the default
		// 'newest' order for the topic order widget
		// Get the current default sort
		flowBoard.topicIdSort = mw.flow.system.getBoard().getSortOrder();

		// HACK: On load more, populate the board dm
		flowBoard.on( 'loadmore', function ( topiclist ) {
			// Add to the DM board
			mw.flow.system.populateBoardTopicsFromJson( topiclist );
		} );

		// HACK: Update the DM when topic is refreshed
		flowBoard.on( 'refreshTopic', function ( workflowId, topicData ) {
			var revisionId, revision,
				topic = dmBoard.getItemById( workflowId ),
				data = topicData.flow['view-topic'].result.topic;

			if ( !topic ) {
				// New topic
				mw.flow.system.populateBoardTopicsFromJson( data, 0 );
			} else {
				// Topic already exists. Repopulate
				revisionId = data.posts[ workflowId ];
				revision = data.revisions[ revisionId ];
				topic.populate( revision );
			}
		} );

		if ( mw.flow.data && mw.flow.data.blocks.topiclist && mw.flow.data.blocks.header ) {
			// Populate the rendered topics
			mw.flow.system.populateBoardTopicsFromJson( mw.flow.data.blocks.topiclist );
			// Populate header
			mw.flow.system.populateBoardDescriptionFromJson( mw.flow.data.blocks.header );
			// Populate the ToC topics
			mw.flow.system.populateBoardTopicsFromJson( mw.flow.data.toc );
		} else {
			mw.flow.system.populateBoardFromApi();
		}
	} );
}( jQuery ) );
