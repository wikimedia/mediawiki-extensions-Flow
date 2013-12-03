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
			deferred.fail( this.conflict.bind( this, deferred, data ) );

			return deferred;
		},

		/**
		 * Called when submitFunction is resolved.
		 *
		 * @param {jQuery} $container
		 * @param {object} data mw.flow.header.prepareResult return value
		 * @param {object} output
		 */
		render: function ( output ) {
			this.header.$container
				.find( '#flow-header-content' )
				.empty()
				.append( $( output.rendered ) );
		},

		/**
		 * Called when submitFunction failed.
		 *
		 * @param {jQuery.Deferred} deferred
		 * @param {object} data Old (invalid) this.prepareResult return value
		 * @param {string} error
		 * @param {object} errorData
		 */
		conflict: function ( deferred, data, error, errorData ) {
			if (
				error === 'block-errors' &&
				errorData.header && errorData.header.prev_revision &&
				errorData.header.prev_revision.extra && errorData.header.prev_revision.extra.revision_id
			) {
				var $textarea = this.header.$container.find( 'textarea' );

				/*
				 * Overwrite data revision & content.
				 * We'll use raw editor content & editor format to avoid having
				 * to parse it.
				 */
				data.content = mw.flow.editor.getRawContent( $textarea );
				data.format = mw.flow.editor.getFormat( $textarea );
				data.revision = errorData.header.prev_revision.extra.revision_id;

				/*
				 * At this point, we're still in the deferred's reject callbacks.
				 * Only after these are completed, is the spinner removed and the
				 * error message added.
				 * I'm adding another fail-callback, which will be executed after
				 * the fail has been handled. Only then, we can properly clean up.
				 */
				deferred.fail( function( data, error, errorData ) {
					/*
					 * Tipsy will be positioned at the element where it's bound
					 * to, at the time it's asked to show. It won't reposition
					 * if the element moves. Since we re-launch the form, there
					 * may be some movement, so let's have this as callback when
					 * the form has completed loading before doing these changes.
					 */
					var formLoaded = function () {
						var $button = this.header.$container.find( '.flow-edit-header-submit' );
						$button.val( mw.msg( 'flow-edit-header-submit-overwrite' ) );
						this.tipsy( $button, errorData.header.prev_revision.message );

						/*
						 * Trigger keyup in editor, to trick setupEmptyDisabler
						 * into believing we've made a change & enable submit.
						 */
						this.header.$container.find( 'textarea' ).keyup();
					}.bind( this, data, error, errorData );

					// kill form & error message & re-launch edit form
					this.header.$container.find( 'form, flow-error' ).remove();
					this.setupEditForm( data, formLoaded );
				}.bind( this, data, error, errorData ) );
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
				.click( function () {
					$( this ).tipsy( 'hide' );
				} )
				.tipsy( {
					fade: true,
					gravity: 'w',
					html: true,
					trigger: 'manual',
					className: 'flow-tipsy-destructive',
					title: function () {
						/*
						 * I'd prefer to only return content here, instead of wrapping
						 * it in a div. But we need to add some padding inside the tipsy.
						 * Tipsy has an option "className", which we could use to target
						 * the element though CSS, but that className is only applied
						 * _after_ tipsy has calculated position, so it's positioning
						 * would then be incorrect.
						 * Tossing in the content inside another div (which does have a
						 * class to target) works around this problem.
						 *
						 * @see https://gerrit.wikimedia.org/r/#/c/103531/
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
