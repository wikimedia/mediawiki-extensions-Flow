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
	 * HTML of the cancel link (well, jQuery node with the html of the link)
	 *
	 * @returns {jQuery}
	 */
	mw.flow.action.prototype.cancelLink = function () {
		return $(
			'<a href="#" class="flow-cancel-link mw-ui-button mw-ui-quiet">' +
				mw.msg( 'flow-cancel' ) +
			'</a>' +
			// add some whitespace to separate from what's next ;)
			' '
		);
	};

	/**
	 * HTML of the edit form (well, jQuery node with the html of the edit form)
	 *
	 * @returns {jQuery}
	 */
	mw.flow.action.prototype.editForm = function () {
		var $form = $(
			'<form class="flow-edit-form">' +
				'<textarea class="mw-ui-input flow-edit-content"></textarea>' +
				'<div class="flow-form-controls flow-edit-controls">' +
					// cancel link will be added here
					'<input type="submit" class="mw-ui-button mw-ui-constructive flow-edit-submit" value="' + mw.msg( 'flow-edit-' + this.object.type + '-submit' ) + '">' +
				'</div>' +
			'</form>'
		);

		this.cancelLink().prependTo( $form.find( '.flow-form-controls' ) );

		return $form;
	};

	/**
	 * Creates an edit form.
	 *
	 * @param {object} data this.prepareResult return value
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.prototype.setupEditForm = function ( data, loadFunction ) {
		if ( this.object.$container.hasClass( 'flow-edit-form-active' ) ) {
			return;
		}

		// build form DOM & attach to content
		var $form = this.editForm();
		$form.appendTo( this.object.$container );

		// add class to identify this form as being active
		this.object.$container.addClass( 'flow-edit-form-active' );

		// bind click on cancel, which should destroy this form
		$form.find( '.flow-cancel-link' ).on( 'click.mw-flow-discussion', $.proxy( function ( event ) {
			event.preventDefault();
			$form.slideUp( 'fast', $.proxy( this.destroyEditForm, this ) );
		}, this ) );

		// setup preview
		$form.flow( 'setupPreview' );

		// setup submission callback
		$form.flow( 'setupFormHandler',
			'.flow-edit-submit',
			$.proxy( this.submitFunction, this, data ),
			$.proxy( this.loadParametersCallback, this ),
			$.proxy( this.validateCallback, this ),
			$.proxy( function ( deferred ) {
				deferred.done( $.proxy( this.destroyEditForm, this ) );
			}, this )
		);

		// setup disabler (disables submit button until content is entered)
		$form.flow( 'setupEmptyDisabler',
			['.flow-edit-content'],
			'.flow-edit-submit'
		);

		/*
		 * Setting focus inside an event that grants focus (like
		 * clicking the edit icon), is tricky. This is a workaround.
		 */
		setTimeout( $.proxy( function( $form, data, loadFunction ) {
			var $textarea = $form.find( 'textarea' ),
				initialContent = this.initialContent( data );

			mw.flow.editor
				.load( $textarea, initialContent.content, initialContent.format )
				.done(
					// Run the callback function
					loadFunction,
					// Then scroll to the form (after callback to make sure we have the right height)
					function () {
						$form.conditionalScrollIntoView().queue( function () {
							mw.flow.editor.focus( $textarea );
							mw.flow.editor.moveCursorToEnd( $textarea );
							$( this ).dequeue();
						} );
					}
				);
		}, this, $form, data, loadFunction ), 0 );
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
	 * Creates a reply form.
	 *
	 * Unlike edit forms, reply forms already exit in HTML, we just have
	 * to "activate" some JS magic.
	 *
	 * @param {function} [loadFunction] callback to be executed when form is loaded
	 */
	mw.flow.action.prototype.loadReplyForm = function ( loadFunction ) {
		if ( this.object.$container.hasClass( 'flow-reply-form-active' ) ) {
			return;
		}

		var $form = this.$form.find( 'form' );

		// add class to identify this form as being active
		this.object.$container.addClass( 'flow-reply-form-active' );
		this.$form.addClass( 'flow-reply-form-active' );

		// hide topic reply form
		// can't do this in CSS, since selectors traveling upwards don't really exist ;)
		this.object.$container.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).hide();
		// make sure to show this form, though
		this.$form.show();

		// add cancel link
		this.cancelLink()
			.on( 'click.mw-flow-discussion', $.proxy( function ( event ) {
				event.preventDefault();
				$form.slideUp( 'fast', $.proxy( this.destroyReplyForm, this ) );
			}, this ) )
			.prependTo( $form.find( '.flow-form-controls' ) );

		// setup preview
		$form.flow( 'setupPreview' );

		// setup submission callback
		$form.flow( 'setupFormHandler',
			'.flow-reply-submit',
			$.proxy( this.submitFunction, this ),
			$.proxy( this.loadParametersCallback, this, $form ),
			$.proxy( this.validateCallback, this ),
			$.proxy( function ( deferred ) {
				deferred.done( $.proxy( this.destroyReplyForm, this ) );
			}, this )
		);

		// setup disabler (disables submit button until content is entered)
		$form.flow( 'setupEmptyDisabler',
			['.flow-reply-content'],
			'.flow-reply-submit'
		);

		/*
		 * Setting focus inside an event that grants focus (like
		 * clicking the reply button), is tricky. This is a workaround.
		 */
		setTimeout( $.proxy( function( $form, loadFunction ) {
			var $textarea = $form.find( 'textarea' ),
				initialContent = this.initialContent();

			$textarea
				.removeClass( 'flow-reply-box-closed' )
				// Override textarea height; doesn't need to be too large initially,
				// it'll auto-expand
				.attr( 'rows', '6' )
				// Textarea will auto-expand + textarea padding will cause the
				// resize grabber to be positioned badly (in FF) so get rid of it
				.css( 'resize', 'none' );

			mw.flow.editor
				.load( $textarea, initialContent.content, initialContent.format )
				.done(
					// Run the callback function
					loadFunction,
					// Then scroll to the form (after callback to make sure we have the right height)
					function () {
						$form.conditionalScrollIntoView().queue( function () {
							mw.flow.editor.focus( $textarea );
							mw.flow.editor.moveCursorToEnd( $textarea );
							$( this ).dequeue();
						} );
					}
				);
		}, this, $form, loadFunction ), 0 );
	};

	/**
	 * Hides the reply form & restores content.
	 */
	mw.flow.action.prototype.destroyReplyForm = function () {
		var $form = this.$form.find( 'form' ),
			$textarea = $form.find( 'textarea' );

		this.object.$container.removeClass( 'flow-reply-form-active' );
		this.$form.removeClass( 'flow-reply-form-active' );

		mw.flow.editor.destroy( $textarea );
		$form.flow( 'hidePreview' );

		// when closed, display topic reply form again
		// can't do this in CSS, since selectors traveling upwards don't really exist ;)
		this.object.$container.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).show();

		/*
		 * Because we're not entirely killing the forms, we have to clean up
		 * after cancelling a form (e.g. reply-forms may be re-used across posts
		 * - when max threading depth has been reached)
		 *
		 * After submitting the reply, kill the events that were bound to the
		 * submit button via setupEmptyDisabler and setupFormHandler.
		 */
		$form.find( '.flow-reply-submit' ).off();
		$form.find( '.flow-cancel-link, .flow-content-preview, .flow-preview-submit, .flow-error' ).remove();

		// when hidden via slideUp, some inline CSS is added to keep the
		// elements hidden, but we want it in it's original state (where CSS
		// scoping :not(.flow-reply-form-active) will make the form stay hidden)
		$form.css( 'display', '' );
	};

	/**
	 * Submit function for flow( 'setupFormHandler' ).
	 *
	 * @param {object} data this.prepareResult return value
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.prototype.submitFunction = function ( data ) {
		// base action.js has no default submitFunction - will need custom implementation
		throw 'submitFunction not yet implemented';
	};

	/**
	 * Feeds parameters to flow( 'setupFormHandler' ).
	 *
	 * @param {jQuery} $form
	 * @return {array} Array with params, to be fed to validateCallback &
	 * submitFunction
	 */
	mw.flow.action.prototype.loadParametersCallback = function ( $form ) {
		var $textarea = $form.find( 'textarea' ),
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
