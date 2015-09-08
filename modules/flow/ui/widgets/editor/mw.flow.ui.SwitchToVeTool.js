( function ( mw, OO ) {
	'use strict';

	/**
	 * Tool for switching to VE from the wikitext editor.
	 *
	 * @class
	 * @extends OO.ui.Tool
	 *
	 * @constructor
	 * @param {OO.ui.ToolGroup} toolGroup
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.SwitchToVeTool = function mwFlowUiSwitchToVeTool( toolGroup, config ) {
		mw.flow.ui.SwitchToVeTool.parent.call( this, toolGroup, config );

		// This tool always appears as pressed
		this.setActive( true );
	};

	/* Inheritance */

	OO.inheritClass( mw.flow.ui.SwitchToVeTool, OO.ui.Tool );

	/* Static properties */

	mw.flow.ui.SwitchToVeTool.static.name = 'flowSwitchEditor';
	mw.flow.ui.SwitchToVeTool.static.icon = 'wikiText';
	mw.flow.ui.SwitchToVeTool.static.title = mw.msg( 'flow-wikitext-switch-editor-tooltip' );

	/* Methods */

	mw.flow.ui.SwitchToVeTool.prototype.onUpdateState = function () {
	};

	mw.flow.ui.SwitchToVeTool.prototype.onSelect = function () {
		// Eww :(
		this.toolbar.emit( 'switchEditor' );
	};

}( mediaWiki, OO ) );
