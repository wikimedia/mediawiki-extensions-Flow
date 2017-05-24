( function ( mw ) {
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
	mw.flow.ui.MWEditModeTool = function VeUiMWEditModeTool() {
	};

	/* Inheritance */

	OO.initClass( mw.flow.ui.MWEditModeTool );

	/* Methods */

	// Inherits from mw.libs.ve.MWEditModeTool
	mw.flow.ui.MWEditModeTool.prototype.getMode = function () {
		return this.toolbar.surface ? 'visual' : 'source';
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
	mw.flow.ui.MWEditModeSourceTool = function VeUiMWEditModeSourceTool() {
		// Parent constructor
		mw.flow.ui.MWEditModeSourceTool.parent.apply( this, arguments );
		// Mixin constructor
		mw.flow.ui.MWEditModeTool.call( this );
	};
	OO.inheritClass( mw.flow.ui.MWEditModeSourceTool, mw.libs.ve.MWEditModeSourceTool );
	OO.mixinClass( mw.flow.ui.MWEditModeSourceTool, mw.flow.ui.MWEditModeTool );
	mw.flow.ui.MWEditModeSourceTool.prototype.onSelect = function () {
		if ( this.getMode() === 'visual' ) {
			this.toolbar.surface.executeCommand( 'flowSwitchEditor' );
		}
	};

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
	mw.flow.ui.MWEditModeVisualTool = function VeUiMWEditModeVisualTool() {
		// Parent constructor
		mw.flow.ui.MWEditModeVisualTool.parent.apply( this, arguments );
		// Mixin constructor
		mw.flow.ui.MWEditModeTool.call( this );
	};
	OO.inheritClass( mw.flow.ui.MWEditModeVisualTool, mw.libs.ve.MWEditModeVisualTool );
	OO.mixinClass( mw.flow.ui.MWEditModeVisualTool, mw.flow.ui.MWEditModeTool );
}( mediaWiki ) );
