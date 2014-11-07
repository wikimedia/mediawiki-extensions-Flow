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
			author = $.trim( $post.find( '.flow-author' ).filter( ':first' ).find( '.mw-userlink' ).text() ),
			eventLog = new mw.flow.EventLog( 'FlowReplies' ); // @todo: create a shared object somewhere with the shared properties for FlowReplies schema

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
		flowBoard.emitWithReturn(
			'addFormCancelCallback',
			$form.find( 'form' ).addBack( 'form' ),
			function () {
				$post.removeData( 'flow-replying' );
				$form.remove();
			}
		);

		// Assign event log object to log cancel event
		flowBoard.emitWithReturn(
			'setFormCancelEventLog',
			$form.find( 'form' ).addBack( 'form' ),
			eventLog
		);

		// Add reply form below the post being replied to (WRT max depth)
		$targetPost.children( '.flow-replies' ).append( $form );
		$form.conditionalScrollIntoView();

		eventLog.logEvent( { action: 'initiate' } );
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
