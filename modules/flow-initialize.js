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
		var flowBoard,
			$component = $( '.flow-component' ),
			$board = $( '.flow-board' ),
			pageTitle = mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			initializer = new mw.flow.Initializer( {
				$component: $component,
				$board: $board
			} );

		// Initialize component
		if ( !initializer.setComponentDom( $component ) ) {
			initializer.finishLoading();
			return;
		}

		// Initializer old system
		initializer.initOldComponent( $component );

		// Initialize board
		if ( initializer.setBoardDom( $board ) ) {
			// Set up flowBoard
			flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $board );
			initializer.setBoardObject( flowBoard );

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

			// Special board views (undo)
			if ( $( 'form[data-module="topic"]' ) ) {
				this.replaceEditorInUndoEditPost( $( 'form[data-module="topic"]' ) );
			} else if ( $( 'form[data-module="header"]' ) ) {
				this.replaceEditorInUndoHeaderPost( $( 'form[data-module="header"]' ) );
			} else {
				// Replace the no-js editor if we are editing in a
				// new page
				this.replaceNoJSEditor( $( '.flow-edit-post-form' ) );

				// Create and replace UI widgets
				initializer.initializeWidgets();

				// Fall back to mw.flow.data, which was used until September 2015
				// NOTICE: This block must be after the initialization of the ui widgets so
				// they can populate themselves according to the events.
				initializer.populateDataModel( mw.config.get( 'wgFlowData' ) || ( mw.flow && mw.flow.data ) );
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
	} );
}( jQuery ) );
