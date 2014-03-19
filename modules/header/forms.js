( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises header object.
	 */
	mw.flow.header = function () {
		this.$container = $( '#flow-header' );
		this.workflowId = this.$container.flow( 'getTopicWorkflowId' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );
		this.type = 'header';

		this.actions = {
			// init edit-header interaction
			edit: new mw.flow.action.header.edit( this )
		};
	};

	mw.flow.action.header = {};

	/**
	 * Initialises edit-header interaction object.
	 *
	 * @param {object} header
	 */
	mw.flow.action.header.edit = function ( header ) {
		this.object = header;
		this.$container = this.object.$container;

		// Overload "edit header" link.
		this.$container.find( '.flow-header-edit-link' ).click( $.proxy( this.edit, this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.header.edit.prototype = new mw.flow.action();
	mw.flow.action.header.edit.prototype.constructor = mw.flow.action.header.edit;

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.header.edit.prototype.getAction = function() {
		return 'edit';
	};

	/**
	 * Fired when edit-header is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.header.edit.prototype.edit = function ( e ) {

		// don't follow link that will lead to &action=edit-header
		e.preventDefault();

		// quit if edit form is already open
		if ( this.$container.find( '.flow-edit-header-form' ).length ) {
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
	 * Fetches header info.
	 *
	 * @see includes/Block/Header.php Header::renderAPI
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.header.edit.prototype.read = function () {
		return mw.flow.api.readHeader(
			this.object.pageName,
			this.object.workflowId,
			{
				header: {
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
	mw.flow.action.header.edit.prototype.prepareResult = function ( data ) {
		var deferred = $.Deferred();

		if ( !data[0] ) {
			return deferred.reject( '', {} );
		}

		return deferred.resolve( {
			content: data[0].missing ? '' : data[0]['*'],
			format: data[0].missing ? 'wikitext' : data[0].format,
			revision: data[0]['header-id']
		} );
	};

	/**
	 * Builds the edit form.
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.header.edit.prototype.setupEditForm = function ( data, loadFunction ) {
		// call parent setupEditForm function
		mw.flow.action.prototype.setupEditForm.call(
			this,
			data,
			loadFunction
		);

		this.$container.find( '.flow-header-edit-link' ).hide();
	};

	/**
	 * Removes the edit form & restores content.
	 */
	mw.flow.action.header.edit.prototype.destroyEditForm = function () {
		this.$container.find( '.flow-header-edit-link' ).show();

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
	mw.flow.action.header.edit.prototype.submitFunction = function ( data, content ) {
		var deferred = mw.flow.api.editHeader( {
				workflowId: this.object.workflowId,
				page: this.object.pageName
			},
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
	mw.flow.action.header.edit.prototype.render = function ( output ) {
		this.$container
			.find( '.flow-edit-header-form' )
				.remove()
				.end()
			.find( '#flow-header-content' )
				.empty()
				.removeClass( 'flow-header-empty' )
				.append( $( output.rendered ) )
				.end()
			.find( '.flow-header-edit-link' )
				.show();
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
	mw.flow.action.header.edit.prototype.submitFail = function ( deferred, data, error, errorData ) {
		if (
			// edit conflict
			error === 'block-errors' &&
			errorData.header && errorData.header.prev_revision &&
			errorData.header.prev_revision.extra && errorData.header.prev_revision.extra.revision_id
		) {
			var $textarea = this.$container.find( '.flow-edit-content' ),
				buttonText = mw.msg( 'flow-edit-header-submit-overwrite' ),
				tipsyText = errorData.header.prev_revision.message;

			/*
			 * Overwrite data revision & content.
			 * We'll use raw editor content & editor format to avoid having
			 * to parse it.
			 */
			data.content = mw.flow.editor.getRawContent( $textarea );
			data.format = mw.flow.editor.getFormat( $textarea );
			data.revision = errorData.header.prev_revision.extra.revision_id;

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
	mw.flow.action.header.edit.prototype.showError = function ( error, errorData ) {
		this.$container.flow( 'showError', arguments );
	};

	$( document ).flow( 'registerInitFunction', function () {
		new mw.flow.header();
	} );
} ( jQuery, mediaWiki ) );
