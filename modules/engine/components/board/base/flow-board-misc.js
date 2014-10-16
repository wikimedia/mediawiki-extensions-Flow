/*!
 * Contains miscellaneous functionality needed for FlowBoardComponents.
 * @todo Find a better place for this code.
 */

( function ( $, mw ) {
	/**
	 *
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentMiscMixin( $container ) {
		this.on( 'scroll', _flowBoardOnWindowScroll );
	}
	OO.initClass( FlowBoardComponentMiscMixin );

	//
	// Methods
	//

	/**
	 * Removes the preview and unhides the form fields.
	 * @param {jQuery} $cancelButton
	 * @return {bool} true if success
	 * @todo genericize into FlowComponent
	 */
	function flowBoardComponentResetPreview( $cancelButton ) {
		var $form = $cancelButton.closest( 'form' ),
			$button = $form.find( '[name=preview]' ),
			oldData = $button.data( 'flow-return-to-edit' );

		if ( oldData ) {
			// We're in preview mode. Revert it back.
			$button.text( oldData.text );

			// Show the inputs again
			$form.find( '.flow-preview-target-hidden' ).removeClass( 'flow-preview-target-hidden' ).focus();

			// Remove the preview
			oldData.$nodes.remove();

			// Remove this reset info
			$button.removeData( 'flow-return-to-edit' );

			return true;
		}
		return false;
	}
	FlowBoardComponentMiscMixin.prototype.resetPreview = flowBoardComponentResetPreview;

	//
	// Static functions
	//

	/**
	 * Dispatches to other window.scroll events.
	 * @param {Event} event
	 */
	function _flowBoardOnWindowScroll( event ) {
		// Infinite scroll handler
		_flowBoardInfiniteScrollCheck();
	}

	//
	// Private functions
	//

	/**
	 * Called on window.scroll. Checks to see if a FlowBoard needs to have more content loaded.
	 */
	function _flowBoardInfiniteScrollCheck() {
		$.each( mw.flow.getPrototypeMethod( 'board', 'getInstances' )(), function () {
			var flowBoard = this,
				$loadMoreNodes = flowBoard.$loadMoreNodes || $();

			// Check each loadMore button
			$loadMoreNodes.each( function () {
				if ( _isEndNear( $( this ) ) ) {
					$( this ).trigger( 'click' );
				}
			} );
		} );
	}

	/**
	 * Checks whether the user is near the end of the viewport
	 */
	function _isEndNear( $end ) {
		var $window = $( window ),
			threshold = $window.height(),
			scrollBottom = $window.scrollTop() + $window.height();
		return scrollBottom + threshold > $end.offset().top;
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentMiscMixin );
}( jQuery, mediaWiki ) );
