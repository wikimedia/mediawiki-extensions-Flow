( function ( mw, $ ) {
	/**
	 * Controller for Structured Discussion system
	 * @class
	 *
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	mw.sd.Controller = function MwSdController( viewModel, config ) {
		config = config || {}:

		this.model = viewModel;
		this.api = new mw.sd.ApiHandler(
			this.model.getPageTitle().getPrefixedDb(),
			config.api || {}
		);
	};

	/* Initialization */
	OO.initClass( mw.sd.Controller );
}( mediaWiki, jQuery ) );
