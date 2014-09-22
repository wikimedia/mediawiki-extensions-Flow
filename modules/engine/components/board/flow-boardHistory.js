/*!
 *
 */

( function ( $, mw ) {
	/**
	 *
	 * @example <div class="flow-component" data-flow-component="boardHistory" data-flow-id="rqx495tvz888x5ur">...</div>
	 * @param {jQuery} $container
	 * @return {FlowBoardHistoryComponent|bool}
	 * @extends FlowBoardComponentBase
	 * @constructor
	 */
	function FlowBoardHistoryComponent( $container ) {

	}
	OO.initClass( FlowBoardHistoryComponent );

	mw.flow.registerComponent( 'boardHistory', FlowBoardHistoryComponent/*, 'boardAndHistoryBase'*/ );
}( jQuery, mediaWiki ) );