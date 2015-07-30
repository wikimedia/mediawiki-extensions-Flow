( function ( $, mw ) {

	$( document ).ready( function () {

		$( 'form.posted' ).each( function ( index, form ) {
			$( form ).submit( function ( event ) {
				var $textarea = $( form ).find( 'textarea.flow-editor-initialized' ),
					moduleName = $( form ).data( 'module' ),
					editorExist = mw.flow.editor.exists( $textarea ),
					content, format;

				if ( editorExist ) {
					content = mw.html.escape( mw.flow.editor.getRawContent( $textarea ) );
					format = mw.flow.editor.getFormat( $textarea );

					$( form )
						.append( '<input type="hidden" name="' + moduleName + '_content" value="' + content + '" />' )
						.append( '<input type="hidden" name="' + moduleName + '_format" value="' + format + '" />' );
				}
			} );
		} );

	} );

} )( jQuery, mediaWiki );
