( function ( $ ) {
	/**
	 * Flow wikitext editor widget
	 *
	 * @class
	 * @extends mw.flow.ui.AbstractEditorWidget
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
			label: $( '<span>' ).append( '&lt;/&gt;' ),
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

		// Events
		this.switcher.connect( this, { click: [ 'emit', 'switchEditor' ] } );

		// Initialize
		this.$element
			.addClass( 'flow-ui-wikitextEditorWidget' )
			.append(
				this.textarea.$element,
				$( '<div>' )
					.addClass( 'flow-ui-wikitextEditorWidget-actions' )
					.append(
						label.$element,
						this.switcher.$element,
						$( '<div>' ).css( 'clear', 'both' )
					)
			);
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.WikitextEditorWidget, mw.flow.ui.AbstractEditorWidget );

	/* Static Methods */

	/**
	 * Type of content to use
	 *
	 * @var {string}
	 */
	mw.flow.ui.WikitextEditorWidget.static.format = 'plaintext';

	/**
	 * Name of this editor
	 *
	 * @var string
	 */
	mw.flow.ui.WikitextEditorWidget.static.name = 'wikitext';

	/* Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.focus = function () {
		this.textarea.$input.focus();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.reloadContent = function ( content ) {
		var deferred = $.Deferred();
		if ( !content ) {
			deferred.resolve();
		}

		deferred.resolve(); // TESTING ONLY
		return deferred.promise();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.getRawContent = function ( content ) {
		return this.textarea.getValue();
	};
}( jQuery ) );
