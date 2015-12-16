( function ( $ ) {
	/**
	 * This implements the UI portion of the CAPTCHA.
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config]
	 */
	mw.flow.ui.CaptchaWidget = function mwFlowUiCaptchaWidget( model, config ) {
		mw.flow.ui.CaptchaWidget.parent.call( this, config );

		this.toggle( false );

		this.model = model;
		this.model.connect( this, {
			update: 'onUpdate'
		} );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.CaptchaWidget, OO.ui.LabelWidget );

	/* Methods */

	/**
	 * Gets the CAPTCHA information, if any.
	 *
	 * @return {Object|null} captcha CAPTCHA information
	 * @return {string} captcha.id CAPTCHA ID
	 * @return {string} captcha.answer CAPTCHA answer (user-provided)
	 */
	mw.flow.ui.CaptchaWidget.prototype.getResponse = function () {
		var $captchaField = this.$element.find( '[name="wpCaptchaWord"]' ),
			captcha = null;

		if ( $captchaField.length > 0 ) {
			captcha = {
				id: this.$element.find( '[name="wpCaptchaId"]' ).val(),
				answer: $captchaField.val()
			};
		}

		return captcha;
	};

	/**
	 * Updates the widget in response to event
	 *
	 * @param {boolean} isRequired Whether a CAPTCHA is required
	 * @param {OO.ui.HtmlSnippet|null} content HTML to show for CAPTCHA, or null
	 */
	mw.flow.ui.CaptchaWidget.prototype.onUpdate = function ( isRequired, content ) {
		if ( isRequired ) {
			this.setLabel( content );
			this.toggle( true );
		} else {
			this.toggle( false );
			this.setLabel( '' );
		}
	};
}( jQuery ) );
