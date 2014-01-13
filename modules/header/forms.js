( function ( $, mw ) {
	'use strict';

	/**
	 * Initialises header object.
	 */
	mw.flow.header = function () {
		this.$container = $( '#flow-header' );
		this.workflowId = this.$container.flow( 'getTopicWorkflowId' );
		this.pageName = this.$container.closest( '.flow-container' ).data( 'page-title' );

		// init edit-header interaction
		new mw.flow.header.edit( this );
	};

	/**
	 * Initialises edit-header interaction object.
	 *
	 * @param {object} header
	 */
	mw.flow.header.edit = function ( header ) {
		this.header = header;

		// Overload "edit header" link.
		this.header.$container.find( '.flow-header-edit-link' ).click( this.edit.bind( this ) );
	};

	/**
	 * Fired when edit-header link is clicked.
	 *
	 * @param {Event} e
	 */
	mw.flow.header.edit.prototype = {
		/**
		 * Fired when edit-header is clicked.
		 *
		 * @param {Event} e
		 */
		edit: function ( e ) {
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
		 * Fetches header info.
		 *
		 * @see includes/Block/Header.php Header::renderAPI
		 * @return {jQuery.Deferred}
		 */
		read: function () {
			return mw.flow.api.readHeader(
				this.header.pageName,
				this.header.workflowId,
				{
					header: {
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

			if ( !data[0] ) {
				return deferred.reject( '', {} );
			}

			return deferred.resolve( {
				content: data[0].missing ? '' : data[0]['*'],
				format: data[0].missing ? 'wikitext' : data[0].format,
				revision: data[0]['header-id']
			} );
		},


		/**
		 * Builds the edit form.
		 *
		 * @param {object} data this.prepareResult return value
		 * @param {function} [loadFunction] callback to be executed when form is loaded
		 */
		setupEditForm: function ( data, loadFunction ) {
			var $editLink = this.header.$container.find( '.flow-header-edit-link' );

			this.header.$container.find( '#flow-header-content' ).flow(
				'setupEditForm',
				'header',
				{
					content: data.content,
					format: data.format
				},
				this.submitFunction.bind( this, data ),
				loadFunction
			);

			// hide edit link and re-reveal it if the cancel link - which is
			// added by flow( 'setupEditForm' ) - is clicked.
			$editLink.hide();
			this.header.$container.find( '.flow-cancel-link' ).click( function () {
				$editLink.show();
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
			var deferred = mw.flow.api.editHeader( {
					workflowId: this.header.workflowId,
					page: this.header.pageName
				},
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
			this.header.$container
				.find( '#flow-header-content' )
				.empty()
				.append( $( output.rendered ) );
		},

		/**
		 * Display an error if something when wrong.
		 *
		 * @param {string} error
		 * @param {object} errorData
		 */
		showError: function ( error, errorData ) {
			this.header.$container.flow( 'showError', arguments );
		}
	};

	$( document ).flow( 'registerInitFunction', function () {
		new mw.flow.header();
	} );
} ( jQuery, mediaWiki ) );
