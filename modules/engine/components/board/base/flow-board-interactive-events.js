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
	 * @returns {$.Promise}
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.activateForm = function ( event ) {
		var $el, $form,
			href = $( this ).prop( 'href' ),
			hash = href.match( /#.+$/ ),
			$target = hash ? $( hash ) : false,
			flowBoard,
			$deferred = $.Deferred();

		// Can't find target.
		if ( !$target || !$target.length ) {
			return $deferred.reject().promise();
		}

		$el = $( hash[0] );
		$form = $el.closest( 'form' );

		// Can't find form to activate
		if ( !$el.length || !$form.length ) {
			return $deferred.reject().promise();
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

		return $deferred.resolve().promise();
	};

	/**
	 * Toggles collapse state
	 *
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.collapserCollapsibleToggle = function ( event ) {
		var $target = $( this ).closest( '.flow-element-collapsible' ),
			$deferred = $.Deferred();

		if ( $target.is( '.flow-element-collapsed' ) ) {
			$target.removeClass( 'flow-element-collapsed' ).addClass( 'flow-element-expanded' );
		} else {
			$target.addClass( 'flow-element-collapsed' ).removeClass( 'flow-element-expanded' );
		}

		return $deferred.resolve().promise();
	};

	/**
	 * @param {Event} event
	 * @returns {$.Promise}
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.activateReplyTopic = function ( event ) {
		var $topic = $( this ).closest( '.flow-topic' ),
			topicId = $topic.data( 'flow-id' ),
			component;

		// The reply form is used in multiple places. This will check if it was
		// triggered from inside the topic reply form.
		if ( $( this ).closest( '#flow-reply-' + topicId ).length === 0 ) {
			// Not in topic reply form
			return $.Deferred().reject();
		}

		// Only if the textarea is compressed, is it being activated. Otherwise,
		// it has already expanded and this focus is now just re-focussing the
		// already active form
		if ( !$( this ).hasClass( 'flow-input-compressed' ) ) {
			// Form already activated
			return $.Deferred().reject();
		}

		component = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( this ) );
		component.logEvent(
			'FlowReplies',
			// log data
			{
				entrypoint: 'reply-bottom',
				action: 'initiate'
			},
			// nodes to forward funnel to
			$( this ).findWithParent(
				'< .flow-reply-form [data-role="cancel"],' +
				'< .flow-reply-form [data-role="action"][name="preview"],' +
				'< .flow-reply-form [data-role="submit"]'
			)
		);

		return $.Deferred().resolve();
	};

	/**
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.activateNewTopic = function ( event ) {
		var $form = $( this ).closest( '.flow-newtopic-form' ),
			component;

		// Only if the textarea is compressed, is it being activated. Otherwise,
		// it has already expanded and this focus is now just re-focussing the
		// already active form
		if ( $form.find( '.flow-input-compressed' ).length === 0 ) {
			// Form already activated
			return $.Deferred().reject();
		}

		component = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( this ) );
		component.logEvent(
			'FlowReplies',
			// log data
			{
				entrypoint: 'new-topic',
				action: 'initiate'
			},
			// nodes to forward funnel to
			$( this ).findWithParent(
				'< .flow-newtopic-form [data-role="cancel"],' +
				'< .flow-newtopic-form [data-role="action"][name="preview"],' +
				'< .flow-newtopic-form [data-role="submit"]'
			)
		);

		return $.Deferred().resolve();
	};

	/**
	 * @param {Event} event
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.activateReplyPost = function ( event ) {
		event.preventDefault();

		var $form,
			$this = $( this ),
			topicId = $this.closest( '.flow-topic' ).data( 'flow-id' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this ),
			$post = $this.closest( '.flow-post' ),
			href = $this.attr( 'href' ),
			uri = new mw.Uri( href ),
			postId = uri.query.topic_postId,
			$targetPost = $( '#flow-post-' + postId ),
			topicTitle = $post.closest( '.flow-topic' ).find( '.flow-topic-title' ).text(),
			replyToContent = $post.find( '.flow-post-content' ).filter( ':first' ).text() || topicTitle,
			author = $.trim( $post.find( '.flow-author' ).filter( ':first' ).find( '.mw-userlink' ).text() ),
			$deferred = $.Deferred();

		if ( $targetPost.length === 0 ) {
			$targetPost = $( '#flow-topic-' + postId );
		}

		// forward all top level replys to the topic reply box
		if ( $targetPost.is( '.flow-topic' ) ) {
			// @todo why does this need to trigger focus twice to work? With only
			// one call to focus nothing happens.
			$targetPost.find( '#flow-post-' + postId + '-form-content' ).focus().focus();
			return $deferred.reject().promise();
		}

		// Check if reply form has already been opened
		if ( $post.data( 'flow-replying' ) ) {
			return $deferred.reject().promise();
		}
		$post.data( 'flow-replying', true );

		$form = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_reply_form',
			// arguments can be empty: we just want an empty reply form
			{
				actions: {
					reply: {
						url: href,
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
				},
				// Topic:UUID
				articleTitle: mw.config.get( 'wgFormattedNamespaces' )[2600] + ':' + topicId[0].toUpperCase() + topicId.slice(1)
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

		// focus the input
		$form.find('textarea').focus();

		return $deferred.resolve().promise();
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
