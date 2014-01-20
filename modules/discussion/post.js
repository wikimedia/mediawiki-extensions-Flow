( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises post object.
	 *
	 * @param {string} postId
	 */
	mw.flow.discussion.post = function ( postId ) {
		this.postId = postId;
		this.$container = $( '#flow-post-' + this.postId );
		this.workflowId = this.$container.flow( 'getTopicWorkflowId' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );
		this.type = 'post';

		this.actions = {
			// init edit-post interaction
			edit: new mw.flow.action.post.edit( this ),
			// init reply interaction
			reply: new mw.flow.action.post.reply( this )
		};
	};

	mw.flow.action.post = {};

	/**
	 * Initialises edit-post interaction object.
	 *
	 * @param {object} post
	 */
	mw.flow.action.post.edit = function ( post ) {
		this.object = post;

		// Overload "edit post" link.
		this.object.$container.find( '.flow-edit-post-link' ).click( $.proxy( this.edit, this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.post.edit.prototype = new mw.flow.action();
	mw.flow.action.post.edit.prototype.constructor = mw.flow.action.post.edit;

	/**
	 * Fired when edit-link is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.post.edit.prototype.edit = function ( e ) {
		// don't follow link that will lead to &action=edit-post
		e.preventDefault();

		// quit if edit form is already open
		if ( this.object.$container.find( '.flow-edit-post-form' ).length ) {
			return;
		}

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
	 * Fetches post info.
	 *
	 * @see includes/Block/Topic.php TopicBlock::renderAPI
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.post.edit.prototype.read = function () {
		return mw.flow.api.readTopic(
			this.object.pageName,
			this.object.workflowId,
			{
				topic: {
					'no-children': true,
					postId: this.object.postId,
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
	mw.flow.action.post.edit.prototype.prepareResult = function ( data ) {
		var deferred = $.Deferred();

		if ( !data[0] || !data[0].content ) {
			return deferred.reject( '', {} );
		}

		return deferred.resolve( {
			content: data[0].content['*'],
			format: data[0].content.format,
			revision: data[0]['revision-id']
		} );
	};

	/**
	 * Builds the edit form.
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.post.edit.prototype.setupEditForm = function ( data, loadFunction ) {
		var $editLink = this.object.$container.find( '.flow-edit-post-link' ),
			$container = this.object.$container.addClass( 'flow-post-nocontrols' );

		// call parent setupEditForm function
		mw.flow.action.prototype.setupEditForm.call(
			this,
			data,
			loadFunction
		);

		// hide post controls & edit link and re-reveal it if the cancel link
		// - which is added by flow( 'setupEditForm' ) - is clicked.
		$editLink.hide();
		this.object.$container.find( '.flow-cancel-link' ).click( function () {
			$editLink.show();
			$container.removeClass( 'flow-post-nocontrols' );
		} );
	};

	/**
	 * Submit function for flow( 'setupEditForm' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.post.edit.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.editPost(
			this.object.workflowId,
			this.object.postId,
			content,
			data.revision
		);

		deferred.done( $.proxy( this.render, this ) );
		deferred.fail( $.proxy( this.conflict, this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.post.edit.prototype.render = function ( output ) {
		var $content = $( output.rendered );
		$( '.flow-post', $content )
			.replaceAll( this.object.$container )
			// replacing container node with new content will result in binds on old
			// nodes being useless and we'll need to bind again to the new DOM
			.trigger( 'flow_init' );
	};

	/**
	 * Called when submitFunction failed.
	 *
	 * @param {jQuery.Deferred} deferred
	 * @param {object} data Old (invalid) this.prepareResult return value
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.post.edit.prototype.conflict = function ( deferred, data, error, errorData ) {
		if (
			error === 'block-errors' &&
			errorData.topic && errorData.topic.prev_revision &&
			errorData.topic.prev_revision.extra && errorData.topic.prev_revision.extra.revision_id
		) {
			var $textarea = this.object.$container.find( 'textarea' );

			/*
			 * Overwrite data revision & content.
			 * We'll use raw editor content & editor format to avoid having
			 * to parse it.
			 */
			data.content = mw.flow.editor.getRawContent( $textarea );
			data.format = mw.flow.editor.getFormat( $textarea );
			data.revision = errorData.topic.prev_revision.extra.revision_id;

			/*
			 * At this point, we're still in the deferred's reject callbacks.
			 * Only after these are completed, is the spinner removed and the
			 * error message added.
			 * I'm adding another fail-callback, which will be executed after
			 * the fail has been handled. Only then, we can properly clean up.
			 */
			deferred.fail( $.proxy( function ( data, error, errorData ) {
				/*
				 * Tipsy will be positioned at the element where it's bound
				 * to, at the time it's asked to show. It won't reposition
				 * if the element moves. Since we re-launch the form, there
				 * may be some movement, so let's have this as callback when
				 * the form has completed loading before doing these changes.
				 */
				var formLoaded = $.proxy( function () {
					var $button = this.object.$container.find( '.flow-edit-submit' );
					$button.val( mw.msg( 'flow-edit-post-submit-overwrite' ) );
					this.tipsy( $button, errorData.topic.prev_revision.message );

					/*
					 * Trigger keyup in editor, to trick setupEmptyDisabler
					 * into believing we've made a change & enable submit.
					 */
					this.object.$container.find( 'textarea' ).keyup();
				}, this, data, error, errorData );

				// kill form & error message & re-launch edit form
				this.destroyEditForm();
				this.setupEditForm( data, formLoaded );
			}, this, data, error, errorData ) );
		}
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.post.edit.prototype.showError = function ( error, errorData ) {
		$( '.flow-post-content', this.object.$container ).flow( 'showError', arguments );
	};

	/**
	 * Initialises post reply interaction object.
	 *
	 * @param {object} post
	 */
	mw.flow.action.post.reply = function ( post ) {
		this.object = post;

		// Overload "reply" link.
		this.object.$container.find( '.flow-reply-link' ).click( $.proxy( this.reply, this ) );
	};

	// extend reply action from "shared functionality" mw.flow.action class
	mw.flow.action.post.reply.prototype = new mw.flow.action();
	mw.flow.action.post.reply.prototype.constructor = mw.flow.action.post.reply;

	/**
	 * Fired when reply-link is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.post.reply.prototype.reply = function ( e ) {
		// don't follow link that will lead to &action=reply
		e.preventDefault();

		// find matching edit form at (max threading depth - 1)
		this.$form = $( this.object.$container )
			.closest( '.flow-post-container:not(.flow-post-max-depth)' )
			.find( '.flow-post-reply-container' );

		// quit if reply form is already open
		if ( this.$form.is( ':visible' ) ) {
			return;
		}

		// load the form
		this.loadReplyForm();
	};

	/**
	 * Returns the initial content, to be served to loadReplyForm.
	 *
	 * @return {object}
	 */
	mw.flow.action.post.reply.prototype.initialContent = function () {
		// fetch username/IP
		var username = this.$form.closest( '.flow-post-container' ).data( 'creator-name' );

		// if we have a real username, turn it into "[[User]]" (otherwise, just "127.0.0.1")
		if ( !mw.util.isIPv4Address( username , true ) && !mw.util.isIPv6Address( username , true ) ) {
			username = '[[' + mw.Title.newFromText( username, 2 ).getPrefixedText() + '|' + username + ']]';
		}

		return {
			content: username + ': ',
			format: 'wikitext'
		};
	};

	/**
	 * Builds the reply form.
	 *
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.post.reply.prototype.loadReplyForm = function ( loadFunction ) {
		this.$form.flow(
			'loadReplyForm',
			this.object.type,
			this.initialContent(),
			$.proxy( this.submitFunction, this ),
			loadFunction
		);
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 * Arguments passed to this function are the return value of
	 * loadParametersCallback
	 *
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.post.reply.prototype.submitFunction = function ( content ) {
		var deferred = mw.flow.api.reply(
			this.object.workflowId,
			this.$form.find( 'input[name="topic_replyTo"]' ).val(),
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
	mw.flow.action.post.reply.prototype.render = function ( output ) {
		$( output.rendered )
			.hide()
			.insertBefore( this.$form )
			// the new post's node will need to have some events bound
			.trigger( 'flow_init' )
			.slideDown()
			.scrollIntoView();
	};
} ( jQuery, mediaWiki ) );
