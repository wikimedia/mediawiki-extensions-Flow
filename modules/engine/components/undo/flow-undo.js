/*!
 *
 */

( function ( $, mw ) {
	/**
	 *
	 * @example <div class="flow-component" data-flow-component="undo" data-flow-id="rqx495tvz888x5ur">...</div>
	 * @param {jQuery} $container
	 * @extends FlowBoardAndHistoryComponentBase
	 * @constructor
	 */
	function FlowUndoComponent( $container ) {
		this.bindNodeHandlers( FlowUndoComponent.UI.events );
	}
	OO.initClass( FlowUndoComponent );

	FlowUndoComponent.UI = {
		events: {
			apiHandlers: {}
		}
	};

	mw.flow.registerComponent( 'undo', FlowUndoComponent, 'undo' );

	//
	// API handlers
	//

}( jQuery, mediaWiki ) );
