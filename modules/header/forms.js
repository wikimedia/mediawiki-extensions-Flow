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
		 * @param {function} [loadFunction] callback to be executed when form is loaded
		 */
		setupEditForm: function ( data, loadFunction ) {
			var $editLink = $( this ).find( '.flow-header-edit-link' );

			$( this ).find( '#flow-header-content' )
				.flow(
					'setupEditForm',
					'header',
					{
						content: data.content,
						format: data.format
					},
					mw.flow.header.submitFunction.bind( this, data ),
					loadFunction
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
			var deferred = mw.flow.api.editHeader( {
					workflowId: data.workflow,
					page: data.page
				},
				content,
				data.revision
			);

			deferred.done( mw.flow.header.render.bind( deferred, $( this ), data ) );
			deferred.fail( mw.flow.header.conflict.bind( deferred, $( this ), data ) );

			return deferred;
		},

		/**
		 * Called when submitFunction is resolved.
		 *
		 * @param {jQuery} $container
		 * @param {object} data mw.flow.header.prepareResult return value
		 * @param {string} output
		 */
		render: function ( $container, data, output ) {
			$container
				.find( '#flow-header-content' )
				.empty()
				.append( $( output.rendered ) );
		},

		/**
		 * Called when submitFunction failed.
		 *
		 * this = #flow-header
		 *
		 * @param {jQuery} $container
		 * @param {object} data Old (invalid) mw.flow.header.prepareResult return value
		 * @param {string} error
		 * @param {object} errorData
		 */
		conflict: function ( $container, data, error, errorData ) {
			if (
				error === 'block-errors' &&
				errorData.header && errorData.header.prev_revision &&
				errorData.header.prev_revision.extra && errorData.header.prev_revision.extra.revision_id
			) {
				var $textarea = $container.find( 'textarea' );

				/*
				 * Overwrite data revision & content.
				 * We'll use raw editor content & editor format to avoid having
				 * to parse it.
				 */
				data.revision = errorData.header.prev_revision.extra.revision_id;
				data.format = mw.flow.editor.getFormat( $textarea );
				data.content = mw.flow.editor.getRawContent( $textarea );

				/*
				 * At this point, we're still in the deferred's reject callbacks.
				 * Only after these are completed, is the spinner removed and the
				 * error message added.
				 * I'm adding another fail-callback, which will be executed after
				 * the fail has been handled. Only then, we can properly clean up.
				 */
				this.fail( function( data, error, errorData ) {
					/*
					 * Tipsy will be positioned at the element where it's bound
					 * to, at the time it's asked to show. It won't reposition
					 * if the element moves. Since we re-launch the form, there
					 * may be some movement, so let's have this as callback when
					 * the form has completed loading before doing these changes.
					 */
					var formLoaded = function () {
						var $button = $( this ).find( '.flow-edit-header-submit' );
						$button.val( mw.msg( 'flow-edit-header-submit-overwrite' ) );
						mw.flow.header.tipsy( $button, errorData.header.prev_revision.message );

						/*
						 * Trigger keyup in editor, to trick setupEmptyDisabler
						 * into believing we've made a change & enable submit.
						 */
						$( this ).find( 'textarea' ).keyup();
					}.bind( this, data, error, errorData );

					// kill form & error message & re-launch edit form
					$( this ).find( 'form, flow-error' ).remove();
					mw.flow.header.setupEditForm.call( this, data, formLoaded );
				}.bind( $container.get( 0 ), data, error, errorData ) );
			}
		},

		/**
		 * Adds tipsy to an element, with the given text.
		 *
		 * @param {jQuery} $element
		 * @param {string} text
		 */
		tipsy: function ( $element, text ) {
			$element
				.click( function() {
					$( this ).tipsy( 'hide' );
				} )
				.tipsy( {
					fade: true,
					gravity: 'w',
					html: true,
					trigger: 'manual',
					className: 'flow-tipsy-destructive',
					title: function() {
						/*
						 * I'd prefer to only return content here, instead of wrapping
						 * it in a div. But we need to add some padding inside the tipsy.
						 * Tipsy has an option "className", which we could use to target
						 * the element though CSS, but that className is only applied
						 * _after_ tipsy has calculated position, so it's positioning
						 * would then be incorrect.
						 * Tossing in the content inside another div (which does have a
						 * class to target) works around this problem.
						 */

						// .html() only returns inner html, so attach the node to a new
						// parent & grab the full html there
						var $warning = $( '<div class="flow-tipsy-noflyout">' ).text( text );
						return $( '<div>' ).append( $warning ).html();
					}
				} )
				.tipsy( 'show' );
		},

		/**
		 * Display an error if something when wrong.
		 *
		 * this = #flow-header
		 *
		 * @param {string} error
		 * @param {object} errorData
		 */
		showError: function ( error, errorData ) {
			$( this ).flow( 'showError', arguments );
		}
	};

	$( document ).flow( 'registerInitFunction', mw.flow.header.init );
} ( jQuery, mediaWiki ) );
