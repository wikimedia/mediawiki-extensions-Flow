(function ( $, mw ) {
	'use strict';

	/**
	 * The base object for initializing a Flow board
	 */
	mw.flow.discussion = {
		/** Stores the jQuery container element for Flow */
		$container: null,

		/**
		 * Triggered to collapse (hide) a topic's replies. Speed 0 hides instantly.
		 * @param {jQuery} $el
		 * @param {int|string} [speed]
		 */
		topicCollapse: function ( $el, speed ) {
			$el.closest( '.flow-topic-container' ).addClass( 'flow-topic-closed' ).children( '.flow-topic-children-container' );

			if ( speed === 0 ) {
				$el.hide();
			} else {
				$el.slideUp( speed );
			}
		},

		/**
		 * Triggered to expand (show) a topic's replies. Speed 0 shows instantly.
		 * @param {jQuery} $el
		 * @param {int|string} [speed]
		 */
		topicExpand: function ( $el, speed ) {
			$el.closest( '.flow-topic-container' ).removeClass( 'flow-topic-closed' ).children( '.flow-topic-children-container' );

			if ( speed === 0 ) {
				$el.show();
			} else {
				$el.slideDown( speed );
			}
		},

		/**
		 * Adds $elem's nearest .flow-post-container by adding .flow-post-highlighted, then scrolls the viewport to it.
		 * @param {jQuery|$} $el
		 * @param {string|int} [speed]
		 */
		highlightElement: function ( $el, speed ) {
			if ( !$el.length ) {
				return;
			}

			this.$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );
			$el
				.closest( '.flow-post-container' )
				.addClass( 'flow-post-highlighted' );

			$el.conditionalScrollIntoView( speed );
		},

		/**
		 * Initializes Tipsy on Flow
		 */
		setupTipsy: function () {
			// Set up tipsy to show the user who made last topic/post modification.
			this.$container.find( '.flow-content-modified-tipsy-link' ).each( function () {
				$( this ).flow(
					'setupTipsy',
					$( this ).parent().find( '.flow-content-modified-tipsy-flyout' ),
					'mouseover'
				);
			} );

			// Tipsy markup: <div class=flow-tipsy><a class="flow-tipsy-link"/><div class="flow-tipsy-flyout"/></div>
			// .flow-tipsy-link is the target that, when clicked, will trigger the tipsy dialog.
			// .flow-tipsy-flyout is the dialog content.
			this.$container.find( '.flow-tipsy-link' ).each( function () {
				$( this ).flow(
					'setupTipsy',
					$( this ).parent( '.flow-tipsy' ).find( '.flow-tipsy-flyout' ),
					'click'
				);
			} );

			// Handle document clicks so as to globally close tipsy
			$( document )
				.off( 'click.tipsy' ) // Unbind; this is global, so only have one at a time
				.on( 'click.tipsy', function ( event ) {
					// If the parents are not tipsy, close the dialog.
					if ( !$( event.target ).parents().is( '.flow-tipsy-link, .flow-tipsy-flyout' ) ) {
						$( '.flow-tipsy-open' ).each( function () {
							$( this )
								.removeClass( 'flow-tipsy-open' )
								.tipsy( 'hide' );
						} );
					}
				} );
		},

		/**
		 * Initialize topics, posts, and bind event handlers
		 */
		setupFlow: function () {
			var $newTopic = this.$container.find( '.flow-new-topic-container' ).addBack( '.flow-new-topic-container' ),
				$topics = this.$container.find( '.flow-topic-container' ).addBack( '.flow-topic-container' ),
				$posts = this.$container.find( '.flow-post' ).addBack( '.flow-post' );

			// init new title interaction
			if ( $newTopic.length && !$newTopic.data( 'flow-initialised-new-topic' ) ) {
				new mw.flow.action.topic.new();

				$newTopic.data( 'flow-initialised-new-topic', true );
			}

			// init topic interaction
			$topics.each( function () {
				/*
				 * Initialisation (registerInitFunction) is re-done after every
				 * change (e.g. adding a new topic), by .trigger( 'flow_init' )
				 *
				 * We're now creating JS objects by looping all DOM title nodes.
				 * We don't want to create multiple objects when re-initializing
				 * so let's mark initialised state in a data-attribute.
				 */
				if ( !$( this ).data( 'flow-initialised-topic' ) ) {
					var topicId = $( this ).data( 'topic-id' );
					new mw.flow.discussion.topic( topicId );

					$( this ).data( 'flow-initialised-topic', true );
				}
			} );

			// init post interaction
			$posts.each( function () {
				/*
				 * Initialisation (registerInitFunction) is re-done after every
				 * change (e.g. adding a new topic), by .trigger( 'flow_init' )
				 *
				 * We're now creating JS objects by looping all DOM title nodes.
				 * We don't want to create multiple objects when re-initializing
				 * so let's mark initialised state in a data-attribute.
				 */
				if ( !$( this ).data( 'flow-initialised-post' ) ) {
					var postId = $( this ).parent().data( 'post-id' );
					new mw.flow.discussion.post( postId );

					$( this ).data( 'flow-initialised-post', true );
				}
			} );

			this.$container
				// Remove old event handlers
				.off( '.mw.flow.discussion' )
				// On topic titlebar click, trigger collapse/expand event
				.on( 'click.mw.flow.discussion', '.flow-titlebar', mw.flow.action.ui.titlebarClick )
				// On permalink click, go to topic or post and highlight it
				.on( 'click.mw.flow.discussion', '.flow-icon-permalink', mw.flow.action.ui.permalinkClick)
				// On topic collapser click, choose a different collapsing level
				.on( 'click.mw.flow.discussion', '.topic-collapser li', mw.flow.action.ui.topicCollapserClick);
		},

		/**
		 * Prepare moderation handlers in Flow.
		 * @todo cleanup
		 */
		setupModeration: function () {
			// Moderation controls
			var moderationTypes = [ 'hide', 'delete', 'suppress', 'restore' ],
				classes = [],
				i, j, classNames;
			for ( i = moderationTypes.length; i--; ) {
				classNames = [
					'.flow-' + moderationTypes[i] + '-post-link',
					'.flow-' + moderationTypes[i] + '-topic-link'
				];

				for ( j = classNames.length; j--; ) {
					classes.push( classNames[j] );

					// don't use .data() to set the type, because this is not the
					// exact node that will be clicked on: tipsy will clone the node,
					// outside of $container
					this.$container.find( classNames[j] ).attr( 'data-moderation-type', moderationTypes[i] );
				}
			}

			// live bind to document because tipsy will move the links outside $container
			// only bind once! when node is replaced (e.g. after moderation), we don't
			// want the event to be bound again, since document node won't have changed
			if ( !$( document ).data( 'moderation-bound' ) ) {
				$( document )
					.data( 'moderation-bound', true )
					.on( 'click', classes.join( ', ' ), function ( event ) {
						var moderationType = $( this ).data( 'moderation-type' );
						event.preventDefault();

						// while moderation is being loaded, hide buttons & show spinner
						$( this ).closest( 'li' )
							.fadeOut( 'fast', function () {
								$( this ).injectSpinner( { 'size': 'medium', 'id': 'flow-moderation-loading' } );
							} );

						mw.loader.using( ['ext.flow.moderation'], function () {
							var $tipsyTrigger = $( '.flow-tipsy-open' );

							$.removeSpinner( 'flow-moderation-loading' );

							// close tipsy
							$tipsyTrigger.each( function () {
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
				.on( 'click', function ( event ) {
					event.preventDefault();

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
				.prependTo( this.$container.find( '.flow-post-content-allowed' ) );
		}
	};

	/** Stores event handlers */
	mw.flow.action.ui = {
		/**
		 * onclick handler for the collapser options; will set a different collapsing style on the Flow container.
		 * @param {Event} event
		 */
		topicCollapserClick: function (event) {
			event.preventDefault();

			var $this = $( this ),
				$container = $this.closest( '.flow-container' ),
				collapseClass;

			// If this collapser is already active, do nothing.
			if ( $this.is( '.active' ) ) {
				return;
			}

			// Deactivate the current collapser
			collapseClass = $this.siblings( '.active' ).removeClass( 'active' ).data( 'collapse-class' ); // return the current collapse class

			// Activate the current class
			$this.addClass( 'active' );

			// Remove the previous collapse class
			if (collapseClass) {
				$container.removeClass( collapseClass );
			}

			// Expand or collapse based on clicked element's collapse class.
			collapseClass = $this.data( 'collapse-class' );
			if ( collapseClass === undefined ) {
				// Expand all topic containers
				mw.flow.discussion.topicExpand( $container.find( '.flow-topic-children-container' ) );
			} else {
				mw.flow.discussion.topicCollapse( $container.addClass( collapseClass ).find( '.flow-topic-children-container' ) );
			}
		},

		/**
		 * onclick handler for the titlebar of an existing topic to collapse or expand it.
		 * @param {Event} event
		 * @todo Discuss this; the behavior is not at all intuitive -- it does not look like a button.
		 */
		titlebarClick: function ( event ) {
			// If any of these child elements of the titlebar are clicked, ignore this. They have other actions.
			var ignore = [
				'.flow-edit-topic-link',
				'.flow-edit-title-form',
				'.flow-actions',
				'.flow-icon-permalink',
				'.flow-icon-watchlist',
				'.flow-datestamp a',
				'.flow-topic-comments-link'
			].join( ',' );

			if ( $( event.target ).is( ignore ) || $( event.target ).closest( ignore ).length ) {
				return;
			}

			var $topicContainer = $( this ).closest( '.flow-topic-container' ),
				$topicContainerChildren = $topicContainer.find( '.flow-topic-children-container' );
			if ( $topicContainer.is( '.flow-topic-closed' ) ) {
				// Or if this is if already closed
				mw.flow.discussion.topicExpand( $topicContainerChildren );
			} else {
				// Otherwise, collapse
				mw.flow.discussion.topicCollapse( $topicContainerChildren );
			}
		},

		/**
		 * onclick handler for permalink anchors; triggers highlightElement on the target post or topic.
		 * @param {Event} event
		 */
		permalinkClick: function ( event ) {
			event.preventDefault();

			mw.flow.discussion.highlightElement( $( this.hash ), 0 );
		}
	};

	/**
	 * To be bound to flow.registerInitFunction
	 * @param {Event} event
	 */
	mw.flow.discussion.init = ( function ( event ) {
		this.$container = $( event.target );

		this.setupFlow();
		this.setupTipsy();
		this.setupModeration();

		// Jump to a specific post if one is in the URL
		if ( window.location.hash ) {
			this.highlightElement( $( '.flow-post' + window.location.hash ) );
		}
	} ).bind( mw.flow.discussion );

	// Go!
	$( document ).flow( 'registerInitFunction', mw.flow.discussion.init );
})( jQuery, mediaWiki );
