/*!
 * Implements element interactive handler callbacks for FlowBoardComponent
 */

( function ( $, mw ) {
	/**
	 * Binds element interactive (click) handlers for FlowBoardComponent
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentInteractiveEventsMixin( $container ) {
		this.bindNodeHandlers( FlowBoardComponentInteractiveEventsMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentInteractiveEventsMixin );

	FlowBoardComponentInteractiveEventsMixin.UI = {
		events: {
			interactiveHandlers: {}
		}
	};

	//
	// interactive handlers
	//

	/**
	 * The activateForm handler will expand, scroll to, and then focus onto a form (target = field).
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.activateForm = function ( event ) {
		var $el, $form,
			href = $( this ).prop( 'href' ),
			hash = href.match( /#.+$/ ),
			$target = hash ? $( hash ) : false,
			flowBoard;

		// If this element is leading to another element on the page, find it.
		if ( !$target || !$target.length ) {
			return;
		}

		$el = $( hash[0] );
		$form = $el.closest( 'form' );

		if ( !$el.length || !$form.length ) {
			return;
		}

		flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form );

		// Is this a hidden form or invisible field? Make it visible.
		flowBoard.emitWithReturn( 'showForm', $form );

		if ( ! $form.is( ':visible' ) ) {
			flowBoard.emitWithReturn( 'expandTopicIfNecessary', $form.closest( '.flow-topic' ) );
		}

		// Is this a form field? Scroll to the form instead of jumping.
		$form.conditionalScrollIntoView().queue( function ( next ) {
			var $el = $( hash[0] );

			// After scroll, focus onto the form field itself
			if ( $el.is( 'textarea, :text' ) ) {
				$el.focus();
			}

			// jQuery.dequeue
			next();
		});

		// OK, we're done here. Don't use the hard link.
		event.preventDefault();
	};

	/**
	 * Cancels and closes a form. If text has been entered, issues a warning first.
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.cancelForm = function ( event ) {
		var target = this,
			$form = $( this ).closest( 'form' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form ),
			$fields = $form.find( 'textarea, :text' ),
			changedFieldCount = 0;

		event.preventDefault();

		// Check for non-empty fields of text
		$fields.each( function () {
			if ( $( this ).val() !== this.defaultValue ) {
				changedFieldCount++;
				return false;
			}
		} );
		// If all the text fields are empty, OR if the user confirms to close this with text already entered, do it.
		if ( !changedFieldCount || confirm( flowBoard.constructor.static.TemplateEngine.l10n( 'flow-cancel-warning' ) ) ) {
			// Reset the form content
			$form[0].reset();

			// Trigger for flow-actions-disabler
			$form.find( 'textarea, :text' ).trigger( 'keyup' );

			// Hide the form
			flowBoard.emitWithReturn( 'hideForm', $form );

			// Get rid of existing error messages
			flowBoard.emitWithReturn( 'removeError', $form );

			// Trigger the cancel callback
			if ( $form.data( 'flow-cancel-callback' ) ) {
				$.each( $form.data( 'flow-cancel-callback' ), function ( idx, fn ) {
					fn.call( target, event );
				} );
			}
		}
	};

	/**
	 * Calls FlowBoardComponent.UI.collapserState to set and render the new Collapser state.
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.collapserGroupToggle = function ( event ) {
		var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		// Don't apply to titlebars in the topic namespace
		if ( flowBoard.constructor.static.inTopicNamespace( $( this ) ) ) {
			return;
		}

		flowBoard.collapserState( flowBoard, this.href.match( /[a-z]+$/ )[0] );

		event.preventDefault();
	};

	/**
	 * Sets the visibility class based on the user toggle action.
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.collapserCollapsibleToggle = function ( event ) {
		var topicId, states,
			$target = $( event.target ),
			$this = $( this ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this ),
			isNotClickableElement = $target.not( '.flow-menu-js-drop' ) &&
				!$target.closest( 'a, button, input, textarea, select, ul, ol' ).length;

		// Don't apply to titlebars in the topic namespace
		if ( flowBoard.constructor.static.inTopicNamespace( $this ) ) {
			return;
		}

		if ( isNotClickableElement ) {
			$target = $( this ).closest( '.flow-post-main, .flow-topic' ); // @todo genericize this

			if ( flowBoard.$container.is( '.flow-board-collapsed-compact, .flow-board-collapsed-topics' ) ) {
				// Board default is collapsed; topic can be overridden to
				// expanded, or not.

				// We also remove flow-element-collapsed.  That is set on the
				// server for moderated posts, but an explicit user action
				// overrides that.
				if ( $target.is( '.flow-element-expanded' ) ) {
					$target.addClass( 'flow-element-collapsed' ).removeClass( 'flow-element-expanded' );
				} else {
					$target.removeClass( 'flow-element-collapsed' ).addClass( 'flow-element-expanded' );
				}
			} else {
				// .flow-board-collapsed-full; Board default is expanded;
				// topic can be overridden to collapsed, or not.
				if ( $target.is( '.flow-element-collapsed' ) ) {
					$target.removeClass( 'flow-element-collapsed' ).addClass( 'flow-element-expanded' );
				} else {
					$target.addClass( 'flow-element-collapsed' ).removeClass( 'flow-element-expanded' );
				}
			}

			topicId = $target.data('flow-id');

			// Save in sessionStorage
			states = mw.flow.StorageEngine.sessionStorage.getItem( 'collapserStates' ) || {};
			// Opposite of STORAGE_TO_CLASS
			if ( $target.hasClass( 'flow-element-expanded' ) ) {
				states[ topicId ] = '+';
			} else if ( $target.hasClass( 'flow-element-collapsed' ) ) {
				states[ topicId ] = '-';
			} else {
				delete states[ topicId ];
			}
			mw.flow.StorageEngine.sessionStorage.setItem( 'collapserStates', states );

			event.preventDefault();
			this.blur();
		}
	};

	/**
	 * Shows the form for editing a topic title, it's not already showing
	 *
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.editTopicTitle = function( event ) {
		var $title, flowBoard, $form, cancelCallback, linkParams,
			$link = $( this ),
			$topic = $link.closest( '.flow-topic' ),
			$topicTitleBar = $topic.children( '.flow-topic-titlebar' );

		$form = $topicTitleBar.find( 'form' );

		if ( $form.length === 0 ) {
			$title = $topicTitleBar.find( '.flow-topic-title' );

			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $link );

			cancelCallback = function() {
				$form.remove();
				$title.show();
			};

			linkParams = flowBoard.Api.getQueryMap( $link.attr( 'href' ) );

			$title.hide();

			$form = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_edit_topic_title',
				{
					'actions' : {
						'edit' : {
							'url' : $link.attr( 'href' )
						}
					},
					'content': {
						'content' : $title.data( 'title' )
					},
					'revisionId' : linkParams.etrevId
				}
			) ).children();


			flowBoard.emitWithReturn( 'addFormCancelCallback', $form, cancelCallback );
			$form
				.data( 'flow-initial-state', 'hidden' )
				.insertAfter( $title );
		}

		$form.find( '.mw-ui-input' ).focus();

		event.preventDefault();
	};

	/**
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.activateReplyPost = function ( event ) {
		event.preventDefault();

		var $form,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) ),
			$post = $( this ).closest( '.flow-post' ),
			$targetPost = $( this ).closest( '.flow-post:not([data-flow-post-max-depth])' ),
			postId = $targetPost.data( 'flow-id' ),
			topicTitle = $post.closest( '.flow-topic' ).find( '.flow-topic-title' ).text(),
			replyToContent = $post.find( '.flow-post-content' ).filter( ':first' ).text() || topicTitle,
			author = $.trim( $post.find( '.flow-author' ).filter( ':first' ).find( '.mw-userlink' ).text() );

		// Check if reply form has already been opened
		if ( $post.data( 'flow-replying' ) ) {
			return;
		}
		$post.data( 'flow-replying', true );

		$form = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_reply_form',
			// arguments can be empty: we just want an empty reply form
			{
				actions: {
					reply: {
						url: $( this ).attr( 'href' ),
						title: mw.msg( 'flow-reply-link', author )
					}
				},
				postId: postId,
				author: {
					name: author
				},
				// text for flow-reply-topic-title-placeholder placeholder
				properties: {
					'topic-of-post': $.trim( replyToContent ).substr( 0, 200 )
				}
			}
		) ).children();

		// Set the cancel callback on this form so that it gets rid of the form.
		// We have to make sure the data attribute is added to the form; the
		// addBack is failsafe for when form is actually the root node in $form
		// already (there may or may not be parent containers)
		flowBoard.emitWithReturn( 'addFormCancelCallback', $form.find( 'form' ).addBack( 'form' ), function () {
			$post.removeData( 'flow-replying' );
			$form.remove();
		} );

		// Add reply form below the post being replied to (WRT max depth)
		$targetPost.children( '.flow-replies' ).append( $form );
		$form.conditionalScrollIntoView();
	};

	/**
	 * Allows you to open a flow-menu from a secondary click handler elsewhere.
	 * Uses data-flow-menu-target="< foo .flow-menu"
	 * @param {Event} event
	 */
	function flowEventsMixinMenuToggle( event ) {
		var $this = $( this ),
			flowComponent = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $this ),
			target = $this.data( 'flowMenuTarget' ),
			$target = $.findWithParent( $this, target );

		event.preventDefault();

		if ( !$target || !$target.length ) {
			flowComponent.debug( 'Could not find openFlowMenu target', arguments );
			return;
		}

		$target.find( '.flow-menu-js-drop' ).trigger( 'click' );
	}
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.menuToggle = flowEventsMixinMenuToggle;

	/**
	 *
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.moderationDialog = function ( event ) {
		var $form,
			$this = $( this ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this ),
			// hide, delete, suppress
			// @todo this could just be detected from the url
			role = $this.data( 'role' ),
			template = $this.data( 'template' ),
			params = {
				editToken: mw.user.tokens.get( 'editToken' ), // might be unnecessary
				submitted: {
					moderationState: role
				},
				actions: {}
			},
			modal;

		event.preventDefault();

		params.actions[role] = { url: $this.attr( 'href' ), title: $this.attr( 'title' ) };

		// Render the modal itself with mw-ui-modal
		modal = mw.Modal( {
			open:  $( mw.flow.TemplateEngine.processTemplateGetFragment( template, params ) ).children(),
			disableCloseOnOutsideClick: true
		} );

		// @todo remove this data-flow handler forwarder when data-mwui handlers are implemented
		// Have the events begin bubbling up from $board
		flowBoard.assignSpawnedNode( modal.getNode(), flowBoard.$board );

		// Run loadHandlers
		flowBoard.emitWithReturn( 'makeContentInteractive', modal.getContentNode() );

		// Set flowDialogOwner for API callback @todo find a better way of doing this with mw.Modal
		$form = modal.getContentNode().find( 'form' ).data( 'flow-dialog-owner', $this );
		// Bind the cancel callback on the form
		flowBoard.emitWithReturn( 'addFormCancelCallback', $form, function () {
			mw.Modal.close( this );
		} );

		modal = null; // avoid permanent reference
	};

	// @todo remove these data-flow handler forwarder callbacks when data-mwui handlers are implemented
	$( [ 'close', 'prevOrClose', 'nextOrSubmit', 'prev', 'next' ] ).each( function ( i, fn ) {
		// Assigns each handler with the prefix 'modal', eg. 'close' becomes 'modalClose'
		FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers[ 'modal' + fn.charAt(0).toUpperCase() + fn.substr( 1 ) ] = function ( event ) {
			event.preventDefault();

			// eg. call mw.Modal.close( this );
			mw.Modal[ fn ]( this );
		};
	} );

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentInteractiveEventsMixin );
}( jQuery, mediaWiki ) );