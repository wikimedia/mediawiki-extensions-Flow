<<<<<<< HEAD   (60d78f Merge "Move templates and fix handlebars.js compatbility" in)
=======
( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises topic object.
	 *
	 * @param {string} topicId
	 */
	mw.flow.discussion.topic = function ( topicId ) {
		this.topicId = topicId;
		this.$container = $( '#flow-topic-' + topicId );
		this.workflowId = this.$container.data( 'topic-id' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );
		this.type = 'topic';

		this.actions = {
			// init edit-title interaction
			edit: new mw.flow.action.topic.edit( this ),
			// init reply interaction
			reply: new mw.flow.action.topic.reply( this ),
			// init summarize interaction
			summarize: new mw.flow.action.topic.summarize( this ),
			// init close interaction
			close: new mw.flow.action.topic.close( this ),
			// init reopen interaction
			reopen: new mw.flow.action.topic.reopen( this )
		};
	};

	mw.flow.action.topic = {};

	/**
	 * Initialises a shared object for summarizing/closing/reopening topic
	 * interaction objects
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.summarizebase = function( topic ) {
		var action = this.getAction();
		this.object = topic;
		this.$container =  $( '.flow-element-container :first', this.object.$container );

		// Overload "summarize"/"close"/"reopen" link in topic action menu.
		this.$container
			// Store this callback on the topic container itself
			.data( action + '-topic-callback', $.proxy( this.edit, this ) )
			// Then store the topic-id on the link so we can refer back to the topic container's callback onclick
			.find( '.flow-' + action + '-topic-link' )
				.attr( 'data-topic-id', this.object.topicId );
		$( document )
			.off( 'click.mw-flow-discussion-' + action + '-topic-link' )
			.on(
				'click.mw-flow-discussion-' + action + '-topic-link',
				'.flow-' + action + '-topic-link',
				function ( event ) {
					// Find the topic container, get the stored callback, and then call it
					return $( '.flow-element-container :first', $( '#flow-topic-' + $( this ).data( 'topic-id') ) )
						.data( action + '-topic-callback' )
						.apply( this, arguments );
				}
			);
	};
	mw.flow.action.topic.summarizebase.prototype = new mw.flow.action();
	mw.flow.action.topic.summarizebase.prototype.constructor = mw.flow.action.topic.summarizebase;

	/**
	 * Get the container for topic summary.  If the container doesn't exist
	 * yet create a new one
	 *
	 * @return {object}
	 */
	mw.flow.action.topic.summarizebase.prototype.getSummaryContainer = function() {
		var $titleBar = $( '.flow-titlebar', this.$container ),
			$summaryContainer = $( '.flow-topic-summary', $titleBar );
		if ( !$summaryContainer.length ) {
			$summaryContainer = $( '<div>' ).addClass( 'flow-topic-summary' );
			$summaryContainer.appendTo( $titleBar );
		}
		return $summaryContainer;
	};

	/**
	 * Fired when close/summarize is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.topic.summarizebase.prototype.edit = function ( e ) {
		// don't follow link that will lead to &action=edit-topic-summary
		e.preventDefault();

		// close tipsy
		var $tipsyTrigger = this.$container.find( '.flow-tipsy-open' );
		$tipsyTrigger.each( function() {
			$( this ).removeClass( 'flow-tipsy-open' );
			$( this ).tipsy( 'hide' );
		} );

		/*
		 * Fetch current revision data (content, revision id, ...) that
		 * we'll need to initialise the edit form.
		 */
		this.read()
			/*
			 * This is a .then: then callbacks can return a value, which is
			 * then passed to .done or .fail. This allows us to format the
			 * returned data to how we want it in .done, or reject (which
			 * will result in .fail being called) if the data is invalid.
			 */
			.then( $.proxy( this.prepareResult, this ) )
			/*
			 * Once we have successfully fetched & verified the data, the
			 * edit form can be built.
			 */
			.done( $.proxy( this.setupEditForm, this ) )
			/*
			 * If anything went wrong (either in original deferred object or
			 * in the one returned by .then), show an error message.
			 */
			.fail( $.proxy( this.showError, this ) );
	};

	/**
	 * Fetches summary info.
	 *
	 * @see includes/Block/TopicSummary.php TopicSummaryBlock::renderAPI
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.summarizebase.prototype.read = function () {
		return mw.flow.api.readTopicSummary(
			this.object.pageName,
			this.object.workflowId,
			{
				topicsummary: {
					contentFormat: mw.flow.editor.getFormat()
				}
			}
		);
	};

	/**
	 * Processes the result of this.read & the returned deferred
	 * will be passed to setupEditForm.
	 *
	 * if invalid data is encountered, the (new) deferred will be rejected,
	 * resulting in any fail() bound to be executed.
	 *
	 * @param {object} data
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.summarizebase.prototype.prepareResult = function ( data ) {
		var deferred = $.Deferred(), revId = null;

		if ( !data[0] ) {
			return deferred.reject( '', {} );
		}

		if ( data[0]['topicsummary-id'] ) {
			revId = data[0]['topicsummary-id'];
		}

		return deferred.resolve( {
			content: data[0]['*'],
			format: data[0].format,
			revision: revId
		} );
	};

	/**
	 * Builds the edit form.
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.topic.summarizebase.prototype.setupEditForm = function ( data, loadFunction ) {
		// call parent setupEditForm function
		mw.flow.action.prototype.setupEditForm.call(
			this,
			data,
			loadFunction
		);
		this.$container.find( '.flow-topic-summary' ).hide();
	};

	/**
	 * Removes the edit form & restores content.
	 */
	mw.flow.action.topic.summarizebase.prototype.destroyEditForm = function () {
		this.getSummaryContainer().show();

		// call parent destroyEditForm function
		mw.flow.action.prototype.destroyEditForm.call( this );
	};

	/**
	 * Called when submitFunction failed.
	 *
	 * @param {jQuery.Deferred} deferred
	 * @param {object} data Old (invalid) this.prepareResult return value
	 * @param {string} error
	 * @param {object} errorData
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.summarizebase.prototype.submitFail = function ( deferred, data, error, errorData ) {
		if (
			// edit conflict
			error === 'block-errors' &&
			errorData.topicsummary && errorData.topicsummary.prev_revision &&
			errorData.topicsummary.prev_revision.extra && errorData.topicsummary.prev_revision.extra.revision_id
		) {
			var $textarea = this.$container.find( '.flow-edit-content' ),
				buttonText = mw.msg( 'flow-edit-topicsummary-submit-overwrite' ),
				tipsyText = errorData.header.prev_revision.message;

			/*
			 * Overwrite data revision & content.
			 * We'll use raw editor content & editor format to avoid having
			 * to parse it.
			 */
			data.content = mw.flow.editor.getRawContent( $textarea );
			data.format = mw.flow.editor.getFormat( $textarea );
			data.revision = errorData.topicsummary.prev_revision.extra.revision_id;

			// return conflict's deferred
			return this.conflict( data, buttonText, tipsyText );
		}

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.summarizebase.prototype.render = function ( output ) {
		// refresh 'summarize' in dropdown to 'edit summary'
		this.object.$container
			.find( '.flow-summarize-topic-link' )
			.text( mw.msg( 'flow-topic-action-resummarize-topic' ) );
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.topic.summarizebase.prototype.showError = function ( error, errorData ) {
		this.getSummaryContainer().flow( 'showError', arguments );
	};

	/**
	 * Initialises topic summarizing interaction object.
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.close = function( topic ) {
		mw.flow.action.topic.summarizebase.call( this, topic );
	};

	// Extend close action from "shared functionality" mw.flow.action.topic.summarizebase class
	// using new() to extend parent would actully invoke a call to the parent constructor,
	// we would want to avoid that in this case because parent constructor requires topic
	mw.flow.action.topic.close.prototype = Object.create( mw.flow.action.topic.summarizebase.prototype );
	mw.flow.action.topic.close.prototype.constructor = mw.flow.action.topic.close;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.close.prototype.getAction = function() {
		return 'close';
	};

	/**
	 * Get the terms of use for closing topic form
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.close.prototype.getTerms = function() {
		return mw.config.get( 'wgFlowTermsOfUseCloseTopic' );
	};

	/**
	 * Submit function for flow( 'setupEditForm' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.close.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.closeReopenTopic( {
				workflow: this.object.workflowId,
				page: this.object.pageName
			},
			content,
			'close',
			data.revision
		);

		deferred.done( $.proxy( this.render, this ) );
		deferred = deferred.then( null, $.proxy( this.submitFail, this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.close.prototype.render = function ( output ) {
		mw.flow.action.topic.summarizebase.prototype.render.call( this, output );
		var $newContainer = $( output.topic.rendered );
		this.object.$container.replaceWith( $newContainer );
		$newContainer.trigger( 'flow_init' );
	};

	/**
	 * Initialises topic summarizing interaction object.
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.summarize = function( topic ) {
		mw.flow.action.topic.summarizebase.call( this, topic );
	};

	// Extend summarize action from "shared functionality" mw.flow.action.topic.summarizebase class
	mw.flow.action.topic.summarize.prototype = Object.create( mw.flow.action.topic.summarizebase.prototype );
	mw.flow.action.topic.summarize.prototype.constructor = mw.flow.action.topic.summarize;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.summarize.prototype.getAction = function() {
		return 'summarize';
	};

	/**
	 * Get the placeholder for the edit form
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.summarize.prototype.getEditFormPlaceHolder = function() {
		return mw.msg( 'flow-summarize-topic-placeholder' );
	};

	/**
	 * Get the css style for topic summary preview container
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.summarize.prototype.getPreviewCSS = function() {
		return 'flow-topic-summary';
	};

	/**
	 * Get the terms of use for summarizing topic form
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.summarize.prototype.getTerms = function() {
		return mw.config.get( 'wgFlowTermsOfUseSummarize' );
	};

	/**
	 * Submit function for flow( 'setupEditForm' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.summarize.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.editTopicSummary( {
				workflow: this.object.workflowId,
				page: this.object.pageName
			},
			content,
			data.revision
		);

		deferred.done( $.proxy( this.render, this ) );
		deferred = deferred.then( null, $.proxy( this.submitFail, this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.summarize.prototype.render = function ( output ) {
		mw.flow.action.topic.summarizebase.prototype.render.call( this, output );
		this.$container
			.find( '.flow-edit-form' )
				.remove();
		this.getSummaryContainer()
				.html ( output.rendered )
				.show()
				.end();
	};

	/**
	 * Initialises topic reopening interaction object.
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.reopen = function( topic ) {
		mw.flow.action.topic.summarizebase.call( this, topic );
	};

	// Extend reopen action from "shared functionality" mw.flow.action.topic.summarizebase class
	mw.flow.action.topic.reopen.prototype = Object.create( mw.flow.action.topic.summarizebase.prototype );
	mw.flow.action.topic.reopen.prototype.constructor = mw.flow.action.topic.reopen;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.reopen.prototype.getAction = function() {
		return 'reopen';
	};

	/**
	 * Get the terms of use for restoring topic form
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.reopen.prototype.getTerms = function() {
		return mw.config.get( 'wgFlowTermsOfUseReopenTopic' );
	};

	/**
	 * Submit function for flow( 'setupEditForm' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.reopen.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.closeReopenTopic( {
				workflow: this.object.workflowId,
				page: this.object.pageName
			},
			content,
			'restore',
			data.revision
		);

		deferred.done( $.proxy( this.render, this ) );
		deferred = deferred.then( null, $.proxy( this.submitFail, this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.reopen.prototype.render = function ( output ) {
		mw.flow.action.topic.summarizebase.prototype.render.call( this, output );
		var $newContainer = $( output.topic.rendered );
		this.object.$container.replaceWith( $newContainer );
		$newContainer.trigger( 'flow_init' );
	};

	/**
	 * Initialises topic edit-title interaction object.
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.edit = function ( topic ) {
		this.object = topic;
		this.$container = this.object.$container;

		// Overload "edit title" link.
		// Bit of a hack due to the requirement of proxying this to this.edit,
		// and that tipsy only supports HTML, not passing along DOM nodes.
		this.$container
			// Store this callback on the topic container itself
			.data( 'edit-topic-callback', $.proxy( this.edit, this ) )
			// Then store the topic-id on the link so we can refer back to the topic container's callback onclick
			.find( '.flow-edit-topic-link' )
				.attr( 'data-topic-id', this.object.topicId );

		$( document )
			.off( 'click.mw-flow-discussion-edit-topic-link' )
			.on(
				'click.mw-flow-discussion-edit-topic-link',
				'.flow-edit-topic-link',
				function ( event ) {
					// Find the topic container, get the stored callback, and then call it
					return $( '#flow-topic-' + $( this ).data( 'topic-id') ).data( 'edit-topic-callback' ).apply( this, arguments );
				}
			);
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.topic.edit.prototype = new mw.flow.action();
	mw.flow.action.topic.edit.prototype.constructor = mw.flow.action.topic.edit;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.edit.prototype.getAction = function() {
		return 'edit';
	};

	/**
	 * Fired when edit-title link is clicked.
	 *
	 * @param {Event} event
	 */
	mw.flow.action.topic.edit.prototype.edit = function ( event ) {
		// don't follow link that will lead to &action=edit-title
		event.preventDefault();

		// close tipsy
		var $tipsyTrigger = this.$container.find( '.flow-tipsy-open' );
		$tipsyTrigger.each( function() {
			$( this ).removeClass( 'flow-tipsy-open' );
			$( this ).tipsy( 'hide' );
		} );

		/*
		 * Fetch current revision data (content, revision id, ...) that
		 * we'll need to initialise the edit form.
		 */
		this.read()
			/*
			 * This is a .then: then callbacks can return a value, which is
			 * then passed to .done or .fail. This allows us to format the
			 * returned data to how we want it in .done, or reject (which
			 * will result in .fail being called) if the data is invalid.
			 */
			.then( $.proxy( this.prepareResult, this ) )
			/*
			 * Once we have successfully fetched & verified the data, the
			 * edit form can be built.
			 */
			.done( $.proxy( this.setupEditForm, this ) )
			/*
			 * If anything went wrong (either in original deferred object or
			 * in the one returned by .then), show an error message.
			 */
			.fail( $.proxy( this.showError, this ) );
	};

	/**
	 * Fetches title info.
	 *
	 * @see includes/Block/Topic.php TopicBlock::renderAPI
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.edit.prototype.read = function () {
		return mw.flow.api.readTopic(
			this.object.pageName,
			this.object.workflowId,
			{
				topic: {
					'no-children': true,
					postId: this.object.workflowId, // fetch title post (not full topic)
					contentFormat: mw.flow.editor.getFormat()
				}
			}
		);
	};

	/**
	 * Processes the result of this.read & the returned deferred
	 * will be passed to setupEditForm.
	 *
	 * if invalid data is encountered, the (new) deferred will be rejected,
	 * resulting in any fail() bound to be executed.
	 *
	 * @param {object} data
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.edit.prototype.prepareResult = function ( data ) {
		var deferred = $.Deferred();

		if ( !data[0] || !data[0].content ) {
			return deferred.reject( '', {} );
		}

		return deferred.resolve( {
			// content should already be (fake) wikitext, this is just failsafe
			content: mw.flow.parsoid.convert( data[0].content.format, 'wikitext', data[0].content['*'], this.object.pageName ),
			format: 'wikitext',
			revision: data[0]['revision-id']
		} );
	};

	/**
	 * Builds the edit form.
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.topic.edit.prototype.setupEditForm = function ( data, loadFunction ) {
		// create form html
		this.createEditForm( data );

		var $titleEditForm = $( 'form.flow-edit-title-form', this.$container );

		$titleEditForm.flow( 'setupPreview', { '.flow-edit-content': 'plain' } );
		$titleEditForm.flow( 'setupFormHandler',
			'.flow-edit-submit',
			$.proxy( this.submitFunction, this, data ),
			$.proxy( this.loadParametersCallback, this ),
			$.proxy( this.validateCallback, this )
		);

		if ( loadFunction instanceof Function ) {
			loadFunction();
		}
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {string} workflowId
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.edit.prototype.submitFunction = function ( data, workflowId, content ) {
		var deferred = mw.flow.api.changeTitle(
			workflowId,
			content,
			data.revision
		);

		deferred.done( $.proxy( this.render, this ) );
		// allow hijacking the fail-stack to gracefully recover from errors
		deferred = deferred.then( null, $.proxy( this.submitFail, this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.edit.prototype.render = function ( output ) {
		this.destroyEditForm();
		// content, like output.rendered, is pre-sanitized for direct html output
		// regardless of format.
		$( '.flow-realtitle', this.$container ).html( output.rendered );
	};

	/**
	 * Called when submitFunction failed.
	 *
	 * @param {jQuery.Deferred} deferred
	 * @param {object} data Old (invalid) this.prepareResult return value
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.topic.edit.prototype.submitFail = function ( deferred, data, error, errorData ) {
		if (
			// edit conflict
			error === 'block-errors' &&
			errorData.topic && errorData.topic.prev_revision &&
			errorData.topic.prev_revision.extra && errorData.topic.prev_revision.extra.revision_id
		) {
			var $input = this.$container.find( '.flow-edit-content' ),
				buttonText = mw.msg( 'flow-edit-title-submit-overwrite' ),
				tipsyText = errorData.topic.prev_revision.message;

			/*
			 * Overwrite data revision & content.
			 */
			data.content = $input.val();
			data.format = 'wikitext';
			data.revision = errorData.topic.prev_revision.extra.revision_id;

			// return conflict's deferred
			return this.conflict( data, buttonText, tipsyText );
		}

		return deferred;
	};

	/**
	 * Parameter supplier (to submitFunction) for flow( 'setupFormHandler' ).
	 *
	 * @return {array}
	 */
	mw.flow.action.topic.edit.prototype.loadParametersCallback = function () {
		var $titleEditForm = $( 'form', this.$container ),
			content = $titleEditForm.find( '.flow-edit-content' ).val();

		return [ this.object.workflowId, content ];
	};

	/**
	 * Validation (of loadParametersCallback return values) for flow( 'setupFormHandler' ).
	 *
	 * @return {bool}
	 */
	mw.flow.action.topic.edit.prototype.validateCallback = function ( content ) {
		return content !== '';
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.topic.edit.prototype.showError = function ( error, errorData ) {
		this.destroyEditForm();
		$( '.flow-topic-title', this.$container ).flow( 'showError', arguments );
	};

	/**
	 * Constructs the edit form HTML.
	 *
	 * @param {object} data this.prepareResult return value
	 */
	mw.flow.action.topic.edit.prototype.createEditForm = function ( data ) {
		// destroy existing edit form (if any)
		this.destroyEditForm();

		var $editLink = $( '.flow-edit-topic-link', this.$container ),
			$titleBar = $( '.flow-topic-title', this.$container ),
			$realTitle = $( '.flow-realtitle', this.$container ),
			$modifiedTipsy = $( '.flow-content-modified-tipsy-link', this.$container ),
			$titleEditForm = $( '<form />' );

		$realTitle.hide();
		$modifiedTipsy.hide();
		$editLink.hide();

		$titleEditForm
			.addClass( 'flow-edit-title-form' )
			.append(
				$( '<input />', {
					'class': 'mw-ui-input flow-edit-content',
					'type':  'text',
					// the returned content is pre-sanitized for html display.
					// jQuery also attempts to escape this data so we decode
					// it before passing in.
					'value': $( '<textarea>' ).html( data.content ).val()
				} ).byteLimit( mw.config.get( 'wgFlowMaxTopicLength' ) )
			)
			.append(
				$( '<div />', { 'class': 'flow-edit-title-controls' } )
					.append(
						$( '<div>' ).addClass( 'flow-terms-of-use plainlinks' )
						.html( mw.config.get( 'wgFlowTermsOfUseEdit' ) )
					)
					.append(
						$( '<a/>' )
							.addClass( 'flow-cancel-link mw-ui-button mw-ui-quiet' )
							.attr( 'href', '#' )
							.text( mw.msg( 'flow-cancel' ) )
							.on( 'click.mw-flow-discussion', $.proxy( function ( event ) {
								event.preventDefault();
								this.destroyEditForm();
							}, this ) )
					)
					.append( ' ' )
					.append(
						$( '<input />' )
							.addClass( 'flow-edit-submit mw-ui-button mw-ui-constructive' )
							.attr( 'type', 'submit' )
							.val( mw.msg( 'flow-edit-title-submit' ) )
					)
					.append(
						$( '<div>' ).addClass( 'clear' )
					)
			)
			.appendTo( $titleBar );

		$titleEditForm.find( '.flow-edit-content' )
			.focus()
			.select();
	};

	/**
	 * Removes the edit form & restores content.
	 */
	mw.flow.action.topic.edit.prototype.destroyEditForm = function () {
		var $editLink = $( '.flow-edit-topic-link', this.$container ),
			$realTitle = $( '.flow-realtitle', this.$container ),
			$modifiedTipsy = $( '.flow-content-modified-tipsy-link', this.$container ),
			$titleEditForm = $( 'form.flow-edit-title-form', this.$container );

		if ( $titleEditForm.length === 0 ) {
			return;
		}

		// remove any tipsies opened from within the form
		$titleEditForm.find( '.flow-tipsy-trigger' ).each( function () {
			$( this ).removeClass( 'flow-tipsy-trigger' );
			if ( $( this ).data( 'tipsy' ) ) {
				$( this ).tipsy( 'hide' );
			}
		} );

		$realTitle.show();
		$modifiedTipsy.show();
		$editLink.show();
		$titleEditForm
			.remove()
			.flow( 'hidePreview' );
	};

	/**
	 * Initialises topic reply interaction object.
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.reply = function ( topic ) {
		this.object = topic;
		this.$container = this.object.$container;

		this.$form = this.$container.find( '.flow-topic-reply-container' );

		// Overload focus in textarea, triggering full reply form
		this.$form.find( '.flow-reply-content' ).on( 'focus.mw-flow-discussion', $.proxy( this.reply, this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.topic.reply.prototype = new mw.flow.action();
	mw.flow.action.topic.reply.prototype.constructor = mw.flow.action.topic.reply;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.reply.prototype.getAction = function() {
		return 'reply';
	};

	/**
	 * Fired when textarea is clicked.
	 *
	 * @param {Event} event
	 */
	mw.flow.action.topic.reply.prototype.reply = function ( event ) {
		// don't follow link that will lead to &action=reply
		event.preventDefault();

		// load the form
		this.loadReplyForm();
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 *
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.reply.prototype.submitFunction = function ( content ) {
		var deferred = mw.flow.api.reply(
			this.object.workflowId,
			this.$form.data( 'post-id' ),
			content
		);

		deferred.done( $.proxy( this.render, this ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.reply.prototype.render = function ( output ) {
		$( output.rendered )
			.hide()
			.insertBefore( this.$form )
			.trigger( 'flow_init' )
			.slideDown( 'normal', function () {
				$( this ).conditionalScrollIntoView();
			} );
	};

	/**
	 * Initialises new topic interaction object.
	 */
	mw.flow.action.topic.create = function () {
		this.$form = $( '.flow-new-topic-container' );

		// fetch workflow parameters
		this.workflow = this.$form.flow( 'getWorkflowParameters' );

		// Overload focus in textarea, triggering full new topic form
		this.$form.find( '.flow-newtopic-title' )
			.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
			.on( 'focus.mw-flow-discussion', $.proxy( this.create, this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.topic.create.prototype = new mw.flow.action();
	mw.flow.action.topic.create.prototype.constructor = mw.flow.action.topic.create;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.topic.create.prototype.getAction = function() {
		return 'create';
	};

	/**
	 * Fired when textarea is clicked.
	 *
	 * @param {Event} event
	 */
	mw.flow.action.topic.create.prototype.create = function ( event ) {
		// don't follow link that will lead to &action=new-topic
		event.preventDefault();

		// don't re-bind if form is already active
		if ( this.$form.hasClass( 'flow-new-form-active' ) ) {
			return;
		}

		// mark form as active
		this.$form.addClass( 'flow-new-form-active' );

		// load the form
		this.loadNewForm();

		// add anon warning if required
		if ( mw.user.isAnon() ) {
			this.showAnonWarning( this.$form.find( '.flow-newtopic-title' ), 's' );
		}
	};

	/**
	 * Builds the new topic form.
	 */
	mw.flow.action.topic.create.prototype.loadNewForm = function () {
		this.$form.find( '.flow-newtopic-title' )
			.byteLimit( mw.config.get( 'wgFlowMaxTopicLength' ) )
			.attr( 'placeholder', mw.msg( 'flow-newtopic-title-placeholder' ) );

		$( 'form.flow-newtopic-form' ).flow( 'setupEmptyDisabler',
			[ '.flow-newtopic-title' ],
			'.flow-newtopic-submit'
		);

		mw.flow.editor.load( this.$form.find( '.flow-newtopic-content' ) );

		// Overload 'new topic' handler.
		this.$form.flow( 'setupFormHandler',
			'.flow-newtopic-submit',
			$.proxy( this.submitFunction, this ),
			$.proxy( this.loadParametersCallback, this ),
			$.proxy( this.validateCallback, this ),
			$.proxy( this.promiseCallback, this )
		);

		// add cancel link
		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link mw-ui-button mw-ui-quiet' )
			.text( mw.msg( 'flow-cancel' ) )
			.on( 'click.mw-flow-discussion', $.proxy( function ( event ) {
				event.preventDefault();
				this.destroyForm();
			}, this ) )
			.after( ' ' )
			.insertBefore( this.$form.find( '.flow-newtopic-form input[type=submit]' ) );

		// attach preview functionaly
		this.$form.find( 'form' ).flow( 'setupPreview', {
			'.flow-newtopic-title': 'plain',
			textarea: 'parsed'
		} );
	};

	/**
	 * Destroys the JS magic & restores the form in its original state
	 */
	mw.flow.action.topic.create.prototype.destroyForm = function () {
		mw.flow.editor.destroy( this.$form.find( '.flow-newtopic-content' ) );

		this.$form.find( '.flow-newtopic-step2' )
			.slideUp( 'fast', $.proxy( function () {
				// reset form in it's original blank state
				this.$form.find( '.flow-newtopic-title' )
					.val( '' )
					.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) );

				this.$form.find( 'form' )
					.flow( 'hidePreview' )
					.trigger( 'flow-form-destroyed' );

				/*
				 * After submitting the new topic, kill the events that were bound to
				 * the submit button via flow( 'setupEmptyDisabler' ) and
				 * flow( 'setupFormHandler' ).
				 * Otherwise, they'd be re-bound as soon as we trigger the form again.
				 */
				$( '.flow-newtopic-submit', this.$form ).off();

				// cleanup what's been added via JS
				this.$form.removeClass( 'flow-new-form-active' );
				this.$form.find( '.flow-cancel-link, .flow-content-preview, .flow-preview-submit' ).remove();
				this.$form.find( '.flow-error' ).remove();

				// slideUp adds some inline CSS to keep the elements hidden,
				// but we want it in it's original state (where CSS scoping
				// :not(.flow-new-form-active) will make the form stay hidden)
				this.$form.find( '.flow-newtopic-step2' ).css( 'display', '' );
			}, this ) );
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 *
	 * @param {string} title
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.create.prototype.submitFunction = function ( title, content ) {
		var deferred = mw.flow.api.newTopic(
			this.workflow,
			title,
			content
		);

		deferred.done( $.proxy( this.render, this ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.create.prototype.render = function ( output ) {
		$( output.rendered )
			.hide()
			.prependTo( $( '.flow-topics' ) )
			.trigger( 'flow_init' )
			.slideDown( 'normal', function () {
				$( this ).conditionalScrollIntoView();
			} );
	};

	/**
	 * Feeds parameters to flow( 'setupFormHandler' ).
	 *
	 * @return {array} Array with params, to be fed to validateCallback &
	 * submitFunction
	 */
	mw.flow.action.topic.create.prototype.loadParametersCallback = function () {
		var title = this.$form.find( '.flow-newtopic-title' ).val(),
			content = mw.flow.editor.getContent( this.$form.find( '.flow-newtopic-content' ) );

		return [title, content];
	};

	/**
	 * Validates parameters for flow( 'setupFormHandler' ).
	 * Parameters are supplied by this.loadParametersCallback.
	 *
	 * @param {string} title
	 * @param {string} content
	 * @return {bool}
	 */
	mw.flow.action.topic.create.prototype.validateCallback = function ( title, content ) {
		return !!title;
	};

	/**
	 * @param {jQuery.Deferred} deferred
	 */
	mw.flow.action.topic.create.prototype.promiseCallback = function ( deferred ) {
		deferred.done( $.proxy( this.destroyForm, this ) );
	};
} ( jQuery, mediaWiki ) );
>>>>>>> BRANCH (aab2ad Localisation updates from https://translatewiki.net.)
