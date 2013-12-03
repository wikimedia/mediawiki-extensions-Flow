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
					.data( 'revision_id', data[0]['header-id'] )
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
								},
								revisionId = $headerContainer.data( 'revision_id' );

							return mw.flow.api.editHeader( spec, content, revisionId );
						}
					).done( function ( output ) {
						$headerContainer
							.find( '#flow-header-content' )
							.empty()
							.append( $( output.rendered ) );
					} )
					.fail( function( error, data, $errorDiv ) {
						if (
							error === 'block-errors' &&
							data.header && data.header.prev_revision &&
							data.header.prev_revision.extra && data.header.prev_revision.extra.revision_id
						) {
							// update revision id
							$headerContainer.data( 'revision_id', data.header.prev_revision.extra.revision_id );

							$headerContainer.find( '.flow-edit-header-submit' )
								// change button message
								.val( mw.msg( 'flow-edit-header-submit-overwrite' ) )
								// add tipsy
								.click( function() {
									$( this ).tipsy( 'hide' );
								} )
								.tipsy( {
									fade: true,
									gravity: 'w',
									html: true,
									trigger: 'manual',
									className: 'flow-tipsy-destructive',
									title: function() {
										/*
										 * I'd prefer to only return content here, instead of wrapping
										 * it in a div. But we need to add some padding inside the tipsy.
										 * Tipsy has an option "className", which we could use to target
										 * the element though CSS, but that className is only applied
										 * _after_ tipsy has calculated position, so it's positioning
										 * would then be incorrect.
										 * Tossing in the content inside another div (which does have a
										 * class to target) works around this problem.
										 */

										// .html() only returns inner html, so attach the node to a new
										// parent & grab the full html there
										var $warning = $( '<div class="flow-tipsy-noflyout">' ).text( data.header.prev_revision.message );
										return $( '<div>' ).append( $warning ).html();
									}
								} )
								.tipsy( 'show' );

							// this error has been handled, remove warning message
							$errorDiv.remove();
						}
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