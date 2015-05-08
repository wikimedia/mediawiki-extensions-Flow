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
		var topicListWidget,
			tocLabel = new OO.ui.ButtonWidget( {
				label: 'Browse topics',
				framed: false
			} );

		mw.flow.initComponent( $( '.flow-component' ) );

		// Load data model
		mw.flow.system = new mw.flow.dm.System( {
			pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			tocPostLimit: 20,
			boardId: $( '.flow-component' ).data( 'flow-id' )
		} );
		mw.flow.system.populateBoardFromApi();

		// Replace TOC with topic list widget
		topicListWidget = new mw.flow.ui.TopicMenuSelectWidget( mw.flow.system );
		$( '.flow-board-header-menu' ).replaceWith( topicListWidget.$element );
//		$( 'body' ).prepend( topicListWidget.$element );
		$( '.flow-board-navigator-first' ).replaceWith( tocLabel.$element );
		tocLabel.on( 'click', function () {
			topicListWidget.toggle();
		} )
	} );
}( jQuery ) );
