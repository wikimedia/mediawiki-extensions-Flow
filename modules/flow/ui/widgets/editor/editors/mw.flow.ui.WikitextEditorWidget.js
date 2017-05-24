( function ( mw, $ ) {
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
			// The following classes can be used here:
			// * mw-editfont-default
			// * mw-editfont-monospace
			// * mw-editfont-sans-serif
			// * mw-editfont-serif
			classes: [ 'flow-ui-wikitextEditorWidget-input', 'mw-editfont-' + mw.user.options.get( 'editfont' ) ]
		} );

		// Label and switcher
		$usesWikitext = $( '<div>' )
			.html( mw.message( 'flow-wikitext-editor-help-uses-wikitext' ).parse() )
			.find( 'a' )
			.attr( 'target', '_blank' )
			.end();

		if ( mw.flow.ui.WikitextEditorWidget.static.switchable ) {
			$preview = $( '<a>' )
				.attr( 'href', '#' )
				.addClass( 'flow-ui-wikitextEditorWidget-label-preview' )
				.text( mw.message( 'flow-wikitext-editor-help-preview-the-result' ).text() );

			$message = $( '<span>' ).append(
				mw.message( 'flow-wikitext-editor-help-and-preview' ).params( [
					$usesWikitext.html(),
					$preview[ 0 ].outerHTML
				] ).parse()
			);
			$message.find( '.flow-ui-wikitextEditorWidget-label-preview' )
				.on( 'click', function () {
					widget.emit( 'switch' );
					return false;
				} );

			mw.loader.using( 'ext.flow.switching', function () {
				// Toolbar
				var toolFactory = new OO.ui.ToolFactory(),
					toolGroupFactory = new OO.ui.ToolGroupFactory();

				toolFactory.register( mw.flow.ui.MWEditModeVisualTool );
				toolFactory.register( mw.flow.ui.MWEditModeSourceTool );

				widget.toolbar = new OO.ui.Toolbar( toolFactory, toolGroupFactory, { position: 'bottom' } );
				// HACK: Disable narrow mode
				widget.toolbar.narrowThreshold = 0;
				widget.toolbar.setup( [ {
					type: 'list',
					icon: 'edit',
					title: mw.msg( 'visualeditor-mweditmode-tooltip' ),
					include: [ 'editModeVisual', 'editModeSource' ]
				} ] );
				widget.toolbar.emit( 'updateState' );

				widget.$element.append( widget.toolbar.$element );

				// Events
				widget.toolbar.on( 'switchEditor', function ( mode ) {
					if ( mode === 'visual' ) {
						widget.emit( 'switch' );
					}
				} );
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
			.prepend(
				this.input.$element,
				label.$element
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
	mw.flow.ui.WikitextEditorWidget.prototype.afterAttach = function () {
		if ( this.toolbar ) {
			this.toolbar.initialize();
			this.toolbar.emit( 'updateState' );
		}
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.teardown = function () {
		this.input.setValue( '' );
		return $.Deferred().resolve().promise();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.WikitextEditorWidget.prototype.destroy = function () {
		if ( this.toolbar ) {
			this.toolbar.destroy();
		}
	};
}( mediaWiki, jQuery ) );
