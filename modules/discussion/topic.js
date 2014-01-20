( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises topic object.
	 *
	 * @param {string} topicId
	 */
	mw.flow.discussion.topic = function ( topicId ) {
		this.topicId = topicId;
		this.$container = $( '#flow-topic-' + this.topicId );
		this.workflowId = this.$container.data( 'topic-id' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );
		this.type = 'topic';

		this.actions = {
			// init edit-title interaction
			edit: new mw.flow.action.topic.edit( this ),
			// init reply interaction
			reply: new mw.flow.action.topic.reply( this )
		};
	};

	mw.flow.action.topic = {};

	/**
	 * Initialises topic edit-title interaction object.
	 *
	 * @param {object} topic
	 */
	mw.flow.action.topic.edit = function ( topic ) {
		this.object = topic;

		// Overload "edit title" link.
		this.object.$container.find( '.flow-edit-topic-link' ).click( this.edit.bind( this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.topic.edit.prototype = new mw.flow.action();
	mw.flow.action.topic.edit.prototype.constructor = mw.flow.action.topic.edit;

	/**
	 * Fired when edit-title link is clicked.
	 *
	 * @param {Event} event
	 */
	mw.flow.action.topic.edit.prototype.edit = function ( event ) {
		// don't follow link that will lead to &action=edit-title
		event.preventDefault();

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
			.then( this.prepareResult.bind( this ) )
			/*
			 * Once we have successfully fetched & verified the data, the
			 * edit form can be built.
			 */
			.done( this.setupEditForm.bind( this ) )
			/*
			 * If anything went wrong (either in original deferred object or
			 * in the one returned by .then), show an error message.
			 */
			.fail( this.showError.bind( this ) );
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

		var $titleEditForm = $( 'form', this.object.$container );

		$titleEditForm.flow( 'setupPreview', { '.flow-edit-title-textbox': 'plain' } );
		$titleEditForm.flow( 'setupFormHandler',
			'.flow-edit-title-submit',
			this.submitFunction.bind( this, data ),
			this.loadParametersCallback.bind( this ),
			this.validateCallback.bind(this )
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

		deferred.done( this.render.bind( this ) );
		deferred.fail( this.conflict.bind( this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.edit.prototype.render = function ( output ) {
		this.destroyEditForm();
		$( '.flow-realtitle', this.object.$container ).text( output.rendered );
	};

	/**
	 * Called when submitFunction failed.
	 *
	 * @param {jQuery.Deferred} deferred
	 * @param {object} data Old (invalid) this.prepareResult return value
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.topic.edit.prototype.conflict = function ( deferred, data, error, errorData ) {
		if (
			error === 'block-errors' &&
			errorData.topic && errorData.topic.prev_revision &&
			errorData.topic.prev_revision.extra && errorData.topic.prev_revision.extra.revision_id
		) {
			var $input = this.object.$container.find( 'input' );

			/*
			 * Overwrite data revision & content.
			 * We'll use raw editor content & editor format to avoid having
			 * to parse it.
			 */
			data.content = $input.val();
			data.format = 'wikitext';
			data.revision = errorData.topic.prev_revision.extra.revision_id;

			/*
			 * At this point, we're still in the deferred's reject callbacks.
			 * Only after these are completed, is the spinner removed and the
			 * error message added.
			 * I'm adding another fail-callback, which will be executed after
			 * the fail has been handled. Only then, we can properly clean up.
			 */
			deferred.fail( function ( data, error, errorData ) {
				/*
				 * Tipsy will be positioned at the element where it's bound
				 * to, at the time it's asked to show. It won't reposition
				 * if the element moves. Since we re-launch the form, there
				 * may be some movement, so let's have this as callback when
				 * the form has completed loading before doing these changes.
				 */
				var formLoaded = function () {
					var $button = this.object.$container.find( '.flow-edit-title-submit' );
					$button.val( mw.msg( 'flow-edit-title-submit-overwrite' ) );
					this.tipsy( $button, errorData.topic.prev_revision.message );

					/*
					 * Trigger keyup in editor, to trick setupEmptyDisabler
					 * into believing we've made a change & enable submit.
					 */
					this.object.$container.find( 'input' ).keyup();
				}.bind( this, data, error, errorData );

				// kill form & error message & re-launch edit form
				this.object.$container.find( 'form, flow-error' ).remove();
				this.setupEditForm( data, formLoaded );
			}.bind( this, data, error, errorData ) );
		}
	};

	/**
	 * Parameter supplier (to submitFunction) for flow( 'setupFormHandler' ).
	 *
	 * @return {array}
	 */
	mw.flow.action.topic.edit.prototype.loadParametersCallback = function () {
		var $titleEditForm = $( 'form', this.object.$container ),
			content = $titleEditForm.find( '.flow-edit-title-textbox' ).val();

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
		$( '.flow-topic-title', this.object.$container ).flow( 'showError', arguments );
	};

	/**
	 * Constructs the edit form HTML.
	 *
	 * @param {object} data this.prepareResult return value
	 */
	mw.flow.action.topic.edit.prototype.createEditForm = function ( data ) {
		// destroy existing edit form (if any)
		this.destroyEditForm();

		var $editLink = $( '.flow-edit-topic-link', this.object.$container ),
			$titleBar = $( '.flow-topic-title', this.object.$container ),
			$realTitle = $( '.flow-realtitle', this.object.$container ),
			$modifiedTipsy = $( '.flow-content-modified-tipsy-link', this.object.$container ),
			$titleEditForm = $( '<form />' );

		$realTitle.hide();
		$modifiedTipsy.hide();
		$editLink.hide();

		$titleEditForm
			.addClass( 'flow-edit-title-form' )
			.append(
				$( '<input />' )
					.addClass( 'mw-ui-input' )
					.addClass( 'flow-edit-title-textbox' )
					.attr( 'type', 'text' )
					.byteLimit( mw.config.get( 'wgFlowMaxTopicLength' ) )
					.val( data.content )
			)
			.append(
				$( '<div class="flow-edit-title-controls"></div>' )
					.append(
						$( '<a/>' )
							.addClass( 'flow-cancel-link mw-ui-button mw-ui-quiet' )
							.attr( 'href', '#' )
							.text( mw.msg( 'flow-cancel' ) )
							.click( function ( event ) {
								event.preventDefault();
								this.destroyEditForm();
							}.bind( this ) )
					)
					.append( ' ' )
					.append(
						$( '<input />' )
							.addClass( 'flow-edit-title-submit' )
							.addClass( 'mw-ui-button' )
							.addClass( 'mw-ui-constructive' )
							.attr( 'type', 'submit' )
							.val( mw.msg( 'flow-edit-title-submit' ) )
					)
			)
			.appendTo( $titleBar );

		$titleEditForm.find( '.flow-edit-title-textbox' )
			.focus()
			.select();
	};

	/**
	 * Removes the edit form & restores content.
	 */
	mw.flow.action.topic.edit.prototype.destroyEditForm = function () {
		var $editLink = $( '.flow-edit-topic-link', this.object.$container ),
			$titleBar = $( '.flow-topic-title', this.object.$container ),
			$realTitle = $( '.flow-realtitle', this.object.$container ),
			$modifiedTipsy = $( '.flow-content-modified-tipsy-link', this.object.$container ),
			$titleEditForm = $( 'form', this.object.$container );

		if ( $titleEditForm.length === 0 ) {
			return;
		}

		$titleBar.children( 'form' ).remove();
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
		this.$form = this.object.$container.find( '.flow-topic-reply-container' );

		// Overload click in textarea, triggering full reply form
		this.$form.find( '.flow-topic-reply-content' ).click( this.reply.bind( this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.topic.reply.prototype = new mw.flow.action();
	mw.flow.action.topic.reply.prototype.constructor = mw.flow.action.topic.reply;

	/**
	 * Fired when textarea is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.topic.reply.prototype.reply = function ( e ) {
		// don't follow link that will lead to &action=reply
		e.preventDefault();

		// load the form
		this.loadReplyForm();
	};

	/**
	 * Builds the reply form.
	 *
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.topic.reply.prototype.loadReplyForm = function ( loadFunction ) {
		this.$form.flow(
			'loadReplyForm',
			this.object.type,
			this.initialContent(),
			this.submitFunction.bind( this ),
			loadFunction
		);
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

		deferred.done( this.render.bind( this ) );

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
			.slideDown()
			.scrollIntoView();
	};

	/**
	 * Initialises new topic interaction object.
	 */
	mw.flow.action.topic.new = function () {
		this.$form = $( '.flow-new-topic-container' );

		// fetch workflow parameters
		this.workflow = this.$form.flow( 'getWorkflowParameters' );

		// Overload click in textarea, triggering full new topic form
		this.$form.find( '.flow-newtopic-title' )
			.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
			.click( this.new.bind( this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.topic.new.prototype = new mw.flow.action();
	mw.flow.action.topic.new.prototype.constructor = mw.flow.action.topic.new;

	/**
	 * Fired when textarea is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.topic.new.prototype.new = function ( e ) {
		// don't follow link that will lead to &action=new-topic
		e.preventDefault();

		// don't re-bind if form is already active
		if ( this.$form.hasClass( 'flow-form-active' ) ) {
			return;
		}

		// mark form as active
		this.$form.addClass( 'flow-form-active' );

		// load the form
		this.loadNewForm();
	};

	/**
	 * Builds the new topic form.
	 */
	mw.flow.action.topic.new.prototype.loadNewForm = function () {
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
			this.submitFunction.bind( this ),
			this.loadParametersCallback.bind( this ),
			this.validateCallback.bind( this ),
			this.promiseCallback.bind( this )
		);

		// add cancel link
		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link mw-ui-button mw-ui-quiet' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function ( e ) {
				e.preventDefault();
				this.destroyForm();
			}.bind( this ) )
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
	mw.flow.action.topic.new.prototype.destroyForm = function () {
		mw.flow.editor.destroy( this.$form.find( '.flow-newtopic-content' ) );

		this.$form.find( '.flow-newtopic-step2' )
			.slideUp( 'fast', function () {
				// reset form in it's original blank state
				this.$form.find( '.flow-newtopic-title' )
					.val( '' )
					.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) );

				this.$form.find( 'form' ).flow( 'hidePreview' );

				/*
				 * After submitting the new topic, kill the events that were bound to
				 * the submit button via flow( 'setupEmptyDisabler' ) and
				 * flow( 'setupFormHandler' ).
				 * Otherwise, they'd be re-bound as soon as we trigger the form again.
				 */
				$( '.flow-newtopic-submit', this.$form ).off();

				// cleanup what's been added via JS
				this.$form.removeClass( 'flow-form-active' );
				this.$form.find( '.flow-cancel-link, .flow-content-preview, .flow-preview-submit' ).remove();
				this.$form.find( '.flow-error' ).remove();

				// slideUp adds some inline CSS to keep the elements hidden,
				// but we want it in it's original state (where CSS scoping
				// :not(.flow-form-active) will make the form stay hidden)
				this.$form.find( '.flow-newtopic-step2' ).css( 'display', '' );
			}.bind( this ) );
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 *
	 * @param {string} title
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.topic.new.prototype.submitFunction = function ( title, content ) {
		var deferred = mw.flow.api.newTopic(
			this.workflow,
			title,
			content
		);

		deferred.done( this.render.bind( this ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.topic.new.prototype.render = function ( output ) {
		$( output.rendered )
			.hide()
			.prependTo( $( '.flow-topics' ) )
			.trigger( 'flow_init' )
			.slideDown()
			.scrollIntoView();
	};

	/**
	 * Feeds parameters to flow( 'setupFormHandler' ).
	 *
	 * @return {array} Array with params, to be fed to validateCallback &
	 * submitFunction
	 */
	mw.flow.action.topic.new.prototype.loadParametersCallback = function () {
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
	mw.flow.action.topic.new.prototype.validateCallback = function ( title, content ) {
		return !!title;
	};

	/**
	 * @param {jQuery.Deferred} deferred
	 */
	mw.flow.action.topic.new.prototype.promiseCallback = function ( deferred ) {
		deferred.done( this.destroyForm.bind( this ) );
	};
} ( jQuery, mediaWiki ) );
