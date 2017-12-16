/*!
 * VisualEditor MediaWiki UserInterface edit mode tool classes.
 *
 * @copyright 2011-2017 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * MediaWiki UserInterface edit mode source tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeSourceTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
mw.flow.ui.MWEditModeSourceTool = function VeUiMWEditModeSourceTool() {
	// Parent constructor
	mw.flow.ui.MWEditModeSourceTool.parent.apply( this, arguments );
};

OO.inheritClass( mw.flow.ui.MWEditModeSourceTool, ve.ui.MWEditModeSourceTool );

mw.flow.ui.MWEditModeSourceTool.prototype.switch = function () {
	this.toolbar.getTarget().switchMode();
};

ve.ui.toolFactory.register( mw.flow.ui.MWEditModeSourceTool );

/**
 * MediaWiki UserInterface edit mode visual tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeVisualTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
mw.flow.ui.MWEditModeVisualTool = function VeUiMWEditModeVisualTool() {
	// Parent constructor
	mw.flow.ui.MWEditModeVisualTool.parent.apply( this, arguments );
};

OO.inheritClass( mw.flow.ui.MWEditModeVisualTool, ve.ui.MWEditModeVisualTool );

mw.flow.ui.MWEditModeVisualTool.prototype.switch = function () {
	this.toolbar.getTarget().switchMode();
};

ve.ui.toolFactory.register( mw.flow.ui.MWEditModeVisualTool );
