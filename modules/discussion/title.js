( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises topic title object.
	 *
	 * @param {string} topicId
	 */
	mw.flow.discussion.title = function ( topicId ) {
		this.topicId = topicId;
		this.$container = $( '#flow-topic-' + this.topicId );
		this.workflowId = this.$container.data( 'topic-id' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );

		// Overload "edit title" link.
		this.$container.find( '.flow-edit-topic-link' ).click( this.edit.bind( this ) );
	};

	/**
	 * Fired when edit-link is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.discussion.title.prototype.edit = function ( e ) {
		// don't follow link that will lead to &action=edit-title
		e.preventDefault();

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
	 * @returns {jQuery.Deferred}
	 */
	mw.flow.discussion.title.prototype.read = function () {
		return mw.flow.api.readTopic(
			this.pageName,
			this.workflowId,
			{
				topic: {
					'no-children': true,
					postId: this.workflowId, // fetch title post (not full topic)
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
	mw.flow.discussion.title.prototype.prepareResult = function ( data ) {
		var deferred = $.Deferred();

		if ( !data[0] || !data[0].content ) {
			return deferred.reject( '', {} );
		}

		return deferred.resolve( {
			// content should already be (fake) wikitext, this is just failsafe
			content: mw.flow.parsoid.convert( data[0].content.format, 'wikitext', data[0].content['*'], this.pageName ),
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
	mw.flow.discussion.title.prototype.setupEditForm = function ( data, loadFunction ) {
		// create form html
		this.createEditForm( data );

		var $titleEditForm = $( 'form', this.$container );

		$titleEditForm.flow( 'setupPreview', { '.flow-edit-title-textbox': 'plain' } );
		$titleEditForm.flow( 'setupFormHandler',
			'.flow-edit-title-submit',
			this.submitFunction.bind( this, data ),
			this.loadParametersCallback.bind( this ),
			this.validateCallback.bind(this )
		);

		if ( loadFunction instanceof Function) {
			loadFunction();
		}
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {string} content
	 * @return {jQuery.Deferred}
	 */
	mw.flow.discussion.title.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.changeTitle(
			this.workflowId,
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
	mw.flow.discussion.title.prototype.render = function ( output ) {
		this.destroyEditForm();
		$( '.flow-realtitle', this.$container ).text( output.rendered );
	};

	/**
	 * Parameter supplier (to submitFunction) for flow( 'setupFormHandler' ).
	 *
	 * @return {array}
	 */
	mw.flow.discussion.title.prototype.loadParametersCallback = function () {
		var $titleEditForm = $( 'form', this.$container ),
			content = $titleEditForm.find( '.flow-edit-title-textbox' ).val();

		return [ content ];
	};

	/**
	 * Validation (of loadParametersCallback return values) for flow( 'setupFormHandler' ).
	 *
	 * @return {bool}
	 */
	mw.flow.discussion.title.prototype.validateCallback = function ( content ) {
		return content !== '';
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.discussion.title.prototype.showError = function ( error, errorData ) {
		this.destroyEditForm();
		$( '.flow-topic-title', this.$container ).flow( 'showError', arguments );
	};

	/**
	 * Constructs the edit form HTML.
	 *
	 * @param {object} data this.prepareResult return value
	 */
	mw.flow.discussion.title.prototype.createEditForm = function ( data ) {
		// destroy existing edit form (if any)
		this.destroyEditForm();

		var $editLink = $( '.flow-edit-topic-link', this.$container ),
			$titleBar = $( '.flow-topic-title', this.$container ),
			$realTitle = $( '.flow-realtitle', this.$container ),
			$titleEditForm = $( '<form />' );

		$realTitle.hide();
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
							.addClass( 'flow-cancel-link' )
							.addClass( 'mw-ui-button' )
							.addClass( 'mw-ui-text' )
							.attr( 'href', '#' )
							.text( mw.msg( 'flow-cancel' ) )
							.click( function ( e ) {
								e.preventDefault();
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
	mw.flow.discussion.title.prototype.destroyEditForm = function() {
		var $editLink = $( '.flow-edit-topic-link', this.$container ),
			$titleBar = $( '.flow-topic-title', this.$container ),
			$realTitle = $( '.flow-realtitle', this.$container ),
			$titleEditForm = $( 'form', this.$container );

		if ( $titleEditForm.length === 0 ) {
			return;
		}

		$titleBar.children( 'form' ).remove();
		$realTitle.show();
		$editLink.show();
		$titleEditForm
			.remove()
			.flow( 'hidePreview' );
	};
} ( jQuery, mediaWiki ) );
