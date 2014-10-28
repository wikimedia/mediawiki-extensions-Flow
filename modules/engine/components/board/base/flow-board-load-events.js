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

	/**
	 * Bind the navigation header bar to the window.scroll event.
	 * @param {jQuery} $boardNavigation
	 */
	function flowBoardLoadEventsBoardNavigation( $boardNavigation ) {
		this
			.on( 'scroll', _flowBoardAdjustTopicNavigationHeader, [ $boardNavigation ] )
			.on( 'resize', _flowBoardAdjustTopicNavigationHeader, [ $boardNavigation ] );

		// The topic navigation header becomes fixed to the window beyond its position
		_flowBoardAdjustTopicNavigationHeader.call( this, $boardNavigation, {} );
	}
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.boardNavigation = flowBoardLoadEventsBoardNavigation;

	/**
	 * Stores the board navigation title.
	 * @param {jQuery} $boardNavigation
	 */
	function flowBoardLoadEventsBoardNavigationTitle( $boardNavigationTitle ) {
		if ( !$boardNavigationTitle.closest( '.flow-board-navigation-affixed' ).length ) {
			// We only care about this when it's in the affixed board navigation header, not the main one
			return;
		}

		this.$boardNavigationTitle = $boardNavigationTitle;
	}
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.boardNavigationTitle = flowBoardLoadEventsBoardNavigationTitle;

	/**
	 * Stores every topic title currently on the page
	 * @param {jQuery} $topicTitle
	 */
	function flowBoardLoadEventsTopicTitle( $topicTitle ) {
		if ( !this.topicTitles ) {
			this.topicTitles = {};
		}

		// Get the topic ID from the parent container
		var topicId = $topicTitle.closest( '[data-flow-id]' ).data( 'flow-id' );

		// Store this title by ID
		if ( topicId ) {
			this.topicTitles[topicId] = $topicTitle;
		}
	}
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.topicTitle = flowBoardLoadEventsTopicTitle;

	//
	// Private functions
	//

	/**
	 * On window.scroll, we clone the nav header bar and fix it to the window top.
	 * We clone so that we have an original which always remains in the same place for calculation purposes,
	 * as it can vary depending on whether or not new content is rendered or the window is resized.
	 * @param {jQuery} $boardNavigation
	 * @param {Event} event
	 */
	function _flowBoardAdjustTopicNavigationHeader( $boardNavigation, event ) {
		var $clone = $boardNavigation.data( 'flowScrollClone' ),
			boardNavigationPosition = $boardNavigation.offset(),
			bottomScrollPosition, $topicInView, topicText;

		if ( window.scrollY <= boardNavigationPosition.top ) {
			// Board nav is still in view; don't affix it
			if ( $clone ) {
				// Remove the old clone if it exists
				$clone.remove();
				$boardNavigation.removeData( 'flowScrollClone' );
				delete this.$boardNavigationTitle;
			}
			return;
		}

		if ( !$clone ) {
			// Make a new clone
			$clone = $boardNavigation.clone( true );

			// Add new classes, and remove the main load handler so we don't trigger it again
			$clone
				.removeData( 'flow-load-handler' )
				.removeClass( 'flow-load-interactive' )
				.addClass( 'flow-board-navigation-affixed' );

			$boardNavigation
				// Store this
				.data( 'flowScrollClone', $clone )
				// Insert it
				.after( $clone );

			// Run loadHandlers
			this.emitWithReturn( 'makeContentInteractive', $clone );
		}

		// The only thing that needs calculating is its left offset
		if ( $clone.css( 'left' ) !== boardNavigationPosition.left ) {
			$clone.css( {
				left: boardNavigationPosition.left
			} );
		}

		// Find out what the bottom of the board nav is touching
		bottomScrollPosition = window.scrollY + $boardNavigation.outerHeight();

		$.each( this.topicTitles || {}, function ( topicId, $topicTitle ) {
			if ( $topicTitle.offset().top > bottomScrollPosition ) {
				return false; // stop, this topic is too far
			}
			$topicInView = $topicTitle;
			topicText = $topicInView.text();
		} );

		// Find out if we need to change the title
		if ( $topicInView && this.$boardNavigationTitle && this.$boardNavigationTitle.text() !== topicText ) {
			// Change it
			this.$boardNavigationTitle.text( topicText );
		}
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentLoadEventsMixin );
}( jQuery, mediaWiki ) );
