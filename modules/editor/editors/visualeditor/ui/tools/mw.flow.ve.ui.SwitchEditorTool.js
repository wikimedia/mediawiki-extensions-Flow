( function ( mw, OO, ve ) {
	'use strict';

	/**
	 * Tool for switching editors
	 *
	 * @class
	 * @extends ve.ui.Tool
	 *
	 * @constructor
	 * @param {OO.ui.ToolGroup} toolGroup
	 * @param {Object} [config] Configuration options
	 */

	mw.flow.ve.ui.SwitchEditorTool = function FlowVeSwitchEditorTool( toolGroup, config ) {
		mw.flow.ve.ui.SwitchEditorTool.parent.call( this, toolGroup, config );
	};

	OO.inheritClass( mw.flow.ve.ui.SwitchEditorTool, ve.ui.Tool );

	// Static
	mw.flow.ve.ui.SwitchEditorTool.static.commandName = 'flowSwitchEditor';
	mw.flow.ve.ui.SwitchEditorTool.static.name = 'flowSwitchEditor';
	mw.flow.ve.ui.SwitchEditorTool.static.icon = 'wikiText';
	mw.flow.ve.ui.SwitchEditorTool.static.title = OO.ui.deferMsg( 'flow-ve-switch-editor-tool-title' );

	ve.ui.toolFactory.register( mw.flow.ve.ui.SwitchEditorTool );
}( mediaWiki, OO, ve ) );
