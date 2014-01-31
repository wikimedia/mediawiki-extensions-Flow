( function ( $, mw ) {
	/**
	 * @param {object} data
	 * @returns {jQuery}
	 */
	var getPagingLink = function ( data ) {
		var direction = data.direction,
			offset = data.offset;
		
		return $( '<div/>' )
			.addClass( 'flow-paging' )
			.addClass( 'flow-paging-'+direction )
			.data( 'direction', direction )
			.data( 'offset', offset )
			.append(
				$( '<a/>' )
					.attr( 'href', '#' )
					.text( mw.msg( 'flow-paging-' + direction ) )
			);
	};

	$( document ).flow( 'registerInitFunction', function () {
		$( this ).find( '.flow-paging a' ).click( function ( e ) {
			e.preventDefault();
			var $pagingLinkDiv = $( this ).closest( '.flow-paging' ),
				pageSizes, $flowContainer, offset, direction, workflowId, pageName, request;

			if( $pagingLinkDiv.hasClass( 'flow-paging-loading' ) ) {
				return;
			}

			$pagingLinkDiv.addClass( 'flow-paging-loading' );

			pageSizes      = mw.config.get( 'wgFlowPageSize' );
			$flowContainer = $( this ).closest( '.flow-container' );
			offset         = $pagingLinkDiv.data( 'offset' );
			direction      = $pagingLinkDiv.data( 'direction' );
			workflowId     = $( this ).flow( 'getTopicWorkflowId' );
			pageName       = $( this ).closest( '.flow-container' ).data( 'page-title' );
			request = {
				'topiclist' : {
					'offset-dir' : direction,
					'offset-id'  : offset,
					'limit'      : pageSizes.expanded,
					'render'     : true
				}
			};

			if ( $flowContainer.hasClass( 'topic-collapsed-one-line' ) ) {
				request.topiclist.limit = pageSizes['collapsed-oneline'];
			} else if ( $flowContainer.hasClass( 'topic-collapsed-full' ) ) {
				request.topiclist.limit = pageSizes['collapsed-full'];
			}

			mw.flow.api.readTopicList( pageName, workflowId, request )
				.done( function ( data ) {
					var topics = [],
						$output = $( '<div/>' ),
						$replaceContent;

					$.each( data, function ( k, v ) {
						// all topics have a numeric key - other keys are
						// irrelevant here
						if ( !isNaN( k ) ) {
							topics.push( v );
						}
					} );


					if ( direction === 'rev' && data.paging.rev ) {
						$output.append(
							getPagingLink(
								data.paging.rev
							)
						);
					}

					$.each( topics, function ( k, topic ) {
						$output.append( topic.rendered );
					} );

					if ( $( '.topic-collapsed-one-line, .topic-collapsed-full' ).length > 0 ) {
						$output.find( '.flow-topic-container' ).addClass( 'flow-topic-closed'  )
							.children( '.flow-topic-children-container' )
							.css( 'display', 'none' );
					}

					if ( direction === 'fwd' && data.paging.fwd ) {
						$output.append(
							getPagingLink(
								data.paging.fwd
							)
						);
					}

					$replaceContent = $output.children();
					$pagingLinkDiv.next( '.flow-error' ).remove();
					$pagingLinkDiv.replaceWith( $replaceContent );
					$replaceContent.trigger( 'flow_init' );

					// trigger a new scroll, to check if new data should not be
					// fetched already (e.g. if all comments were deleted, this
					// batch may have come up empty, in which case we need to
					// load new data immediately, not wait for another scroll)
					$( window ).trigger( 'scroll' );
				} )
				.fail( function () {
					$( '<div/>' )
						.flow( 'showError', arguments )
						.insertAfter( $pagingLinkDiv );
				} );
		} );
	} );

	$( function () {
		var $window = $( window );
		$window
			.scroll( function () {
				$( '.flow-paging-fwd' ).each( function () {
					var $pagingLinkDiv = $( this ),

						// Trigger infinite scroll when the user is half a screenlength
						// away from the end.
						windowEnd = $window.scrollTop() + ( 1.5 * $window.height() );

					if ( $pagingLinkDiv.hasClass( 'flow-paging-loading' ) ) {
						// Already loading
						return;
					}

					if ( $pagingLinkDiv.position().top < windowEnd ) {
						$pagingLinkDiv.find( 'a' ).click();
					}
				} );
			} );
	} );
} )( jQuery, mediaWiki );
