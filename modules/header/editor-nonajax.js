( function ( $, mw ) {
	'use strict';

	$( function () {
		var $form = $( '.flow-header-form' );

		// edit-form is showing
		if ( $form.length > 0 ) {
			var $textarea = $form.find( 'textarea' ),
				workflowId = $form.flow( 'getTopicWorkflowId' ),
				pageName = $form.closest( '.flow-container' ).data( 'page-title' );

			// convert text-area into editor
			mw.flow.editor.load( $textarea, $textarea.val() );

			// when submitting the form, grab the editor's content
			$form.submit( function () {
				var $textarea = $( this ).find( 'textarea' ),
					content = mw.flow.editor.getContent( $textarea );

				// unload editor & paste content into textarea
				mw.flow.editor.destroy( $textarea );
				$textarea.val( content );
			} );
		}
	});
} ( jQuery, mediaWiki ) );
