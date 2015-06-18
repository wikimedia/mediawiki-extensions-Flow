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
		var descriptionWidget, navWidget, flowBoard, dmBoard,
			$component = $( '.flow-component' );

		// HACK: If there is no component, we are not on a flow
		// board at all, and there's no need to load anything.
		// This is especially true for tests, though we should
		// fix this by telling ResourceLoader to not load
		// flow-initialize at all on tests.
		if ( $component.length === 0 ) {
			return;
		}

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

		// HACK: These event handlers should be in the prospective widgets
		// they will move once we have Board UI and Topic UI widgets
		mw.flow.system.on( 'resetBoardStart', function () {
			$component.addClass( 'flow-api-inprogress' );
			// Before we reinitialize the board we have to detach
			// the navigation widget. This should not be necessary when
			// the board and topics are OOUI widgets
			navWidget.$element.detach();
		} );

		mw.flow.system.on( 'resetBoardEnd', function ( data ) {
			var $rendered;

			// HACK: Trim the data for only the rendered topics
			// We can't have the API request use a limit because when we
			// rebuild the board (when we reorder) we want the full 50
			// topics for the ToC widget. When the topic- and board-widget
			// are operational, we should render properly without using this
			// hack.
			data.topiclist.roots = data.topiclist.roots.splice( 0, mw.flow.system.getRenderedTopics() );

			$rendered = $(
				mw.flow.TemplateEngine.processTemplateGetFragment(
					'flow_block_loop',
					{ blocks: data }
				)
			).children();
			// Run this on a short timeout so that the other board handler in FlowBoardComponentLoadMoreFeatureMixin can run
			// TODO: Using a timeout doesn't seem like the right way to do this.
			setTimeout( function () {
				// Reinitialize the whole board with these nodes
				// flowBoard.reinitializeContainer( $rendered );
				$( '.flow-board' ).empty().append( $rendered[ 1 ] );

				// Since we've replaced the entire board, we need to reinitialize
				// it. This also takes away the original navWidget, so we need to
				// make sure it's reinitialized too
				flowBoard.reinitializeContainer( $rendered );
				$( '.flow-board-navigation' ).append( navWidget.$element );

				$component.removeClass( 'flow-api-inprogress' );
			}, 50 );

		} );

		/* UI Widgets */
		/* UI Widgets */
		dmBoard = mw.flow.system.getBoard();
		descriptionWidget = new mw.flow.ui.BoardDescriptionWidget( dmBoard.getDescription(), $( '.flow-board-header-content' ) );
		navWidget = new mw.flow.ui.NavigationWidget( dmBoard, {
			defaultSort: flowBoard.topicIdSort
		} );
		$( '.flow-board-navigation' ).append( navWidget.$element );
		$( '.flow-board-header-detail-view' )
			// HACK: We will need to find a good way of creating the OOUI js widget
			// out of the existing content, rather than rebuilding it. For the moment,
			// however, this is done to make sure the widget can display properly
//			.empty()
			.append( descriptionWidget.$element );

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

		// Load a topic from the ToC that isn't rendered on
		// the page yet. This will be gone once board, topic
		// and post are widgetized.
		navWidget.on( 'loadTopic', function ( topicId ) {
			flowBoard.jumpToTopic( topicId );
		} );
		navWidget.on( 'reorderTopics', function ( newOrder ) {
			flowBoard.topicIdSort = newOrder;
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
