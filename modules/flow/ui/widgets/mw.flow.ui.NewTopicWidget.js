( function ( $ ) {
	/**
	 * Flow reply widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {string} page The page name, including namespace, that the
	 *  board of this topic belongs to.
	 * @param {Object} [config] Configuration object
	 */
	mw.flow.ui.NewTopicWidget = function mwFlowUiNewTopicWidget( page, config ) {
		var widget = this;

		config = config || {};

		// Parent constructor
		mw.flow.ui.NewTopicWidget.parent.call( this, config );

		this.page = page;
		this.expanded = false;

		this.anonWarning = new mw.flow.ui.AnonWarningWidget();
		this.anonWarning.toggle( false );

		this.title = new OO.ui.TextInputWidget( {
			placeholder: mw.msg( 'flow-newtopic-start-placeholder' ),
			multiline: false,
			classes: [ 'flow-ui-newTopicWidget-title' ]
		} );

		this.editor = new mw.flow.ui.EditorWidget( {
			placeholder: mw.msg( 'flow-newtopic-content-placeholder', this.page ),
			saveMsgKey: mw.user.isAnon() ? 'flow-newtopic-save-anonymously' : 'flow-newtopic-save',
			autoFocus: false,
			classes: [ 'flow-ui-newTopicWidget-editor' ]
		} );
		this.editor.toggle( false );

		this.captcha = new mw.flow.dm.Captcha();
		this.captchaWidget = new mw.flow.ui.CaptchaWidget( this.captcha );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-newTopicWidget-error flow-errors errorbox' ]
		} );
		this.error.toggle( false );

		this.api = new mw.flow.dm.APIHandler( this.page );

		// Events
		this.editor.connect( this, {
			change: 'updateSaveButtonState',
			saveContent: 'onEditorSaveContent',
			cancel: 'onEditorCancel'
		} );
		this.editor.once( 'switch', function ( promise ) {
			// Listen to editor switch event and re-enable auto-focus.
			// This is done so that in the first construction of the widget the
			// focus stays in the title widget and is not stolen by the editor.
			promise.always( function () {
				widget.editor.toggleAutoFocus( true );
			} );
		} );

		this.title.connect( this, {
			change: 'updateSaveButtonState'
		} );
		this.title.$element.on( 'focusin', this.onTitleFocusIn.bind( this ) );
		this.title.$element.on( 'keydown', this.onTitleKeydown.bind( this ) );

		// Initialization
		this.updateSaveButtonState();

		this.$element
			.addClass( 'flow-ui-newTopicWidget' )
			.append(
				this.anonWarning.$element,
				this.error.$element,
				this.captchaWidget.$element,
				this.title.$element,
				this.editor.$element
			);
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.NewTopicWidget, OO.ui.Widget );

	/**
	 * Update the state of the save button.
	 * @private
	 */
	mw.flow.ui.NewTopicWidget.prototype.updateSaveButtonState = function () {
		this.editor.editorControlsWidget.toggleSaveable(
			this.title.getValue() &&
			!this.editor.isEmpty()
		);
	};

	/**
	 * Respond to title input focusin event
	 * @private
	 */
	mw.flow.ui.NewTopicWidget.prototype.onTitleFocusIn = function () {
		this.activate();
	};

	/**
	 * Expand the widget and make it ready to create a new topic
	 */
	mw.flow.ui.NewTopicWidget.prototype.activate = function () {
		if ( !this.isExpanded() ) {
			// Expand the editor
			this.toggleExpanded( true );
			this.editor.activate();
			this.title.focus();
		}
	};

	/**
	 * Preload the widget with title and content.
	 *
	 * @param {string} title
	 * @param {string} content
	 * @param {string} format
	 */
	mw.flow.ui.NewTopicWidget.prototype.preload = function ( title, content, format ) {
		this.activate();
		this.title.setValue( title );

		if ( content && format ) {
			this.editor.setContent( content, format ).then( this.updateSaveButtonState.bind( this ) );
		}
	};

	/**
	 * Respond to keydown events in title input, and cancel when escape is pressed.
	 * @param {jQuery.Event} e Keydown event
	 */
	mw.flow.ui.NewTopicWidget.prototype.onTitleKeydown = function ( e ) {
		if ( e.which === OO.ui.Keys.ESCAPE ) {
			this.onEditorCancel();
			e.preventDefault();
			e.stopPropagation();
		}
	};

	/**
	 * Respond to editor save event
	 *
	 * @param {string} content Content
	 * @param {string} format Content format
	 */
	mw.flow.ui.NewTopicWidget.prototype.onEditorSaveContent = function ( content, format ) {
		var widget = this,
			title = this.title.getValue(),
			captchaResponse;

		this.editor.pushPending();
		this.title.pushPending();
		this.title.setDisabled( true );

		captchaResponse = this.captchaWidget.getResponse();

		this.error.setLabel( '' );
		this.error.toggle( false );

		this.api.saveNewTopic( title, content, format, captchaResponse )
			.then( function ( topicId ) {
				widget.captchaWidget.toggle( false );

				widget.toggleExpanded( false );
				widget.emit( 'save', topicId );
			} )
			.then( null, function ( errorCode, errorObj ) {
				widget.captcha.update( errorCode, errorObj );
				if ( !widget.captcha.isRequired() ) {
					widget.error.setLabel( new OO.ui.HtmlSnippet( errorObj.error && errorObj.error.info || errorObj.exception ) );
					widget.error.toggle( true );
				}
			} )
			.always( function () {
				widget.editor.popPending();
				widget.title.popPending();
				widget.title.setDisabled( false );
			} )
			.done( function () {
				// Clear for next use
				widget.title.setValue( '' );
				widget.editor.setContent( '', 'html' );
				widget.updateSaveButtonState();
			} );
	};

	/**
	 * Respond to editor cancel event
	 */
	mw.flow.ui.NewTopicWidget.prototype.onEditorCancel = function () {
		// Hide the editor
		this.toggleExpanded( false );
		// Take focus away from the title input, if it was focused (T109353)
		this.title.blur();
	};

	/**
	 * Get the expanded state of the widget
	 * @return {boolean} expanded Widget is expanded
	 */
	mw.flow.ui.NewTopicWidget.prototype.isExpanded = function () {
		return this.expanded;
	};

	/**
	 * Toggle the expanded state of the widget
	 * @param {boolean} expanded Widget is expanded
	 */
	mw.flow.ui.NewTopicWidget.prototype.toggleExpanded = function ( expanded ) {
		this.expanded = expanded !== undefined ? expanded : !this.expanded;

		this.editor.toggle( this.expanded );
		this.anonWarning.toggle( this.expanded );
		// Hide errors
		this.error.toggle( false );

		if ( !this.expanded ) {
			// Reset the title
			this.title.setValue( '' );
		}
	};
}( jQuery ) );
