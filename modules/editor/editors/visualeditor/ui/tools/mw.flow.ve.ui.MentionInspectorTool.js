( function ( mw, OO, ve ) {
	'use strict';

	/**
	 * Tool for user mentions
	 *
	 * @class
	 * @extends ve.ui.InspectorTool
	 *
	 * @constructor
	 * @param {OO.ui.ToolGroup} toolGroup
	 * @param {Object} [config] Configuration options
	 */

	mw.flow.ve.ui.MentionInspectorTool = function FlowVeMentionInspectorTool( toolGroup, config ) {
		mw.flow.ve.ui.MentionInspectorTool.parent.call( this, toolGroup, config );
	};

	OO.inheritClass( mw.flow.ve.ui.MentionInspectorTool, ve.ui.InspectorTool );

	// Static
	mw.flow.ve.ui.MentionInspectorTool.static.commandName =	'flowMention';
	mw.flow.ve.ui.MentionInspectorTool.static.name = 'flowMention';
	mw.flow.ve.ui.MentionInspectorTool.static.icon = 'flow-mention';
	mw.flow.ve.ui.MentionInspectorTool.static.title = OO.ui.deferMsg( 'flow-ve-mention-tool-title' );

	mw.flow.ve.ui.MentionInspectorTool.static.template = 'ping'; // XXX: Needs to be configurable

	/**
	 * Checks whether the model represents a user mention
	 *
	 * @return boolean
	 */
	mw.flow.ve.ui.MentionInspectorTool.static.isCompatibleWith = function ( model ) {
		return model instanceof ve.dm.MWTransclusionNode &&
			model.isSingleTemplate( this.template );
	};

	ve.ui.toolFactory.register( mw.flow.ve.ui.MentionInspectorTool );
} ( mediaWiki, OO, ve ) );
