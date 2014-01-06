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

		// Overload "edit post" link.
		this.$container.find( '.flow-edit-post-link' ).click( this.edit.bind( this ) );
	};

	/**
	 * Fired when edit-link is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.discussion.post.prototype.edit = function ( e ) {
		// don't follow link that will lead to &action=edit-post
		e.preventDefault();

		// quit if edit form is already open
		if ( this.$container.find( '.flow-edit-post-form' ).length ) {
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
	};

	/**
	 * Fetches post info.
	 *
	 * @see includes/Block/Topic.php TopicBlock::renderAPI
	 * @returns {jQuery.Deferred}
	 */
	mw.flow.discussion.post.prototype.read = function () {
		return mw.flow.api.readTopic(
			this.pageName,
			this.workflowId,
			{
				topic: {
					'no-children': true,
					postId: this.postId,
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
	mw.flow.discussion.post.prototype.prepareResult = function ( data ) {
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
	mw.flow.discussion.post.prototype.setupEditForm = function ( data, loadFunction ) {
		var $editLink = this.$container.find( '.flow-edit-post-link' );
		var $container = this.$container.addClass( 'flow-post-nocontrols' );

		this.$container.find( '.flow-post-content' ).flow(
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
		this.$container.find( '.flow-cancel-link' ).click( function () {
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
	mw.flow.discussion.post.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.editPost(
			this.workflowId,
			this.postId,
			content,
			data.revision
		);

		deferred.done( this.render.bind( this ) );
//		deferred.fail( this.conflict.bind( this, deferred ) ); // @todo: not yet implemented

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.discussion.post.prototype.render = function ( output ) {
		var $content = $( output.rendered );
		this.$container.replaceWith( $( '.flow-post', $content ) );

		// replacing container node with new content will result in binds on old
		// nodes being useless and we'll need to bind again to the new DOM
		$content.trigger( 'flow_init' );
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.discussion.post.prototype.showError = function ( error, errorData ) {
		$( '.flow-post-content', this.$container ).flow( 'showError', arguments );
	};
} ( jQuery, mediaWiki ) );
