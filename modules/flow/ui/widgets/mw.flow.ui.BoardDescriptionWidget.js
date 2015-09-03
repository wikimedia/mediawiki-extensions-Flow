( function ( $ ) {
	/**
	 * Flow board description widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {mw.flow.dm.Board} boardModel The board model
	 * @param {Object} [config]
	 * @cfg {jQuery} $existing A jQuery object of the existing contents of the board description
	 */
	mw.flow.ui.BoardDescriptionWidget = function mwFlowUiBoardDescriptionWidget( boardModel, config ) {
		var $content = $();

		config = config || {};

		// Parent constructor
		mw.flow.ui.BoardDescriptionWidget.parent.call( this, config );

		this.attachModel( boardModel.getDescription() );

		// Since the content is already displayed, we will "steal" the already created
		// node to avoid having to render it twice.
		// Upon creation of this widget, this should be the rendering of the data
		// that exists in the model. Take care, however, that if this widget is
		// used elsewhere, the model and rendering must be synchronized.
		if ( config.$existing ) {
			$content = config.$existing;
		}

		this.$content = $( '<div>' )
			.addClass( 'flow-ui-boardDescriptionWidget-content' )
			.append( $content );

		this.api = new mw.flow.dm.APIHandler(
			boardModel.getPageTitle().getPrefixedDb(),
			{
				currentRevision: this.model.getRevisionId()
			}
		);

		this.editor = new mw.flow.ui.EditorWidget( {
			saveMsgKey: mw.user.isAnon() ? 'flow-edit-header-submit-anonymously' : 'flow-edit-header-submit',
			classes: [ 'flow-ui-boardDescriptionWidget-editor' ]
		} );
		this.editor.toggle( false );

		this.anonWarning = new mw.flow.ui.AnonWarningWidget();
		this.anonWarning.toggle( false );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-boardDescriptionWidget-error flow-errors errorbox' ]
		} );
		this.error.toggle( false );

		this.button = new OO.ui.ButtonWidget( {
			label: mw.msg( 'flow-edit-header-link' ),
			framed: false,
			icon: 'edit',
			flags: 'progressive',
			classes: [ 'flow-ui-boardDescriptionWidget-editButton' ]
		} );

		// Events
		this.button.connect( this, { click: 'onEditButtonClick' } );
		this.editor.connect( this, {
			saveContent: 'onEditorSaveContent',
			cancel: 'onEditorCancel'
		} );
		// Initialize
		this.$element
			.append(
				this.error.$element,
				this.anonWarning.$element,
				this.button.$element,
				this.$content,
				this.editor.$element
			)
			.addClass( 'flow-ui-boardDescriptionWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.BoardDescriptionWidget, OO.ui.Widget );

	/* Methods */

	/**
	 * Respond to edit button click. Switch to the editor widget
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditButtonClick = function () {
		var widget = this,
			contentFormat = this.editor.getInitialFormat() || 'wikitext';

		// Hide the edit button, any errors, and the content
		this.button.toggle( false );
		this.error.toggle( false );
		this.$content.addClass( 'oo-ui-element-hidden' );

		this.editor.toggle( true );

		// Load the editor
		this.editor.pushPending();
		this.anonWarning.toggle( true );
		this.editor.activate();

		// Get the description from the API
		this.api.getDescription( contentFormat )
			.then(
				function ( desc ) {
					var content = OO.getProp( desc, 'content', 'content' ),
						format = OO.getProp( desc, 'content', 'format' );

					if ( content !== undefined && format !== undefined ) {
						// Give it to the editor
						widget.editor.setContent( content, format );

						// Update revisionId in the API
						widget.api.setCurrentRevision( widget.model.getRevisionId() );
					}
				},
				// Error fetching description
				function ( error ) {
					// Display error
					widget.error.setLabel( mw.msg( 'flow-error-external', error ) );
					widget.error.toggle( true );

					// Return to read mode
					widget.showContent( false );
				}
			)
			.always( function () {
				// Unset pending editor
				widget.editor.popPending();
				// Focus again: pending editors are disabled and can't be focused
				widget.editor.focus();
			} );

	};

	/**
	 * Respond to an editor cancel event
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditorCancel = function () {
		this.showContent( true );
	};

	/**
	 * Respond to editor save event. Save the content and display the new description.
	 *
	 * @param {string} content Content to save
	 * @param {string} contentFormat Format of content
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditorSaveContent = function ( content, format ) {
		var widget = this,
			$captchaField, captcha;

		this.editor.pushPending();

		$captchaField = this.error.$label.find( '[name="wpCaptchaWord"]' );
		if ( $captchaField.length > 0 ) {
			captcha = {
				id: this.error.$label.find( '[name="wpCaptchaId"]' ).val(),
				answer: $captchaField.val()
			};
		}

		this.error.setLabel( '' );
		this.error.toggle( false );

		this.api.saveDescription( content, format, captcha )
			.then( function ( newRevisionId ) {
				// Update revisionId in the API
				widget.api.setCurrentRevision( newRevisionId );
				// Get the new header to update the dm.BoardDescription
				// The widget should update automatically by its events
				return widget.api.getDescription( 'html' );
			} )
			.then( function ( description ) {
				// Update the model
				widget.model.populate( description );
				return widget.api.getDescription( 'fixed-html' );
			} )
			.then( function ( desc ) {
				// Change the actual content
				widget.$content.empty().append( $.parseHTML( desc.content.content ) );
				widget.showContent( true );
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
	 * Show the content instead of the editor
	 *
	 * @param {boolean} [hideErrors] Hide error bar
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.showContent = function ( hideErrors ) {
		// Hide the editor
		this.editor.toggle( false );
		this.anonWarning.toggle( false );

		if ( !hideErrors ) {
			// Hide errors
			this.error.toggle( false );
		}

		// Display the edit button and the content
		this.button.toggle( true );
		this.$content.removeClass( 'oo-ui-element-hidden' );
	};

	/**
	 * Attach a model to the widget
	 *
	 * @param {mw.flow.dm.BoardDescription} model Board model
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.attachModel = function ( model ) {
		if ( this.model ) {
			this.model.disconnect( this );
		}

		this.model = model;
	};

}( jQuery ) );
