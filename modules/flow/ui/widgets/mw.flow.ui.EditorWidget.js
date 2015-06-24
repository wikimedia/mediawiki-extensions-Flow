( function ( $ ) {
	/**
	 * Flow editor widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {FlowBoardComponent} [board] The board this widget is attached to
	 * @cfg {string} [content] An initial content for the textarea
	 * @cfg {string} [action='newtopic'] The action for this editor. These actions set the text
	 *  for the terms label that appears near the buttons.
	 *  available actions are 'new-topic', 'reply', 'edit', 'summarize', 'lock-topic', 'unlock-topic'
	 * @cfg {boolean} [loaded] Display the editor as fully loaded. Defaults to false, which loads the
	 *  plain $textarea first with the editor hidden.
	 */
	mw.flow.ui.EditorWidget = function mwFlowUiEditorWidget( config ) {
		config = config || {};
console.log( 'mw.flow.ui.EditorWidget constructed' );
		// Parent constructor
		mw.flow.ui.EditorWidget.parent.call( this, config );

		this.action = config.action || 'new-topic';
		this.board = config.board;
		// The base textarea
		// TODO: Change this to be an ooui widget from the
		// get go rather than have the 'none' editor change
		// it into one
		this.$textarea = $( '<textarea>' )
			.addClass( 'flow-ui-editorWidget-textarea' )
			.val( config.content || '' );
		this.content = this.$textarea.val();

		this.title = new OO.ui.TextInputWidget( {
			multiline: false,
			placeholder: mw.msg( 'flow-newtopic-start-placeholder' ),
			classes: [ 'flow-ui-editorWidget-title mw-ui-input-large' ]
		} );

		this.saveButton = new OO.ui.ButtonWidget( {
			flags: [ 'primary', 'constructive' ],
			label: mw.msg( 'flow-newtopic-save' ),
			classes: [ 'flow-ui-editorWidget-saveButton' ]
		} );
		this.cancelButton = new OO.ui.ButtonWidget( {
			framed: false,
			flags: 'destructive',
			label: mw.msg( 'flow-cancel' ),
			classes: [ 'flow-ui-editorWidget-cancelButton' ]
		} );

		this.termsLabel = new OO.ui.LabelWidget( {
			label: mw.msg( 'flow-terms-of-use-' + this.action ),
			classes: [ 'flow-ui-editorWidget-termsLabel' ]
		} );

		this.$footer = $( '<div>' )
			.addClass( 'flow-ui-editorWidget-footer' )
			.append(
				$( '<div>' )
					.addClass( 'flow-ui-editorWidget-terms' )
					.append( this.termsLabel.$element ),
				$( '<div>' )
					.addClass( 'flow-ui-editorWidget-buttons' )
					.append(
						this.cancelButton.$element,
						this.saveButton.$element
					)
			);

		if ( this.action === 'new-topic' ) {
			this.title.$input.on( 'focus', this.onTitleFocus.bind( this ) );

			this.$element
				.append( this.title.$element );
//				.addClass( 'flow-ui-editorWidget-collapsed' );
		}

		// Initialize
		this.$element
			.append(
				$( '<div>' )
					.addClass( 'flow-editor' )
					.append( this.$textarea ),
				this.$footer
			)
			.addClass( 'flow-ui-editorWidget' );
		// Load the editor if we need to
		if ( config.loaded ) {
			this.loadEditor();
		}
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );

	mw.flow.ui.EditorWidget.prototype.onTitleFocus = function () {
		debugger;
		this.$element.removeClass( 'flow-ui-editorWidget-collapsed' );
		this.loadEditor();
	};

	/**
	 * Load the editor
	 */
	mw.flow.ui.EditorWidget.prototype.loadEditor = function () {
		var widget = this;

		this.$textarea.val( '' );
		mw.loader.using( 'ext.flow.editor', function () {
			mw.flow.editor.load( widget.$textarea, widget.content, widget.board );
		} );
	};

	/**
	 * Destroy the editor
	 */
	mw.flow.ui.EditorWidget.prototype.destroy = function () {
		if ( mw.flow.editor.exists( this.$textarea ) ) {
			mw.flow.editor.destroy( this.$textarea );
debugger;
			this.$element.addClass( 'flow-ui-editorWidget-collapsed' );
		}
	};
}( jQuery ) );
