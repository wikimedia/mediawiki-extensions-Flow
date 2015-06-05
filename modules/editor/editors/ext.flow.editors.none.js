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
		this.$node = $node;
		this.$node.val( content || '' );

		this.$node.css( 'overflow', 'hidden' );
		this.$node.css( 'resize', 'none' );

		// auto-expansion shouldn't shrink too much; set default height as min
		this.$node.css( 'min-height', this.$node.outerHeight() );

		// initialize at height of existing content & update on every keyup
		this.$node.keyup( this.autoExpand );
		this.autoExpand.call( this.$node.get( 0 ) );

		// Add focused class when textarea is focused
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

		// HACK: we really need a TextInputWidget here
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
		// remove the help+switcher information
		this.$node.siblings( '.flow-switcher-controls' ).remove();
		// unset min-height that was set for auto-expansion
		this.$node.css( 'min-height', '' );
		// unset height that was set by auto-expansion
		this.$node.css( 'height', '' );
		// clear content
		this.$node.val( '' );
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.none.prototype.getRawContent = function () {
		return this.$node.val();
	};

	/**
	 * Checks whether the field is empty
	 *
	 * @return {boolean} True if and only if it's empty
	 */
	mw.flow.editors.none.prototype.isEmpty = function () {
		return this.getRawContent() === '';
	};

	/**
	 * Auto-expand/shrink as content changes.
	 */
	mw.flow.editors.none.prototype.autoExpand = function () {
		var scrollHeight, innerHeight, outerHeight, maxInnerHeight, measurementError, idealHeight,
			$this = $( this );

		$this
			.attr( 'rows', '' )
			// Set inline height property to 0 to measure scroll height
			.css( 'height', 0 );

		scrollHeight = $this[0].scrollHeight;

		// Remove inline height property to measure natural heights
		$this.css( 'height', '' );
		innerHeight = $this.innerHeight();
		outerHeight = $this.outerHeight();

		idealHeight = scrollHeight;

		// Only apply inline height when expansion beyond natural height is needed
		if ( idealHeight > innerHeight ) {
			// Use the difference between the inner and outer height as a buffer
			$this.css( 'height', idealHeight + ( outerHeight - innerHeight ) );
		} else {
			$this.css( 'height', '' );
		}
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

		// now that we've added a new element to the form, re-calculate the
		// size of the textarea (we want the entire form to remain visible)
		this.autoExpand.call( this.$node.get( 0 ) );
	};

	mw.flow.editors.none.prototype.focus = function () {
		return this.$node.focus();
	};
}( jQuery, mediaWiki ) );
