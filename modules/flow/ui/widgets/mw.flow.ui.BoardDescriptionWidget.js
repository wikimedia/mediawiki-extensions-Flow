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

		this.board = boardModel;
		this.attachModel( this.board.getDescription() );

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
			this.board.getPageTitle().getPrefixedDb(),
			{
				currentRevision: this.model.getRevisionId()
			}
		);

		this.editor = new mw.flow.ui.EditorWidget( {
			saveMsgKey: 'flow-edit-header-submit',
			classes: [ 'flow-ui-boardDescriptionWidget-editor' ]
		} );
		this.editor.toggle( false );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-boardDescriptionWidget-error flow-error errorbox' ]
		} );
		this.error.toggle( false );

		this.button = new OO.ui.ButtonWidget( {
			label: mw.msg( 'flow-edit-header-link' ),
			framed: false,
			icon: 'edit',
			flags: 'progressive',
			classes: [ 'flow-ui-boardDescriptionWidget-editButton' ]
		} );

		this.categoriesWidget = new mw.flow.ui.CategoriesWidget( this.board );

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
				this.button.$element,
				this.$content,
				this.editor.$element,
				this.categoriesWidget.$element
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
		this.categoriesWidget.$element.addClass( 'oo-ui-element-hidden' );

		// Load the editor
		this.editor.pushPending();
		this.editor.toggle( true );
		this.editor.activate();

		// Set the editor to pending
		this.api.getDescription( contentFormat )
			.then(
				function ( desc ) {
					// Give it to the editor
					widget.editor.setContent( desc.content.content, desc.content.format );

					// Update revisionId in the API
					widget.api.setCurrentRevision( widget.model.getRevisionId() );
				},
				// Error fetching description
				function ( error ) {
					// Display error
					this.error.setLabel( mw.msg( 'flow-error-external', error ) );
					this.error.toggle( true );

					// Return to read mode
					widget.editor.toggle( false );
					widget.$content.removeClass( 'oo-ui-element-hidden' );
					widget.categoriesWidget.$element.removeClass( 'oo-ui-element-hidden' );
				}
			)
			.always( function () {
				// Unset pending editor
				widget.editor.popPending();
			} );

	};

	/**
	 * Respond to an editor cancel event
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditorCancel = function () {
		this.showContent();
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
			.then( null, function ( errorCode, errorObj ) {
				var $errorMessage = $( '<span>' ).text( errorObj.error.info );
				widget.error.setLabel( $errorMessage );
				widget.error.toggle( true );
			} )
			// Get the new categories
			.then( this.api.getCategories.bind( this.api ) )
			.then( function ( catObject ) {
				var page, catName,
					categoryDMs = [];
				// Create category data models
				for ( page in catObject ) {
					catName = catObject[page].title;
					categoryDMs.push( new mw.flow.dm.CategoryItem( catName, {
						exists: catObject[page].missing !== undefined
					} ) );
				}

				// Update the board data model
				widget.board.clearCategories();
				widget.board.addCategories( categoryDMs );
			} )
			// Remove the editor and show content
			.then( function () {
				widget.showContent();
			} )
			// Always pop pending for the editor
			.always( function () {
				widget.editor.popPending();
			} );
	};

	/**
	 * Show the content instead of the editor
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.showContent = function () {
		// Hide the editor
		this.editor.toggle( false );
		// Hide errors
		this.error.toggle( false );

		// Display the edit button and the content
		this.button.toggle( true );
		this.$content.removeClass( 'oo-ui-element-hidden' );
		this.categoriesWidget.$element.removeClass( 'oo-ui-element-hidden' );
	};

	/**
	 * Respond to content change. Content change can be in any of the
	 * editors formats, but we care to show it only in fixed-html.
	 * So when the model changes, we must ask the API for a fixed-html
	 * version of the content to display.
	 *
	 * @param {string} content Description content
	 * @param {string} contentFormat Description content format
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onContentChange = function () {
		var widget = this;

		this.$element.addClass( 'flow-api-inprogress' );
		this.button.setDisabled( true );
		this.api.getDescription( 'fixed-html' )
			.then(
				function ( desc ) {
					widget.$content.empty().append( $.parseHTML( desc.content.content ) );
					widget.$element.removeClass( 'flow-api-inprogress' );
					widget.button.setDisabled( false );
				}
			);
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
