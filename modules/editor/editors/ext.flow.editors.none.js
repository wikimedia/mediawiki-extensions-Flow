( function ( $, mw ) {
	'use strict';

	/**
	 * @param {jQuery} $node
	 * @param {string} [content='']
	 */
	mw.flow.editors.none = function ( $node, content ) {
		this.$node = $node;
		this.$node.val( content || '' );
	};

	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var string
	 */
	mw.flow.editors.none.format = 'wikitext';

	mw.flow.editors.none.prototype.destroy = function () {

	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.none.prototype.getRawContent = function () {
		return this.$node.val();
	};
} ( jQuery, mediaWiki ) );
