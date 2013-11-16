( function ( $, mw ) {
	'use strict';

	mw.flow.preview = {
		'show': function( $container, contents, $form ) {
			var content = '';
			for ( var identifier in contents ) {
				if ( content ) {
					content += '<br />';
				}
				if ( contents[identifier] === 'parse' ) {
					content += mw.flow.parsoid.convert( mw.flow.editor.getFormat(), 'html', $form.find( identifier ).val() );
				} else {
					content += mw.html.escape( $form.find( identifier ).val() );
				}
			}
			$container.html( content ).show();
		},

		'hide': function( $form ) {
			$form.find( '.flow-content-preview' ).empty().hide();
		},

		'attachPreview': function( $form, contents ) {
			if ( !contents ) {
				contents = { 'textarea': 'parse' };
			}
			$( '<input />' )
				.attr( 'type', 'submit' )
				.val( mw.msg( 'flow-preview' ) )
				.addClass( 'mw-ui-button' )
				.addClass( 'flow-preview-submit' )
				.click( function ( e ) {
					e.preventDefault();
					mw.flow.preview.show( $form.find( '.flow-content-preview' ), contents, $form );
				} ).insertAfter( $form.find( '.flow-cancel-link' ) );

			$form.prepend( $( '<div>' ).addClass( 'flow-content-preview' ) );
		},
	};
} ) ( jQuery, mediaWiki );
