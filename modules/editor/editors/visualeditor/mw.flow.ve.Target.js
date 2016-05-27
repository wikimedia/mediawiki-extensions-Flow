( function ( mw, OO, ve ) {
	'use strict';

	mw.flow.ve = {
		ui: {}
	};

	/**
	 * Flow-specific target, inheriting from the stand-alone target
	 *
	 * @class
	 * @extends ve.init.mw.Target
	 */
	mw.flow.ve.Target = function FlowVeTarget() {
		mw.flow.ve.Target.parent.call( this, {
			toolbarConfig: { actions: true }
		} );

		// HACK: stop VE's education popups from appearing (T116643)
		this.dummyToolbar = true;
	};

	OO.inheritClass( mw.flow.ve.Target, ve.init.mw.Target );

	// Static

	mw.flow.ve.Target.static.name = 'flow';

	mw.flow.ve.Target.static.toolbarGroups = ve.copy( mw.flow.ve.Target.static.toolbarGroups );
	// Exclude heading1 and heading2 from the format dropdown
	mw.flow.ve.Target.static.toolbarGroups[1].exclude =
		( mw.flow.ve.Target.static.toolbarGroups[1].exclude || [] )
			.concat( [ 'heading1', 'heading2' ] );
	// Also remove heading1 and heading2 from the demote list
	mw.flow.ve.Target.static.toolbarGroups[1].demote =
		mw.flow.ve.Target.static.toolbarGroups[1].demote &&
		mw.flow.ve.Target.static.toolbarGroups[1].demote.filter( function ( tool ) {
			return tool !== 'heading1' && tool !== 'heading2';
		} );
	// Exclude heading1, heading2 and flowSwitchEditor from the catch-all insert menu
	mw.flow.ve.Target.static.toolbarGroups[5].exclude =
		( mw.flow.ve.Target.static.toolbarGroups[5].exclude || [] )
			.concat( [ 'heading1', 'heading2', 'flowSwitchEditor' ] );
	// Remove the more/fewer functionality in the insert menu
	delete mw.flow.ve.Target.static.toolbarGroups[5].forceExpand;
	// Add flowThank after link
	mw.flow.ve.Target.static.toolbarGroups.splice( 3, 0, { include: [ 'flowMention' ] } );

	// Allow pasting links
	mw.flow.ve.Target.static.importRules = ve.copy( mw.flow.ve.Target.static.importRules );
	mw.flow.ve.Target.static.importRules.external.blacklist = OO.simpleArrayDifference(
		mw.flow.ve.Target.static.importRules.external.blacklist,
		[ 'link/mwExternal' ]
	);

	// Static Methods
	mw.flow.ve.Target.static.setSwitchable = function ( switchable ) {
		// FIXME this isn't supposed to be a global state thing, it's supposed to be
		// variable per EditorWidget instance

		if ( switchable ) {
			mw.flow.ve.Target.static.actionGroups = [
				{ include: [ 'flowSwitchEditor' ] }
			];
		} else {
			mw.flow.ve.Target.static.actionGroups = [];
		}
	};

	// Methods

	mw.flow.ve.Target.prototype.loadHtml = function ( html ) {
		var doc = this.parseHtml( html );
		this.documentReady( doc );
	};

	// These tools aren't available so don't bother generating them
	mw.flow.ve.Target.prototype.generateCitationFeatures = function () {};

	mw.flow.ve.Target.prototype.attachToolbar = function () {
		// HACK ve.ui.Surface appends a debugBar *after* itself instead of putting it
		// inside itself (T106927)
		// Work around this by appending the toolbar after the debugBar if it's there, and
		// after the surface otherwise.
		var surface = this.getToolbar().getSurface();
		( surface.debugBar || surface ).$element.after( this.getToolbar().$element );
	};

	mw.flow.ve.Target.prototype.setDisabled = function ( disabled ) {
		var i, len;
		for ( i = 0, len = this.surfaces.length; i < len; i++ ) {
			this.surfaces[ i ].setDisabled( disabled );
		}
	};

	// Registration

	ve.init.mw.targetFactory.register( mw.flow.ve.Target );

}( mediaWiki, OO, ve ) );
