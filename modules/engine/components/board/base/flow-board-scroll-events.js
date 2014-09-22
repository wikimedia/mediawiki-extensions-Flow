/*!
 * @todo break this down into mixins for each callback section (eg. post actions, read topics)
 */

( function ( $, mw ) {
	/**
	 * Binds window.scroll events to FlowBoardComponent
	 * @param {jQuery} $container
	 * @return {FlowBoardComponentScrollEventsMixin}
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentScrollEventsMixin( $container ) {
		// Bind event callbacks
		//this.bindNodeHandlers( FlowBoardComponentScrollEventsMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentScrollEventsMixin );

	//
	// Static methods
	//

	// Extend FlowBoardComponent
	mw.flow.extendComponent( 'board', FlowBoardComponentScrollEventsMixin );
}( jQuery, mediaWiki ) );