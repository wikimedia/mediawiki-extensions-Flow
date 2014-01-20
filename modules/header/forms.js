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

		// Overload "edit header" link.
		this.object.$container.find( '.flow-header-edit-link' ).click( this.edit.bind( this ) );
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
		if ( this.object.$container.find( '.flow-edit-header-form' ).length ) {
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
		var $editLink = this.object.$container.find( '.flow-header-edit-link' );

		// call parent setupEditForm function
		mw.flow.action.prototype.setupEditForm.call(
			this,
			data,
			loadFunction
		);

		// hide edit link and re-reveal it if the cancel link - which is
		// added by flow( 'setupEditForm' ) - is clicked.
		$editLink.hide();
		this.object.$container.find( '.flow-cancel-link' ).click( function () {
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
				workflowId: this.object.workflowId,
				page: this.object.pageName
			},
			content,
			data.revision
		);

		deferred.done( this.render.bind( this ) );
		// allow hijacking the fail-stack to gracefully recover from errors
		deferred = deferred.then( null, this.conflict.bind( this, deferred, data ) );

		return deferred;
	};

	/**
	 * Called when submitFunction is resolved.
	 *
	 * @param {object} output
	 */
	mw.flow.action.header.edit.prototype.render = function ( output ) {
		this.object.$container
			.find( '#flow-header-content' )
			.empty()
			.append( $( output.rendered ) );
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
	mw.flow.action.header.edit.prototype.conflict = function ( deferred, data, error, errorData ) {
		if (
			error === 'block-errors' &&
			errorData.header && errorData.header.prev_revision &&
			errorData.header.prev_revision.extra && errorData.header.prev_revision.extra.revision_id
		) {
			var $textarea = this.object.$container.find( 'textarea' );

			/*
			 * Overwrite data revision & content.
			 * We'll use raw editor content & editor format to avoid having
			 * to parse it.
			 */
			data.content = mw.flow.editor.getRawContent( $textarea );
			data.format = mw.flow.editor.getFormat( $textarea );
			data.revision = errorData.header.prev_revision.extra.revision_id;

			/*
			 * Tipsy will be positioned at the element where it's bound
			 * to, at the time it's asked to show. It won't reposition
			 * if the element moves. Since we re-launch the form, there
			 * may be some movement, so let's have this as callback when
			 * the form has completed loading before doing these changes.
			 */
			var formLoaded = function () {
				var $button = this.object.$container.find( '.flow-edit-header-submit' );
				$button.val( mw.msg( 'flow-edit-header-submit-overwrite' ) );
				this.tipsy( $button, errorData.header.prev_revision.message );

				/*
				 * Trigger keyup in editor, to trick setupEmptyDisabler
				 * into believing we've made a change & enable submit.
				 */
				this.object.$container.find( 'textarea' ).keyup();
			}.bind( this, data, error, errorData );

			// kill form & error message & re-launch edit form
			this.destroyEditForm();
			this.setupEditForm( data, formLoaded );

			/*
			 * Promise a new deferred, so that follow-up fail (or done)
			 * callbacks are never executed. We don't want getFormHandler's
			 * default callbacks to be executed: succeeding will replace new
			 * content, failing displays an error message. Ee don't want either.
			 */
			deferred = ( new $.Deferred() ).promise();
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
		this.object.$container.flow( 'showError', arguments );
	};

	$( document ).flow( 'registerInitFunction', function () {
		new mw.flow.header();
	} );
} ( jQuery, mediaWiki ) );
