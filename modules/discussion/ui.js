(function ( $, mw ) {
	'use strict';

	/**
	 * The base object for initializing a Flow board
	 */
	mw.flow.discussion = {
		/** Stores the jQuery container element for Flow */
		$container: null,

		/**
		 * Triggered to collapse (hide) a topic's replies. Speed 0 hides instantly.
		 * @param {jQuery} $el
		 * @param {int|string} [speed]
		 */
		topicCollapse: function ( $el, speed ) {
			$el.closest( '.flow-topic-container' ).addClass( 'flow-topic-closed' ).children( '.flow-topic-children-container' );

			if ( speed === 0 ) {
				$el.hide();
			} else {
				$el.slideUp( speed );
			}
		},

		/**
		 * Triggered to expand (show) a topic's replies. Speed 0 shows instantly.
		 * @param {jQuery} $el
		 * @param {int|string} [speed]
		 */
		topicExpand: function ( $el, speed ) {
			$el.closest( '.flow-topic-container' ).removeClass( 'flow-topic-closed' ).children( '.flow-topic-children-container' );

			if ( speed === 0 ) {
				$el.show();
			} else {
				$el.slideDown( speed );
			}
		},

		/**
		 * Adds $elem's nearest .flow-post-container by adding .flow-post-highlighted, then scrolls the viewport to it.
		 * @param {jQuery|$} $el
		 * @param {string|int} [speed]
		 */
		highlightElement: function ( $el, speed ) {
			if ( !$el.length ) {
				return;
			}

			this.$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );
			$el
				.closest( '.flow-post-container' )
				.addClass( 'flow-post-highlighted' );

			$el.conditionalScrollIntoView( speed );
		},

		/**
		 * Initializes Tipsy on Flow
		 */
		setupTipsy: function () {
			// Set up tipsy to show the user who made last topic/post modification.
			this.$container.find( '.flow-content-modified-tipsy-link' ).each( function () {
				$( this ).flow(
					'setupTipsy',
					$( this ).parent().find( '.flow-content-modified-tipsy-flyout' ),
					'mouseover'
				);
			} );

			// Tipsy markup: <div class=flow-tipsy><a class="flow-tipsy-link"/><div class="flow-tipsy-flyout"/></div>
			// .flow-tipsy-link is the target that, when clicked, will trigger the tipsy dialog.
			// .flow-tipsy-flyout is the dialog content.
			this.$container.find( '.flow-tipsy-link' ).each( function () {
				$( this ).flow(
					'setupTipsy',
					$( this ).parent( '.flow-tipsy' ).find( '.flow-tipsy-flyout' ),
					'click'
				);
			} );

			// Handle document clicks so as to globally close tipsy
			$( document )
				.off( 'click.mw-flow-discussion-tipsy' ) // Unbind; this is global, so only have one at a time
				.on( 'click.mw-flow-discussion-tipsy', function ( event ) {
					// If the parents are not tipsy, close the dialog.
					if ( !$( event.target ).parents().addBack().is( '.flow-tipsy-link, .flow-tipsy-flyout' ) ) {
						$( '.flow-tipsy-open' ).each( function () {
							$( this )
								.removeClass( 'flow-tipsy-open' )
								.tipsy( 'hide' );
						} );
					}
				} );
		},

		/**
		 * Initialize topics, posts, and bind event handlers
		 */
		setupFlow: function () {
			var $newTopic = this.$container.find( '.flow-new-topic-container' ).addBack( '.flow-new-topic-container' ),
				$topics = this.$container.find( '.flow-topic-container' ).addBack( '.flow-topic-container' ),
				$posts = this.$container.find( '.flow-post' ).addBack( '.flow-post' );

			// init new title interaction
			if ( $newTopic.length && !$newTopic.data( 'flow-initialised-new-topic' ) ) {
				new mw.flow.action.topic.create();

				$newTopic.data( 'flow-initialised-new-topic', true );
			}

			// init topic interaction
			$topics.each( function () {
				/*
				 * Initialisation (registerInitFunction) is re-done after every
				 * change (e.g. adding a new topic), by .trigger( 'flow_init' )
				 *
				 * We're now creating JS objects by looping all DOM title nodes.
				 * We don't want to create multiple objects when re-initializing
				 * so let's mark initialised state in a data-attribute.
				 */
				if ( !$( this ).data( 'flow-initialised-topic' ) ) {
					var topicId = $( this ).data( 'topic-id' );
					new mw.flow.discussion.topic( topicId );

					$( this ).data( 'flow-initialised-topic', true );
				}

				$( this ).find( '.flow-titlebar' )
					// Remove old event handlers
					.off( '.mw-flow-discussion' )
					// On topic titlebar click, trigger collapse/expand event
					.on( 'click.mw-flow-discussion', mw.flow.action.ui.titlebarClick )
			} );

			// init post interaction
			$posts.each( function () {
				/*
				 * Initialisation (registerInitFunction) is re-done after every
				 * change (e.g. adding a new topic), by .trigger( 'flow_init' )
				 *
				 * We're now creating JS objects by looping all DOM title nodes.
				 * We don't want to create multiple objects when re-initializing
				 * so let's mark initialised state in a data-attribute.
				 */
				if ( !$( this ).data( 'flow-initialised-post' ) ) {
					var postId = $( this ).parent().data( 'post-id' );
					new mw.flow.discussion.post( postId );

					$( this ).data( 'flow-initialised-post', true );
				}
			} );

			this.$container.find( '.topic-collapser li' )
				.off( '.mw-flow-discussion' )
				// On topic collapser click, choose a different collapsing level
				.on( 'click.mw-flow-discussion', mw.flow.action.ui.topicCollapserClick );
		},

		/**
		 * Prepare moderation handlers in Flow.
		 * @todo cleanup
		 */
		setupModeration: function () {
			// Moderation controls
			var moderationTypes = [ 'hide', 'delete', 'suppress', 'restore' ],
				classes = [],
				i, j, classNames;
			for ( i = moderationTypes.length; i--; ) {
				classNames = [
					'.flow-' + moderationTypes[i] + '-post-link',
					'.flow-' + moderationTypes[i] + '-topic-link'
				];

				for ( j = classNames.length; j--; ) {
					classes.push( classNames[j] );

					// don't use .data() to set the type, because this is not the
					// exact node that will be clicked on: tipsy will clone the node,
					// outside of $container
					this.$container.find( classNames[j] ).attr( 'data-moderation-type', moderationTypes[i] );
				}
			}

			// live bind to document because tipsy will move the links outside $container
			// only bind once! when node is replaced (e.g. after moderation), we don't
			// want the event to be bound again, since document node won't have changed
			if ( !$( document ).data( 'moderation-bound' ) ) {
				$( document )
					.data( 'moderation-bound', true )
					.on( 'click', classes.join( ', ' ), function ( event ) {
						var moderationType = $( this ).data( 'moderation-type' );
						event.preventDefault();

						// while moderation is being loaded, hide buttons & show spinner
						$( this ).closest( 'li' )
							.fadeOut( 'fast', function () {
								$( this ).injectSpinner( { 'size': 'medium', 'id': 'flow-moderation-loading' } );
							} );

						mw.loader.using( ['ext.flow.moderation'], function () {
							var $tipsyTrigger = $( '.flow-tipsy-open' );

							$.removeSpinner( 'flow-moderation-loading' );

							// close tipsy
							$tipsyTrigger.each( function () {
								$( this ).removeClass( 'flow-tipsy-open' );
								$( this ).tipsy( 'hide' );
							} );

							/*
							 * $tipsyTrigger is actually just the link that opens
							 * the tipsy window, not the moderation button we just
							 * clicked. For the purpose of showModerationDialog, it
							 * should do just fine though; all it needs the element
							 * for is to fetch parent post & topic nodes.
							 *
							 * @todo: we should refactor showModerationDialog some
							 * day to not rely on a specific node being tossed in;
							 * it's too vague what exactly it should be, and we
							 * may not always be able to guarantee nodes' positions
							 * withing the DOM (e.g. tipsy pulls them out)
							 */
							$tipsyTrigger.flow( 'showModerationDialog', moderationType );
						} );
					} );
			}

			this.$container.find( '.flow-post-content-allowed' ).each( function() {
				var $parent = $( this ).parent( '.flow-post-moderated' ),
					showText = $parent.find( '.flow-post-moderated-show-message' ).html(),
					hideText = $parent.find( '.flow-post-moderated-hide-message' ).html();
				$( this ).find( '.flow-post-moderated-view' )
					.html( showText )
					.click( function( e ) {
						e.preventDefault();
						var $post = $( this ).closest( '.flow-post-moderated' ),
							$comment = $post.find( '.flow-post-main' );

						if ( $comment.is( ':visible' ) ) {
							$comment.hide();
							$post.removeClass( 'flow-post-moderated-visible' );
							$( this ).html( showText );
						} else {
							$post.addClass( 'flow-post-moderated-visible' );
							$comment.show();
							$( this ).html( hideText );
						}
					} );
			} );
		}
	};

	/** Stores event handlers */
	mw.flow.action.ui = {
		/**
		 * onclick handler for the collapser options; will set a different collapsing style on the Flow container.
		 * @param {Event} event
		 */
		topicCollapserClick: function (event) {
			event.preventDefault();

			var $this = $( this ),
				$container = $this.closest( '.flow-container' ),
				collapseClass;

			// If this collapser is already active, do nothing.
			if ( $this.is( '.active' ) ) {
				return;
			}

			// Deactivate the current collapser
			collapseClass = $this.siblings( '.active' ).removeClass( 'active' ).data( 'collapse-class' ); // return the current collapse class

			// Activate the current class
			$this.addClass( 'active' );

			// Remove the previous collapse class
			if (collapseClass) {
				$container.removeClass( collapseClass );
			}

			// Expand or collapse based on clicked element's collapse class.
			collapseClass = $this.data( 'collapse-class' );
			if ( collapseClass === undefined ) {
				// Expand all topic containers
				mw.flow.discussion.topicExpand( $container.find( '.flow-topic-children-container' ) );
			} else {
				mw.flow.discussion.topicCollapse( $container.addClass( collapseClass ).find( '.flow-topic-children-container' ) );
			}
		},

		/**
		 * onclick handler for the titlebar of an existing topic to collapse or expand it.
		 * @param {Event} event
		 * @todo Discuss this; the behavior is not at all intuitive -- it does not look like a button.
		 */
		titlebarClick: function ( event ) {
			// If any of these child elements of the titlebar are clicked, ignore this. They have other actions.
			var ignore = [
				'.flow-edit-title-form',
				'.flow-actions',
				'.flow-icon-watchlist',
				'.flow-topic-comments-link'
			].join( ',' ),
				$topicContainer, $topicContainerChildren;

			if ( $( event.target ).is( ignore ) || $( event.target ).closest( ignore ).length ) {
				return;
			}

			$topicContainer = $( this ).closest( '.flow-topic-container' );
			$topicContainerChildren = $topicContainer.find( '.flow-topic-children-container' );
			if ( $topicContainer.is( '.flow-topic-closed' ) ) {
				// Or if this is if already closed
				mw.flow.discussion.topicExpand( $topicContainerChildren );
			} else {
				// Otherwise, collapse
				mw.flow.discussion.topicCollapse( $topicContainerChildren );
			}
		}
	};

	/**
	 * To be bound to flow.registerInitFunction
	 * @param {Event} event
	 */
	mw.flow.discussion.init = $.proxy( function ( event ) {
		this.$container = $( event.target );

		this.setupFlow();
		this.setupTipsy();
		this.setupModeration();

		// Jump to a specific post if one is in the URL
		if ( window.location.hash ) {
			var kind, uid;
			if ( 0 === window.location.hash.indexOf( '#flow-post-' ) ) {
				kind = 'post';
				uid = window.location.hash.substr( '#flow-post-'.length );
			} else if ( 0 === window.location.hash.indexOf( '#flow-topic-' ) ) {
				kind = 'topic';
				uid = window.location.hash.substr( '#flow-topic-'.length );
			}
			if ( uid.length === 32 ) {
				// converts 128 bit hex uuid to 88 bit base 36 by truncating the right
				// side of the uuid.
				window.location.hash = '#flow-' + kind + '-' + baseConvert( uid.substr( 0, 22 ), 16, 36 );
			}
			mw.flow.discussion.highlightElement( $( window.location.hash ), 0 );
		}
	}, mw.flow.discussion );

	// Go!
	$( document ).flow( 'registerInitFunction', mw.flow.discussion.init );

	// Direct translation of wfBaseConvert.  Can't be done with parseInt and
	// String.toString because javascript uses doubles for math, giving only
	// 53 bits of precision.
	function baseConvert( input, sourceBase, destBase ) {
		var regex = new RegExp( "^[" + '0123456789abcdefghijklmnopqrstuvwxyz'.substr( 0, sourceBase ) + "]+$" ),
			baseChars = {
				'10': 'a', '11': 'b', '12': 'c', '13': 'd', '14': 'e', '15': 'f',
				'16': 'g', '17': 'h', '18': 'i', '19': 'j', '20': 'k', '21': 'l',
				'22': 'm', '23': 'n', '24': 'o', '25': 'p', '26': 'q', '27': 'r',
				'28': 's', '29': 't', '30': 'u', '31': 'v', '32': 'w', '33': 'x',
				'34': 'y', '35': 'z',

				'0':  0, '1':  1, '2':  2, '3':  3, '4':  4, '5': 5,
				'6':  6, '7':  7, '8':  8, '9':  9, 'a': 10, 'b': 11,
				'c': 12, 'd': 13, 'e': 14, 'f': 15, 'g': 16, 'h': 17,
				'i': 18, 'j': 19, 'k': 20, 'l': 21, 'm': 22, 'n': 23,
				'o': 24, 'p': 25, 'q': 26, 'r': 27, 's': 28, 't': 29,
				'u': 30, 'v': 31, 'w': 32, 'x': 33, 'y': 34, 'z': 35
			},
			inDigits = [],
			result = [],
			i, work, workDigits;

		input = String( input );
		if ( sourceBase < 2 ||
			sourceBase > 36 ||
			destBase < 2 ||
			destBase > 36 ||
			sourceBase !== parseInt( sourceBase, 10 ) ||
			destBase !== parseInt( destBase, 10 ) ||
			!regex.test( input )
		) {
			return false;
		}

		for ( i in input ) {
			inDigits.push( baseChars[input[i]] );
		}

		// Iterate over the input, modulo-ing out an output digit
		// at a time until input is gone.
		while( inDigits.length ) {
			work = 0;
			workDigits = [];

			// Long division...
			for ( i in inDigits ) {
				work *= sourceBase;
				work += inDigits[i];

				if ( workDigits.length || work >= destBase ) {
					workDigits.push( parseInt( work / destBase, 10 ) );
				}
				work %= destBase;
			}

			// All that division leaves us with a remainder,
			// which is conveniently our next output digit
			result.push( baseChars[work] );

			// And we continue
			inDigits = workDigits;
		}

		return result.reverse().join("");
	}
} )( jQuery, mediaWiki );

