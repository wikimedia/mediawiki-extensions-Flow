( function ( $, mw ) {
	$( document ).flow( 'registerInitFunction', function ( e ) {
		var $container = $( e.target ),
			$topicContainers = $container.is( '.flow-topic-container' ) ? $container : $container.find( '.flow-topic-container' ),
			moderationTypes = [ 'hide', 'delete', 'suppress', 'restore' ],
			classes = [],
			kind, uid;

		/*
		 * Set up tipsy to show the user who made last topic/post modification
		 */
		$container.find( '.flow-content-modified-tipsy-link' ).each( function() {
			$( this ).flow(
				'setupTipsy',
				$( this ).parent().find( '.flow-content-modified-tipsy-flyout' ),
				'mouseover'
			);
		} );

		/*
		 * Set up tipsy.
		 *
		 * Tipsy html should look like this:
		 * <div class="flow-tipsy">
		 *     <a class="flow-tipsy-link"></a>
		 *     <div class="flow-tipsy-flyout"></div>
		 * </div>
		 *
		 * .flow-tipsy-link is the target that, when clicked, will trigger the
		 * tipsy dialog. .flow-tipsy-flyout is the dialog content.
		 */
		$container.find( '.flow-tipsy-link' ).each( function() {
			$( this ).flow(
				'setupTipsy',
				$( this ).parent( '.flow-tipsy' ).find( '.flow-tipsy-flyout' ),
				'click'
			);
		} );

		$( document )
			.click( function ( e ) {
				// check if clicked on tipsy trigger link or inside tipsy dialog
				var inTipsy =
					$( e.target ).closest( '.flow-tipsy-link' ).length > 0 ||
					$( e.target ).closest( '.flow-tipsy-flyout' ).length > 0;

				// if clicked anywhere outside of tipsy, close dialog
				if ( !inTipsy ) {
					$( '.flow-tipsy-open' ).each( function() {
						$( this )
							.removeClass( 'flow-tipsy-open' )
							.tipsy( 'hide' );
					} );
				}
			} );

		// Set up folding
		$topicContainers
			.on( "collapse", function() {
				$( this ).addClass( 'flow-topic-closed' )
					.children( '.flow-topic-children-container' )
					.slideUp();
			} )
			.on( "expand", function() {
				$( this ).removeClass( 'flow-topic-closed' )
					.children( '.flow-topic-children-container' )
					.slideDown();
			} );
		$container.find( '.flow-titlebar' )
			.on( "click", function ( e ) {
				/*
				 * When clicked anywhere in these elements, the click should not
				 * collapse/expand the topic (likely because the node is, in
				 * HTML inside this node, but not visually)
				 */
				var ignore = [
					'.flow-edit-title-form',
					'.flow-actions',
					'.flow-icon-permalink',
					'.flow-icon-watchlist',
					'.flow-topic-comments-link'
				],
					$topicContainer;
				if ( $( e.target ).closest( ignore.join( ',' ) ).length > 0 ) {
					return true;
				}

				$topicContainer = $( this ).closest( '.flow-topic-container' );
				if ( $topicContainer.is( '.flow-topic-closed' ) ) {
					$topicContainer.trigger( "expand" );
				} else {
					$topicContainer.trigger( "collapse" );
				}
			} );

		// Moderation controls
		$.each( moderationTypes, function( k, moderationType ) {
			var classNames = ['.flow-'+moderationType+'-post-link', '.flow-'+moderationType+'-topic-link'],
				i;

			for ( i in classNames ) {
				classes.push( classNames[i] );

				// don't use .data() to set the type, because this is not the
				// exact node that will be clicked on: tipsy will clone the node,
				// outside of $container
				$container.find( classNames[i] ).attr( 'data-moderation-type', moderationType );
			}
		} );

		// live bind to document because tipsy will move the links outside $container
		// only bind once! when node is replaced (e.g. after moderation), we don't
		// want the event to be bound again, since document node won't have changed
		if ( !$( document ).data( 'moderation-bound' ) ) {
			$( document )
				.data( 'moderation-bound', true )
				.on( 'click', classes.join( ', ' ), function( e ) {
					var moderationType = $( this ).data( 'moderation-type' );
					e.preventDefault();

					// while moderation is being loaded, hide buttons & show spinner
					$( this ).closest( 'li' )
						.fadeOut( 'fast', function() {
							$( this ).injectSpinner( { 'size' : 'medium', 'id' : 'flow-moderation-loading' } );
						} );

					mw.loader.using( ['ext.flow.moderation'], function() {
						var $tipsyTrigger = $( '.flow-tipsy-open' );

						$.removeSpinner( 'flow-moderation-loading' );

						// close tipsy
						$tipsyTrigger.each( function() {
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

		$container.find( '.flow-post-content-allowed' ).each( function() {
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

		function highlightPost( $elem ) {
			if ( !$elem.length ) {
				return;
			}

			$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );
			$elem
				.closest( '.flow-post-container' )
				.addClass( 'flow-post-highlighted' );
			$elem.scrollIntoView();
		}

		if ( window.location.hash ) {
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
			highlightPost( $( '.flow-post' + window.location.hash ) );
		}

		// perform same highlighting to anchors within page for consistency
		$container.find( '.flow-icon-permalink' ).click( function( e ) {
			highlightPost( $( this.hash ) );
		} );

		// Setup topic collapser
		$container.find( '.topic-collapser li' )
			.on( "enable", function() {
				var $this = $( this ),
					collapseClass = $this.data( 'collapse-class' ),
					topicContainers = $( '.flow-topic-container' );

				$this.addClass( 'active' );
				if ( collapseClass === undefined ) {
					topicContainers.trigger( "expand" );
				} else {
					$( '.flow-container' ).addClass( collapseClass );
					topicContainers.trigger( "collapse" );
				}
			} )
			.on( "disable", function() {
				var $this = $( this ),
					collapseClass = $this.data( 'collapse-class' );
				$this.removeClass( 'active' );
				if ( collapseClass !== undefined ) {
					$( '.flow-container' ).removeClass( collapseClass );
				}
			} )
			.on( "click", function( e ) {
				var $this = $( this );

				e.preventDefault();
				if ( $this.is( '.active' ) ) {
					return;
				}

				$this.siblings( '.active' ).trigger( "disable" );
				$this.trigger( "enable" );
			} );
	} );

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

