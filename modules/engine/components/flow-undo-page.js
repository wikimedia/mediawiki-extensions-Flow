( function ( $, mw ) {

	$( document ).ready( function () {

		$( 'form.posted' ).each( function( index, form ) {
			$( form ).submit( function( event ) {
				var $textarea = $( form ).find( 'textarea.flow-editor-initialized' ),
					moduleName = $( form ).data( 'module' ),
					editorExist = mw.flow.editor.exists( $textarea );

				if ( editorExist ) {
					var content = mw.html.escape( mw.flow.editor.getRawContent( $textarea ) ),
						format = mw.flow.editor.getFormat( $textarea ),
						contentInput = $( '<input type="hidden" name="' + moduleName + '_content" value="' + content + '" />' ),
						formatInput = $( '<input type="hidden" name="' + moduleName + '_format" value="' + format + '" />' );

					$( form ).append( contentInput ).append( formatInput );
				}
			});
		} );

	});

})( jQuery, mediaWiki );
