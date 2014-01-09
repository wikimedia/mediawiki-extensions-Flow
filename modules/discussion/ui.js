( function ( $, mw ) {
	$( document ).flow( 'registerInitFunction', function ( e ) {
		var $container = $( e.target );

		/*
		 * Set up tipsy to show the user who made last topic/post modification
		 */
		$container.find( '.flow-content-modified-tipsy-link' ).each( function() {
			$( this ).flow(
				'setupTipsy',
				$( this ).parent().find( '.flow-content-modified-tipsy-flyout' ),
				'mouseover'
			);
		} );
		/*
		 * Set up tipsy.
		 *
		 * Tipsy html should look like this:
		 * <div class="flow-tipsy">
		 *     <a class="flow-tipsy-link"></a>
		 *     <div class="flow-tipsy-flyout"></div>
		 * </div>
		 *
		 * .flow-tipsy-link is the target that, when clicked, will trigger the
		 * tipsy dialog. .flow-tipsy-flyout is the dialog content.
		 */
		$container.find( '.flow-tipsy-link' ).each( function() {
			$( this ).flow(
				'setupTipsy',
				$( this ).parent( '.flow-tipsy' ).find( '.flow-tipsy-flyout' ),
				'click'
			);
		} );

		$( document )
			.click( function ( e ) {
				// check if clicked on tipsy trigger link or inside tipsy dialog
				var inTipsy =
					$( e.target ).closest( '.flow-tipsy-link' ).length > 0 ||
					$( e.target ).closest( '.flow-tipsy-flyout' ).length > 0;

				// if clicked anywhere outside of tipsy, close dialog
				if ( !inTipsy ) {
					$( '.flow-tipsy-open' ).each( function() {
						$( this )
							.removeClass( 'flow-tipsy-open' )
							.tipsy( 'hide' );
					} );
				}
			} );

		// Set up folding
		var $topicContainers = $container.is( '.flow-topic-container' ) ? $container : $container.find( '.flow-topic-container' );
		$topicContainers
			.on( "collapse", function() {
				$( this ).addClass( 'flow-topic-closed' )
					.children( '.flow-topic-children-container' )
					.slideUp();
			} )
			.on( "expand", function() {
				$( this ).removeClass( 'flow-topic-closed' )
					.children( '.flow-topic-children-container' )
					.slideDown();
			} );
		$container.find( '.flow-titlebar' )
			.on( "click", function ( e ) {
				/*
				 * When clicked anywhere in these elements, the click should not
				 * collapse/expand the topic (likely because the node is, in
				 * HTML inside this node, but not visually)
				 */
				var ignore = [
					'.flow-edit-topic-link',
					'.flow-edit-title-form',
					'.flow-actions',
					'.flow-icon-permalink',
					'.flow-icon-watchlist',
					'.flow-datestamp a',
					'.flow-topic-comments-link'
				];
				if ( $( e.target ).closest( ignore.join( ',' ) ).length > 0 ) {
					return true;
				}

				var $topicContainer = $( this ).closest( '.flow-topic-container' );
				if ( $topicContainer.is( '.flow-topic-closed' ) ) {
					$topicContainer.trigger( "expand" );
				} else {
					$topicContainer.trigger( "collapse" );
				}
			} );

		// Moderation controls
		var moderationTypes = [ 'hide', 'delete', 'suppress', 'restore' ],
			classes = [];
		$.each( moderationTypes, function( k, moderationType ) {
			var classNames = ['.flow-'+moderationType+'-post-link', '.flow-'+moderationType+'-topic-link'];

			for ( var i in classNames ) {
				classes.push( classNames[i] );

				// don't use .data() to set the type, because this is not the
				// exact node that will be clicked on: tipsy will clone the node,
				// outside of $container
				$container.find( classNames[i] ).attr( 'data-moderation-type', moderationType );
			}
		} );

		// live bind to document because tipsy will move the links outside $container
		// only bind once! when node is replaced (e.g. after moderation), we don't
		// want the event to be bound again, since document node won't have changed
		if ( !$( document ).data( 'moderation-bound' ) ) {
			$( document )
				.data( 'moderation-bound', true )
				.on( 'click', classes.join( ', ' ), function( e ) {
					var moderationType = $( this ).data( 'moderation-type' );
					e.preventDefault();

					// while moderation is being loaded, hide buttons & show spinner
					$( this ).closest( 'li' )
						.fadeOut( 'fast', function() {
							$( this ).injectSpinner( { 'size' : 'medium', 'id' : 'flow-moderation-loading' } );
						} );

					mw.loader.using( ['ext.flow.moderation'], function() {
						var $tipsyTrigger = $( '.flow-tipsy-open' );

						$.removeSpinner( 'flow-moderation-loading' );

						// close tipsy
						$tipsyTrigger.each( function() {
							$( this ).removeClass( 'flow-tipsy-open' );
							$( this ).tipsy( 'hide' );
						} );

						/*
						 * $tipsyTrigger is actually just the link that opens
						 * the tipsy window, not the moderation button we just
						 * clicked. For the purpose of showModerationDialog, it
						 * should do just fine though; all it needs the element
						 * for is to fetch parent post & topic nodes.
						 *
						 * @todo: we should refactor showModerationDialog some
						 * day to not rely on a specific node being tossed in;
						 * it's too vague what exactly it should be, and we
						 * may not always be able to guarantee nodes' positions
						 * withing the DOM (e.g. tipsy pulls them out)
						 */
						$tipsyTrigger.flow( 'showModerationDialog', moderationType );
					} );
				} );
		}

		// Moderated posts need click to display content
		$( '<a href="#" class="flow-post-moderated-view"></a>' )
			.after( ' ' )
			.text( mw.msg( 'flow-post-moderated-toggle-show' ) )
			.click( function ( e ) {
				e.preventDefault();

				var $post = $( this ).closest( '.flow-post-moderated' ),
					$comment = $post.find( '.flow-post-main' );

				if ( $comment.is( ':visible' ) ) {
					$comment.hide();
					$post.removeClass( 'flow-post-moderated-visible' );
					$( this ).text( mw.msg( 'flow-post-moderated-toggle-show' ) );
				} else {
					$post.addClass( 'flow-post-moderated-visible' );
					$comment.show();
					$( this ).text( mw.msg( 'flow-post-moderated-toggle-hide' ) );
				}
			} )
			// only add toggle if user is allowed to view the content
			.prependTo( $container.find( '.flow-post-content-allowed' ) );

		var highlightPost = function( $elem ) {
			if ( !$elem.length ) {
				return;
			}

			$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );
			$elem
				.closest( '.flow-post-container' )
				.addClass( 'flow-post-highlighted' );
			$elem.scrollIntoView();
		};

		if ( window.location.hash ) {
			highlightPost( $( '.flow-post' + window.location.hash ) );
		}

		// perform same highlighting to anchors within page for consistency
		$container.find( '.flow-icon-permalink' ).click( function( e ) {
			highlightPost( $( this.hash ) );
		} );

		// Setup topic collapser
		$container.find( '.topic-collapser li' )
			.on( "enable", function() {
				var $this = $( this ),
					collapseClass = $this.data( 'collapse-class' ),
					topicContainers = $( '.flow-topic-container' );

				$this.addClass( 'active' );
				if ( collapseClass === undefined ) {
					topicContainers.trigger( "expand" );
				} else {
					$( '.flow-container' ).addClass( collapseClass );
					topicContainers.trigger( "collapse" );
				}
			} )
			.on( "disable", function() {
				var $this = $( this ),
					collapseClass = $this.data( 'collapse-class' );
				$this.removeClass( 'active' );
				if ( collapseClass !== undefined ) {
					$( '.flow-container' ).removeClass( collapseClass );
				}
			} )
			.on( "click", function( e ) {
				var $this = $( this );

				e.preventDefault();
				if ( $this.is( '.active' ) ) {
					return;
				}

				$this.siblings( '.active' ).trigger( "disable" );
				$this.trigger( "enable" );
			} );
	} );
} )( jQuery, mediaWiki );
