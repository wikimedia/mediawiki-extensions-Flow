( function ( $ ) {
	/**
	 * Flow wikitext editor widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [content] An initial content for the textarea
	 */
	mw.flow.ui.WikitextEditorWidget = function mwFlowUiWikitextEditorWidget( config ) {
		var label,
			widget = this;

		config = config || {};

		// Parent constructor
		mw.flow.ui.WikitextEditorWidget.parent.call( this, config );

		// Main textarea
		this.textarea = new OO.ui.TextInputWidget( {
			value: config.content || '',
			multiline: true,
			autosize: true,
			maxRows: 999,
			classes: [ 'flow-ui-wikitextEditorWidget-input' ]
		} );

		// Label and switcher
		label = new OO.ui.LabelWidget( {
			label: $( '<span>' ).append( mw.message( 'flow-wikitext-editor-help-uses-wikitext' ).parse() ),
			classes: [ 'flow-ui-wikitextEditorWidget-label' ]
		} );
		label.$element.find( 'a' ).attr( 'target', '_blank' );

		// Switcher
		this.switcher = new OO.ui.ButtonWidget( {
			label: '&lt;/&gt;'
		} );
		this.switcher.$element.attr( 'title', mw.msg( 'flow-wikitext-switch-editor-tooltip' ) );
		this.switcher.toggle( !!config.showSwitcher );

		// Events
		this.switcher.connect( this, { click: [ 'emit', 'switch' ] } );
		this.textarea.$element
			.on( 'focusin', function () {
				widget.emit( 'focusin' );
			} )
			.on( 'focusout', function () {
				widget.emit( 'focusout' );
			} );

		// Initialize
		this.$element
			.addClass( 'flow-ui-wikitextEditorWidget' )
			.append(
				this.textarea.$element,
				$( '<div>' )
					.addClass( 'flow-ui-wikitextEditorWidget-actions' )
					.append( label, this.switcher.$element )
			);
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.WikitextEditorWidget, OO.ui.AbstractEditorWidget );

	/* Static Methods */

	/* Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.reloadContent = function ( content ) {
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.getContent = function ( content ) {
		return this.textarea.getValue();
	};
}( jQuery ) );
