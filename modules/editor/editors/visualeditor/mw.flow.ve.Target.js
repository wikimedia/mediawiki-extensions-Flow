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
			'desktop',
			{ floatable: false }
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
		this.getToolbar().$element.insertAfter( this.getToolbar().getSurface().$element );
	};

}( mediaWiki, OO, ve ) );
