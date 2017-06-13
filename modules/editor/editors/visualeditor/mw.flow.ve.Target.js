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
			toolbarConfig: { actions: true, position: 'bottom' }
		} );

		// HACK: stop VE's education popups from appearing (T116643)
		this.dummyToolbar = true;
	};

	OO.inheritClass( mw.flow.ve.Target, ve.init.mw.Target );

	// Static

	mw.flow.ve.Target.static.name = 'flow';

	mw.flow.ve.Target.static.toolbarGroups = [
		{
			type: 'list',
			icon: 'textStyle',
			title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
			include: [ 'bold', 'italic' ],
			forceExpand: [ 'bold', 'italic' ]
		},

		{ include: [ 'link' ] },

		{ include: [ 'flowMention' ] }
	];

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
			ve.ui.toolFactory.register( mw.flow.ui.MWEditModeVisualTool );
			ve.ui.toolFactory.register( mw.flow.ui.MWEditModeSourceTool );
			mw.flow.ve.Target.static.actionGroups = [ {
				type: 'list',
				icon: 'edit',
				title: mw.msg( 'visualeditor-mweditmode-tooltip' ),
				include: [ 'editModeVisual', 'editModeSource' ]
			} ];
		} else {
			mw.flow.ve.Target.static.actionGroups = [];
		}
	};

	// Methods

	mw.flow.ve.Target.prototype.loadHtml = function ( html ) {
		var doc = this.constructor.static.parseDocument( html );
		this.documentReady( doc );
	};

	// These tools aren't available so don't bother generating them
	mw.flow.ve.Target.prototype.generateCitationFeatures = function () {};

	mw.flow.ve.Target.prototype.attachToolbar = function () {
		this.$element.after( this.getToolbar().$element );
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
