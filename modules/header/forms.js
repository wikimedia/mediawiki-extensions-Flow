( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises header object.
	 */
	mw.flow.header = function () {
		this.$container = $( '#flow-header' );
		this.workflowId = this.$container.flow( 'getTopicWorkflowId' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );

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
		this.header = header;

		// Overload "edit header" link.
		this.header.$container.find( '.flow-header-edit-link' ).click( $.proxy( this.edit, this ) );
	};

	// extend edit action from "shared functionality" mw.flow.action class
	mw.flow.action.header.edit.prototype = new mw.flow.action();
	mw.flow.action.header.edit.prototype.constructor = mw.flow.action.header.edit;

	/**
	 * Fired when edit-header is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.action.header.edit.prototype.edit = function ( e ) {
		// don't follow link that will lead to &action=edit-header
		e.preventDefault();

		// quit if edit form is already open
		if ( this.header.$container.find( '.flow-edit-header-form' ).length ) {
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
			this.header.pageName,
			this.header.workflowId,
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
		var $editLink = this.header.$container.find( '.flow-header-edit-link' );

		this.header.$container.find( '#flow-header-content' ).flow(
			'setupEditForm',
			'header',
			{
				content: data.content,
				format: data.format
			},
			$.proxy( this.submitFunction, this, data ),
			loadFunction
		);

		// hide edit link and re-reveal it if the cancel link - which is
		// added by flow( 'setupEditForm' ) - is clicked.
		$editLink.hide();
		this.header.$container.find( '.flow-cancel-link' ).click( function () {
			$editLink.show();
		} );
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
				workflowId: this.header.workflowId,
				page: this.header.pageName
			},
			content,
			data.revision
		);

		deferred.done( $.proxy( this.render, this ) );
//		deferred.fail( $.proxy( this.conflict, this, deferred ) ); // @todo: not yet implemented

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.header.edit.prototype.render = function ( output ) {
		this.header.$container
			.find( '#flow-header-content' )
			.empty()
			.removeClass( 'flow-header-empty' )
			.append( $( output.rendered ) );
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.header.edit.prototype.showError = function ( error, errorData ) {
		this.header.$container.flow( 'showError', arguments );
	};

	$( document ).flow( 'registerInitFunction', function () {
		new mw.flow.header();
	} );
} ( jQuery, mediaWiki ) );
