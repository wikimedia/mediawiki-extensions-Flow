( function ( mw, OO, ve ) {
	'use strict';

	mw.flow.ve = {
		ui: {}
	};

	/**
	 * Flow-specific target, inheriting from the stand-alone target
	 *
	 * @class
	 * @extends ve.init.sa.Target
	 */
	mw.flow.ve.Target = function FlowVeTarget() {
		mw.flow.ve.Target.parent.call(
			this,
			{ toolbarConfig: { floatable: false } }
		);
	};

	OO.inheritClass( mw.flow.ve.Target, ve.init.sa.Target );

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

	if ( mw.flow.editors.none.static.isSupported() ) {
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

}( mediaWiki, OO, ve ) );
