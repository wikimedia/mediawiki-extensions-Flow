( function($, mw) {
	$( function() {
		var $form = $( '.flow-summary-form' ),
			$textarea = $form.find( 'textarea' );

		// convert text-area into editor
		mw.flow.editor.load( $textarea, $textarea.data( 'summary-id' ), 'storage.summary' );

		// when submitting the form, grab the editor's content
		$form.submit( function() {
			var $textarea = $( this ).find( 'textarea' ),
				content = mw.flow.editor.getContent( $textarea );

			// unload editor & paste content into textarea
			mw.flow.editor.destroy( $textarea );
			$textarea.val( content );
		} );
	});
} )( jQuery, mediaWiki );
