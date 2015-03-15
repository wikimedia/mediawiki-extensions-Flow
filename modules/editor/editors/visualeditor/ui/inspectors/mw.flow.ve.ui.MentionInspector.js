( function ( mw, OO, ve ) {
	'use strict';

	/**
	 * Inspector for editing Flow mentions.  This is a friendly
	 * UI for a transclusion (e.g. {{ping}}, template varies by wiki).
	 *
	 * @class
	 * @extends ve.ui.NodeInspector
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */

	mw.flow.ve.ui.MentionInspector = function FlowVeMentionInspector( config ) {
		mw.flow.ve.ui.MentionInspector.parent.call( this, config );
	};

	OO.inheritClass( mw.flow.ve.ui.MentionInspector, ve.ui.NodeInspector );

	// Static

	mw.flow.ve.ui.MentionInspector.static.name = 'flowMention';

	mw.flow.ve.ui.MentionInspector.static.icon = 'flow-mention';

	mw.flow.ve.ui.MentionInspector.static.title = OO.ui.deferMsg( 'flow-ve-mention-inspector-title' );

	mw.flow.ve.ui.MentionInspector.static.modelClasses = [ ve.dm.MWTransclusionNode ];

	// Buttons
	mw.flow.ve.ui.MentionInspector.static.actions = [
		{
			action: 'remove',
			label: OO.ui.deferMsg( 'flow-ve-mention-inspector-remove-label' )
		}
	].concat( mw.flow.ve.ui.MentionInspector.parent.static.actions );

	// Instance Methods
	/**
	 * Initialize UI of inspector
	 */
	mw.flow.ve.ui.MentionInspector.prototype.initialize = function () {
		var overlay = this.manager.getOverlay();

		mw.flow.ve.ui.MentionInspector.parent.prototype.initialize.call( this );

		// Properties
		this.targetInput = new mw.flow.ve.ui.MentionTargetInputWidget( {
			$: this.$,
			$overlay: overlay ? overlay.$element : this.$frame
		} );

		// Initialization
		this.$content.addClass( 'flow-ve-ui-mentionInspector-content' );
		this.form.$element.append( this.targetInput.$element );
	};

	// Based on ve.ui.CommentInspector.prototype.getActionProcess
	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getActionProcess = function ( action ) {
		if ( action === 'remove' || action === 'insert' ) {
			return new OO.ui.Process( function () {
				this.close( { action: action } );
			}, this );
		}
		return mw.flow.ve.ui.MentionInspector.parent.prototype.getActionProcess.call( this, action );
	};

	// Based partly on ve.ui.CommentInspector.prototype.getSetupProcess
	/**
	 * Pre-populate the username based on the node
	 *
	 * @param {Object} [data] Inspector initial data
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getSetupProcess = function ( data ) {
		var selectedNode;

		return mw.flow.ve.ui.MentionInspector.parent.prototype.getSetupProcess.call( this, data )
			.next( function () {
				this.getFragment().getSurface().pushStaging();

				if ( this.selectedNode ) {
					this.targetInput.setValue( this.selectedNode.getPartsList[0].content );
				} else {
					this.targetInput.setValue( '' );
					// TODO: Does this need to insert an empty ping transclusion the way CommentInspector inserts an empty comment?
				}
			}, this );
	};


	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getReadyProcess = function ( data ) {
		return mw.flow.ve.ui.MentionInspector.parent.prototype.getReadyProcess.call( this, data )
			.next( function () {
				this.getFragment().getSurface().enable();
				this.targetInput.focus();
			}, this );
	};

	// Based on ve.ui.CommentInspector.prototype.getTeardownProcess
	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getTeardownProcess = function ( data ) {
		data = data || {};
		return mw.flow.ve.ui.MentionInspector.parent.prototype.getTeardownProcess.call( this, data )
			.first( function () {
				var surfaceModel = this.getFragment().getSurface(),
					text = this.targetInput.getValue();

				if ( data.action === 'remove' ) {
					surfaceModel.popStaging();

					this.getFragment().removeContent();
				} else {
					// TODO: Need to insert/edit the ping transclusion
				}

				this.targetInput.setValue( '' );
			}, this );
	};

	/**
	 * Gets the transclusion node representing this mention
	 *
	 * @param {Object} [data] Inspector opening data
	 * @return {ve.dm.Node} Selected node
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getSelectedNode = function () {
		// Checks the model class
		var node = mw.flow.ve.ui.MentionInspector.parent.prototype.getSelectedNode.call( this );
		if ( node !== null ) {
			if ( node.isSingleTemplate( mw.flow.ve.ui.MentionInspectorTool.static.template ) ) {
				return node;
			}
		}

		return null;
	};

	ve.ui.windowFactory.register( mw.flow.ve.ui.MentionInspector );
} ( mediaWiki, OO, ve ) );
