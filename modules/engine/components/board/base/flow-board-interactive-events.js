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
	 * Toggles collapse state
	 *
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.collapserCollapsibleToggle = function ( event ) {
		var $target = $( this ).closest( '.flow-element-collapsible' );

		if ( $target.is( '.flow-element-collapsed' ) ) {
			$target.removeClass( 'flow-element-collapsed' ).addClass( 'flow-element-expanded' );
		} else {
			$target.addClass( 'flow-element-collapsed' ).removeClass( 'flow-element-expanded' );
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
			$targetPost = $( this ).closest( '.flow-post:not(.flow-post-max-depth)' ),
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
