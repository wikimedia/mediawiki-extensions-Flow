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

	mw.flow.ve.Target.static.toolbarGroups = [
		{
			type: 'list',
			icon: 'textStyle',
			indicator: 'down',
			title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
			include: [ 'bold', 'italic' ],
			forceExpand: [ 'bold', 'italic' ]
		},

		{ include: [ 'link' ] },

		{ include: [ 'flowMention' ] }
	];

	// FIXME this isn't supposed to be a global state thing, it's supposed to be
	// variable per EditorWidget instance
	if ( mw.flow.ui.WikitextEditorWidget.static.isSupported() ) {
		mw.flow.ve.Target.static.actionGroups = [
			{ include: [ 'flowSwitchEditor' ] }
		];
	}

	// Allow pasting links
	mw.flow.ve.Target.static.importRules = ve.copy( mw.flow.ve.Target.static.importRules );
	mw.flow.ve.Target.static.importRules.external.blacklist = OO.simpleArrayDifference(
		mw.flow.ve.Target.static.importRules.external.blacklist,
		[ 'link' ]
	);

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
