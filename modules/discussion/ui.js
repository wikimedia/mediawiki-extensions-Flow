(function ( $, mw ) {
	var FlowUI = {
		/** Stores the jQuery container element for Flow */
		$container: null,

		/** Stores event handlers */
		events: {
			/**
			 * onclick handler for the "reply" buttons in topic-reply, post-reply, and
			 * @param {Event} event
			 */
			replyClick: function ( event ) {
				event.preventDefault();

				var $this = $( this ),
					$form_container,
					$textarea,
					defaultContent = '';

				if ( $this.is( '.flow-topic-comments-link' ) ) {
					// This is a topic-reply link
					$form_container = $this.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' );

					// Expand the topic if it is currently collapsed
					FlowUI.topicExpand( $form_container.closest( '.flow-topic-children-container' ), 0 );

					// Focus onto the textarea
					$textarea = $form_container.find( '.flow-topic-reply-content' ).focus();
				} else {
					// This is a post-reply link
					$form_container = $this.closest( '.flow-post-container:not(.flow-post-max-depth)' ).find( '.flow-post-reply-container' );
					$textarea = $form_container.find( 'textarea' );

					// Hide the topic-reply form, as it can be confusing
					$this.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).hide();

					// Prefill reply textarea with link to User we're replying to
					var username = $this.closest( '.flow-post-container' ).data( 'creator-name' );
					if ( !mw.util.isIPv4Address( username, true ) && !mw.util.isIPv6Address( username, true ) ) {
						username = '[[' + mw.Title.newFromText( username, 2 ).getPrefixedText() + '|' + username + ']]';
					}
					defaultContent = username + ': ';
				}

				// Unhide the textarea
				$textarea.removeClass( 'flow-reply-box-closed' );

				// Load the editor
				mw.flow.editor.load( $textarea, defaultContent, 'wikitext' );

				// Scroll viewport to the form if it's not in view
				$form_container
					.show()
					.conditionalScrollIntoView()
					.queue( function ( next ) {
						mw.flow.editor.focus( $textarea );
						mw.flow.editor.moveCursorToEnd( $textarea );

						// Continue the queue
						next();
					} );
			},

			/**
			 * onclick handler for the "cancel" form buttons in new-topic, topic-reply, and post-reply.
			 * @param {Event} event
			 */
			cancelClick: function ( event ) {
				event.preventDefault();

				var $form,
					$this = $( this );

				// If this is a new-topic form...
				$form = $this.closest( '.flow-newtopic-form' );
				if ( $form.length ) {
					// Destroy the editor instance
					mw.flow.editor.destroy( $form.find( '.flow-newtopic-content' ) );

					// Go up to the parent form, and then find the new topic details textarea to collapse it.
					$this.closest( '.flow-newtopic-form' )
						.find( '.flow-newtopic-step2' )
							.stop()
							.slideUp( 'fast', function () {
								var $form = $this.closest( '.flow-newtopic-form' );
								// Reset the topic title field
								$form.find( '.flow-newtopic-title' )
									.val( '' )
									.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
									.addClass( 'flow-newtopic-start' );
								// Empty the textarea
								$form.find( '.flow-newtopic-content' )
									.val( '' );
								// Remove old errors
								$form.find( '.flow-error' )
									.remove();
							} );

					// Kill the preview
					$form.flow( 'hidePreview' );

					return;
				}

				// If this is a topic-reply form...
				$form = $this.closest( '.flow-topic-reply-form' );
				if ( $form.length ) {
					// Destroy the editor instance
					mw.flow.editor.destroy( $form.find( 'textarea' ) );

					// Hide the form controls
					$form.find( '.flow-post-form-controls' ).hide();

					// Kill the preview
					$form.flow( 'hidePreview' );

					return;
				}

				// If this is a post-reply form...
				$form = $this.closest( '.flow-reply-form' );
				if ( $form.length ) {
					// Destroy the editor instance
					mw.flow.editor.destroy(
						$this
							.closest( '.flow-post-reply-container' )
							.find( '.flow-reply-content' )
					);

					// Hide this reply container
					$this.closest( '.flow-post-reply-container' )
						.stop()
						.slideUp( 'fast', function () {
							$this.closest( '.flow-reply-form' )
								.find( 'textarea' )
									.addClass( 'flow-reply-box-closed' )
									.val( '' )
								.end()
								.find( '.flow-error' )
									.remove();
						} );

					// Kill the preview
					$form.flow( 'hidePreview' );

					// Bring back the topic-reply form
					$this.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).show();
				}
			},

			/**
			 * onfocus event for the "new topic" text field.
			 * @param {Event} event
			 */
			newTopicFocus: function ( event ) {
				var $this = $( this );

				// If this topic field is already active, do nothing
				if ( !$this.hasClass( 'flow-newtopic-start' ) ) {
					return;
				}

				// Find the parent form
				var $form = $this.closest( '.flow-newtopic-form' );

				// Find the submit button, and disable it until there is actual content
				$form.find( '.flow-newtopic-submit' ).prop( 'disabled', true );

				// Activate the title field
				$this
					.removeClass( 'flow-newtopic-start' )
					.attr( 'placeholder', mw.msg( 'flow-newtopic-title-placeholder' ) );

				// Show the topic content textarea
				$form.find( '.flow-newtopic-step2' ).show();

				// Load up the editor on the topic content textarea
				mw.flow.editor.load( $form.find( '.flow-newtopic-content' ) );
			},

			/**
			 * onfocus event for the "reply" textarea.
			 * @param {Event} event
			 */
			replyFocus: function ( event ) {
				var $this = $( this );
				mw.flow.editor.load( $this );

				// Display the form controls container
				$this.closest( '.flow-reply-form, .flow-topic-reply-form' ).find( '.flow-post-form-controls' ).show();
			},

			/**
			 * onclick handler for the collapser options; will set a different collapsing style on the Flow container.
			 * @param {Event} event
			 */
			topicCollapserClick: function (event) {
				event.preventDefault();

				var $this = $( this ),
					$container = $this.closest( '.flow-container' ),
					collapse_class;

				// If this collapser is already active, do nothing.
				if ( $this.is( '.active' ) ) {
					return;
				}

				// Deactivate the current collapser
				collapse_class = $this.siblings( '.active' ).removeClass( 'active' ).data( 'collapse-class' ); // return the current collapse class

				// Activate the current class
				$this.addClass( 'active' );

				// Remove the previous collapse class
				if (collapse_class) {
					$container.removeClass( collapse_class );
				}

				// Expand or collapse based on clicked element's collapse class.
				collapse_class = $this.data( 'collapse-class' );
				if ( collapse_class === undefined ) {
					// Expand all topic containers
					FlowUI.topicExpand( $container.find( '.flow-topic-children-container' ) );
				} else {
					FlowUI.topicCollapse( $container.addClass( collapse_class ).find( '.flow-topic-children-container' ) );
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

				var $topic_container = $( this ).closest( '.flow-topic-container' ),
					$topic_container_children = $topic_container.find( '.flow-topic-children-container' );
				if ( $topic_container.is( '.flow-topic-closed' ) ) {
					// Or if this is if already closed
					FlowUI.topicExpand( $topic_container_children );
				} else {
					// Otherwise, collapse
					FlowUI.topicCollapse( $topic_container_children );
				}
			},

			/**
			 * onclick handler for permalink anchors; triggers highlightElement on the target post or topic.
			 * @param {Event} event
			 */
			permalinkClick: function ( event ) {
				event.preventDefault();

				FlowUI.highlightElement( $( this.hash ), 0 );
			}
		},

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
		 * @param {jQuery} $el
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
		 * Performs setup tasks that are generic and useful for all form types
		 */
		setupGeneric: function () {
			// Remove all old cancel buttons
			this.$container.find( '.flow-cancel-link' ).remove();

			// Insert new cancel buttons
			$( '<a/>', {
				'href':  '#',
				'class': 'flow-cancel-link mw-ui-button mw-ui-text',
				'text':  mw.msg( 'flow-cancel' )
			} )
				.after( ' ' )
				.prependTo( this.$container.find('.flow-post-form-controls') );

			// Event handlers:
			this.$container
				// Remove old event handlers
				.off( '.FlowUI' )
				// On topic titlebar click, trigger collapse/expand event
				.on( 'click.FlowUI', '.flow-titlebar', FlowUI.events.titlebarClick )
				// On "reply" link click, show reply form
				.on( 'click.FlowUI', '.flow-reply-link', FlowUI.events.replyClick )
				// On "cancel" button click, hide and destroy reply form
				.on( 'click.FlowUI', '.flow-cancel-link', FlowUI.events.cancelClick )
				// On "new topic" field focus, show new topic form
				.on( 'focus.FlowUI', '.flow-newtopic-title', FlowUI.events.newTopicFocus )
				// On reply textarea focus, show topic/post reply form
				.on( 'focus.FlowUI', '.flow-topic-reply-form textarea', FlowUI.events.replyFocus )
				// On permalink click, go to topic or post and highlight it
				.on( 'click.FlowUI', '.flow-icon-permalink', FlowUI.events.permalinkClick)
				// On topic collapser click, choose a different collapsing level
				.on( 'click.FlowUI', '.topic-collapser li', FlowUI.events.topicCollapserClick);
		},

		/**
		 * Performs setup tasks for elements related to creating topics.
		 */
		setupTopicForms: function () {
			// Setup previews for the "new topic" form
			this.$container.find( '.flow-newtopic-form' ).flow(
				'setupPreview',
				{
					'.flow-newtopic-title': 'plain',
					'textarea': 'parsed'
				}
			);

			// Hide the new topic "add details" textarea until the "new topic" field is focused
			this.$container.find( '.flow-newtopic-step2' ).hide();

			// Initialize the "new topic" field and bind a focus event to it
			this.$container.find( '.flow-newtopic-title' )
				.addClass( 'flow-newtopic-start' )
				.byteLimit( mw.config.get( 'wgFlowMaxTopicLength' ) )
				.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) );
		},

		/**
		 * Performs setup tasks for elements related to replying to posts and topics.
		 */
		setupReplyForms: function () {
			// Setup preview on every reply form
			this.$container.find( 'form' ).filter( '.flow-topic-reply-form, .flow-reply-form' ).each( function () {
				$( this ).flow( 'setupPreview' );
			} );

			// All reply forms are visible by default (for non-JS users), so we hide them until "reply" onclick
			this.$container.find( '.flow-post-reply-container' ).hide();

			// Collapse all "reply" form textareas
			this.$container.find( '.flow-reply-form textarea' )
				.addClass( 'flow-reply-box-closed' )
				.attr( 'rows', '6' );

			// Hide inactive form elements until "reply" clicked
			this.$container.find( '.flow-topic-reply-form' )
				// Hide form controls
				.find( '.flow-post-form-controls' )
					.hide()
				.end()
				// Hide textarea
				.find( '.flow-topic-reply-form textarea' )
					// Override textarea height; doesn't need to be too large initially, it'll auto-expand
					.attr( 'rows', '6' )
					// Auto-expand + textarea padding makes resize grabber badly positioned in FF, so get rid of it
					.css( 'resize', 'none' );
		},

		/**
		 * Run the methods to prepare Flow for interaction.
		 */
		setupFlow: function () {
			// Setup general stuff
			this.setupGeneric();
			// Setup topic stuff
			this.setupTopicForms();
			// Setup reply stuff
			this.setupReplyForms();
		},

		/**
		 * Prepare moderation handlers in Flow.
		 * @todo cleanup
		 */
		setupModeration: function () {
			// Moderation controls
			var moderation_types = [ 'hide', 'delete', 'suppress', 'restore' ],
				classes = [],
				i, j, class_names;
			for ( i = moderation_types.length; i--; ) {
				class_names = [
					'.flow-' + moderation_types[i] + '-post-link',
					'.flow-' + moderation_types[i] + '-topic-link'
				];

				for ( j = class_names.length; j--; ) {
					classes.push( class_names[j] );

					// don't use .data() to set the type, because this is not the
					// exact node that will be clicked on: tipsy will clone the node,
					// outside of $container
					this.$container.find( class_names[j] ).attr( 'data-moderation-type', moderation_types[i] );
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
				.click( function ( event ) {
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

	/**
	 * To be bound to flow.registerInitFunction
	 * @param {Event} event
	 */
	FlowUI.init = ( function ( event ) {
		this.$container = $( event.target );
		this.setupTipsy();
		this.setupFlow();
		this.setupModeration();

		// Jump to a specific post if one is in the URL
		if ( window.location.hash ) {
			this.highlightElement( $( '.flow-post' + window.location.hash ) );
		}
	} ).bind( FlowUI );

	// Go!
	$( document ).flow( 'registerInitFunction', FlowUI.init );
})( jQuery, mediaWiki );
