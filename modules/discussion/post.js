( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises post object.
	 *
	 * @param {string} postId
	 */
	mw.flow.discussion.post = function ( postId ) {
		this.postId = postId;
		this.$container = $( '#flow-post-' + postId );
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
		this.$container = this.object.$container;

		// Overload "edit post" link.
		this.$container.find( '.flow-edit-post-link' ).on( 'click.mw-flow-discussion', $.proxy( this.edit, this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.post.edit.prototype = new mw.flow.action();
	mw.flow.action.post.edit.prototype.constructor = mw.flow.action.post.edit;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.post.edit.prototype.getAction = function() {
		return 'edit';
	};

	/**
	 * Fired when edit-link is clicked.
	 *
	 * @param {Event} event
	 */
	mw.flow.action.post.edit.prototype.edit = function ( event ) {
		// don't follow link that will lead to &action=edit-post
		event.preventDefault();

		// quit if edit form is already open
		if ( this.$container.find( '.flow-edit-post-form' ).length ) {
			return;
		}

		// Remove old error messages
		this.$container.find( '.flow-post-edit-error' ).remove();

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
		// call parent setupEditForm function
		mw.flow.action.prototype.setupEditForm.call(
			this,
			data,
			loadFunction
		);

		this.$container.find( '.flow-edit-post-link' ).hide();
		this.$container.addClass( 'flow-post-nocontrols' );
	};

	/**
	 * Removes the edit form & restores content.
	 */
	mw.flow.action.post.edit.prototype.destroyEditForm = function () {
		this.$container.find( '.flow-edit-post-link' ).show();
		this.$container.removeClass( 'flow-post-nocontrols' );

		// call parent destroyEditForm function
		mw.flow.action.prototype.destroyEditForm.call( this );
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
		// allow hijacking the fail-stack to gracefully recover from errors
		deferred = deferred.then( null, $.proxy( this.submitFail, this, deferred, data ) );

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
			.replaceAll( this.$container )
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
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.post.edit.prototype.submitFail = function ( deferred, data, error, errorData ) {
		if (
			// edit conflict
			error === 'block-errors' &&
			errorData.topic && errorData.topic.prev_revision &&
			errorData.topic.prev_revision.extra && errorData.topic.prev_revision.extra.revision_id
		) {
			var $textarea = this.$container.find( '.flow-edit-content' ),
				buttonText = mw.msg( 'flow-edit-post-submit-overwrite' ),
				tipsyText = errorData.topic.prev_revision.message;

			/*
			 * Overwrite data revision & content.
			 * We'll use raw editor content & editor format to avoid having
			 * to parse it.
			 */
			data.content = mw.flow.editor.getRawContent( $textarea );
			data.format = mw.flow.editor.getFormat( $textarea );
			data.revision = errorData.topic.prev_revision.extra.revision_id;

			// return conflict's deferred
			return this.conflict( data, buttonText, tipsyText );
		}

		return deferred;
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.post.edit.prototype.showError = function ( error, errorData ) {
		$( '.flow-post-content', this.$container )
			.append(
				$( '<div>', { 'class': 'flow-post-edit-error' } ).flow( 'showError', arguments )
			);
	};

	/**
	 * Initialises post reply interaction object.
	 *
	 * @param {object} post
	 */
	mw.flow.action.post.reply = function ( post ) {
		this.object = post;
		this.$container = this.object.$container;

		// Overload "reply" link.
		this.$container.find( '.flow-reply-link' ).on( 'click.mw-flow-discussion', $.proxy( this.reply, this ) );
	};

	// extend reply action from "shared functionality" mw.flow.action class
	mw.flow.action.post.reply.prototype = new mw.flow.action();
	mw.flow.action.post.reply.prototype.constructor = mw.flow.action.post.reply;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.post.reply.prototype.getAction = function() {
		return 'reply';
	};

	/**
	 * Fired when reply-link is clicked.
	 *
	 * @param {Event} event
	 */
	mw.flow.action.post.reply.prototype.reply = function ( event ) {
		// don't follow link that will lead to &action=reply
		event.preventDefault();

		// find matching edit form at (max threading depth - 1)
		this.$form = $( this.$container )
			.closest( '.flow-post-container:not(.flow-post-max-depth)' )
			.find( '.flow-post-reply-container:last' );

		// quit if reply form is already open
		if ( this.$form.is( ':visible' ) ) {
			// Scroll to form instead
			this.$form.conditionalScrollIntoView().queue( function () {
				mw.flow.editor.focus( $( this ).find( 'textarea' ) );
				$( this ).dequeue();
			} );
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
		var username = this.$container.closest( '.flow-post-container' ).data( 'creator-name' );

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
			.slideDown( 'normal', function () {
				$( this ).conditionalScrollIntoView();
			} );
	};
} ( jQuery, mediaWiki ) );
