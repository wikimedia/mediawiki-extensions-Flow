( function ( mw, OO, ve ) {
	'use strict';

	mw.flow.ve = {
		ui: {}
	};

	/**
	 * Flow-specific target, inheriting from the stand-alone target
	 *
	 * @class
	 * @extends ve.init.sa.DesktopTarget
	 */
	mw.flow.ve.Target = function FlowVeTarget() {
		mw.flow.ve.Target.parent.call(
			this,
			{ toolbarConfig: { floatable: false } }
		);
	};

	OO.inheritClass( mw.flow.ve.Target, ve.init.sa.DesktopTarget );

	// Static

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

	// Methods

	mw.flow.ve.Target.prototype.attachToolbar = function () {
		// HACK ve.ui.Surface appends a debugBar *after* itself instead of putting it
		// inside itself (T106927)
		// Work around this by appending the toolbar after the debugBar if it's there, and
		// after the surface otherwise.
		var surface = this.getToolbar().getSurface();
		( surface.debugBar || surface ).$element.after( this.getToolbar().$element );
	};

	mw.flow.ve.Target.prototype.setDisabled = function ( disabled ) {
		// TODO upstream this to ve.init.Target
		var i, len;
		for ( i = 0, len = this.surfaces.length; i < len; i++ ) {
			// T106908: ve.ui.Surface doesn't support setDisabled()
			this.surfaces[i][ disabled ? 'disable' : 'enable' ]();
		}
	};

}( mediaWiki, OO, ve ) );
