( function ( $ ) {
	/**
	 * Flow board description widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {string} replyTo The id this reply is a child of
	 * @param {Object} [config]
	 *
	 */
	mw.flow.ui.ReplyWidget = function mwFlowUiReplyWidget( replyTo, config ) {
		config = config || {};

		this.replyTo = replyTo;

		// Parent constructor
		mw.flow.ui.ReplyWidget.parent.call( this, config );

		this.triggerInput = new OO.ui.TextInputWidget( {
			multiline: false,
			classes: [ 'flow-ui-replyWidget-trigger-input' ],
			placeholder: config.placeholder
		} );

		this.editor = new mw.flow.ui.EditorWidget( {
			placeholder: config.placeholder,
			saveMsgKey: 'flow-reply-link',
			classes: [ 'flow-ui-replyWidget-editor' ]
		} );
		this.editor.toggle( false );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-replyWidget-error flow-error errorbox' ]
		} );
		this.error.toggle( false );

		this.api = new mw.flow.dm.APIHandler();

		// Events
		this.editor.connect( this, {
			saveContent: 'onEditorSave',
			cancel: 'onEditorCancel'
		} );
		this.triggerInput.$element.on( 'focusin', this.onTriggerFocusIn.bind( this ) );

		this.$element
			.addClass( 'flow-ui-replyWidget' )
			.append(
				this.triggerInput.$element,
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
		this.editor.activate();
		this.editor.toggle( true );
		this.triggerInput.setValue( '' );
		this.triggerInput.toggle( false );
	};

	/**
	 * Respond to editor cancel
	 */
	mw.flow.ui.ReplyWidget.prototype.onEditorCancel = function () {
		this.error.toggle( false );
		this.editor.toggle( false );
		this.triggerInput.toggle( true );
	};

	/**
	 * Respond to editor save
	 */
	mw.flow.ui.ReplyWidget.prototype.onEditorSave = function ( content, format ) {
		var widget = this;

		this.error.toggle( false );
		this.editor.pushPending();
		this.api.saveReply( this.replyTo, content, format )
			.then( function ( workflow ) {
				widget.editor.toggle( false );
				widget.triggerInput.toggle( true );
				widget.emit( 'saveContent', workflow, content, format );
			} )
			.then( null, function ( errorCode, errorObj ) {
				var $errorMessage = $( '<span>' ).text( errorObj.error.info );
				widget.error.setLabel( $errorMessage );
				widget.error.toggle( true );
			} )
			.always( function () {
				// TODO: Handle errors better
				widget.editor.popPending();
			} );
	};

	/**
	 * Destroy the widget
	 */
	mw.flow.ui.ReplyWidget.prototype.destroy = function () {
		this.editor.destroy();
	};

}( jQuery ) );
