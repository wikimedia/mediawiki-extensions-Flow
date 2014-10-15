/*!
 * Implements element on-load callbacks for FlowBoardComponent
 */

( function ( $, mw ) {
	/**
	 * Binds element load handlers for FlowBoardComponent
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentLoadEventsMixin( $container ) {
		this.bindNodeHandlers( FlowBoardComponentLoadEventsMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentLoadEventsMixin );

	FlowBoardComponentLoadEventsMixin.UI = {
		events: {
			loadHandlers: {}
		}
	};

	//
	// On element-load handlers
	//

	/**
	 * Replaces $time with a new time element generated by TemplateEngine
	 * @param {jQuery} $time
	 */
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.timestamp = function ( $time ) {
		$time.replaceWith(
			mw.flow.TemplateEngine.callHelper(
				'timestamp',
				parseInt( $time.attr( 'datetime' ), 10) * 1000,
				$time.data( 'time-ago-only' ) === "1"
			)
		);
	};

	/**
	 * Stores the load more button for use with infinite scroll.
	 * @param {jQuery} $button
	 */
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.loadMore = function ( $button ) {
		var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $button );

		if ( !flowBoard.$loadMoreNodes ) {
			// Create a new $loadMoreNodes list
			flowBoard.$loadMoreNodes = $();
		} else {
			// Remove any loadMore nodes that are no longer in the body
			flowBoard.$loadMoreNodes = flowBoard.$loadMoreNodes.filter( function () {
				return $( this ).closest( 'body' ).length;
			} );
		}

		// Store this new loadMore node
		flowBoard.$loadMoreNodes = flowBoard.$loadMoreNodes.add( $button );
	};

	//
	// Private functions
	//

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentLoadEventsMixin );
}( jQuery, mediaWiki ) );
