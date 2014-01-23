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
} ( jQuery, mediaWiki ) );
