( function($, mw) {
mw.flow.discussion = {
	'setupEmptyDisabler' : function( formSelector, fieldSelectors, submitSelector ) {
		var validateFunction = function( $container ) {
			var isOk = true;

			$.each( fieldSelectors, function() {
				// I have no idea why "toString()" is necessary
				if ( ! $container.find( this.toString() ).val() ) {
					isOk = false;
				}
			} );

			if ( isOk ) {
				$container.find( submitSelector )
					.removeAttr( 'disabled' );
			} else {
				$container.find( submitSelector )
					.attr( 'disabled', 'disabled' );
			}
		};
		$.each( fieldSelectors, function() {
				$(this.toString()).keyup( function(e) {
					$container = $(e.target).closest( formSelector );
					validateFunction( $container );
				} );
			} );

		$( formSelector ).each( function() {
			validateFunction( $(this) );
		} );
	}
};
$( function() {
	// Set up menus
	$( '.flow-post-actions, .flow-topic-actions' )
		.click( function(e) {
			$(this).children( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
				.show();
		} );

	$('body')
		.click( function(e) {
			if ( $(e.target)
				.closest('.flow-post-actions, .flow-topic-actions' )
				.length === 0
			) {
				$( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
					.fadeOut( 'fast' );
			}
		} );

	// Set up reply form
	$( '.flow-reply-form textarea' )
		.addClass( 'flow-reply-box-closed' )
		.click( function(e) {
			$(this).removeClass( 'flow-reply-box-closed' );
			$(this).closest( 'form' )
				.children( '.flow-post-form-extras' )
				.show();
		} );

	$( '.flow-post-form-extras' )
		.hide();

	$( '<a />' )
		.attr( 'href', '#' )
		.addClass( 'flow-cancel-link' )
		.addClass( 'mw-ui-destructive' )
		.text( mw.msg( 'flow-cancel' ) )
		.click( function(e) {
			e.preventDefault();
			$(this).closest( '.flow-post-form-extras' )
				.slideUp( 'fast', function() {
					$(this).closest( '.flow-reply-form' )
						.find( 'textarea' )
							.addClass( 'flow-reply-box-closed' )
							.val('');
				});
		} )
		.insertBefore( $('.flow-reply-form input[type=submit]') );

	mw.flow.discussion.setupEmptyDisabler(
		'form.flow-reply-form',
		['.flow-reply-content'],
		'.flow-reply-submit'
	);

	// Set up new topic form
	$( '.flow-newtopic-step2' ).hide();
	$( '.flow-newtopic-title' )
		.addClass( 'flow-newtopic-start' )
		.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
		.click( function(e) {
			if ( ! $(this).hasClass('flow-newtopic-start' ) ) {
				return;
			}
			$( '.flow-newtopic-step2' )
				.show();
			$(this)
				.removeClass( 'flow-newtopic-start' )
				.attr( 'placeholder', mw.msg( 'flow-newtopic-title-placeholder' ) );
			$( '.flow-newtopic-submit' )
				.attr( 'disabled', 'disabled' );
		} );

	mw.flow.discussion.setupEmptyDisabler(
		'form.flow-newtopic-form',
		[
			'.flow-newtopic-title',
			'.flow-newtopic-content'
		],
		'.flow-newtopic-submit'
	);

	$( '<a />' )
		.attr( 'href', '#' )
		.addClass( 'flow-cancel-link' )
		.addClass( 'mw-ui-destructive' )
		.text( mw.msg( 'flow-cancel' ) )
		.click( function(e) {
			e.preventDefault();
			$( '.flow-newtopic-step2' )
				.slideUp( 'fast', function() {
					$( '.flow-newtopic-title' )
						.val( '' )
						.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
						.addClass( 'flow-newtopic-start' );
					$( '.flow-newtopic-content' )
						.val( '' );
				} );
		} )
		.insertBefore( $('.flow-newtopic-form input[type=submit]') );
});
} )( jQuery, mediaWiki );