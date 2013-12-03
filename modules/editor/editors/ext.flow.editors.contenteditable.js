( function ( $, mw ) {
	'use strict';

	/**
	 * @param {jQuery} $node
	 * @param {string} [content='']
	 */
	mw.flow.editors.contenteditable = function ( $node, content ) {
		this.$node = $node;
		$node.hide();

		// create contenteditable node
		this.$target = $( '<div contenteditable="true">' )
			.addClass( $node.get( 0 ).className )
			.insertAfter( $node );

		this.$target.focus();

		/*
		 * Now make sure there's enough starting space for at least 1 line.
		 * If there's no content, the empty paragraph's height will be 0, so
		 * let's just add some dummy content and use it to set the
		 * contenteditable's min height.
		 */
		this.setRawContent( 'temp' );
		this.$target.css( 'min-height', this.$target.height() );
		this.setRawContent( content || '' );

		// let's never get into a situation where the cursor is outside a p
		this.$target.on( 'focus keydown mousedown', this.resetCursor.bind( this ) );

		// simulate a keyup event on the original node, so the validation code
		// will pick up changes in the new node
		this.$target.keyup( function () {
			this.$node.keyup();
		}.bind( this ) );
	};

	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var string
	 */
	mw.flow.editors.contenteditable.format = 'wikitext';

	mw.flow.editors.contenteditable.isSupported = function() {
		return 'contentEditable' in document.documentElement;
	};

	mw.flow.editors.contenteditable.prototype.destroy = function () {
		this.$target.remove();

		// re-display original node
		this.$node.show();
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.contenteditable.prototype.getRawContent = function () {
		var $content = this.$target.clone();

		// transform paragraphs into double newlines, breaks into single newline
		$content.find( 'p' ).before( "\n\n" );
		$content.find( 'br' ).before( "\n" );

		return $content.text().replace( /^\s+|\s+$/g, '' );
	};

	/**
	 * @param {string} content
	 */
	mw.flow.editors.contenteditable.prototype.setRawContent = function ( content ) {
		// make sure no html is injected
		content = $( '<p>' ).text( content ).html();

		// transform double newlines into paragraphs, single newlines into break
		content = '<p>' + content + '</p>';
		content = content.replace( "\n\n", '</p><p>' );
		content = content.replace( "\n", '<br>' );

		var $content = $( content );
		this.$target.empty();
		this.$target.append( $content );

		// move the cursor ad the end of the last node
		this.moveCursor( $content.eq( $content.length - 1 ) );
	};

	/**
	 * Add p element inside node & get cursor inside that element. Then,
	 * pressing enter will generate a new paragraph, shift-enter a br.
	 *
	 * Once a p element is there, all will work well. However, we should check
	 * on every keypress to make sure that the p element is still there:
	 * <CTRL-A + delete> may kill the p node.
	 */
	mw.flow.editors.contenteditable.prototype.resetCursor = function () {
		// p node already exists, we're good
		if ( this.$target.find( 'p' ).length > 0 ) {
			return;
		}

		/**
		 * Reset the content (actually, the only time this should occur is when
		 * the content is completely empty, but let's just play safe & fetch
		 * content anyway)
		 * Setting the content will make sure that a <p> node is inserted &
		 * the cursor is at the end of the inserted content.
		 */
		this.setRawContent( this.getRawContent() );
	};

	/**
	 * Move the cursor to the end of a given $node.
	 *
	 * @param {jQuery} $node
	 */
	mw.flow.editors.contenteditable.prototype.moveCursor = function( $node ) {
		var range = document.createRange(),
			selection = window.getSelection(),
			childNodes = $node.get( 0 ).childNodes,
			content = $node.html();

		// add dummy textNode if content is empty (needed for selection)
		$node.html( content || 'temp' );

		// set cursor at end of node
		range.setStartAfter( childNodes[childNodes.length - 1] );
		range.collapse( false );
		selection.removeAllRanges();
		selection.addRange( range );

		if ( !content ) {
			$node.html( '' );
		}
	};
} ( jQuery, mediaWiki ) );
