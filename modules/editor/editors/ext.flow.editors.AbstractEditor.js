( function ( mw, OO ) {
	'use strict';

	mw.flow = mw.flow || {};

	/**
	 * Abstract editor class for Flow content
	 * Sets certain defaults, but most have to be implemented in subclasses
	 *
	 * @class mw.flow.editors.AbstractEditor
	 * @abstract
	 * @mixins OO.EventEmitter
	 *
	 * @constructor
	 */
	mw.flow.editors = {
		AbstractEditor: function () {
			// Mixin constructor
			OO.EventEmitter.call( this );
		}
	};

	OO.initClass( mw.flow.editors.AbstractEditor );
	OO.mixinClass( mw.flow.editors.AbstractEditor, OO.EventEmitter );

	/**
	 * @event change
	 * Fired when the user changes the content of the editor.
	 */

	// Static methods

	/**
	 * Determines if this editor is supported for the current user and
	 * environment (browser, etc.)
	 *
	 * @return {boolean}
	 */
	mw.flow.editors.AbstractEditor.static.isSupported = function () {
		return true;
	};
}( mediaWiki, OO ) );
