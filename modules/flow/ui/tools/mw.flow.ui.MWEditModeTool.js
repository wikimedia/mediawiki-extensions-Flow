/*!
 * VisualEditor MediaWiki UserInterface edit mode tool classes.
 *
 * @copyright 2011-2017 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * MediaWiki UserInterface edit mode tool.
 *
 * @class
 * @abstract
 */
mw.flow.ui.MWEditModeTool = function MwFlowUiMWEditModeTool() {
};

/* Inheritance */

OO.initClass( mw.flow.ui.MWEditModeTool );

/* Methods */

mw.flow.ui.MWEditModeTool.prototype.getMode = function () {
	return this.toolbar.getSurface().getMode();
};

mw.flow.ui.MWEditModeTool.prototype.isModeAvailable = function () {
	// If we're showing the switcher, then both modes are available
	return true;
};

/**
 * MediaWiki UserInterface edit mode source tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeSourceTool
 * @mixins mw.flow.ui.MWEditModeTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
mw.flow.ui.MWEditModeSourceTool = function MwFlowUiMWEditModeSourceTool() {
	// Parent constructor
	mw.flow.ui.MWEditModeSourceTool.parent.apply( this, arguments );
};

OO.inheritClass( mw.flow.ui.MWEditModeSourceTool, mw.libs.ve.MWEditModeSourceTool );
OO.mixinClass( mw.flow.ui.MWEditModeSourceTool, mw.flow.ui.MWEditModeTool );

mw.flow.ui.MWEditModeSourceTool.prototype.switch = function () {
	this.toolbar.getTarget().switchMode();
};

ve.ui.toolFactory.register( mw.flow.ui.MWEditModeSourceTool );

/**
 * MediaWiki UserInterface edit mode visual tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeVisualTool
 * @mixins mw.flow.ui.MWEditModeTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
mw.flow.ui.MWEditModeVisualTool = function MwFlowUiMWEditModeVisualTool() {
	// Parent constructor
	mw.flow.ui.MWEditModeVisualTool.parent.apply( this, arguments );
};

OO.inheritClass( mw.flow.ui.MWEditModeVisualTool, mw.libs.ve.MWEditModeVisualTool );
OO.mixinClass( mw.flow.ui.MWEditModeVisualTool, mw.flow.ui.MWEditModeTool );

mw.flow.ui.MWEditModeVisualTool.prototype.switch = function () {
	this.toolbar.getTarget().switchMode();
};

ve.ui.toolFactory.register( mw.flow.ui.MWEditModeVisualTool );
