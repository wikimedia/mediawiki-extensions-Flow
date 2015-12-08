( function ( $ ) {
	/**
	 * Flow Content class
	 *
	 * @class
	 *
	 * @constructor
	 * @param {object} representations; Object where key=format name and value=string representation
	 * For instance: { wikitext: "Hello ''world''", html: "Hello <i>world</i>", plaintext: "Hello world" }
	 */
	mw.flow.dm.Content = function mwFlowContent( representations ) {
		// Mixin constructor
		OO.EventEmitter.call( this );

		// Initialize properties
		this.set( representations );
	};

	/* Inheritance */

	OO.mixinClass( mw.flow.dm.Content, OO.EventEmitter );

	/* Events */

	/**
	 * Change of content
	 *
	 * @event contentChange
	 * @param {mw.flow.dm.Content} content
	 */

	/* Methods */

	/**
	 * Get content representation for the specified format or the default format if none is specified.
	 *
	 * @param {string} format; can be wikitext, html, fixed-html, topic-title-wikitext, topic-title-html, plaintext
	 * @return {string} Content
	 */
	mw.flow.dm.Content.prototype.get = function ( format ) {
		if ( !this.contentRepresentations ) {
			return null;
		}

		format = format || this.contentRepresentations.format;

		if ( this.contentRepresentations.hasOwnProperty( format ) ) {
			return this.contentRepresentations[ format ];
		}
		return null;
	};

	/**
	 * Set content representations
	 *
	 * @param {object} contentRepresentations
	 * @fires contentChange
	 */
	mw.flow.dm.Content.prototype.set = function ( contentRepresentations ) {
		if ( contentRepresentations ) {
			this.contentRepresentations = contentRepresentations;
			this.contentRepresentations[contentRepresentations.format] = contentRepresentations.content;
		} else {
			this.contentRepresentations = {};
		}
		this.emit( 'contentChange', this );
	};
}( jQuery ) );
