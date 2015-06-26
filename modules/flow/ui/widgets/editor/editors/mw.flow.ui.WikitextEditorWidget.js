( function ( $ ) {
	/**
	 * Flow wikitext editor widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [content=''] An initial content for the textarea
	 * @cfg {string} [placeholder] A placeholder for the textarea
	 * @cfg {boolean} [showSwitcher=false] Show the switcher button
	 * @cfg {boolean} [expanded=false] Start the widget already expanded
	 */
	mw.flow.ui.WikitextEditorWidget = function mwFlowUiWikitextEditorWidget( config ) {
		var label,
			widget = this;

		config = config || {};

		// Parent constructor
		mw.flow.ui.WikitextEditorWidget.parent.call( this, config );

		this.expanded = false;

		// Main textarea
		this.textarea = new OO.ui.TextInputWidget( {
			value: config.content || '',
			multiline: true,
			autosize: true,
			maxRows: 999,
			placeholder: config.placeholder,
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
			label: '&lt;/&gt;',
			classes: [ 'flow-ui-wikitextEditorWidget-switcher' ]
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

		this.toggleExpanded( !!config.expanded );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.WikitextEditorWidget, mw.flow.ui.AbstractEditorWidget );

	/* Methods */

	mw.flow.ui.WikitextEditorWidget.prototype.toggleExpanded = function ( expanded ) {
		expanded = expanded !== undefined ? expanded : !this.expanded;
		if ( this.expanded !== expanded ) {
			this.expanded = expanded;
			this.$element.toggleClass( 'flow-ui-wikitextEditorWidget-expanded', this.expanded );
		}
	};

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
