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

		// init edit-post interaction
		new mw.flow.discussion.post.edit( this );

		// init reply interaction
		new mw.flow.discussion.post.reply( this );
	};

	/**
	 * Initialises topic edit-post interaction object.
	 *
	 * @param {object} post
	 */
	mw.flow.discussion.post.edit = function ( post ) {
		this.post = post;

		// Overload "edit post" link.
		this.post.$container.find( '.flow-edit-post-link' ).click( this.edit.bind( this ) );
	};

	/**
	 * Fired when edit-title link is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.discussion.post.edit.prototype = {
		/**
		 * Fired when edit-link is clicked.
		 *
		 * @param {Event} e
		 */
		edit: function ( e ) {
			// don't follow link that will lead to &action=edit-post
			e.preventDefault();

			// quit if edit form is already open
			if ( this.post.$container.find( '.flow-edit-post-form' ).length ) {
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
		},

		/**
		 * Fetches post info.
		 *
		 * @see includes/Block/Topic.php TopicBlock::renderAPI
		 * @returns {jQuery.Deferred}
		 */
		read: function () {
			return mw.flow.api.readTopic(
				this.post.pageName,
				this.post.workflowId,
				{
					topic: {
						'no-children': true,
						postId: this.post.postId,
						contentFormat: mw.flow.editor.getFormat()
					}
				}
			);
		},

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
		prepareResult: function ( data ) {
			var deferred = $.Deferred();

			if ( !data[0] || !data[0].content ) {
				return deferred.reject( '', {} );
			}

			return deferred.resolve( {
				content: data[0].content['*'],
				format: data[0].content.format,
				revision: data[0]['revision-id']
			} );
		},

		/**
		 * Builds the edit form.
		 *
		 * @param {object} data this.prepareResult return value
		 * @param {function} [loadFunction] callback to be executed when form is loaded
		 */
		setupEditForm: function ( data, loadFunction ) {
			var $editLink = this.post.$container.find( '.flow-edit-post-link' ),
				$container = this.post.$container.addClass( 'flow-post-nocontrols' );

			this.post.$container.find( '.flow-post-content' ).flow(
				'setupEditForm',
				'post',
				{
					content: data.content,
					format: data.format
				},
				this.submitFunction.bind( this, data ),
				loadFunction
			);

			// hide post controls & edit link and re-reveal it if the cancel link
			// - which is added by flow( 'setupEditForm' ) - is clicked.
			$editLink.hide();
			this.post.$container.find( '.flow-cancel-link' ).click( function () {
				$editLink.show();
				$container.removeClass( 'flow-post-nocontrols' );
			} );
		},

		/**
		 * Submit function for flow( 'setupEditForm' ).
		 *
		 * @param {object} data this.prepareResult return value
		 * @param {string} content
		 * @return {jQuery.Deferred}
		 */
		submitFunction: function ( data, content ) {
			var deferred = mw.flow.api.editPost(
				this.post.workflowId,
				this.post.postId,
				content,
				data.revision
			);

			deferred.done( this.render.bind( this ) );
//			deferred.fail( this.conflict.bind( this, deferred ) ); // @todo: not yet implemented

			return deferred;
		},

		/**
		 * Called when submitFunction is resolved.
		 *
		 * @param {object} output
		 */
		render: function ( output ) {
			var $content = $( output.rendered );
			this.post.$container.replaceWith( $( '.flow-post', $content ) );

			// replacing container node with new content will result in binds on old
			// nodes being useless and we'll need to bind again to the new DOM
			$content.trigger( 'flow_init' );
		},

		/**
		 * Display an error if something when wrong.
		 *
		 * @param {string} error
		 * @param {object} errorData
		 */
		showError: function ( error, errorData ) {
			$( '.flow-post-content', this.post.$container ).flow( 'showError', arguments );
		}
	};

	/**
	 * Initialises topic reply interaction object.
	 *
	 * @param {object} post
	 */
	mw.flow.discussion.post.reply = function ( post ) {
		this.post = post;

		// Overload "reply" link.
		this.post.$container.find( '.flow-reply-link' ).click( this.reply.bind( this ) );
	};

	/**
	 * Fired when reply form is initialized.
	 *
	 * @param {Event} e
	 */
	mw.flow.discussion.post.reply.prototype = {
		/**
		 * Fired when reply-link is clicked.
		 *
		 * @param {Event} e
		 */
		reply: function ( e ) {
			// don't follow link that will lead to &action=reply
			e.preventDefault();

			// find matching edit form at (max threading depth - 1)
			this.$form = $( this.post.$container )
				.closest( '.flow-post-container:not(.flow-post-max-depth)' )
				.find( '.flow-post-reply-container' );

			// quit if reply form is already open
			if ( this.$form.is( ':visible' ) ) {
				return;
			}

			// load the form
			this.setupReplyForm();
		},

		/**
		 * Builds the reply form.
		 */
		setupReplyForm: function () {
			// fetch username/IP
			var username = this.$form.closest( '.flow-post-container' ).data( 'creator-name' );

			// if we have a real username, turn it into "[[User]]" (otherwise, just "127.0.0.1")
			if ( !mw.util.isIPv4Address( username , true ) && !mw.util.isIPv6Address( username , true ) ) {
				username = '[[' + mw.Title.newFromText( username, 2 ).getPrefixedText() + '|' + username + ']]';
			}

			// init form: load editor & scroll into view
			mw.flow.discussion.loadReplyForm( this.$form, username + ': ' );

			// setup disabler (disables submit button until content is entered)
			this.$form.flow( 'setupEmptyDisabler',
				['.flow-reply-content'],
				'.flow-reply-submit'
			);

			// init form submission callbacks
			this.$form.flow( 'setupFormHandler',
				'.flow-reply-submit',
				this.submitFunction.bind( this ),
				this.loadParametersCallback.bind( this ),
				this.validateCallback.bind( this ),
				this.promiseCallback.bind( this )
			);
		},

		/**
		 * Submit function for flow( 'setupFormHandler' ).
		 * Arguments passed to this function are the return value of
		 * loadParametersCallback
		 *
		 * @param {string} workflowId
		 * @param {string} replyToId
		 * @param {string} content
		 * @return {jQuery.Deferred}
		 */
		submitFunction: function ( workflowId, replyTo, content ) {
			var deferred = mw.flow.api.reply(
				workflowId,
				replyTo,
				content
			);

			deferred.done( this.render.bind( this ) );

			return deferred;
		},

		/**
		 * Called when submitFunction is resolved.
		 *
		 * @param {object} output
		 */
		render: function ( output ) {
			$( output.rendered )
				.hide()
				.insertBefore( this.$form )
				// the new post's node will need to have some events bound
				.trigger( 'flow_init' )
				.slideDown()
				.scrollIntoView();
		},

		/**
		 * Parameter supplier (to submitFunction) for flow( 'setupFormHandler' ).
		 *
		 * @return {array}
		 */
		loadParametersCallback: function () {
			var replyToId = this.$form.find( 'input[name="topic[replyTo]"]' ).val(),
				content = mw.flow.editor.getContent( this.$form.find( '.flow-reply-content' ) );

			return [ this.post.workflowId, replyToId, content ];
		},

		/**
		 * Validation (of loadParametersCallback return values) for flow( 'setupFormHandler' ).
		 *
		 * @return {bool}
		 */
		validateCallback: function ( content ) {
			return content !== '';
		},

		/**
		 * Because reply-forms may be re-used across posts (when max
		 * threading depth has been reached), we have to make sure not
		 * to bind more than 1 submission-handler to the same form.
		 *
		 * After submitting the new post, kill the events that were bound to
		 * the submit button via flow( 'setupEmptyDisabler' ) and
		 * flow( 'setupFormHandler' ).
		 *
		 * @param deferred
		 */
		promiseCallback: function ( deferred ) {
			deferred.done( function () {
				$( '.flow-reply-submit', this.$form ).off();
			}.bind( this ) );
		}
	};
} ( jQuery, mediaWiki ) );
