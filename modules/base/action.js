<<<<<<< HEAD   (89cff0 Remove debugging die statement)
=======
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
			// content is always pre-escaped, but we need to undo that
			// for editing to work correctly.
			content: $( '<textarea>' ).html( data.content || '' ).val(),
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
	 * Get the terms of use for this for edit form
	 *
	 * @return {string}
	 */
	mw.flow.action.prototype.getTerms = function() {
		return mw.config.get( 'wgFlowTermsOfUseEdit' );
	};

	/**
	 * Get the action name of the current action
	 *
	 * @return {string}
	 */
	mw.flow.action.prototype.getAction = function() {
		throw 'mw.flow.action concrete subclasses must implement getAction()!';
	};

	/**
	 * Get the form placeholder text, default is none
	 *
	 * @return {string}
	 */
	mw.flow.action.prototype.getEditFormPlaceHolder = function() {
		return '';
	};

	/**
	 * Get additional div class for the container previewing this content,
	 * for example, topic summary has a different display, default is none
	 *
	 * @return {string}
	 */
	mw.flow.action.prototype.getPreviewCSS = function() {
		return '';
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
					'<div class="flow-terms-of-use plainlinks">' +
					this.getTerms() +
					'</div>' +
					// cancel link will be added here
					'<input type="submit" class="mw-ui-button mw-ui-constructive flow-edit-submit" value="' + mw.msg( 'flow-' + this.getAction() + '-' + this.object.type + '-submit' ) + '">' +
				'</div>' +
			'</form>'
		), placeholder = this.getEditFormPlaceHolder();

		if ( placeholder ) {
			$( '.flow-edit-content', $form ).attr( 'placeholder', placeholder );
		}

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
		if ( this.$container.hasClass( 'flow-edit-form-active' ) ) {
			return;
		}

		// build form DOM & attach to content
		var $form = this.$form = this.editForm();

		$form.appendTo( this.$container );

		// add class to identify this form as being active
		this.$container.addClass( 'flow-edit-form-active' );

		// bind click on cancel, which should destroy this form
		$form.find( '.flow-cancel-link' ).on( 'click.mw-flow-discussion', $.proxy( function ( $form, event ) {
			event.preventDefault();
			$form.slideUp( 'fast', $.proxy( this.destroyEditForm, this ) );
		}, this, $form ) );

		// setup preview
		$form.flow( 'setupPreview', null, this.getPreviewCSS() );

		// setup submission callback
		$form.flow( 'setupFormHandler',
			'.flow-edit-submit',
			$.proxy( this.submitFunction, this, data ),
			$.proxy( this.loadParametersCallback, this, $form ),
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

		if ( mw.user.isAnon() ) {
			this.showAnonWarning( $form.find( '.flow-edit-content' ), 'w' );
		}

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
		this.$container.removeClass( 'flow-edit-form-active' );

		// remove any tipsies opened from within the form
		this.$container.find( '.flow-edit-form .flow-tipsy-trigger' ).each( function () {
			$( this ).removeClass( 'flow-tipsy-trigger' );
			if ( $( this ).data( 'tipsy' ) ) {
				$( this ).tipsy( 'hide' );
			}
		} );

		this.$container.find( '.flow-edit-form' )
			.flow( 'hidePreview' )
			.trigger( 'flow-form-destroyed' )
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
		if ( this.$container.hasClass( 'flow-reply-form-active' ) ) {
			return;
		}

		var $form = this.$form.find( 'form' );

		// add class to identify this form as being active
		this.$container.addClass( 'flow-reply-form-active' );
		this.$form.addClass( 'flow-reply-form-active' );

		// hide topic reply form
		// can't do this in CSS, since selectors traveling upwards don't really exist ;)
		this.$container.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).hide();
		// make sure to show this form, though
		this.$form.show();

		// add cancel link
		this.cancelLink()
			.on( 'click.mw-flow-discussion', $.proxy( function ( $form, event ) {
				event.preventDefault();
				$form.slideUp( 'fast', $.proxy( this.destroyReplyForm, this ) );
			}, this, $form ) )
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

		// add anon warning if required
		if ( mw.user.isAnon() ) {
			this.showAnonWarning( $form.find( '.flow-creator > a' ) );
		}
	};

	/**
	 * Hides the reply form & restores content.
	 */
	mw.flow.action.prototype.destroyReplyForm = function () {
		var $form = this.$form.find( 'form' ),
			$textarea = $form.find( 'textarea' );

		this.$container.removeClass( 'flow-reply-form-active' );
		this.$form.removeClass( 'flow-reply-form-active' );

		mw.flow.editor.destroy( $textarea );
		$form.flow( 'hidePreview' );

		// when closed, display topic reply form again
		// can't do this in CSS, since selectors traveling upwards don't really exist ;)
		this.$container.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).show();

		/*
		 * Because we're not entirely killing the forms, we have to clean up
		 * after cancelling a form (e.g. reply-forms may be re-used across posts
		 * - when max threading depth has been reached)
		 *
		 * After submitting the reply, kill the events that were bound to the
		 * submit button via setupEmptyDisabler and setupFormHandler.
		 */
		$form.find( '.flow-reply-submit' ).off();
		$form.trigger( 'flow-form-destroyed' );
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
	 * When an edit conflict occurs, we want to display an error message, and
	 * give the user the opportunity to overwrite the other content.
	 * In order to do so, we have to kill & reload the form with the new data,
	 * after which we can then make changes to the elements (add error msg,
	 * change button text)
	 *
	 * This function returns a new (un-resolved/rejected) promise, which can be
	 * fed to the original deferred's fail() callbacks, so they aren't called.
	 *
	 * @param {object} data Initialisation data for setupEditForm
	 * @param {string} buttonText Text to display on submit button
	 * @param {string} tipsyText Text to display on tipsy flyout
	 * @return {jQuery.Deferred}
	 */
	mw.flow.action.prototype.conflict = function ( data, buttonText, tipsyText ) {
		/*
		 * Tipsy will be positioned at the element where it's bound
		 * to, at the time it's asked to show. It won't reposition
		 * if the element moves. Since we re-launch the form, there
		 * may be some movement, so let's have this as callback when
		 * the form has completed loading before doing these changes.
		 */
		var formLoaded = $.proxy( function ( buttonText, tipsyText ) {
			var $button = this.$container.find( '.flow-edit-submit' ),
				$tipsy;
			$button.val( buttonText );
			$tipsy = this.tipsy( $button, tipsyText );
			$tipsy.on( 'click.mw-flow-discussion', function () {
				$button.tipsy( 'hide' );
			} );

			/*
			 * Trigger keyup in editor, to trick setupEmptyDisabler
			 * into believing we've made a change & enable submit.
			 */
			this.$container.find( '.flow-edit-content' ).keyup();
		}, this, buttonText, tipsyText );

		// kill & re-launch edit form
		this.destroyEditForm();
		this.setupEditForm( data, formLoaded );

		/*
		 * Promise a new deferred, so that follow-up fail (or done)
		 * callbacks are never executed. We don't want getFormHandler's
		 * default callbacks to be executed: succeeding will replace new
		 * content, failing displays an error message. Ee don't want either.
		 */
		return ( new $.Deferred() ).promise();
	};

	/**
	 * Display an error if something when wrong.
	 *
	 * @param {string} error
	 * @param {object} errorData
	 */
	mw.flow.action.prototype.showError = function ( error, errorData ) {
		$( this.$container ).flow( 'showError', arguments );
	};

	/**
	 * Adds tipsy to an element, with the given text.
	 *
	 * @param {jQuery} $element
	 * @param {string|jQuery} text or jQuery object containing content
	 * @param {Object} options Overrides
	 */
	mw.flow.action.prototype.tipsy = function ( $element, text, options ) {
		var $form = this.$form;

		$element
			.addClass( 'flow-tipsy-trigger' )
			.tipsy( $.extend( {
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
					var $warning = $( '<div class="flow-tipsy-noflyout">' );
					if ( typeof text === 'object' ) {
						$warning.append( text );
					} else {
						$warning.text( text );
					}
					return $( '<div>' ).append( $warning ).html();
				}
			}, options ) )
			.tipsy( 'show' );

		setTimeout( function() {
			$form.on( 'keyup', 'input, textarea', function(e) {
				$element.tipsy( 'hide' );
			} );
		}, 1500 );

		// return tipsy flyout node
		return $element.tipsy( 'tip' );
	};

	/**
	 * Shows a tooltip, dismissed by keyup in the form.
	 * @param {jQuery} $el The element to display the warning on.
	 */
	mw.flow.action.prototype.showAnonWarning = function( $el, gravity ) {
		var msg,
			loginLink,
			registerLink,
			returnTo = mw.config.get( 'wgPageName' ),
			jumpElement,
			$form = this.$form;

		gravity = gravity || 'w';

		if ( !( $el && $el.length ) ) {
			$el = $form.find( '.flow-creator > a' );
		}

		if ( ! $el.length ) {
			$el = $form.find( 'textarea:first' );
		}

		if ( this.object && this.object.postId ) {
			returnTo += '#flow-post-' + this.object.postId;
		} else {
			jumpElement = $form.closest( '[id]' );

			if ( jumpElement.length && jumpElement.attr( 'id' ) !== 'mw-content-text' ) {
				returnTo += '#' + jumpElement.attr( 'id' );
			}
		}

		loginLink = mw.util.getUrl( 'Special:UserLogin', { 'returnto' : returnTo } );
		registerLink = mw.util.getUrl( 'Special:UserLogin/signup', { 'returnto' : returnTo } );

		msg = mw.message( 'flow-anon-warning', loginLink, registerLink ).parse();

		this.tipsy(
			$el,
			$( '<div/>' ).html( msg ).contents(),
			{
				'className' : 'flow-tipsy-warning flow-anon-warning',
				'gravity' : gravity
			}
		);

		$form
			.on( 'flow-form-destroyed', '*', function(e) {
				$el.tipsy( 'hide' );
			} );
	};
} ( jQuery, mediaWiki ) );
>>>>>>> BRANCH (6acf37 Merge "Prevent double escape of content to be edited")
