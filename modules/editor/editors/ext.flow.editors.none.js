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

		var $editor = $node.closest( '.flow-editor' ),
			prevContent = content;

		// node the editor is associated with.
		this.$node = $node;

		this.widget = new OO.ui.TextInputWidget( {
			value: content || '',
			multiline: true,
			autosize: true,
			maxRows: 999
		} );

		// Hide textarea & attach widget instead
		$editor
			.hide()
			/*
			 * @todo only attaching AFTER (not inside) .flow-editor because
			 * flowEventsMixinInitializeEditors tries to create these editor
			 * objects for every .flow-editor textarea:not(.flow-input-compressed),
			 * so every focus inside a textarea inside .flow-editor would create a
			 * new object here...
			 * However, we may want to be inside .flow-editor for some CSS?
			 */
			.after( this.widget.$element );

		return; // @todo stuff below hasn't been addressed yet

		// Add focused class when textarea is focused
		/*
		 * @todo the flow-ui-focused was to add a focus border on the div around
		 * the textarea (which held textarea + legal text + switch button & make
		 * it look like all of that is just 1 big area)
		 * Now that this is a TextInputWidget, we'll probably still have to do
		 * the same (but with another parent?) and undo the border-radius on the
		 * TextInputWidget textarea element.
		 */
		this.$node
			.on( 'focus', function () {
				$editor.addClass( 'flow-ui-focused' );
			} )
			.on( 'blur', function () {
				$editor.removeClass( 'flow-ui-focused' );
			} );
		// Add focused class if textarea is already focused
		if ( this.$node.is( ':focus' ) ) {
			$editor.addClass( 'flow-ui-focused' );
		}

		// only attach switcher if VE is actually enabled and supported
		// code to figure out if that VE is supported is in that module
		if ( mw.config.get( 'wgFlowEditorList' ).indexOf( 'visualeditor' ) !== -1 ) {
			mw.loader.using( 'ext.flow.editors.visualeditor', $.proxy( this.attachControls, this ) );
		}

		// @todo figure out how/where to attach this to...
		$node.on( 'keydown mouseup cut paste change input select', $.proxy( function () {
			var newVal = $node.val();
			if ( newVal !== prevContent ) {
				prevContent = newVal;
				this.emit( 'change' );
			}
		}, this ) );
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
		var $editor = this.$node.closest( '.flow-editor' );

		this.widget.$element.remove(); // @todo: destroy widget object entirely...
		$editor.css( 'display', '' );

		// remove the help+switcher information
		this.$node.siblings( '.flow-switcher-controls' ).remove();
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.none.prototype.getRawContent = function () {
		return this.widget.getInputElement().val();
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
				'data-flow-target': '< form textarea'
			} ).text( mw.message( 'flow-wikitext-editor-help-preview-the-result' ).text() );

			templateArgs = {
				enable_switcher: true,
				help_text: mw.message( 'flow-wikitext-editor-help-and-preview' ).params( [
					$usesWikitext.html(),
					$preview[0].outerHTML
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
		board.emitWithReturn( 'makeContentInteractive', $controls.insertAfter( this.$node ) );
	};

	mw.flow.editors.none.prototype.focus = function () {
		// @todo fixme
		return this.$node.focus();
	};
}( jQuery, mediaWiki ) );
