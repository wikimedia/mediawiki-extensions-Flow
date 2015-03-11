/*!
 * Contains the base constructor for FlowBoardComponent.
 * @todo Clean up the remaining code that may not need to be here.
 */

( function ( $, mw ) {
	/**
	 * Constructor class for instantiating a new Flow board.
	 * @example <div class="flow-component" data-flow-component="board" data-flow-id="rqx495tvz888x5ur">...</div>
	 * @param {jQuery} $container
	 * @extends FlowBoardAndHistoryComponentBase
	 * @mixins FlowComponentEventsMixin
	 * @mixins FlowComponentEnginesMixin
	 * @mixins FlowBoardComponentApiEventsMixin
	 * @mixins FlowBoardComponentInteractiveEventsMixin
	 * @mixins FlowBoardComponentLoadEventsMixin
	 * @mixins FlowBoardComponentMiscMixin
	 * @mixins FlowBoardComponentLoadMoreFeatureMixin
	 * @constructor
	 */
	function FlowBoardComponent( $container ) {
		var uri = new mw.Uri( location.href ),
			uid = String( location.hash.match( /[0-9a-z]{16,19}$/i ) || '' );

		// Default API submodule for FlowBoard URLs is to fetch a topiclist
		this.Api.setDefaultSubmodule( 'view-topiclist' );

		// Set up the board
		if ( this.reinitializeContainer( $container ) === false ) {
			// Failed to init for some reason
			return false;
		}

		// Handle URL parameters
		if ( uid ) {
			if ( uri.query.fromnotif ) {
				_flowHighlightPost( $container, uid, 'newer' );
			} else {
				_flowHighlightPost( $container, uid );
			}
		}

		_overrideWatchlistNotification();
	}
	OO.initClass( FlowBoardComponent );

	// Register
	mw.flow.registerComponent( 'board', FlowBoardComponent, 'boardAndHistoryBase' );

	//
	// Methods
	//

	/**
	 * Sets up the board and base properties on this class.
	 * Returns either FALSE for failure, or jQuery object of old nodes that were replaced.
	 * @param {jQuery|boolean} $container
	 * @return {Boolean|jQuery}
	 */
	function flowBoardComponentReinitializeContainer( $container ) {
		if ( $container === false ) {
			return false;
		}

		// Trigger this on FlowBoardAndHistoryComponentBase
		// @todo use EventEmitter to do this?
		var $retObj = FlowBoardComponent.parent.prototype.reinitializeContainer.call( this, $container ),
		// Find any new (or previous) elements
			$header = $container.find( '.flow-board-header' ).addBack().filter( '.flow-board-header:first' ),
			$boardNavigation = $container.find( '.flow-board-navigation' ).addBack().filter( '.flow-board-navigation:first' ),
			$board = $container.find( '.flow-board' ).addBack().filter( '.flow-board:first' );

		if ( $retObj === false ) {
			return false;
		}

		// Remove any of the old elements that are still in use
		if ( $header.length ) {
			if ( this.$header ) {
				$retObj = $retObj.add( this.$header.replaceWith( $header ) );
				this.$header.remove();
			}

			this.$header = $header;
		}
		if ( $boardNavigation.length ) {
			if ( this.$boardNavigation ) {
				$retObj = $retObj.add( this.$boardNavigation.replaceWith( $boardNavigation ) );
				this.$boardNavigation.remove();
			}

			this.$boardNavigation = $boardNavigation;
		}
		if ( $board.length ) {
			if ( this.$board ) {
				$retObj = $retObj.add( this.$board.replaceWith( $board ) );
				this.$board.remove();
			}

			this.$board = $board;
		}

		// Second, verify that this board in fact exists
		if ( !this.$board || !this.$board.length ) {
			// You need a board, dammit!
			this.debug( 'Could not find .flow-board', arguments );
			return false;
		}

		this.emitWithReturn( 'makeContentInteractive', this );

		// Initialize editors, turning them from textareas into editor objects
		if ( typeof this.editorTimer === 'undefined' ) {
			/*
			 * When this method is first run, all page elements are initialized.
			 * We probably don't need editor immediately, so defer loading it
			 * to speed up the rest of the work that needs to be done.
			 */
			this.editorTimer = setTimeout( $.proxy( function ( $container ) { this.emitWithReturn( 'initializeEditors', $container ); }, this, $container ), 20000 );
		} else {
			/*
			 * Subsequent calls here (e.g. when rendering the edit header form)
			 * should immediately initialize the editors!
			 */
			clearTimeout( this.editorTimer );
			this.emitWithReturn( 'initializeEditors', $container );
		}

		return $retObj;
	}
	FlowBoardComponent.prototype.reinitializeContainer = flowBoardComponentReinitializeContainer;

	//
	// Private functions
	//

	/**
	 * Helper receives
	 * @param {jQuery} $container
	 * @param {string} uid
	 * @param {string} option
	 * @return {jQuery}
	 */
	function _flowHighlightPost( $container, uid, option ) {
		var $target = $container.find( '#flow-post-' + uid );

		// reset existing highlights
		$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );

		if ( option === 'newer' ) {
			$target.addClass( 'flow-post-highlight-newer' );
			if ( uid ) {
				$container.find( '.flow-post' ).each( function( idx, el ) {
					var $el = $( el ),
						id = $el.data( 'flow-id' );
					if ( id && id > uid ) {
						$el.addClass( 'flow-post-highlight-newer' );
					}
				} );
			}
		} else {
			$target.addClass( 'flow-post-highlighted' );
		}

		return $target;
	}

	/**
	 * We want the default behavior of watch/unwatch for page. However, we
	 * do want to show our own tooltip after this has happened.
	 * We'll override mw.notify, which is fired after successfully
	 * (un)watchlisting, to stop the notification from being displayed.
	 * If the action we just intercepted was after succesful watching, we'll
	 * want to show our own tooltip instead.
	 */
	function _overrideWatchlistNotification() {
		var _notify = mw.notify;
		mw.notify = function( message, options ) {
			// override message when we've just watched the board
			if ( options.tag === 'watch-self' && $( '#ca-watch' ).length ) {
				// Render a div telling the user that they have subscribed
				message = $( mw.flow.TemplateEngine.processTemplateGetFragment(
					'flow_subscribed.partial',
					{
						type: 'board',
						username: mw.user.getName()
					}
				) ).children();
			}

			_notify.apply( this, arguments );
		};
	}
}( jQuery, mediaWiki ) );
