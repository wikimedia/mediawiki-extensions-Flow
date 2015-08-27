( function ( $ ) {
	/**
	 * This is a base class for widgets that display creatable/editable content (and integrate
	 * with editors for creating/editing that content).
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config]
	 */
	mw.flow.ui.ContentWidget = function mwFlowUiContentWidget( config ) {
		mw.flow.ui.ContentWidget.parent.call( this, config );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.ContentWidget, OO.ui.Widget );

	/* Methods */

	/**
	 * Gets the CAPTCHA information from the error field, if any.
	 *
	 * @return {Object|null} captcha CAPTCHA information
	 * @return {string} captcha.id CAPTCHA ID
	 * @return {string} captcha.answer CAPTCHA answer (user-provided)
	 */
	mw.flow.ui.ContentWidget.prototype.getCaptcha = function () {
		var $captchaField = this.error.$label.find( '[name="wpCaptchaWord"]' ),
			captcha = null;

		if ( $captchaField.length > 0 ) {
			captcha = {
				id: this.error.$label.find( '[name="wpCaptchaId"]' ).val(),
				answer: $captchaField.val()
			};
		}

		return captcha;
	};

	/**
	 * Handles failures to save content
	 *
	 * @param {string} errorCode
	 * @param {Object} errorObj Further details, such as errorObj.error, errorObj.info, or
	 *   errorObj.exception
	 */
	mw.flow.ui.ContentWidget.prototype.onSaveContentFailure = function ( errorCode, errorObj ) {
		if ( /spamfilter$/.test( errorCode ) && errorObj.error.spamfilter === 'flow-spam-confirmedit-form' ) {
			this.error.setLabel(
				// CAPTCHA form
				new OO.ui.HtmlSnippet( errorObj.error.info )
			);
		} else {
			this.error.setLabel( errorObj.error && errorObj.error.info || errorObj.exception );
		}

		this.error.toggle( true );
	};
}( jQuery ) );
