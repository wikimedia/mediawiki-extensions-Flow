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
		this.emitWithReturn( 'makeContentInteractive', this );

		// We don't replace anything with this method (we do with flowBoardComponentReinitializeContainer)
		return $();
	};

	//
	// Interactive handlers
	//

	/**
	 *
	 * @param {Event} event
	 */
	FlowBoardAndHistoryComponentBase.UI.events.interactiveHandlers.moderationDialog = function ( event ) {
		var $form,
			$this = $( this ),
			flowComponent = mw.flow.getPrototypeMethod( 'boardAndHistoryBase', 'getInstanceByElement' )( $this ),
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
	};

	/**
	 * Cancels and closes a form. If text has been entered, issues a warning first.
	 * @param {Event} event
	 */
	FlowBoardAndHistoryComponentBase.UI.events.interactiveHandlers.cancelForm = function ( event ) {
		var target = this,
			$form = $( this ).closest( 'form' ),
			flowComponent = mw.flow.getPrototypeMethod( 'boardAndHistoryBase', 'getInstanceByElement' )( $form ),
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
		if ( !changedFieldCount || confirm( flowComponent.constructor.static.TemplateEngine.l10n( 'flow-cancel-warning' ) ) ) {
			// Reset the form content
			$form[0].reset();

			// Trigger for flow-actions-disabler
			$form.find( 'textarea, :text' ).trigger( 'keyup' );

			// Hide the form
			flowComponent.emitWithReturn( 'hideForm', $form );

			// Get rid of existing error messages
			flowComponent.emitWithReturn( 'removeError', $form );

			// Trigger the cancel callback
			if ( $form.data( 'flow-cancel-callback' ) ) {
				$.each( $form.data( 'flow-cancel-callback' ), function ( idx, fn ) {
					fn.call( target, event );
				} );
			}
		}
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
