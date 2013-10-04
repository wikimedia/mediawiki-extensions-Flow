( function ( $, mw ) {
	$( document ).on( 'flow_init', function ( e ) {
		var $container = $( e.target );

		// Set up menus
		$container.find( '.flow-actions-link' )
			.click( function ( e ) {
				e.preventDefault();

				$( this ).next( '.flow-actions-box' )
					.show();
			} );
		$container.find( '.flow-actions-box a' )
			.add( 'body' )
			.click( function ( e ) {
				if ( !$( e.target ).hasClass( 'flow-actions-link' ) ) {
					$( '.flow-actions-box' )
						.fadeOut( 'fast' );
				}
			} );

		// Set up timestamp on-hover
		$container.find( '.flow-topic-datestamp, .flow-datestamp' )
			.hover(function () {
				$(this).children( '.flow-agotime' ).toggle();
				$(this).children( '.flow-utctime' ).toggle();
			} );

		// Set up post creator on-hover
		$container.find( '.flow-creator' )
			.hover( function () {
				$(this).children( '.flow-creator-simple' ).toggle();
				$(this).children( '.flow-creator-full' ).toggle();
			} );

		// Set up reply form
		$container.find( '.flow-reply-form textarea' )
			.addClass( 'flow-reply-box-closed' );

		// Reply form is meant to be visible for non-JS users
		// when JS is available, hide until button is clicked
		$container.find( '.flow-reply-form' )
			.hide();

		$container.find( '.flow-reply-link' )
			// when clicked, show textarea, load editor
			.click( function ( e ) {
				e.preventDefault();

				var $form = $( this ).closest( '.flow-post' ).siblings( 'form.flow-reply-form' ),
					$textarea = $form.find( 'textarea' );

				$form
					.show();

				$textarea
					.focus()
					.removeClass( 'flow-reply-box-closed' );
				mw.flow.editor.load( $textarea );
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
						.closest( '.flow-reply-form' )
						.find( ':data(flow-editor)' )
				);

				$( this ).closest( '.flow-reply-form' )
					.slideUp( 'fast', function () {
						$( this ).closest( '.flow-reply-form' )
							.find( 'textarea' )
								.addClass( 'flow-reply-box-closed' )
								.val( '' )
								.end()
							.find( '.flow-error' )
								.remove();
					} );
			} )
			.after( ' ' )
			.insertBefore( $container.find( '.flow-reply-form input[type=submit]' ) );

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
					.attr( 'disabled', 'disabled' );
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
			} )
			.after( ' ' )
			.insertBefore( $container.find( '.flow-newtopic-form input[type=submit]' ) );

		// Set up folding
		$container.find( '.flow-titlebar' )
			.click( function ( e ) {
				/*
				 * When clicked anywhere in these elements, the click should not
				 * collapse/expand the topic (likely because the node is, in
				 * HTML inside this node, but not visually)
				 */
				if ( $( e.target ).closest( '.flow-edit-topic-link, .flow-edit-title-form, .flow-actions' ).length > 0 ) {
					return true;
				}

				var $topicContainer = $( this ).closest( '.flow-topic-container' ),
					$hideElement = $topicContainer.children( '.flow-post-container' );

				$topicContainer.toggleClass( 'flow-topic-closed' );

				if ( $topicContainer.hasClass( 'flow-topic-closed' ) ) {
					$hideElement.slideUp();
				} else {
					$hideElement.slideDown();
				}
			} );
	} );
} )( jQuery, mediaWiki );
