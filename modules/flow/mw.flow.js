mw.flow = mw.flow || {};

mw.flow.totalInstanceCount = 0;
/**
 * Initialize Flow components into the data model
 *
 * @param {jQuery} [$container] Flow board container.
 */
mw.flow.Initialize = function mwFlowInitialize( $container ) {
	var board, boardId,
//		uri = new mw.Uri( window.location.href ),
		uid = String( window.location.hash.match( /[0-9a-z]{16,19}$/i ) || '' );

	$container = $container || $( '.flow-component' );

	boardId = $container.data( 'flowId' );
	if ( !boardId ) {
		// We probably don't need this for a Flow Board, as there is
		// only one and it always exists where it should, even when it's
		// empty.

		// Generate an ID for this component
		boardId = 'flow-generated-' + mw.flow.totalInstanceCount++;
		$container.data( 'flowId', boardId );
	}

	// TODO: Migrate the logic (this should be in the ui widget
	// but the logic of whether we need to see the newer posts
	// or only the highlighted post should probably be here)

	// if ( uri.query.fromnotif ) {
	// 	_flowHighlightPost( $container, uid, 'newer' );
	// } else {
	// 	_flowHighlightPost( $container, uid );
	// }
	board = new mw.flow.dm.Board( {
		id: boardId,
		pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ) || '',
		tocPostsLimit: 20,
		visibleItems: 10,
		// Handle URL parameters
		highlight: uid || null
	} );
	// Initialize
	board.initialize()
		.then( function () {
			// Debugging
			console.log( board.getItems() );
		} );
};
