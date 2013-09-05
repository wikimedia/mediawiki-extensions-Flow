( function ( $, mw ) {
	$( document ).on( 'flow_init', function ( e ) {
		var $container = $( e.target );

		// Set up menus
		$container.find( '.flow-post-actions, .flow-topic-actions' )
			.click( function () {
				$( this ).children( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
					.show();
			} )
			.find( 'li' )
			.click( function () {
				$( this ).closest( '.flow-topic-actions, .flow-post-actions' )
					.children( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
					.fadeOut();
			} );

		// Set up timestamp on-hover
		$container.find( '.flow-topic-datestamp, .flow-datestamp' )
			.hover( function () {
				$( this ).children( '.flow-agotime' ).toggle();
				$( this ).children( '.flow-utctime' ).toggle();
			} );

		// Set up reply form
		$container.find( '.flow-reply-form textarea' )
			.addClass( 'flow-reply-box-closed' )
			.click( function () {
				$( this ).removeClass( 'flow-reply-box-closed' );
				$( this ).closest( 'form' )
					.children( '.flow-post-form-extras' )
					.show();
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
			} );

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-destructive' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function ( e ) {
				e.preventDefault();
				var $form = $( this ).closest( 'form.flow-newtopic-form' );

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
			if ( $( e.target )
				.closest( '.flow-post-actions, .flow-topic-actions' )
				.length === 0
			) {
				$( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
					.fadeOut( 'fast' );
			}
		} );
} )( jQuery, mediaWiki );
