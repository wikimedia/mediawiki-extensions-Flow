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
		var $editor = $node.closest( '.flow-editor' );
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
	mw.flow.editors.none.prototype.autoExpand = function() {
		var scrollHeight, $form, formBottom, windowBottom, maxHeightIncrease,
			$this = $( this ),
			height = $this.height(),
			padding = $this.outerHeight() - $this.height() + 5;

		/*
		 * Collapse to 0 height to get accurate scrollHeight for the content,
		 * then restore height.
		 * Without collapsing, scrollHeight would be the highest of:
		 * * the content height
		 * * the height the textarea already has
		 * Since we're looking to also shrink the textarea when content shrinks,
		 * we want to ignore that last case (hence the collapsing)
		 */
		$this.height( 0 );
		scrollHeight = this.scrollHeight;
		$this.height( height );

		/*
		 * Only animate height change if there actually is a change; we don't
		 * want every keystroke firing a 50ms animation.
		 */
		if ( scrollHeight === $this.data( 'flow-prev-scroll-height' ) ) {
			// no change
			return;
		}
		$this.data( 'flow-prev-scroll-height', scrollHeight );

		$form = $this.closest( 'form' );
		formBottom = $form.offset().top + $form.outerHeight( true );
		windowBottom = $( window ).scrollTop() + $( window ).height();
		// additional padding of 20px so the targeted form has breathing room
		maxHeightIncrease = windowBottom - formBottom - 20;

		if ( scrollHeight - height - padding >= maxHeightIncrease ) {
			// If we can't expand ensure overflow-y is set to auto
			$this.css( 'overflow-y', 'auto' );
		} else if ( scrollHeight !== $this.height() ) {
			$this.css( {
				height: scrollHeight,
				'overflow-y': 'hidden'
			} );
		}
	};

	mw.flow.editors.none.prototype.attachControls = function() {
		var $preview, $usesWikitext, $controls, templateArgs,
			board = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( this.$node );

		if ( mw.flow.editors.visualeditor.static.isSupported() ) {
			$preview = $( '<a>' ).attr( {
				href: '#',
				'data-flow-interactive-handler': 'switchEditor',
				'data-flow-target': '< form textarea'
			} ).text( mw.message( 'flow-wikitext-editor-help-preview-the-result' ).text() );

			$usesWikitext = $( '<div>' )
				.html( mw.message( 'flow-wikitext-editor-help-uses-wikitext' ).parse() )
				.find( 'a' )
					.attr( 'target', '_blank' )
				.end();

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
					mw.message( 'flow-wikitext-editor-help-uses-wikitext' ).parse()
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

	mw.flow.editors.none.prototype.focus = function() {
		return this.$node.focus();
	};
} ( jQuery, mediaWiki ) );
