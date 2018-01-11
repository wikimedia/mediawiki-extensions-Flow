( function ( mw, $ ) {
	/**
	 * View model for Structured Discussion system
	 *
	 * @class
	 * @mixins OO.EventEmitter
	 * @mixins OO.EmitterList
	 *
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	mw.sd.dm.ViewModel = function MwSdDmViewModel( config ) {
		// Mixin constructor
		OO.EventEmitter.call( this );
		OO.EmitterList.call( this );

		this.pageTitle = config.pageTitle;
	};

	/* Initialization */
	OO.initClass( mw.sd.dm.ViewModel );
	OO.mixinClass( mw.sd.dm.ViewModel, OO.EventEmitter );

	/**
	 * Get the page title
	 *
	 * @return {mw.Title} Page title
	 */
	mw.sd.dm.ViewModel.prototype.getPageTitle = function () {
		return this.pageTitle;
	};
}( mediaWiki, jQuery ) );
