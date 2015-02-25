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
		this.$node = $node;
		this.$node.val( content || '' );

		this.$node.css( 'overflow', 'hidden' );
		this.$node.css( 'resize', 'none' );

		// auto-expansion shouldn't shrink too much; set default height as min
		this.$node.css( 'min-height', this.$node.outerHeight() );

		// initialize at height of existing content & update on every keyup
		this.$node.keyup( this.autoExpand );
		this.autoExpand.call( this.$node.get( 0 ) );
	};

	OO.inheritClass( mw.flow.editors.none, mw.flow.editor.AbstractEditor );

	// Static properties
	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var string
	 */
	mw.flow.editors.none.static.format = 'wikitext';

	mw.flow.editors.none.prototype.destroy = function () {
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

	mw.flow.editors.none.prototype.focus = function() {
		return this.$node.focus();
	};
} ( jQuery, mediaWiki ) );
