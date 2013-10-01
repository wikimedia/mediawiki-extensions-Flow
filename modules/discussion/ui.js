( function ( $, mw ) {
	$( document ).on( 'flow_init', function ( e ) {
		var $container = $( e.target );

		// Set up menus
		$container.find( '.flow-post-actions-link, .flow-topic-actions-link' )
			.click( function ( e ) {
				e.preventDefault();

				$( this ).next( '.flow-post-actions, .flow-topic-actions' )
					.show();
			} )
			.find( 'li' )
			.click( function () {
				$( this ).closest( '.flow-topic-actions, .flow-post-actions' )
					.fadeOut();
			} );

		// Set up timestamp on-hover
		$container.find( '.flow-topic-datestamp, .flow-datestamp' )
			.hover(function() {
				$(this).children( '.flow-agotime' ).toggle();
				$(this).children( '.flow-utctime' ).toggle();
			} );

		// Set up post creator on-hover
		$container.find( '.flow-creator' )
			.hover( function() {
				$(this).children( '.flow-creator-simple' ).toggle();
				$(this).children( '.flow-creator-full' ).toggle();
			} );

		// Set up reply form
		$container.find( '.flow-reply-form textarea' )
			.addClass( 'flow-reply-box-closed' )
			// it's meant to be visible for non-JS users
			// when JS is available, hide until button is clicked
			.hide();

		$container.find( '.flow-reply-link' )
			// when clicked, show textarea, load editor
			.click( function ( e ) {
				e.preventDefault();

				var $form = $( this ).closest( '.flow-post' ).siblings( 'form' ),
					$textarea = $form.find( '.flow-reply-form textarea' );

				$form
					.children( '.flow-post-form-extras' )
					.show();

				$textarea.removeClass( 'flow-reply-box-closed' );
				mw.flow.editor.load( $textarea );
			} );

		$container.find( '.flow-post-form-extras' )
			.hide();

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-destructive' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function ( e ) {
				e.preventDefault();

				mw.flow.editor.destroy(
					$( this )
						.closest( '.flow-reply-form' )
						.find( ':data(flow-editor)' )
				);

				$( this ).closest( '.flow-post-form-extras' )
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

				mw.flow.editor.load( $( '.flow-newtopic-content' ) );
			} );

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-destructive' )
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
			} )
			.insertBefore( $container.find( '.flow-newtopic-form input[type=submit]' ) );

		// Set up folding
		$container.find( '.flow-topic-opener' )
			.click( function () {
				var $topicContainer = $( this ).closest( '.flow-topic-container' )
						.toggleClass( 'flow-topic-closed' ),
					$hideElement = $( this ).closest( '.flow-topic-container' )
						.children( '.flow-post-container' );

					if ( $topicContainer.hasClass( 'flow-topic-closed' ) ) {
						$hideElement.slideUp();
					} else {
						$hideElement.slideDown();
					}
			} );
	} );

	$( 'body' )
		.click( function ( e ) {
			if (
				// clicked link to open actions
				!$( e.target ).hasClass( 'flow-post-actions-link' ) &&
				!$( e.target ).hasClass( 'flow-topic-actions-link' ) &&
				// clicked inside actions box
				$( e.target )
					.closest( '.flow-post-actions, .flow-topic-actions' )
					.length === 0
			) {
				$( '.flow-post-actions, .flow-topic-actions' )
					.fadeOut( 'fast' );
			}
		} );
} )( jQuery, mediaWiki );
