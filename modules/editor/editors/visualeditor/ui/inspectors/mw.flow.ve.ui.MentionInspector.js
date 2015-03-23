( function ( $, mw, OO, ve ) {
	'use strict';

	// Based partly on ve.ui.MWTemplateDialog
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

		// this.selectedNode is the ve.dm.MWTransclusionNode, which we inherit
		// from ve.ui.NodeInspector.
		//
		// The templateModel (used locally some places) is a sub-part of the transclusion
		// model.
		this.transclusionModel = null;
		this.loaded = false;
		this.altered = false;

		this.targetInput = null;
	};

	OO.inheritClass( mw.flow.ve.ui.MentionInspector, ve.ui.NodeInspector );

	// Static

	mw.flow.ve.ui.MentionInspector.static.name = 'flowMention';
	mw.flow.ve.ui.MentionInspector.static.icon = 'flow-mention';
	mw.flow.ve.ui.MentionInspector.static.title = OO.ui.deferMsg( 'flow-ve-mention-inspector-title' );
	mw.flow.ve.ui.MentionInspector.static.modelClasses = [ ve.dm.MWTransclusionNode ];

	mw.flow.ve.ui.MentionInspector.static.template = mw.config.get( 'wgFlowMentionTemplate' );
	mw.flow.ve.ui.MentionInspector.static.templateParameterKey = '1'; // 1-indexed positional parameter

	// Buttons
	mw.flow.ve.ui.MentionInspector.static.actions = [
		{
			action: 'remove',
			label: OO.ui.deferMsg( 'flow-ve-mention-inspector-remove-label' ),
			flags: ['destructive'],
			modes: 'edit'
		}
	].concat( mw.flow.ve.ui.MentionInspector.parent.static.actions );

	// Instance Methods

	/**
	 * Handle changes to the input widget
	 */
	mw.flow.ve.ui.MentionInspector.prototype.onTargetInputChange = function () {
		var templateModel, parameterModel, key, value, inspector;

		key = mw.flow.ve.ui.MentionInspector.static.templateParameterKey;
		value = this.targetInput.getValue();
		inspector = this;

		this.pushPending();
		this.targetInput.isValid().done( function ( isValid ) {
			if ( isValid ) {
				// After the updates are done, we'll get onTransclusionModelChange
				templateModel = inspector.transclusionModel.getParts()[0];
				if ( templateModel.hasParameter( key ) ) {
					parameterModel = templateModel.getParameter( key );
					parameterModel.setValue( value );
				} else {
					parameterModel = new ve.dm.MWParameterModel(
						templateModel,
						key,
						value
					);
					templateModel.addParameter( parameterModel );
				}
			} else {
				// Disable save button
				inspector.setApplicableStatus();
			}
		} ).always( function () {
			inspector.popPending();
		} );
	};

	/**
	 * Handle the transclusion becoming ready
	 */
	mw.flow.ve.ui.MentionInspector.prototype.onTransclusionReady = function () {
		var templateModel, key;

		key = mw.flow.ve.ui.MentionInspector.static.templateParameterKey;

		this.loaded = true;
		this.$element.addClass( 'flow-ve-ui-mentionInspector-ready' );
		this.popPending();

		templateModel =	this.transclusionModel.getParts()[0];
		if ( templateModel.hasParameter( key ) ) {
			this.targetInput.setValue( templateModel.getParameter( key ).getValue() );
		}
	};

	/**
	 * Handles the transclusion model changing.  This should only happen when we change
	 * the parameter, then get a callback.
	 */
	mw.flow.ve.ui.MentionInspector.prototype.onTransclusionModelChange = function () {
		if ( this.loaded ) {
			this.altered = true;
			this.setApplicableStatus();
		}
	};

	/**
	 * Sets the abiliities based on the current status
	 *
	 * If it's empty or invalid, it can not be inserted or updated.
	 */
	mw.flow.ve.ui.MentionInspector.prototype.setApplicableStatus = function () {
		var parts = this.transclusionModel.getParts(),
			templateModel = parts[0],
			key = mw.flow.ve.ui.MentionInspector.static.templateParameterKey,
			inspector = this;

		// The template should always be there; the question is whether the first/only
		// positional parameter is.
		//
		// If they edit an existing mention, and make it invalid, they should be able
		// to cancel, but not save.
		if ( templateModel.hasParameter( key ) ) {
			this.pushPending();
			this.targetInput.isValid().done( function ( isValid ) {
				inspector.actions.setAbilities( { done: isValid } );
			} ).always( function () {
				inspector.popPending();
			} );
		} else {
			inspector.actions.setAbilities( { done: false } );
		}
	};

	/**
	 * Initialize UI of inspector
	 */
	mw.flow.ve.ui.MentionInspector.prototype.initialize = function () {
		var overlay = this.manager.getOverlay(), flowBoard;

		mw.flow.ve.ui.MentionInspector.parent.prototype.initialize.call( this );

		// I would much prefer to use dependency injection to get the list of topic posters
		// into the inspector, but I haven't been able to figure out how to pass it through
		// yet.

		flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )(
			this.$element
		);

		// Properties
		this.targetInput = new mw.flow.ve.ui.MentionTargetInputWidget( {
			$: this.$,
			$overlay: overlay ? overlay.$element : this.$frame,
			topicPosters: flowBoard.getTopicPosters( this.$element )
		} );

		// Initialization
		this.$content.addClass( 'flow-ve-ui-mentionInspector-content' );
		this.form.$element.append( this.targetInput.$element );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getActionProcess = function ( action ) {
		var surfaceModel = this.getFragment().getSurface(), dfd, inspector;

		if ( action === 'done' ) {
			dfd = $.Deferred();
			inspector = this;

			this.targetInput.isValid().done( function ( isValid ) {
				var transclusionModelPlain;

				if ( isValid ) {
					transclusionModelPlain = inspector.transclusionModel.getPlainObject();

					// Should be either null or the right template
					if ( inspector.selectedNode instanceof ve.dm.MWTransclusionNode ) {
						inspector.transclusionModel.updateTransclusionNode( surfaceModel, inspector.selectedNode );
					} else if ( transclusionModelPlain !== null ) {
						inspector.fragment = inspector.getFragment().collapseToEnd();
						inspector.transclusionModel.insertTransclusionNode( inspector.getFragment() );
						surfaceModel.setSelection( surfaceModel.getSelection().collapseToEnd() );
					}

					inspector.close( { action: action } );
				}

				// I think we can add an error for !isValid if the disabled button alone isn't
				// clear enough.

				dfd.resolve();
			} );

			return new OO.ui.Process( dfd.promise() );
		} else if ( action === 'remove' ) {
			return new OO.ui.Process( function () {
				var doc, nodeRange;

				doc = surfaceModel.getDocument();
				nodeRange = this.selectedNode.getOuterRange();

				surfaceModel.change(
					ve.dm.Transaction.newFromRemoval( doc, nodeRange )
				);

				this.close( { action: action } );
			}, this );
		}

		return mw.flow.ve.ui.MentionInspector.parent.prototype.getActionProcess.call( this, action );
	};

	/**
	 * Pre-populate the username based on the node
	 *
	 * @param {Object} [data] Inspector initial data
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getSetupProcess = function ( data ) {
		return mw.flow.ve.ui.MentionInspector.parent.prototype.getSetupProcess.call( this, data )
			.next( function () {
				var templateModel, promise;

				this.loaded = false;
				this.altered = false;
				// MWTransclusionModel has some unnecessary behavior for our use
				// case, mainly templatedata lookups.
				this.transclusionModel = new ve.dm.MWTransclusionModel();

				// Events
				this.transclusionModel.connect( this, {
					change: 'onTransclusionModelChange'
				} );

				this.targetInput.connect( this, {
					change:	'onTargetInputChange'
				} );

				// Initialization
				if ( !this.selectedNode ) {
					this.actions.setMode( 'insert' );
					templateModel = ve.dm.MWTemplateModel.newFromName(
						this.transclusionModel,
						mw.flow.ve.ui.MentionInspector.static.template
					);
					promise = this.transclusionModel.addPart( templateModel );
				} else {
					this.actions.setMode( 'edit' );

					// Load existing ping
					promise = this.transclusionModel
						.load( ve.copy( this.selectedNode.getAttribute( 'mw' ) ) );
				}

				// Don't allow saving until we're sure it's valid.
				this.actions.setAbilities( { done: false } );
				this.pushPending();
				promise.always(	this.onTransclusionReady.bind( this ) );
			}, this );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getReadyProcess = function ( data ) {
		return mw.flow.ve.ui.MentionInspector.parent.prototype.getReadyProcess.call( this, data )
			.next( function () {
				this.targetInput.focus();
			}, this );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionInspector.prototype.getTeardownProcess = function ( data ) {
		data = data || {};
		return mw.flow.ve.ui.MentionInspector.parent.prototype.getTeardownProcess.call( this, data )
			.first( function () {
				// Cleanup
				this.$element.removeClass( 'flow-ve-ui-mentionInspector-ready' );
				this.transclusionModel.disconnect( this );
				this.transclusionModel.abortRequests();
				this.transclusionModel = null;

				this.targetInput.disconnect( this );

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
			if ( node.isSingleTemplate( mw.flow.ve.ui.MentionInspector.static.template ) ) {
				return node;
			}
		}

		return null;
	};

	ve.ui.windowFactory.register( mw.flow.ve.ui.MentionInspector );
} ( jQuery, mediaWiki, OO, ve ) );
