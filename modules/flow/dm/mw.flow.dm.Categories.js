( function () {
	/**
	 * Flow Board
	 *
	 * @class
	 * @mixes mw.flow.dm.List
	 * @mixes OO.EventEmitter
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.dm.Categories = function mwFlowDmCategories( config ) {
		// Mixin constructor
		OO.EventEmitter.call( this, config );

		// Mixin constructor
		mw.flow.dm.List.call( this );
	};

	/* Initialization */

	OO.mixinClass( mw.flow.dm.Categories, OO.EventEmitter );
	OO.mixinClass( mw.flow.dm.Categories, mw.flow.dm.List );
}() );
