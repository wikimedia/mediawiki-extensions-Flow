( function ( $, mw ) {
	'use strict';

	/**
	 * Editor class that uses a simple wikitext textarea
	 *
	 * @class
	 * @constructor
	 *
	 * @param {jQuery} $node
	 * @param {string} [content='']
	 */
	mw.flow.editors.none = function ( $node, content ) {
		// Parent constructor
		mw.flow.editors.none.parent.call( this );

		var $editor = $node.closest( '.flow-editor' );

		// node the editor is associated with.
		this.$node = $node;

		this.widget = new OO.ui.TextInputWidget( {
			value: content || '',
			multiline: true,
			autosize: true,
			maxRows: 999,
			placeholder: this.$node.attr( 'placeholder' )
		} );

		// Hide textarea & attach widget instead
		this.$node
			.hide()
			.after( this.widget.$element );

		/*
		 * .flow-ui-focused is added to have a focus border on the div around
		 * the textarea (which holds textarea + legal text + switch button & make
		 * it look like all of that is just 1 big area)
		 */
		this.widget.$element
			.on( 'focusin', function () {
				$editor.addClass( 'flow-ui-focused' );
			} )
			.on( 'focusout', function () {
				$editor.removeClass( 'flow-ui-focused' );
			} );

		if ( this.$node.is( ':focus' ) ) {
			// Move focus to widget textarea
			this.focus();
		}

		// only attach switcher if VE is actually enabled and supported
		// code to figure out if that VE is supported is in that module
		if ( mw.config.get( 'wgFlowEditorList' ).indexOf( 'visualeditor' ) !== -1 ) {
			mw.loader.using( 'ext.flow.editors.visualeditor', $.proxy( this.attachControls, this ) );
		}

		this.widget.connect( this, { change: [ 'emit', 'change' ] } );
	};

	OO.inheritClass( mw.flow.editors.none, mw.flow.editors.AbstractEditor );

	// Static properties
	/**
	 * Type of content to use
	 *
	 * @property {string}
	 * @static
	 */
	mw.flow.editors.none.static.format = 'wikitext';

	/**
	 * Name of this editor
	 *
	 * @property {string}
	 * @static
	 */
	mw.flow.editors.none.static.name = 'none';

	mw.flow.editors.none.prototype.destroy = function () {
		this.widget.disconnect( this );
		this.widget.$element.remove();
		this.$node.css( 'display', '' );

		// remove the help+switcher information
		this.$node.siblings( '.flow-switcher-controls' ).remove();
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.none.prototype.getRawContent = function () {
		return this.widget.getValue();
	};

	/**
	 * Checks whether the field is empty
	 *
	 * @return {boolean} True if and only if it's empty
	 */
	mw.flow.editors.none.prototype.isEmpty = function () {
		return this.getRawContent() === '';
	};

	mw.flow.editors.none.prototype.attachControls = function () {
		var $preview, $usesWikitext, $controls, templateArgs,
			board = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( this.$node );

		$usesWikitext = $( '<div>' )
			.html( mw.message( 'flow-wikitext-editor-help-uses-wikitext' ).parse() )
			.find( 'a' )
			.attr( 'target', '_blank' )
			.end();

		if ( mw.flow.editors.visualeditor.static.isSupported() ) {
			$preview = $( '<a>' ).attr( {
				href: '#',
				'data-flow-interactive-handler': 'switchEditor',
				'data-flow-target': '< .flow-editor textarea.flow-editor-initialized'
			} ).text( mw.message( 'flow-wikitext-editor-help-preview-the-result' ).text() );

			templateArgs = {
				enable_switcher: true,
				help_text: mw.message( 'flow-wikitext-editor-help-and-preview' ).params( [
					$usesWikitext.html(),
					$preview[ 0 ].outerHTML
				] ).parse()
			};
		} else {
			// render just a basic help text
			templateArgs = {
				enable_switcher: false,
				help_text: mw.message( 'flow-wikitext-editor-help' ).params( [
					$usesWikitext.html()
				] ).parse()
			};
		}

		$controls = $( mw.flow.TemplateEngine.processTemplateGetFragment(
			'flow_editor_switcher.partial',
			templateArgs
		) ).children();

		// insert help information + editor switcher, and make it interactive
		board.emitWithReturn( 'makeContentInteractive', $controls.appendTo( this.$node.closest( '.flow-editor' ) ) );
	};

	mw.flow.editors.none.prototype.focus = function () {
		return this.widget.focus();
	};
}( jQuery, mediaWiki ) );
