( function ( mw, OO ) {
	mw.flow.ui = {};

	mw.flow.ui.windowFactory = new OO.Factory();
	mw.flow.ui.windowManager = new OO.ui.WindowManager( { factory: mw.flow.ui.windowFactory } );
}( mediaWiki, OO ) );
