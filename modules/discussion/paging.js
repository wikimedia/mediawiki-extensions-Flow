( function ( $, mw ) {
	/**
	 * @param {object} data
	 * @returns {jQuery}
	 */
	var getPagingLink = function ( data ) {
		var direction = data.direction,
			offset = data.offset,
			limit = data.limit;
		
		return $( '<div/>' )
			.addClass( 'flow-paging' )
			.addClass( 'flow-paging-'+direction )
			.data( 'direction', direction )
			.data( 'offset', offset )
			.data( 'limit', limit )
			.append(
				$( '<a/>' )
					.attr( 'href', '#' )
					.text( mw.msg( 'flow-paging-' + direction ) )
			);
	};

	$( document ).on( 'flow_init', function () {
		$( this ).find( '.flow-paging a' ).click( function ( e ) {
			e.preventDefault();
			var $pagingLinkDiv = $( this ).closest( '.flow-paging' );

			if( $pagingLinkDiv.hasClass( 'flow-paging-loading' ) ) {
				return;
			}
			$pagingLinkDiv.addClass( 'flow-paging-loading' );

			var offset = $pagingLinkDiv.data( 'offset' ),
				direction = $pagingLinkDiv.data( 'direction' ),
				limit = $pagingLinkDiv.data( 'limit' ),
				workflowId = $( this ).flow( 'getTopicWorkflowId' ),
				pageName = $( this ).closest( '.flow-container' ).data( 'page-title' ),

				// One more for paging forward.
				requestLimit = limit + 1,

				request = {
					'topic_list' : {
						'offset-dir' : direction,
						'offset-id' : offset,
						'limit' : requestLimit,
						'render' : true
					}
				};

			mw.flow.api.readTopicList( pageName, workflowId, request )
				.done( function ( data ) {
					var topics = [],
						$output = $( '<div/>' ),
						$replaceContent;

					$.each( data, function ( k, v ) {
						if ( parseInt( k, 10 ) == k ) {
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
