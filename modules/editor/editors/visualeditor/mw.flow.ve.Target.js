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
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [placeholder] Placeholder text
	 */
	mw.flow.ve.Target = function FlowVeTarget( config ) {
		config = config || {};
		this.placeholder = config.placeholder;

		// Parent constructor
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

	mw.flow.ve.Target.prototype.createSurface = function ( dmDoc, config ) {
		config = config || {};
		return mw.flow.ve.Target.parent.prototype.createSurface.call(
			this,
			dmDoc,
			ve.extendObject( { placeholder: this.placeholder }, config )
		);
	};

}( mediaWiki, OO, ve ) );
