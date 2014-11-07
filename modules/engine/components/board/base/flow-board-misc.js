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

			// Restore data attributes
			$.each( oldData.data, function( key, value ) {
				$button.data( key, value );
			} );

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
		// The topic navigation sidebar
		_flowBoardAdjustTopicNavigationSidebar();

		// Infinite scroll handler
		_flowBoardInfiniteScrollCheck();
	}

	//
	// Private functions
	//

	/**
	 * Triggered by window.scroll. It iterates over every FlowBoard's topic navigator list, affixes it to the
	 * viewport, and will apply the necessary adjustments to show the active topic and its read progress.
	 * @todo This should probably be done in TemplateEngine.
	 */
	function _flowBoardAdjustTopicNavigationSidebar() {
		var $temp        = $( '<div/>', { 'class': 'flow-topic-navigator', 'style': 'display: none;' } ).appendTo( 'body' ),
			unreadColor  = $temp.css( 'background-color' ),
			readColor    = $temp.addClass( 'flow-topic-navigator-read' ).css( 'background-color' ),
			scrollTop    = $( window ).scrollTop(),
			windowHeight = $( window ).height();

		$temp.remove();

		$.each( mw.flow.getPrototypeMethod( 'board', 'getInstances' )(), function () {
			var $topicNavigation = this.$topicNavigation,
				$this, $topic, offsetTop, outerHeight, percent;

			// Only proceed with this wacky stuff if the navigation bar is currently in use
			if ( !$topicNavigation || !$topicNavigation.is( ':visible' ) ) {
				return;
			}

			// Affix the sidebar to the top of the window after scrolling past it
			offsetTop = $topicNavigation.data( 'affix-top' );
			if ( offsetTop && scrollTop < offsetTop ) {
				// Scrolled back up, unfix it
				$topicNavigation
					.removeClass( 'flow-topic-navigation-fixed' )
					.removeData( 'affix-top' );
			} else if ( !offsetTop ) {
				// Not affixed yet
				offsetTop = $topicNavigation.offset().top;
				if ( scrollTop > offsetTop ) {
					// Needs to be affixed to window viewport
					$topicNavigation
						.data( 'affix-top', offsetTop )
						.css( { 'right': $( window ).width() - $topicNavigation.offset().left - $topicNavigation.outerWidth() } )
						.addClass( 'flow-topic-navigation-fixed' );
				}
			}

			// Iterate over every topic link
			$topicNavigation.find( '.flow-topic-navigator' ).each( function () {
				$topic = $( this.href.substr(this.href.indexOf('#')) );
				if ( !$topic.length ) {
					return;
				}

				$this = $( this );

				offsetTop = $topic.offset().top;
				outerHeight = $topic.outerHeight();
				percent = ( scrollTop - offsetTop + windowHeight ) / outerHeight * 100;

				if ( percent >= 100 ) {
					// Entire topic is scrolled beyond.
					if ( !$this.hasClass( 'flow-topic-navigator-read' ) ) {
						// Give it the read class
						$this
							.removeClass( 'flow-topic-navigator-active' )
							.addClass( 'flow-topic-navigator-read' )
							.css( { background: '' } );
					}
				} else if ( percent <= 0.1 ) {
					// Topic is not at all in viewport.
					$this
						.removeClass( 'flow-topic-navigator-active' )
						.removeClass( 'flow-topic-navigator-read' )
						.css( { background: '' } );
				} else {
					// Part of topic is in view. Do partial background progress bar.

					if (percent < 0.01) {
						percent = 0;
					}
					$this
						.removeClass( 'flow-topic-navigator-read' )
						.addClass( 'flow-topic-navigator-active' )
						.css( { background: '-moz-linear-gradient(left,  ' + readColor + ' ' + percent + '%, ' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // FF 3.6+
						.css( { background: '-webkit-gradient(linear, left top, right top, color-stop(' + percent + '%,' + readColor + '), color-stop(' + ( percent + 1 ) + '%,' + unreadColor + '))' } ) // Chrome 4+
						.css( { background: '-webkit-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // Chrome 10+
						.css( { background: '-o-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // Opera 11+
						.css( { background: '-ms-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // IE10+
						.css( { background: 'linear-gradient(to right,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ); // CSS3
				}
			} );
		} );
	}

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
