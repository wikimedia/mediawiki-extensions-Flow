( function ( $, mw ) {
	'use strict';

	/**
	 * Common actions code.
	 */
	mw.flow.action = function () {};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.prototype.showError = function ( error, errorData ) {
		$( this.topic.$container ).flow( 'showError', arguments );
	};

	/**
	 * Adds tipsy to an element, with the given text.
	 *
	 * @param {jQuery} $element
	 * @param {string} text
	 */
	mw.flow.action.prototype.tipsy = function ( $element, text ) {
		$element
			.click( function () {
				$( this ).tipsy( 'hide' );
			} )
			.tipsy( {
				fade: true,
				gravity: 'w',
				html: true,
				trigger: 'manual',
				className: 'flow-tipsy-destructive',
				title: function () {
					/*
					 * I'd prefer to only return content here, instead of wrapping
					 * it in a div. But we need to add some padding inside the tipsy.
					 * Tipsy has an option "className", which we could use to target
					 * the element though CSS, but that className is only applied
					 * _after_ tipsy has calculated position, so it's positioning
					 * would then be incorrect.
					 * Tossing in the content inside another div (which does have a
					 * class to target) works around this problem.
					 *
					 * @see https://gerrit.wikimedia.org/r/#/c/103531/
					 */

					// .html() only returns inner html, so attach the node to a new
					// parent & grab the full html there
					var $warning = $( '<div class="flow-tipsy-noflyout">' ).text( text );
					return $( '<div>' ).append( $warning ).html();
				}
			} )
			.tipsy( 'show' );
	};
} ( jQuery, mediaWiki ) );
