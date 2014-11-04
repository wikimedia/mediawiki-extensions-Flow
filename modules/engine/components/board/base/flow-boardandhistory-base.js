/*!
 * Contains the base class for both FlowBoardComponent and FlowBoardHistoryComponent.
 * This is functionality that is used by both types of page, but not any other components.
 */

( function ( $, mw ) {
	/**
	 *
	 * @param {jQuery} $container
	 * @constructor
	 */
	function FlowBoardAndHistoryComponentBase( $container ) {
		this.bindNodeHandlers( FlowBoardAndHistoryComponentBase.UI.events );
	}
	OO.initClass( FlowBoardAndHistoryComponentBase );

	FlowBoardAndHistoryComponentBase.UI = {
		events: {
			apiPreHandlers: {},
			apiHandlers: {},
			interactiveHandlers: {}
		}
	};

	// Register
	mw.flow.registerComponent( 'boardAndHistoryBase', FlowBoardAndHistoryComponentBase );

	//
	// Methods
	//

	/**
	 * Sets up the board and base properties on this class.
	 * Returns either FALSE for failure, or jQuery object of old nodes that were replaced.
	 * @param {jQuery|boolean} $container
	 * @return {boolean|jQuery}
	 */
	FlowBoardAndHistoryComponentBase.prototype.reinitializeContainer = function ( $container ) {
		if ( $container === false ) {
			return false;
		}

		// Progressively enhance the board and its forms
		// @todo Needs a ~"liveUpdateComponents" method, since the functionality in makeContentInteractive needs to also run when we receive new content or update old content.
		// @todo move form stuff
		if ( $container.data( 'flow-component' ) !== 'board' ) {
			// Don't do this for FlowBoardComponent, because that runs makeContentInteractive in its own reinit
			this.emitWithReturn( 'makeContentInteractive', this );
		}

		// We don't replace anything with this method (we do with flowBoardComponentReinitializeContainer)
		return $();
	};

	//
	// Interactive handlers
	//

	/**
	 * @param {Event} event
	 * @returns {$.Promise}
	 */
	FlowBoardAndHistoryComponentBase.UI.events.interactiveHandlers.moderationDialog = function ( event ) {
		var $form,
			$this = $( this ),
			flowComponent = mw.flow.getPrototypeMethod( 'boardAndHistoryBase', 'getInstanceByElement' )( $this ),
			// hide, delete, suppress
			// @todo this could just be detected from the url
			role = $this.data( 'role' ),
			template = $this.data( 'flow-template' ),
			params = {
				editToken: mw.user.tokens.get( 'editToken' ), // might be unnecessary
				submitted: {
					moderationState: role
				},
				actions: {}
			},
			$deferred = $.Deferred(),
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
		flowComponent.assignSpawnedNode( modal.getNode(), flowComponent.$board );

		// Run loadHandlers
		flowComponent.emitWithReturn( 'makeContentInteractive', modal.getContentNode() );

		// Set flowDialogOwner for API callback @todo find a better way of doing this with mw.Modal
		$form = modal.getContentNode().find( 'form' ).data( 'flow-dialog-owner', $this );
		// Bind the cancel callback on the form
		flowComponent.emitWithReturn( 'addFormCancelCallback', $form, function () {
			mw.Modal.close( this );
		} );

		modal = null; // avoid permanent reference

		return $deferred.resolve().promise();
	};

	/**
	 * Cancels and closes a form. If text has been entered, issues a warning first.
	 * @param {Event} event
	 * @returns {$.Promise}
	 */
	FlowBoardAndHistoryComponentBase.UI.events.interactiveHandlers.cancelForm = function ( event ) {
		var target = this,
			$form = $( this ).closest( 'form' ),
			flowComponent = mw.flow.getPrototypeMethod( 'boardAndHistoryBase', 'getInstanceByElement' )( $form ),
			$fields = $form.find( 'textarea, :text' ),
			changedFieldCount = 0,
			$deferred = $.Deferred(),
			callbacks = $form.data( 'flow-cancel-callback' ) || [],
			schemaName = $( this ).data( 'flow-eventlog-schema' ),
			funnelId = $( this ).data( 'flow-eventlog-funnel-id' );

		event.preventDefault();

		// Only log cancel attempt if it was user-initiated, not when the cancel
		// was triggered by code (as part of a post-submit form destroy)
		if ( event.which ) {
			flowComponent.logEvent( schemaName, { action: 'cancel-attempt', funnelId: funnelId } );
		}

		// Check for non-empty fields of text
		$fields.each( function () {
			if ( $( this ).val() !== this.defaultValue ) {
				changedFieldCount++;
				return false;
			}
		} );

		// Only log if user had already entered text (= confirmation was requested)
		if ( changedFieldCount ) {
			if ( confirm( flowComponent.constructor.static.TemplateEngine.l10n( 'flow-cancel-warning' ) ) ) {
				flowComponent.logEvent( schemaName, { action: 'cancel-success', funnelId: funnelId } );
			} else {
				flowComponent.logEvent( schemaName, { action: 'cancel-abort', funnelId: funnelId } );

				// User aborted cancel, quit this function & don't destruct the form!
				return $deferred.reject().promise();
			}
		}

		// Reset the form content
		$form[0].reset();

		// Trigger for flow-actions-disabler
		$form.find( 'textarea, :text' ).trigger( 'keyup' );

		// Hide the form
		flowComponent.emitWithReturn( 'hideForm', $form );

		// Get rid of existing error messages
		flowComponent.emitWithReturn( 'removeError', $form );

		// Trigger the cancel callback
		$.each( callbacks, function ( idx, fn ) {
			fn.call( target, event );
		} );

		return $deferred.resolve().promise();
	};

	//
	// Static methods
	//

	/**
	 * Return true page is in topic namespace,
	 * and if $el is given, that if $el is also within .flow-post.
	 * @param {jQuery} [$el]
	 * @returns {boolean}
	 */
	function flowBoardInTopicNamespace( $el ) {
		return inTopicNamespace && ( !$el || $el.closest( '.flow-post' ).length === 0 );
	}
	FlowBoardAndHistoryComponentBase.static.inTopicNamespace = flowBoardInTopicNamespace;

	var inTopicNamespace = mw.config.get( 'wgNamespaceNumber' ) === mw.config.get( 'wgNamespaceIds' ).topic;
}( jQuery, mediaWiki ) );
