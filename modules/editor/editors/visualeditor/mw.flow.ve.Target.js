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
			icon: 'text-style',
			title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
			include: [ 'bold', 'italic' ],
			forceExpand: [ 'bold', 'italic' ]
		},

		{ include: [ 'link' ] },

		{ include: [ 'flowMention' ] }
	];

	mw.flow.ve.Target.static.actionGroups = [
		{ include: [ 'flowSwitchEditor' ] }
	];

	// Methods

	mw.flow.ve.Target.prototype.setupToolbar = function ( surface ) {
		// submitted to VE as Ie8df94d
		mw.flow.ve.Target.super.prototype.setupToolbar.call( this, surface );

		this.actions.setup( this.constructor.static.actionGroups, this.getSurface() );
	};

	mw.flow.ve.Target.prototype.attachToolbar = function() {
		this.getToolbar().$element.insertAfter( this.getToolbar().getSurface().$element );
	};

	// This is a workaround.
	//
	// We need to make sure the MW platform wins (we need it for e.g. linkCache), because our
	// dependencies do not agree.
	//
	// ext.visualEditor.data depends on ext.visualEditor.mediawiki, which provides
	// ve.init.mw.Platform.js.  However, we also use ext.visualEditor.standalone, which
	// provides ve.init.sa.Platform.  Both of these self-initialize ve.init.platform.
	ve.init.platform = new ve.init.mw.Platform();

	OO.ui.getUserLanguages = ve.init.platform.getUserLanguages.bind( ve.init.platform );

	OO.ui.msg = ve.init.platform.getMessage.bind( ve.init.platform );
} ( mediaWiki, OO, ve ) );
