( function( $, mw ) {
	var getPagingLink = function( data ) {
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
				$('<a/>')
					.attr( 'href', '#' )
					.text( mw.msg( 'flow-paging-'+direction ) )
			);
	};

	$( document ).on( 'flow_init', function( e ) {
		$(this).find( '.flow-paging a' ).click( function(e) {
			e.preventDefault();
			var $pagingLinkDiv = $(this).closest('.flow-paging')
				.addClass( 'flow-paging-loading' );

			var offset = $pagingLinkDiv.data( 'offset' );
			var direction = $pagingLinkDiv.data( 'direction' );
			var limit = $pagingLinkDiv.data( 'limit' );
			var workflowId = $(this).flow( 'getTopicWorkflowId' );
			var pageName = $(this).closest( '.flow-container' ).data( 'page-title' );
			var requestLimit;

			// One more for paging forward.
			requestLimit = limit + 1;

			var request = {
				'topic_list' : {
					'offset-dir' : direction,
					'offset-id' : offset,
					'limit' : requestLimit,
					'render' : true
				}
			};

			mw.flow.api.readTopicList( pageName, workflowId, request )
				.done( function( data ) {
					var nextPage, prevPage;
					var topics = [];
					$.each( data, function( k, v ) {
						if ( k - 0 == k ) {
							topics.push( v );
						}
					} );

					var $output = $('<div/>');

					if ( direction == 'rev' && data.paging.rev ) {
						$output.append(
							getPagingLink(
								data.paging.rev
							)
						);
					}
					$.each( topics, function( k, topic ) {
						$output.append( topic.rendered );
					} );
					if ( direction == 'fwd' && data.paging.fwd ) {
						$output.append(
							getPagingLink(
								data.paging.fwd
							)
						);
					}

					var $replaceContent = $output.children();
					$pagingLinkDiv.next( '.flow-error' ).remove();
					$pagingLinkDiv.replaceWith( $replaceContent );
					$replaceContent.trigger( 'flow_init' );
				} )
				.fail( function() {
					$( '<div/>' )
						.flow( 'showError', arguments )
						.insertAfter( $pagingLinkDiv );
				} );
		} );
	} );

	$( function() {
		var $window = $(window);
		$window
			.scroll( function(e) {
				$( '.flow-paging-fwd' ).each( function() {
					var $pagingLinkDiv = $( this );
					if ( $pagingLinkDiv.hasClass( 'flow-paging-loading' ) ) {
						// Already loading
						return;
					}

					// Trigger infinite scroll when the user is half a screenlength
					//  away from the end.
					var windowEnd = $window.scrollTop() + (1.5 * $window.height() );

					if ( $pagingLinkDiv.position().top < windowEnd ) {
						$pagingLinkDiv.find('a').click();
					}
				} );
			} );
	} );
} )( jQuery, mediaWiki );