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
		if ( !$boardNavigationTitle.closest( '.flow-board-navigation-affixed' ).length ) {
			// We only care about this when it's in the affixed board navigation header, not the main one
			return;
		}

		this.$boardNavigationTitle = $boardNavigationTitle;
	}
	FlowBoardComponentBoardHeaderFeatureMixin.UI.events.loadHandlers.boardNavigationTitle = flowBoardLoadEventsBoardNavigationTitle;

	//
	// Private functions
	//

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
		var self = this,
			boardNavigationPosition = $boardNavigation.offset(),
			bottomScrollPosition, topicText;

		if ( window.scrollY <= boardNavigationPosition.top ) {
			// Board nav is still in view; don't affix it
			if ( this.$boardNavigationClone ) {
				// Remove the old clone if it exists
				this.$boardNavigationClone.remove();
				delete this.$boardNavigationClone;
				delete this.$boardNavigationTitle;
			}
			return;
		}

		if ( !this.$boardNavigationClone ) {
			// Make a new clone
			this.$boardNavigationClone = $boardNavigation.clone( true );

			// Add new classes, and remove the main load handler so we don't trigger it again
			this.$boardNavigationClone
				.removeData( 'flow-load-handler' )
				.removeClass( 'flow-load-interactive' )
				.addClass( 'flow-board-navigation-affixed' );

			// Insert it
			$boardNavigation.after( this.$boardNavigationClone );

			// Run loadHandlers
			this.emitWithReturn( 'makeContentInteractive', this.$boardNavigationClone );
		}

		// The only thing that needs calculating is its left offset
		if ( parseInt( this.$boardNavigationClone.css( 'left' ) ) !== boardNavigationPosition.left ) {
			this.$boardNavigationClone.css( {
				left: boardNavigationPosition.left
			} );
		}

		// Find out what the bottom of the board nav is touching
		bottomScrollPosition = window.scrollY + $boardNavigation.outerHeight();

		$.each( this.renderedTopics || {}, function ( topicId, $topic ) {
			var target = $topic.data( 'flow-toc-scroll-target' ),
				$target = $.findWithParent( $topic, target );

			if ( $target.offset().top > bottomScrollPosition ) {
				return false; // stop, this topic is too far
			}

			topicText = self.topicTitlesById[ topicId ];
			self.readingTopicId = topicId;
		} );

		// Find out if we need to change the title
		if ( topicText && this.$boardNavigationTitle && this.$boardNavigationTitle.text() !== topicText ) {
			// Change it
			this.$boardNavigationTitle.text( topicText );
		}

		// Set TOC active item
		this.$boardNavigationTitle.parent().findWithParent( this.$boardNavigationTitle.parent().data( 'flow-api-target' ) )
			.find( 'a[data-flow-id]' )
				.filter( '[data-flow-id=' + this.readingTopicId + ']' )
					.addClass( 'active' )
				.end().not( '[data-flow-id=' + this.readingTopicId + ']' )
					.removeClass( 'active' );
	}

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentBoardHeaderFeatureMixin );
}( jQuery, mediaWiki ) );
