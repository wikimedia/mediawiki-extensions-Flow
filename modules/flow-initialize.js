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
			// $board = $( '.flow-board' ),
			$overlay = $( '<div>' )
				.addClass( 'mw-sd-ui-overlay' )
				.append(  ),
			viewModel = new mw.sd.dm.ViewModel( {
				pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
				// Initialization data
				flowData: mw.config.get( 'wgFlowData' ) || ( mw.flow && mw.flow.data )
				// ...
			} ),
			controller = new mw.sd.Controller( viewModel ),
			// TODO: This widget should be initialized after the controller
			// and model are fully initialized and the data is in place
			wrapperWidget = new mw.sd.ui.ComponentWrapperWidget(
				viewModel,
				controller,
				{
					$element: $component,
					$overlay: $overlay
				}
			);

		$( 'body' ).append( $overlay );
return;

		// Set component
		// if ( !initializer.setComponentDom( $component ) ) {
		// 	initializer.finishLoading();
		// 	return;
		// }

		// Initialize old system
		// initializer.initOldComponent();
		// Initialize board
		if ( initializer.setBoardDom( $board ) ) {
			// Set up flowBoard
			// flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $board );
			// initializer.setBoardObject( flowBoard );

			// Initialize DM system and board
			initializer.initDataModel( {
				pageTitle: pageTitle,
				tocPostsLimit: 50,
				renderedTopics: $( '.flow-topic' ).length,
				boardId: $component.data( 'flow-id' ),
				defaultSort: $board.data( 'flow-sortby' )
			} );

			// For reference and debugging
			mw.flow.system = initializer.getDataModelSystem();

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
		} else {
			// Editing summary in a separate window. That has no
			// flow-board, but we should still replace the widget
			initializer.startEditTopicSummary(
				false,
				$component.data( 'flow-id' )
			);
		}

		// Show the board
		initializer.finishLoading();

		// Preload VisualEditor
		mw.flow.ui.EditorWidget.static.preload();
	} );
}( jQuery ) );
