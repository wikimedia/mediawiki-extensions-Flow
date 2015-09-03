( function ( $ ) {
	/**
	 * Flow reply widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {string} topicId The id of the topic this reply belongs to
	 * @param {string} replyTo The id this reply is a child of
	 * @param {Object} [config] Configuration object
	 * @cfg {boolean} [expandable=true] Initialize the widget with the editor
	 *  already expanded. Otherwise, the widget is initialized with a trigger input.
	 */
	mw.flow.ui.ReplyWidget = function mwFlowUiReplyWidget( topicId, replyTo, config ) {
		config = config || {};

		this.replyTo = replyTo;
		this.topicId = topicId;
		this.expandable = config.expandable === undefined ? true : config.expandable;
		this.expanded = !this.expandable;

		// Parent constructor
		mw.flow.ui.ReplyWidget.parent.call( this, config );

		if ( this.expandable ) {
			this.triggerInput = new OO.ui.TextInputWidget( {
				multiline: false,
				classes: [ 'flow-ui-replyWidget-trigger-input' ],
				placeholder: config.placeholder
			} );
			this.triggerInput.$element.on( 'focusin', this.onTriggerFocusIn.bind( this ) );
			this.$element.append( this.triggerInput.$element );
		}

		this.editor = new mw.flow.ui.EditorWidget( {
			placeholder: config.placeholder,
			saveMsgKey: mw.user.isAnon() ? 'flow-reply-link-anonymously' : 'flow-reply-link',
			classes: [ 'flow-ui-replyWidget-editor' ]
		} );
		this.editor.toggle( !this.expandable );

		this.anonWarning = new mw.flow.ui.AnonWarningWidget();
		this.anonWarning.toggle( !this.expandable );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-replyWidget-error flow-errors errorbox' ]
		} );
		this.error.toggle( false );

		this.api = new mw.flow.dm.APIHandler();

		// Events
		this.editor.connect( this, {
			saveContent: 'onEditorSaveContent',
			cancel: 'onEditorCancel'
		} );

		this.$element
			.addClass( 'flow-ui-replyWidget' )
			.append(
				this.anonWarning.$element,
				this.error.$element,
				this.editor.$element
			);

	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.ReplyWidget, OO.ui.Widget );

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
	 * Respond to trigger input focusin
	 */
	mw.flow.ui.ReplyWidget.prototype.onTriggerFocusIn = function () {
		this.activateEditor();
	};

	/**
	 * Respond to editor cancel
	 */
	mw.flow.ui.ReplyWidget.prototype.onEditorCancel = function () {
		if ( this.expandable ) {
			this.error.toggle( false );
			this.editor.toggle( false );
			this.anonWarning.toggle( false );
			this.triggerInput.toggle( true );
			this.expanded = false;
		} else {
			this.toggle( false );
		}
	};

	/**
	 * Respond to editor save
	 */
	mw.flow.ui.ReplyWidget.prototype.onEditorSaveContent = function ( content, format ) {
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
		this.error.toggle( false )
		;
		this.editor.pushPending();
		this.api.saveReply( this.topicId, this.replyTo, content, format, captcha )
			.then( function ( workflow ) {
				if ( widget.expandable ) {
					widget.triggerInput.toggle( true );
					widget.editor.toggle( false );
					widget.anonWarning.toggle( false );
					widget.expanded = false;
				}
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
	 * Check if the widget is expandable
	 */
	mw.flow.ui.ReplyWidget.prototype.isExpandable = function () {
		return this.expandable;
	};

	/**
	 * Check if the widget is expanded
	 */
	mw.flow.ui.ReplyWidget.prototype.isExpanded = function () {
		return this.expanded;
	};

	/**
	 * Force activation of the editor
	 */
	mw.flow.ui.ReplyWidget.prototype.activateEditor = function () {
		if ( this.triggerInput ) {
			this.triggerInput.setValue( '' );
			this.triggerInput.toggle( false );
		}
		this.toggle( true );
		this.anonWarning.toggle( true );
		this.editor.toggle( true );
		this.editor.activate();
		// If the editor was already active, focus it
		this.editor.focus();
		this.expanded = true;
	};

	/**
	 * Focus the reply widget on the editor
	 */
	mw.flow.ui.ReplyWidget.prototype.focus = function () {
		if ( this.isExpanded() ) {
			this.editor.focus();
		} else {
			// Trigger the focusin event
			this.activateEditor();
		}
	};

	/**
	 * Destroy the widget
	 */
	mw.flow.ui.ReplyWidget.prototype.destroy = function () {
		this.editor.destroy();
	};

}( jQuery ) );
