( function ( $, mw ) {
	'use strict';

	/**
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
		this.$node.keyup( this.autoExpand );
	};

	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var string
	 */
	mw.flow.editors.none.format = 'wikitext';

	mw.flow.editors.none.prototype.destroy = function () {
		// unset min-height that was set for auto-expansion
		this.$node.css( 'min-height', '' );
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.none.prototype.getRawContent = function () {
		return this.$node.val();
	};

	/**
	 * Auto-expand/shrink as content changes.
	 */
	mw.flow.editors.none.prototype.autoExpand = function() {
		var height = $( this ).height(),
			padding = $( this ).outerHeight() - $( this ).height(),
			scrollHeight;

		/*
		 * Collapse to 0 height to get accurate scrollHeight for the content,
		 * then restore height.
		 * Without collapsing, scrollHeight would be the highest of:
		 * * the content height
		 * * the height the textarea already has
		 * Since we're looking to also shrink the textarea when content shrinks,
		 * we want to ignore that last case (hence the collapsing)
		 */
		$( this ).height( 0 );
		scrollHeight = this.scrollHeight;
		$( this ).height( height );

		/*
		 * Only animate height change if there actually is a change; we don't
		 * want every keystroke firing a 50ms animation.
		 */
		if ( scrollHeight != $( this ).height() ) {
			$( this ).animate( { height: scrollHeight + padding }, 50 );
		}
	};

	mw.flow.editors.none.prototype.focus = function() {
		return this.$node
			.focus()
			.selectRange( this.$node.val().length );
	};
} ( jQuery, mediaWiki ) );
