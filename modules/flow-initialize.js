/*!
 * Runs Flow code, using methods in FlowUI.
 */
( function ( $ ) {
	// Pretend we got some data and run with it
	/*
	 * Now do stuff
	 * @todo not like this
	 */
	$( function () {
		var flowBoard,
			$component = $( '.flow-component' ),
			$board = $( '.flow-board' ),
			pageTitle = mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			initializer = new mw.flow.Initializer( {
				pageTitle: pageTitle,
				$component: $component,
				$board: $board
			} );
debugger;
		// Set component
		if ( !initializer.setComponentDom( $component ) ) {
			initializer.finishLoading();
			return;
		}

		// Initialize old system
		initializer.initOldComponent();
		// Initialize board
		if ( initializer.setBoardDom( $board ) ) {
			// Set up flowBoard
			flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $board );
			initializer.setBoardObject( flowBoard );

			// Initialize controller, which initializes the data model
			initializer.initializeFullBoard( {
				pageTitle: pageTitle,
				tocPostsLimit: 50,
				renderedTopics: $( '.flow-topic' ).length,
				boardId: $component.data( 'flow-id' ),
				defaultSort: $board.data( 'flow-sortby' )
			} )
			// HACK: Temporarily go by this promise. This entire process, though.
			// should be done through initializing widgets and then initializing
			// the controller, without having to wait on promises.
			.then( function () {
				// mw.flow.viewModel = initializer.getViewModel();

				if ( initializer.isUndoForm() ) {
					// Setup undo pages
					initializer.setupUndoPage();
				} else {
					// Replace the no-js editor if we are editing in a
					// new page
					initializer.replaceNoJSEditor( $( '.flow-edit-post-form' ) );

					// Create and replace UI widgets
					initializer.initializeWidgets();

					// Fall back to mw.flow.data, which was used until September 2015
					// NOTICE: This block must be after the initialization of the ui widgets so
					// they can populate themselves according to the events.
					// initializer.populateDataModel( mw.config.get( 'wgFlowData' ) || ( mw.flow && mw.flow.data ) );
				}
			} )
			.then( function () {
				// Show the board
				initializer.finishLoading();

				// Preload VisualEditor
				mw.flow.ui.EditorWidget.static.preload();
			} );
			// For reference and debugging
		} else {
			// Editing summary in a separate window. That has no
			// flow-board, but we should still replace the widget
			initializer.startEditTopicSummary(
				false,
				$component.data( 'flow-id' )
			);

			// Show the board
			initializer.finishLoading();

			// Preload VisualEditor
			mw.flow.ui.EditorWidget.static.preload();
		}
	} );
}( jQuery ) );
