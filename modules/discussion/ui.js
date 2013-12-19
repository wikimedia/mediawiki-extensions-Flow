( function ( $, mw ) {
	$( document ).flow( 'registerInitFunction', function ( e ) {
		var $container = $( e.target );

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
		$container.find( '.flow-tipsy-link' ).tipsy( {
			fade: true,
			gravity: function() {
				// tipsy position can be defined via data-tipsy-gravity
				// attribute, or fall back to north
				return $( this ).data( 'tipsy-gravity') || 'n' ;
			},
			html: true,
			trigger: 'manual',
			title: function() {
				// .html() only returns inner html, so attach the node to a new
				// parent & grab the full html there
				var $clone = $( this ).parent( '.flow-tipsy' ).find( '.flow-tipsy-flyout' ).clone();
				return $( '<div>' ).append( $clone ).html();
			}
		} );
		// open tipsy dialog associated with this link
		$container.find( '.flow-tipsy-link' )
			.click( function ( e ) {
				e.preventDefault();

				// close other tipsy that may be open
				$( '.flow-tipsy-open' ).each( function() {
					$( this )
						.removeClass( 'flow-tipsy-open' )
						.tipsy( 'hide' );
				} );

				$( this ).addClass( 'flow-tipsy-open' );
				$( this ).tipsy( 'show' );
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

		// Set up reply form
		$container.find( '.flow-reply-form textarea' )
			.addClass( 'flow-reply-box-closed' )
			.attr( 'rows', '6' );

		// Reply form is meant to be visible for non-JS users
		// when JS is available, hide until button is clicked
		$container.find( '.flow-post-reply-container' )
			.hide();

		$container.find( '.flow-reply-link' )
			// when clicked, show textarea, load editor
			.click( function ( e ) {
				e.preventDefault();

				var $formContainer,
					$viewport = $( 'main, html' ),
					username = '',
					defaultContent = '';

				if ( $( this ).is( '.flow-topic-comments .flow-reply-link' ) ) {
					// We're in the topic title
					$formContainer = $( this ).closest( '.flow-topic-container' );

				} else {
					$formContainer = $( this ).closest( '.flow-post-container:not(.flow-post-max-depth)' ).find( '.flow-post-reply-container' );
					$( this ).closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).hide();

					// Prefill reply textarea with link to User we're replying to
					username = $( this ).closest( '.flow-post-container' ).data( 'creator-name' );
					if ( !mw.util.isIPv4Address( username , true ) && !mw.util.isIPv6Address( username , true ) ) {
						username = '[[' + mw.Title.newFromText( username, 2 ).getPrefixedText() + '|' + username + ']]';
					}
					defaultContent = username + ': ';
				}

				$formContainer
					.show()
					.find( '.flow-cancel-link' )
					.click( function( e ) {
						e.preventDefault();
						$( this )
							.closest( '.flow-topic-container' )
							.find( '.flow-topic-reply-container' )
							.show();
					} );

				$textarea = $formContainer.find( 'textarea' );
				$textarea
					.focus()
					.removeClass( 'flow-reply-box-closed' );
				mw.flow.editor.load( $textarea, defaultContent, 'wikitext' );

				// Scroll to the form
				$viewport.animate( {
					'scrollTop' : $formContainer.offset().top - $viewport.height() / 2
				}, 500 );
			} );

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-button' )
			.addClass( 'mw-ui-text' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function ( e ) {
				e.preventDefault();

				mw.flow.editor.destroy(
					$( this )
						.closest( '.flow-post-reply-container' )
						.find( ':data(flow-editor)' )
				);

				$( this ).closest( '.flow-post-reply-container' )
					.slideUp( 'fast', function () {
						$( this ).closest( '.flow-reply-form' )
							.find( 'textarea' )
								.addClass( 'flow-reply-box-closed' )
								.val( '' )
								.end()
							.find( '.flow-error' )
								.remove();
					} );
				$( this ).closest( '.flow-reply-form' ).flow( 'hidePreview' );
			} )
			.after( ' ' )
			.insertBefore( $container.find( '.flow-reply-form input[type=submit]' ) );
		$container.find( 'form.flow-reply-form' ).each( function() {
			$( this ).flow( 'setupPreview' );
		} );

		// Set up new topic form
		$container.find( '.flow-newtopic-step2' ).hide();
		$container.find( '.flow-newtopic-title' )
			.addClass( 'flow-newtopic-start' )
			.byteLimit( mw.config.get( 'wgFlowMaxTopicLength' ) )
			.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
			.click( function () {
				if ( !$( this ).hasClass( 'flow-newtopic-start' ) ) {
					return;
				}
				$( '.flow-newtopic-step2' )
					.show();
				$( this )
					.removeClass( 'flow-newtopic-start' )
					.attr( 'placeholder', mw.msg( 'flow-newtopic-title-placeholder' ) );
				$( '.flow-newtopic-submit' )
					.prop( 'disabled', true );
				$container.find( '.flow-new-topic-link' ).hide();

				mw.flow.editor.load( $( '.flow-newtopic-content' ) );
			} );

		$container.find( '.flow-new-topic-link' ).click ( function( e ) {
			e.preventDefault();
			$container.find( '.flow-newtopic-title' ).trigger( 'click' ).focus();
		} );

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-button' )
			.addClass( 'mw-ui-text' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function ( e ) {
				e.preventDefault();
				var $form = $( this ).closest( 'form.flow-newtopic-form' );

				mw.flow.editor.destroy( $form.find( '.flow-newtopic-content' ) );

				$( '.flow-newtopic-step2' )
					.slideUp( 'fast', function () {
						$form.find( '.flow-newtopic-title' )
							.val( '' )
							.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
							.addClass( 'flow-newtopic-start' );
						$form.find( '.flow-newtopic-content' )
							.val( '' );
						$form.find( '.flow-error' )
							.remove();
					} );
				$container.find( '.flow-new-topic-link' ).show();
				$form.flow( 'hidePreview' );
			} )
			.after( ' ' )
			.insertBefore( $container.find( '.flow-newtopic-form input[type=submit]' ) );

		$container.find( 'form.flow-newtopic-form' ).flow( 'setupPreview', { '.flow-newtopic-title': 'plain', 'textarea': 'parsed' } );
		// Hide reply button until user initiates reply (hidden in JS because it needs to be there for non-JS users)
		$container.find( '.flow-topic-reply-form .flow-post-form-controls' ).hide();
		$container.find( '.flow-topic-reply-form textarea' )
			// Override textarea height; doesn't need to be too large initially,
			// it'll auto-expand
			.attr( 'rows', '6' )
			// Textarea will auto-expand + textarea padding will cause the
			// resize grabber to be positioned badly (in FF) so get rid of it
			.css( 'resize', 'none' )
			// Set up topic reply form
			.click( function() {
				var $textbox = $( this );
				mw.flow.editor.load( $textbox );
				$form = $( this ).closest( '.flow-topic-reply-form' );

				// display controls
				$form.find( '.flow-post-form-controls' ).show();
			} );

		// Add cancel link to topic reply forms
		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-button' )
			.addClass( 'mw-ui-text' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function ( e ) {
				e.preventDefault();
				$form = $( this ).closest( '.flow-topic-reply-form' );
				$form.find( '.flow-post-form-controls' )
					.hide();

				mw.flow.editor.destroy( $form.find( 'textarea' ) );
				$form.flow( 'hidePreview' );
			} )
			.prependTo( $container.find( '.flow-topic-reply-form .flow-post-form-controls') );

		$container.find( 'form.flow-topic-reply-form' ).each( function() {
			$( this ).flow( 'setupPreview' );
		} );

		// Set up the scroll to new topic reply form
		$container.find( '.flow-topic-comments .flow-reply-link' ).click(
			function( e ) {
				var $hideElement = $( this ).closest( '.flow-topic-container' ).children( '.flow-post-container' ), self = this;
				e.stopPropagation();

				$hideElement.slideDown( function() {
					var $viewport = $( 'html,body' ),
						$replyContainer = $( '#flow-topic-reply-' + $( self ).data( 'topic-id' ) );

					$viewport.animate( {
						'scrollTop': $replyContainer.offset().top - $viewport.height()/2,
						'complete': $replyContainer.find( '.flow-topic-reply-content' ).click()
					}, 500 );
				} );
			}
		);

		// Set up folding
		var $topicContainers = $container.is( '.flow-topic-container' ) ? $container : $container.find( '.flow-topic-container' );
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
					'.flow-edit-topic-link',
					'.flow-edit-title-form',
					'.flow-actions',
					'.flow-icon-permalink',
					'.flow-icon-watchlist',
					'.flow-datestamp a',
					'.flow-topic-comments-link'
				];
				if ( $( e.target ).closest( ignore.join( ',' ) ).length > 0 ) {
					return true;
				}

				var $topicContainer = $( this ).closest( '.flow-topic-container' );
				if ( $topicContainer.is( '.flow-topic-closed' ) ) {
					$topicContainer.trigger( "expand" );
				} else {
					$topicContainer.trigger( "collapse" );
				}
			} );

		// Moderation controls
		var moderationTypes = [ 'hide', 'delete', 'suppress', 'restore' ],
			classes = [];
		$.each( moderationTypes, function( k, moderationType ) {
			var classNames = ['.flow-'+moderationType+'-post-link', '.flow-'+moderationType+'-topic-link'];

			for ( var i in classNames ) {
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
		$( document )
			.one( 'click', classes.join( ', ' ), function( e ) {
				var moderationType = $( this ).data( 'moderation-type' );

				e.preventDefault();

				// while moderation is being loaded, hide buttons & show spinner
				$( this ).closest( 'li' )
					.fadeOut( 'fast', function() {
						$( this ).injectSpinner( { 'size' : 'medium', 'id' : 'flow-moderation-loading' } );
					} );

				mw.loader.using( ['ext.flow.moderation'], function() {
					$tipsyTrigger = $( '.flow-tipsy-open' );

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
			}
		);

		// Moderated posts need click to display content
		$( '<a href="#" class="flow-post-moderated-view"></a>' )
			.after( ' ' )
			.text( mw.msg( 'flow-post-moderated-toggle-show' ) )
			.click( function ( e ) {
				e.preventDefault();

				var $post = $( this ).closest( '.flow-post-moderated' ),
					$comment = $post.find( '.flow-post-main' );

				if ( $comment.is( ':visible' ) ) {
					$comment.hide();
					$post.removeClass( 'flow-post-moderated-visible' );
					$( this ).text( mw.msg( 'flow-post-moderated-toggle-show' ) );
				} else {
					$post.addClass( 'flow-post-moderated-visible' );
					$comment.show();
					$( this ).text( mw.msg( 'flow-post-moderated-toggle-hide' ) );
				}
			} )
			// only add toggle if user is allowed to view the content
			.prependTo( $container.find( '.flow-post-content-allowed' ) );

		var highlightPost = function( $elem ) {
			var $viewport = $( 'main, html' );

			if ( !$elem.length ) {
				return;
			}
			$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );
			$elem
				.closest( '.flow-post-container' )
				.addClass( 'flow-post-highlighted' );
			$viewport.animate( {
				'scrollTop' : $elem.offset().top - $viewport.height()/2
			}, 500 );
		};

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
			highlightPost( $( window.location.hash ) );
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
			sourceBase != parseInt( sourceBase, 10 ) ||
			destBase != parseInt( destBase, 10 ) ||
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

