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
	 * Sets the Collapser state to newState, and will load this state on next page refresh.
	 * @param {FlowBoardComponent} flowBoard
	 * @param {String} [newState]
	 */
	FlowBoardComponentMiscMixin.prototype.collapserState = function ( flowBoard, newState ) {
		var $heading,
			$container = flowBoard.$container;

		// Don't do collapser states on individual topic pages
		if ( flowBoard.constructor.static.inTopicNamespace() ) {
			return;
		}

		if ( !newState ) {
			// Get last
			newState = mw.flow.StorageEngine.localStorage.getItem( 'collapserState' ) || 'full'; // @todo genericize this
		} else {
			// Save
			mw.flow.StorageEngine.localStorage.setItem( 'collapserState', newState ); // @todo genericize this
			flowBoard.$board.find( '.flow-element-expanded, .flow-element-collapsed' )
				// If moderated topics are currently collapsed, leave them that way
				.not( '.flow-element-moderated.flow-element-collapsed' )
				.not( '.flow-topic-moderated' )
				.removeClass( 'flow-element-expanded flow-element-collapsed' );

			// Remove individual topic states
			mw.flow.StorageEngine.sessionStorage.removeItem( 'collapserStates' );
		}

		// @todo genericize this
		$container
			.removeClass( 'flow-board-collapsed-full flow-board-collapsed-topics flow-board-collapsed-compact' )
			.addClass( 'flow-board-collapsed-' + newState );

		$heading = $container.find( '.flow-topic-title' );
		// In compact mode also truncate the text.
		if ( newState === 'compact' ) {
			$heading.addClass( 'flow-ui-text-truncated' );
		} else {
			$heading.removeClass( 'flow-ui-text-truncated' );
		}
	};

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
