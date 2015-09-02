( function ( $ ) {
	/**
	 * Flow wikitext editor widget
	 *
	 * @class
	 * @extends mw.flow.ui.AbstractEditorWidget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.WikitextEditorWidget = function mwFlowUiWikitextEditorWidget( config ) {
		var label, $message, $usesWikitext, $preview,
			widget = this;

		config = config || {};

		// Parent constructor
		mw.flow.ui.WikitextEditorWidget.parent.call( this, config );

		// Main textarea
		this.input = new OO.ui.TextInputWidget( {
			multiline: true,
			autosize: true,
			maxRows: 999,
			placeholder: config.placeholder,
			classes: [ 'flow-ui-wikitextEditorWidget-input' ]
		} );

		// Label and switcher
		$usesWikitext = $( '<div>' )
			.html( mw.message( 'flow-wikitext-editor-help-uses-wikitext' ).parse() )
			.find( 'a' )
			.attr( 'target', '_blank' )
			.end();

		if ( config.switchable ) {
			// Switcher
			this.switcher = new OO.ui.ButtonWidget( {
				label: $( '<span>' ).append( '&lt;/&gt;' ),
				title: mw.msg( 'flow-wikitext-switch-editor-tooltip' ),
				classes: [ 'flow-ui-wikitextEditorWidget-switcher' ]
			} );

			$preview = $( '<a>' )
				.attr( 'href', '#' )
				.addClass( 'flow-ui-wikitextEditorWidget-label-preview' )
				.text( mw.message( 'flow-wikitext-editor-help-preview-the-result' ).text() );

			$message = $( '<span>' ).append(
				mw.message( 'flow-wikitext-editor-help-and-preview' ).params( [
					$usesWikitext.html(),
					$preview[0].outerHTML
				] ).parse()
			);

			// Events
			this.switcher.connect( this, { click: [ 'emit', 'switch' ] } );
			$message.find( '.flow-ui-wikitextEditorWidget-label-preview' )
				.on( 'click', function () {
					widget.emit( 'switch' );
					return false;
				} );
		} else {
			$message = $( '<span>' ).append(
				mw.message( 'flow-wikitext-editor-help' ).params( [
					$usesWikitext.html()
				] ).parse()
			);
		}

		// Label
		label = new OO.ui.LabelWidget( {
			label: $message,
			classes: [ 'flow-ui-wikitextEditorWidget-label' ]
		} );

		// Events
		this.input.connect( this, { change: [ 'emit', 'change' ] } );

		// Initialize
		this.$element
			.addClass( 'flow-ui-wikitextEditorWidget' )
			.append(
				this.input.$element,
				$( '<div>' )
					.addClass( 'flow-ui-wikitextEditorWidget-actions' )
					.append(
						label.$element,
						this.switcher ? this.switcher.$element : [],
						$( '<div>' ).css( 'clear', 'both' )
					)
			);
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.WikitextEditorWidget, mw.flow.ui.AbstractEditorWidget );

	/* Static Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.static.format = 'wikitext';

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.static.name = 'wikitext';

	/* Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.focus = function () {
		this.input.focus();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.moveCursorToEnd = function () {
		this.input.moveCursorToEnd();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.getContent = function () {
		return this.input.getValue();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.setContent = function ( content ) {
		// Parent method
		mw.flow.ui.WikitextEditorWidget.parent.prototype.setContent.call( this, content );

		this.input.setValue( content );
	};

	mw.flow.ui.WikitextEditorWidget.prototype.setDisabled = function ( disabled ) {
		// Parent method
		mw.flow.ui.WikitextEditorWidget.parent.prototype.setDisabled.call( this, disabled );

		if ( this.input ) {
			this.input.setDisabled( this.isDisabled() );
		}
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.setup = function ( content ) {
		this.setContent( content );
		return $.Deferred().resolve().promise();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.teardown = function () {
		this.input.setValue( '' );
		return $.Deferred().resolve().promise();
	};
}( jQuery ) );
