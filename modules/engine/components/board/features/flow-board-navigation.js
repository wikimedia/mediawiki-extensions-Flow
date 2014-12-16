/*!
 * Contains board navigation header, which affixes to the viewport on scroll.
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for the board header itself.
	 * @param {jQuery} $container
	 * @this FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentBoardHeaderFeatureMixin( $container ) {
		// Bind element handlers
		this.bindNodeHandlers( FlowBoardComponentBoardHeaderFeatureMixin.UI.events );

		/** {String} topic ID currently being read in viewport */
		this.readingTopicId = null;
	}
	OO.initClass( FlowBoardComponentBoardHeaderFeatureMixin );

	FlowBoardComponentBoardHeaderFeatureMixin.UI = {
		events: {
			apiPreHandlers: {},
			apiHandlers: {},
			interactiveHandlers: {},
			loadHandlers: {}
		}
	};

	//
	// Prototype methods
	//

	//
	// API pre-handlers
	//

	//
	// On element-click handlers
	//

	//
	// On element-load handlers
	//

	/**
	 * Bind the navigation header bar to the window.scroll event.
	 * @param {jQuery} $boardNavigation
	 */
	function flowBoardLoadEventsBoardNavigation( $boardNavigation ) {
		this
			.on( 'windowScroll', _flowBoardAdjustTopicNavigationHeader, [ $boardNavigation ] )
			.on( 'windowResize', _flowBoardAdjustTopicNavigationHeader, [ $boardNavigation ] );

		// The topic navigation header becomes fixed to the window beyond its position
		_flowBoardAdjustTopicNavigationHeader.call( this, $boardNavigation, {} );
	}
	FlowBoardComponentBoardHeaderFeatureMixin.UI.events.loadHandlers.boardNavigation = flowBoardLoadEventsBoardNavigation;

	/**
	 * Stores the board navigation title.
	 * @param {jQuery} $boardNavigationTitle
	 */
	function flowBoardLoadEventsBoardNavigationTitle( $boardNavigationTitle ) {
		this.boardNavigationOriginalTitle = $boardNavigationTitle.text();
		this.$boardNavigationTitle = $boardNavigationTitle;
	}
	FlowBoardComponentBoardHeaderFeatureMixin.UI.events.loadHandlers.boardNavigationTitle = flowBoardLoadEventsBoardNavigationTitle;

	//
	// Private functions
	//

	/**
	 * On window.scroll, we clone the nav header bar and fix the original to the window top.
	 * We clone so that we have one which always remains in the same place for calculation purposes,
	 * as it can vary depending on whether or not new content is rendered or the window is resized.
	 * @param {jQuery} $boardNavigation
	 * @param {Event} event
	 */
	function _flowBoardAdjustTopicNavigationHeader( $boardNavigation, event ) {
		var bottomScrollPosition, topicText, newReadingTopicId, $tocContainer, $scrollTarget,
			self = this,
			boardNavigationPosition = ( this.$boardNavigationClone || $boardNavigation ).offset();

		if ( window.scrollY <= boardNavigationPosition.top ) {
			// Board nav is still in view; don't affix it
			if ( this.$boardNavigationClone ) {
				// Un-affix this
				$boardNavigation
					.removeClass( 'flow-board-navigation-affixed' )
					.css( 'left', '' );
				// Remove the old clone if it exists
				this.$boardNavigationClone.remove();
				delete this.$boardNavigationClone;
			}

			if ( this.boardNavigationOriginalTitle && this.$boardNavigationTitle ) {
				this.$boardNavigationTitle.text( this.boardNavigationOriginalTitle );
			}

			return;
		}

		if ( !this.$boardNavigationClone ) {
			// Make a new clone
			this.$boardNavigationClone = $boardNavigation.clone();

			// Add new classes, and remove the main load handler so we don't trigger it again
			this.$boardNavigationClone
				.removeData( 'flow-load-handler' )
				.removeClass( 'flow-load-interactive' )
				// Also get rid of any menus, in case they were open
				.find( '.flow-menu' )
					.remove();

			$boardNavigation
				// Insert it
				.before( this.$boardNavigationClone )
				// Affix the original one
				.addClass( 'flow-board-navigation-affixed' );
		}

		boardNavigationPosition = this.$boardNavigationClone.offset();

		// The only thing that needs calculating is its left offset
		if ( parseInt( $boardNavigation.css( 'left' ) ) !== boardNavigationPosition.left ) {
			$boardNavigation.css( {
				left: boardNavigationPosition.left
			} );
		}

		// Find out what the bottom of the board nav is touching
		bottomScrollPosition = window.scrollY + $boardNavigation.outerHeight( true );

		$.each( this.orderedTopicIds || [], function ( idx, topicId, $topic ) {
			$topic = self.renderedTopics[ topicId ];

			if ( !$topic ) {
				return;
			}

			var target = $topic.data( 'flow-toc-scroll-target' ),
				$target = $.findWithParent( $topic, target );

			if ( $target.offset().top - parseInt( $target.css( "padding-top" ) ) - parseInt( $target.css( "margin-top" ) ) > bottomScrollPosition ) {
				return false; // stop, this topic is too far
			}

			topicText = self.topicTitlesById[ topicId ];
			newReadingTopicId = topicId;
		} );

		self.readingTopicId = newReadingTopicId;

		if ( !this.$boardNavigationTitle ) {
			return;
		}

		function calculateUpdatedTitleText( board, topicText ) {
			// Find out if we need to change the title
			if ( topicText !== undefined ) {
				if ( board.$boardNavigationTitle.text() !== topicText ) {
					// Change it
					return topicText;
				}
			} else if ( board.$boardNavigationTitle.text() !== board.boardNavigationOriginalTitle ) {
				return board.boardNavigationOriginalTitle;
			}
		}

		topicText = calculateUpdatedTitleText( this, topicText );
		if ( topicText === undefined ) {
			return;
		}

		// We only reach this if the visible topic has changed
		this.$boardNavigationTitle.text( topicText );

		// Set TOC active item
		$tocContainer = this.$boardNavigationTitle.parent()
			.findWithParent( this.$boardNavigationTitle.parent().data( 'flow-api-target' ) );

		$scrollTarget = $tocContainer.find( 'a[data-flow-id]' )
			.removeClass( 'active' )
			.filter( '[data-flow-id=' + this.readingTopicId + ']' )
				.addClass( 'active' )
				.closest( 'li' )
				.next();

		if ( !$scrollTarget.length ) {
			// we are at the last list item; use the current one instead
			$scrollTarget = $scrollTarget.end();
		}
		// Scroll to the active item
		if ( $scrollTarget.length ) {
			$tocContainer.scrollTop( $scrollTarget.offset().top - $tocContainer.children().first().offset().top );
		}
	}

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentBoardHeaderFeatureMixin );
}( jQuery, mediaWiki ) );
