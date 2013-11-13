( function( $, mw ) {
$( document ).flow( 'registerInitFunction', function ( e ) {
	$container = $( e.target );
	// Overload "edit header" link.
	$container.find( '.flow-header-edit-link' )
		.click( function( e ) {
			e.preventDefault();
			var $flowContainer = $( this ).closest( '.flow-container' ),
				pageName = $flowContainer.data( 'page-title' ),
				workflowId = $flowContainer.data( 'workflow-id' ),
				$headerContainer = $( this ).closest( '#flow-header' );

			$headerContainer.find( '.flow-header-edit-link' )
				.hide();

			mw.flow.api.readHeader(
				pageName,
				workflowId,
				{
					'header' :
					{
						'contentFormat' : mw.flow.editor.getFormat()
					}
				}
			).done( function( data ) {
				if ( ! data[0] ) {
					console.dir( data );
					$( '<div/>' )
						.addClass( 'flow-error' )
						.text( mw.msg( 'flow-error-other' ) )
						.hide()
						.insertAfter( $headerContainer )
						.slideDown();
					return;
				}

				var startContent = data[0].missing ? '' : data[0]['*'];
				var dataFormat = data[0].missing ? 'wikitext' : data[0].format;

				$headerContainer
					.find( '#flow-header-content' )
					.flow( 'setupEditForm',
						'header',
						{
							'content' : startContent,
							'format' : dataFormat
						},
						function( content ) {
							var spec = {
								'workflowId' : workflowId,
								'page' : pageName
							};
							return mw.flow.api.editHeader( spec, content, data[0]['header-id'] );
						}
					).done( function ( output ) {
						$headerContainer
							.find( '#flow-header-content' )
							.empty()
							.append( $( output.rendered ) );
					} );

					$headerContainer.find( '.flow-cancel-link' )
						.click( function() {
							$headerContainer.find( '.flow-header-edit-link' )
								.show();
						} );
			} );
		} );
} );
} )( jQuery, mediaWiki );