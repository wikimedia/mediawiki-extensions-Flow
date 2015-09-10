( function ( $ ) {
	/**
	 * Flow edit post widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {string} postId The id of the post to edit
	 * @param {Object} [config] Configuration object
	 */
	mw.flow.ui.EditPostWidget = function mwFlowUiEditPostWidget( topicId, postId, config ) {
		config = config || {};

		this.topicId = topicId;
		this.postId = postId;

		// Parent constructor
		mw.flow.ui.EditPostWidget.parent.call( this, config );

		this.editor = new mw.flow.ui.EditorWidget( {
			saveMsgKey: mw.user.isAnon() ? 'flow-post-action-edit-post-submit-anonymously' : 'flow-post-action-edit-post-submit',
			classes: [ 'flow-ui-editPostWidget-editor' ]
		} );
		this.editor.toggle( true );

		this.anonWarning = new mw.flow.ui.AnonWarningWidget();
		this.anonWarning.toggle( true );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-editPostWidget-error flow-errors errorbox' ]
		} );
		this.error.toggle( false );

		this.api = new mw.flow.dm.APIHandler(
			'Topic:' + topicId
		);

		// Events
		this.editor.connect( this, {
			saveContent: 'onEditorSaveContent',
			cancel: 'onEditorCancel'
		} );

		this.$element
			.addClass( 'flow-ui-editPostWidget' )
			.append(
				this.anonWarning.$element,
				this.error.$element,
				this.editor.$element
			);

		// Load the editor
		this.editor.pushPending();
		this.editor.activate();

		// Get the post from the API
		var widget = this,
			contentFormat = this.editor.getContentFormat();

		this.api.getPost( topicId, postId, contentFormat ).then(
			function ( post ) {
				var content = OO.getProp( post, 'content', 'content' ),
					format = OO.getProp( post, 'content', 'format' );

				if ( content !== undefined && format !== undefined ) {
					// Give it to the editor
					widget.editor.setContent( content, format );

					// Update revisionId in the API
					widget.api.setCurrentRevision( post.revisionId );
				}

			},
			// Error fetching description
			function ( error ) {
				// Display error
				widget.error.setLabel( mw.msg( 'flow-error-external', error ) );
				widget.error.toggle( true );
			}
		).always( function () {
			// Unset pending editor
			widget.editor.popPending();
			// Focus again: pending editors are disabled and can't be focused
			widget.editor.focus();
		} );

	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditPostWidget, OO.ui.Widget );

	/* Events */

	/**
	 * Save the content of the reply
	 * @event saveContent
	 * @param {string} workflow The workflow this reply was saved under
	 * @param {string} content The content of the reply
	 * @param {string} contentFormat The format of the content of this reply
	 */

	/* Methods */

	/**
	 * Respond to editor cancel
	 */
	mw.flow.ui.EditPostWidget.prototype.onEditorCancel = function () {
		this.emit( 'cancel' );
	};

	/**
	 * Respond to editor save
	 */
	mw.flow.ui.EditPostWidget.prototype.onEditorSaveContent = function ( content, format ) {
		var widget = this,
			$captchaField, captcha;

		$captchaField = this.error.$label.find( '[name="wpCaptchaWord"]' );
		if ( $captchaField.length > 0 ) {
			captcha = {
				id: this.error.$label.find( '[name="wpCaptchaId"]' ).val(),
				answer: $captchaField.val()
			};
		}

		this.error.setLabel( '' );
		this.error.toggle( false );

		this.editor.pushPending();
		this.api.savePost( this.topicId, this.postId, content, format, captcha )
			.then( function ( workflow ) {
				widget.emit( 'saveContent', workflow, content, format );
			} )
			.then( null, function ( errorCode, errorObj ) {
				if ( /spamfilter$/.test( errorCode ) && errorObj.error.spamfilter === 'flow-spam-confirmedit-form' ) {
					widget.error.setLabel(
						// CAPTCHA form
						new OO.ui.HtmlSnippet( errorObj.error.info )
					);
				} else {
					widget.error.setLabel( errorObj.error && errorObj.error.info || errorObj.exception );
				}

				widget.error.toggle( true );
			} )
			.always( function () {
				widget.editor.popPending();
			} );
	};

	/**
	 * Focus the reply widget on the editor
	 */
	mw.flow.ui.EditPostWidget.prototype.focus = function () {
		this.editor.focus();
	};

	/**
	 * Destroy the widget
	 */
	mw.flow.ui.EditPostWidget.prototype.destroy = function () {
		this.editor.destroy();
	};

}( jQuery ) );
