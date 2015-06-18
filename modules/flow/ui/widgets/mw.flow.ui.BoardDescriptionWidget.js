( function ( $ ) {
	/**
	 * Flow board description widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config]
	 *
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
			saveMsgKey: 'flow-edit-header-submit',
			expanded: true,
			classes: [ 'flow-ui-boardDescriptionWidget-editor' ]
		} );
		this.editor.toggle( false );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-boardDescriptionWidget-error' ]
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
			saveContent: 'onEditorSaveContent'
		} );
		// Initialize
		this.$element
			.append(
				this.error.$element,
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
			contentFormat = (
				this.editor.editorSwitcherWidget.getActiveEditor() &&
				this.editor.editorSwitcherWidget.getActiveEditor().getFormat()
			) || 'wikitext';

		// Hide the edit button, any errors, and the content
		this.button.toggle( false );
		this.error.toggle( false );
		this.$content.addClass( 'oo-ui-element-hidden' );

		// Load the editor
		this.editor.pushPending();
		this.editor.toggleCollapsed( false );
		this.editor.toggle( true );

		// Set the editor to pending
		this.api.getDescription( contentFormat )
			.then(
				function ( desc ) {
					// Update the description model
					widget.model.populate( desc );

					// Give it to the editor
					// widget.editor.setContent( this.model.getContent() );

					// Update revisionId in the API
					widget.api.setCurrentRevision( widget.model.getRevisionId() );

					// TESTING ONLY!!!!!!!!!!!!!!!!!!!!!
					// widget.onEditorSaveContent( '<b>moo</b>', 'html' );
				},
				// Error fetching description
				function ( error ) {
					// Display error
					this.error.setLabel( mw.msg( 'flow-error-external', error ) );
					this.error.toggle( true );

					// Return to read mode
					widget.editor.toggle( false );
					widget.$content.addClass( 'oo-ui-element-hidden' );
				}
			)
			.always( function () {
				// Unset pending editor
				widget.editor.popPending();
			} );

	};

	/**
	 * Respond to editor save event. Save the content and display the new description.
	 *
	 * @param {string} content Content to save
	 * @param {string} contentFormat Format of content
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditorSaveContent = function ( content, format ) {
		var widget = this;

		this.editor.pushPending();
		this.api.saveDescription( content, format )
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
			} )
			.always( function () {
				// Hide the editor
				widget.editor.toggle( false );
				widget.editor.popPending();
				// Display the edit button and the content
				widget.button.toggle( true );
				widget.$content.removeClass( 'oo-ui-element-hidden' );
			} );
	};

	/**
	 * Respond to content change
	 *
	 * @param {string} content Description content
	 * @param {string} contentFormat Description content format
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onContentChange = function ( content, contentFormat ) {
		this.$content.empty().append( $.parseHTML( content ) );
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

		// Events
		this.model.connect( this, {
			contentChange: 'onContentChange'
		} );
	};

}( jQuery ) );
