( function ( $, mw ) {
	'use strict';

	mw.flow.header = {
		/**
		 * @param {Event} e
		 */
		init: function ( e ) {
			// Overload "edit header" link.
			$( e.target ).find( '.flow-header-edit-link' ).click( mw.flow.header.edit );
		},

		/**
		 * Fired when edit-link is clicked.
		 *
		 * @param {Event} e
		 */
		edit: function ( e ) {
			var $flowContainer = $( this ).closest( '.flow-container' ),
				pageName = $flowContainer.data( 'page-title' ),
				workflowId = $flowContainer.data( 'workflow-id' ),
				$headerContainer = $( this ).closest( '#flow-header' );

			// don't follow link that will lead to &action=edit-header
			e.preventDefault();

			/*
			 * Fetch current revision data (content, revision id, ...) that
			 * we'll need to initialise the edit form.
			 */
			mw.flow.header.read( pageName, workflowId )
				/*
				 * This is a .then: then callbacks can return a value, which is
				 * then passed to .done or .fail. This allows us to format the
				 * returned data to how we want it in .done, or reject (which
				 * will result in .fail being called) if the data is invalid.
				 */
				.then( mw.flow.header.prepareResult.bind( $headerContainer, pageName, workflowId ) )
				/*
				 * Once we have successfully fetched & verified the data, the
				 * edit form can be built.
				 */
				.done( mw.flow.header.setupEditForm.bind( $headerContainer ) )
				/*
				 * If anything went wrong (either in original deferred object or
				 * in the one returned by .then), show an error message.
				 */
				.fail( mw.flow.header.showError.bind( $headerContainer ) );
		},

		/**
		 * Fetches header info.
		 *
		 * @see includes/Block/Header.php Header::renderAPI
		 * @param {string} pageName
		 * @param {string} workflowId
		 * @returns {jQuery.Deferred}
		 */
		read: function ( pageName, workflowId ) {
			return mw.flow.api.readHeader(
				pageName,
				workflowId,
				{ header: { contentFormat: mw.flow.editor.getFormat() } }
			);
		},

		/**
		 * Processes the result of mw.flow.header.read & the returned deferred
		 * will be passed to setupEditForm.
		 *
		 * if invalid data is encountered, the (new) deferred will be rejected,
		 * resulting in any fail() bound to be executed.
		 *
		 * @param {string} pageName
		 * @param {string} workflowId
		 * @param {object} data
		 * @return {jQuery.Deferred}
		 */
		prepareResult: function ( pageName, workflowId, data ) {
			var deferred = $.Deferred();

			if ( !data[0] ) {
				return deferred.reject( '', {} );
			}

			return deferred.resolve( {
				page: pageName,
				workflow: workflowId,
				content: data[0].missing ? '' : data[0]['*'],
				format: data[0].missing ? 'wikitext' : data[0].format,
				revision: data[0]['header-id']
			} );
		},

		/**
		 * Builds the edit form.
		 *
		 * this = #flow-header
		 *
		 * @param {object} data mw.flow.header.prepareResult return value
		 */
		setupEditForm: function ( data ) {
			var $editLink = $( this ).find( '.flow-header-edit-link' );

			$( this ).find( '#flow-header-content' )
				.flow(
					'setupEditForm',
					'header',
					{
						content: data.content,
						format: data.format
					},
					mw.flow.header.submitFunction.bind( this, data )
				);

			// hide edit link and re-reveal it if the cancel link - which is
			// added by flow( 'setupEditForm' ) - is clicked.
			$editLink.hide();
			$( this ).find( '.flow-cancel-link' ).click( function () {
				$editLink.show();
			} );
		},

		/**
		 * Submit function for flow( 'setupEditForm' ).
		 *
		 * this = #flow-header
		 *
		 * @param {object} data mw.flow.header.prepareResult return value
		 * @param {string} content
		 * @return {jQuery.Deferred}
		 */
		submitFunction: function ( data, content ) {
			return mw.flow.api.editHeader( {
					workflowId: data.workflow,
					page: data.page
				},
				content,
				data.revision
			).done( mw.flow.header.render.bind( this ) );
		},

		/**
		 * Called when setupEditForm is resolved.
		 *
		 * this = #flow-header
		 *
		 * @param {object} output
		 */
		render: function ( output ) {
			$( this )
				.find( '#flow-header-content' )
				.empty()
				.append( $( output.rendered ) );
		},

		/**
		 * Display an error if something when wrong.
		 *
		 * this = #flow-header
		 *
		 * @param {string} error
		 * @param {object} data
		 */
		showError: function ( error, data ) {
			$( this ).flow( 'showError', arguments );
		}
	};

	$( document ).flow( 'registerInitFunction', mw.flow.header.init );
} ( jQuery, mediaWiki ) );
