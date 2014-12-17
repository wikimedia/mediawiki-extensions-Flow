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

		/** {Object} Map from topic id to its last update timestamp for sorting */
		this.updateTimestampsByTopicId = {};
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
			.off( 'windowScroll', _flowBoardAdjustTopicNavigationHeader )
			.off( 'windowResize', _flowBoardAdjustTopicNavigationHeader )
			.on( 'windowScroll', _flowBoardAdjustTopicNavigationHeader, [ $boardNavigation ] )
			.on( 'windowResize', _flowBoardAdjustTopicNavigationHeader, [ $boardNavigation ] );

		// remove any existing state about the affixed navigation, it has been replaced
		// with a new $boardNavigation to clone from.
		if ( this.$boardNavigationClone ) {
			this.$boardNavigationClone.remove();
			delete this.$boardNavigationClone;
		}
		// The topic navigation header becomes fixed to the window beyond its position
		_flowBoardAdjustTopicNavigationHeader.call( this, $boardNavigation, {} );

		// initialize the board topicId sorting callback.  This expects an element with
		// data-flow-sort of either 'newest' or 'updated'. Typically this is the anchor
		// that shows the current sort and allows changing to a new sort.
		this.topicIdSort = $boardNavigation.find('[data-flow-sort]').data( 'flow-sort' );
		this.updateTopicIdSortCallback();

		// This allows the toc to initialize eagerly before the user looks at it.
		$boardNavigation.find( '[data-flow-api-handler=topicList]' )
			.trigger( 'click', { skipMenuToggle: true, forceNavigationUpdate: true } );
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

	/**
	 * @param {jQuery} $topic
	 */
	function flowBoardLoadEventsTopic( $topic ) {
		var id = $topic.data( 'flow-id' ),
			updated = $topic.data( 'flow-topic-timestamp-updated' );

		this.updateTimestampsByTopicId[id] = updated;
	}
	FlowBoardComponentBoardHeaderFeatureMixin.UI.events.loadHandlers.topic = flowBoardLoadEventsTopic;

	//
	// Private functions
	//

	/**
	 * Initialize the topic id sort callback
	 */
	function _flowBoardUpdateTopicIdSortCallback() {
		if ( this.topicIdSort === 'newest' ) {
			// the sort callback takes advantage of the default utf-8
			// sort in this case
			this.topicIdSortCallback = undefined;
		} else if ( this.topicIdSort === 'updated' ) {
			this.topicIdSortCallback = flowBoardTopicIdGenerateSortRecentlyActive( this );
		} else {
			throw new Error( 'this.topicIdSort has an invalid value' );
		}
	}
	FlowBoardComponentBoardHeaderFeatureMixin.prototype.updateTopicIdSortCallback = _flowBoardUpdateTopicIdSortCallback;

	/**
	 * Generates Array#sort callback for sorting a list of topic ids
	 * by the 'recently active' sort order. This is a numerical
	 * comparison of related timestamps held within the board object.
	 * Also note that this is a reverse sort from newest to oldest.
	 * @param {Object} board Object from which to source
	 *  timestamps which map from topicId to its last updated timestamp
	 * @return {Function}
	 */
	function flowBoardTopicIdGenerateSortRecentlyActive( board ) {
		/**
		 * @param {String} a
		 * @param {String} b
		 * @return {integer} Per Array#sort callback rules
		 */
		return function ( a, b ) {
			var aTimestamp = board.updateTimestampsByTopicId[a],
				bTimestamp = board.updateTimestampsByTopicId[b];

			if ( aTimestamp === undefined && bTimestamp === undefined ) {
				return 0;
			} else if ( aTimestamp === undefined ) {
				return 1;
			} else if ( bTimestamp === undefined ) {
				return -1;
			} else {
				return bTimestamp - aTimestamp;
			}
		};
	}

	/**
	 * On window.scroll, we clone the nav header bar and fix the original to the window top.
	 * We clone so that we have one which always remains in the same place for calculation purposes,
	 * as it can vary depending on whether or not new content is rendered or the window is resized.
	 * @param {jQuery} $boardNavigation
	 * @param {Event} event
	 */
	function _flowBoardAdjustTopicNavigationHeader( $boardNavigation, event, extraParameters ) {
		var bottomScrollPosition, topicText, newReadingTopicId, $tocContainer, $scrollTarget,
			self = this,
			boardNavigationPosition = ( this.$boardNavigationClone || $boardNavigation ).offset();

		extraParameters = extraParameters || {};

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

			// After cloning a new navigation we must always update the sort
			extraParameters.forceNavigationUpdate = true;
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

		// We still need to trigger movemnt when the topic title has not changed
		// in instances where new data has been loaded.
		topicText = calculateUpdatedTitleText( this, topicText );
		if ( topicText !== undefined ) {
			// We only reach this if the visible topic has changed
			this.$boardNavigationTitle.text( topicText );
		} else if ( extraParameters.forceNavigationUpdate !== true ) {
			// If the visible topic has not changed and we are not forced
			// to update(due to new items or other situations), exit early.
			return;
		}

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
			$tocContainer.scrollTop( $scrollTarget.offset().top - $tocContainer.offset().top + $tocContainer.scrollTop() );
			// the above may not trigger the scroll.flow-load-more event within the TOC if the $tocContainer
			// does not have a scrollbar. If that happens you could have a TOC without a scrollbar
			// that refuses to autoload anything else. Fire it again(wasteful) untill we find
			// a better way.
			// This does not seem to work for the initial load, that is handled in flow-boad-loadmore.js
			// when it runs this same code.  This seems to be required for subsequent loads after
			// the initial call.
			if ( this.$loadMoreNodes ) {
				this.$loadMoreNodes
					.filter( '[data-flow-api-handler=topicList]' )
					.trigger( 'scroll.flow-load-more', { forceNavigationUpdate: true } );
			}
		}
	}

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentBoardHeaderFeatureMixin );
}( jQuery, mediaWiki ) );
