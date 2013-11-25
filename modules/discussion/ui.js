( function ( $, mw ) {
	$( document ).flow( 'registerInitFunction', function ( e ) {
		var $container = $( e.target );

		// Set up menus
		$container.find( '.flow-actions-link' )
			.click( function ( e ) {
				e.preventDefault();

				$( this ).next( '.flow-actions-flyout' )
					.show();
			} );
		$container.find( '.flow-actions-flyout a' )
			.add( 'body' )
			.click( function ( e ) {
				if ( !$( e.target ).hasClass( 'flow-actions-link' ) ) {
					$( '.flow-actions-flyout' )
						.fadeOut( 'fast' );
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
		$container.find( '.flow-titlebar' )
			.click( function ( e ) {
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
					'.flow-action-history-link',
					'.flow-topic-comments'
				];
				if ( $( e.target ).closest( ignore.join( ',' ) ).length > 0 ) {
					return true;
				}

				var $topicContainer = $( this ).closest( '.flow-topic-container' ),
					$hideElement = $topicContainer.children( '.flow-topic-children-container' );

				$topicContainer.toggleClass( 'flow-topic-closed' );

				if ( $topicContainer.hasClass( 'flow-topic-closed' ) ) {
					$hideElement.slideUp();
				} else {
					$hideElement.slideDown();
				}
			} );

		// Moderation controls
		var moderationTypes = [ 'hide', 'delete', 'censor', 'restore' ];
		$.each( moderationTypes, function( k, moderationType ) {
			$container
				.find( '.flow-'+moderationType+'-post-link, .flow-'+moderationType+'-topic-link' )
				.click(
				function(e) {
					e.preventDefault();
					var $link = $( this );
					$link
						.closest( '.flow-actions-flyout' )
						.fadeOut( 'fast' )
						.siblings( '.flow-actions-flyout' )
							.injectSpinner( { 'size' : 'small', 'id' : 'flow-moderation-loading' } );

					mw.loader.using( ['ext.flow.moderation'], function() {
						$.removeSpinner( 'flow-moderation-loading' );
						$link.flow( 'showModerationDialog', moderationType );
					} );
				}
			);
		} );

		// Moderated posts need click to display content
		$( '<a href="#" class="flow-post-moderated-view"></a>' )
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

			$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );
			$elem
				.closest( '.flow-post-container' )
				.addClass( 'flow-post-highlighted' );
			$viewport.animate( {
				'scrollTop' : $elem.offset().top - $viewport.height()/2
			}, 500 );
		};

		if ( window.location.hash ) {
			highlightPost( $( '.flow-post' + window.location.hash ) );
		}

		// perform same highlighting to anchors within page for consistency
		$container.find( '.flow-icon-permalink' ).click( function( e ) {
			highlightPost( $( this.hash ) );
		} );
	} );
} )( jQuery, mediaWiki );
