( function ( $, mw ) {

	$( document ).ready( function () {

		$( 'form[method=POST]' ).each( function ( index, form ) {
			$( form ).submit( function ( event ) {
				var $textarea = $( form ).find( 'textarea.flow-editor-initialized' ),
					contentParamName = $textarea.attr( 'name' ),
					moduleName = $( form ).data( 'module' ),
					editorExist = mw.flow.editor.exists( $textarea ),
					content, format;

				if ( editorExist ) {
					content = mw.flow.editor.getRawContent( $textarea );
					format = mw.flow.editor.getFormat( $textarea );

					$textarea.removeAttr( 'name' );
					$( form )
						.append( $( '<input>' ).attr( {
							type: 'hidden',
							name: contentParamName,
							value: content
						} ) )
						.append( $( '<input>' ).attr( {
							type: 'hidden',
							name: moduleName + '_format',
							value: format
						} ) );
				}
			} );
		} );

	} );

} )( jQuery, mediaWiki );
