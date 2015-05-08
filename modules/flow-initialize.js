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
		var navWidget;

		mw.flow.initComponent( $( '.flow-component' ) );

		// Load data model
		mw.flow.system = new mw.flow.dm.System( {
			pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			tocPostsLimit: 50,
			renderedTopics: $( '.flow-topic' ).length,
			boardId: $( '.flow-component' ).data( 'flow-id' )
		} );
		mw.flow.system.populateBoardFromApi();

		// HACK: We need to populate the old code when the
		// new is populated
		mw.flow.system.on( 'populate', function ( topicTitlesById ) {
			var flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );
			flowBoard.topicTitlesById = $.extend( {}, flowBoard.topicTitlesById, topicTitlesById );
		} );

		mw.flow.system.on( 'resetBoardEnd', function ( data ) {
			var flowBoard, $rendered;

			// HACK: Trim the data for only the rendered topics
			// We can't have the API request use a limit because when we
			// rebuild the board (when we reorder) we want the full 50
			// topics for the ToC widget. When the topic- and board-widget
			// are operational, we should render properly without using this
			// hack.
			data.topiclist.roots = data.topiclist.roots.splice( 0, mw.flow.system.getRenderedTopics() );

			flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );
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

				// Since we've replaced the entire boardd, we need to reinitialize
				// it. This also takes away the original navWidget, so we need to
				// make sure it's reinitialized too
				flowBoard.reinitializeContainer( $rendered );
				navWidget.$element.appendTo( $( '.flow-board-navigation' ) );

				$( '.flow-component' ).removeClass( 'flow-api-inprogress' );
			}, 50 );

		} );

		/* UI Widets */
		navWidget = new mw.flow.ui.NavigationWidget( mw.flow.system.getBoard() );

		$( '.flow-board-navigation' ).append( navWidget.$element );

		// Load a topic from the ToC that isn't rendered on
		// the page yet. This will be gone once board, topic
		// and post are widgetized.
		navWidget.on( 'loadTopic', function ( topicId ) {
			var flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );
			flowBoard.jumpToTopic( topicId );
		} );

		// HACK: These event handlers should be in the prospective widgets
		// they will move once we have Board UI and Topic UI widgets
		mw.flow.system.on( 'resetBoardStart', function () {
			$( '.flow-component' ).addClass( 'flow-api-inprogress' );
			// Before we reinitialize the board we have to detach
			// the navigation widget. This should not be necessary when
			// the board and topics are OOUI widgets
			navWidget.$element.detach();
		} );
	} );
}( jQuery ) );
