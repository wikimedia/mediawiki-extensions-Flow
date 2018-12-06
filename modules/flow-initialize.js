/*!
 * Runs Flow code, using methods in FlowUI.
 */
( function () {
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

		// Runs a Topic tour for first users
		if ( mw.config.get( 'wgNamespaceNumber' ) === 2600 ) {
			if ( mw.config.get( 'wgUserName' ) && !mw.user.options.get( 'flow-guidedtour-topic-seen' ) ) {
				document.addEventListener( 'flowReady', function () {
					var api = new mw.Api();
					api.saveOption( 'flow-guidedtour-topic-seen', true );
					// Starts topic tour
					mw.guidedTour.launcher.launchTour( 'flowTopic' );

				} );
			} else if ( !mw.config.get( 'wgUserName' ) && !$.cookie( 'flow-guidedtour-topic-seen' ) ) {
				document.addEventListener( 'flowReady', function () {
					// sets a cookie for IP users which lasts for one year
					$.cookie( 'flow-guidedtour-topic-seen', true, { expires: 365 } );
					// Starts topic tour
					mw.guidedTour.launcher.launchTour( 'flowTopic' );

				} );
			}
		}

		// Show the board
		initializer.finishLoading();

		// Preload VisualEditor
		mw.flow.ui.EditorWidget.static.preload();
	} );
}() );
