( function ( $, mw ) {
	'use strict';

	/**
	 * Common actions code.
	 */
	mw.flow.action = function () {};

	/**
	 * Returns the initial content, to be served to setupEditForm/loadReplyForm.
	 *
	 * @param {object} [data] this.prepareResult return value (or null)
	 * @return {object}
	 */
	mw.flow.action.prototype.initialContent = function ( data ) {
		// let's make sure data exists, before trying to look up values
		data = data || {};

		return {
			content: data.content || '',
			format: data.format || 'wikitext'
		};
	};

	/**
	 * HTML of the edit form (well, jQuery node with the html of the edit form)
	 *
	 * @returns {jQuery}
	 */
	mw.flow.action.prototype.editForm = function () {
		return $(
			'<form class="flow-edit-form">' +
				'<textarea class="mw-ui-input flow-edit-content"></textarea>' +
				'<div class="flow-form-controls flow-edit-controls">' +
					'<a href="#" class="flow-cancel-link mw-ui-button mw-ui-quiet">' +
						mw.msg( 'flow-cancel' ) +
					'</a>' +
					'<input type="submit" class="mw-ui-button mw-ui-constructive flow-edit-submit" value="' + mw.msg( 'flow-edit-' + this.object.type + '-submit' ) + '">' +
				'</div>' +
			'</form>'
		);
	};

	/**
	 * Builds the edit form, using flow( setupEditForm ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.prototype.setupEditForm = function ( data, loadFunction ) {
		if ( this.object.$container.hasClass( 'flow-edit-form-active' ) ) {
			return;
		}

		// build form DOM & attach to content
		var $postForm = this.editForm();
		$postForm.appendTo( this.object.$container );

		// add class to identify this form as being active
		this.object.$container.addClass( 'flow-edit-form-active' );

		// bind click on cancel, which should destroy this form
		$postForm.find( '.flow-cancel-link' ).on( 'click.mw-flow-discussion', $.proxy( function ( event ) {
			event.preventDefault();
			$postForm.slideUp( 'fast', $.proxy( this.destroyEditForm, this ) );
		}, this ) );

		// setup preview
		$postForm.flow( 'setupPreview' );

		// setup submission callback
		$postForm.flow( 'setupFormHandler',
			'.flow-edit-submit',
			$.proxy( this.submitFunction, this, data ),
			$.proxy( this.loadParametersCallback, this ),
			$.proxy( this.validateCallback, this ),
			$.proxy( this.promiseCallback, this )
		);

		// setup disabler (disables submit button until content is entered)
		$postForm.flow( 'setupEmptyDisabler',
			['.flow-edit-content'],
			'.flow-edit-submit'
		);

		/*
		 * Setting focus inside an event that grants focus (like
		 * clicking the edit icon), is tricky. This is a workaround.
		 */

		setTimeout( $.proxy( function( $postForm, data, loadFunction ) {
			var $textarea = $postForm.find( 'textarea' ),
				initialContent = this.initialContent( data );

			mw.flow.editor
				.load( $textarea, initialContent.content, initialContent.format )
				.done(
					// Run the callback function
					loadFunction,
					// Then scroll to the form (after callback to make sure we have the right height)
					function () {
						$postForm.conditionalScrollIntoView().queue( function () {
							mw.flow.editor.focus( $textarea );
							mw.flow.editor.moveCursorToEnd( $textarea );
							$( this ).dequeue();
						} );
					}
				);
		}, this, $postForm, data, loadFunction ), 0 );
	};

	/**
	 * Removes the edit form & restores content.
	 */
	mw.flow.action.prototype.destroyEditForm = function () {
		this.object.$container.removeClass( 'flow-edit-form-active' );
		this.object.$container.find( '.flow-edit-form' )
			.flow( 'hidePreview' )
			.remove();
	};

	/**
	 * Feeds parameters to flow( 'setupFormHandler' ).
	 *
	 * @return {array} Array with params, to be fed to validateCallback &
	 * submitFunction
	 */
	mw.flow.action.prototype.loadParametersCallback = function () {
		var $textarea = this.object.$container.find( '.flow-edit-content' ),
			content = mw.flow.editor.getContent( $textarea );

		return [ content ];
	};

	/**
	 * Validates parameters for flow( 'setupFormHandler' ).
	 * Parameters are supplied by this.loadParametersCallback.
	 *
	 * @param {string} content
	 * @return {bool}
	 */
	mw.flow.action.prototype.validateCallback = function ( content ) {
		return !!content;
	};

	/**
	 * @param {jQuery.Deferred} deferred
	 */
	mw.flow.action.prototype.promiseCallback = function ( deferred ) {
		deferred.done( $.proxy( this.destroyEditForm, this ) );
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.prototype.showError = function ( error, errorData ) {
		$( this.object.$container ).flow( 'showError', arguments );
	};

	/**
	 * Adds tipsy to an element, with the given text.
	 *
	 * @param {jQuery} $element
	 * @param {string} text
	 */
	mw.flow.action.prototype.tipsy = function ( $element, text ) {
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
	};
} ( jQuery, mediaWiki ) );
