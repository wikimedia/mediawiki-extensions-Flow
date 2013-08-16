( function( $, mw ) {
mw.flow.parsoid = {
	/**
	 * @param string from Input format: html|wikitext
	 * @param string to Desired output format: html|wikitext
	 * @param string content Content to convert
	 * @return string
	 */
	convert: function( from, to, content ) {
		if ( from === to ) {
			return content;
		} else if ( from === 'wikitext' && to === 'html' ) {
			return mw.flow.parsoid.toHtml( content );
		} else if ( from === 'html' && to === 'wikitext' ) {
			return mw.flow.parsoid.toWikitext( content );
		} else {
			// @todo: proper error handling
			alert( 'Unknown conversion pair: '+ from +' -> '+ to +'.' );
			return '';
		}
	},

	/**
	 * @param string wikitext Wikitext to convert
	 * @return string
	 */
	toHtml: function( wikitext ) {
		var html = '',
			api = new mw.Api( { ajax: { async: false } } );

		api.post( {
			action: 'visualeditor',
			page: mw.config.get( 'wgPageName' ),
			// basetimestamp: ?,
			// starttimestamp: ?,
			paction: 'parsefragment',
			wikitext: wikitext
		} )
		.done( function( data ) {
			html = data.visualeditor.content;
		} )
		.fail( function( code, data ) {
			// @todo: proper error handling
			alert( data.error.info || 'Failed to convert wikitext to HTML.' );
		} );

		return html;
	},

	/**
	 * @param string html HTML to convert
	 * @return string
	 */
	toWikitext: function( html ) {
		var wikitext = '',
			api = new mw.Api( { ajax: { async: false } } );

		api.post( {
			action: 'visualeditor',
			page: mw.config.get( 'wgPageName' ),
			// basetimestamp: ?,
			// starttimestamp: ?,
			paction: 'serialize',
			html: html
		} )
		.done( function( data ) {
			wikitext = data.visualeditor.content;
		} )
		.fail( function( code, data ) {
			// @todo: proper error handling
			alert( data.error.info || 'Failed to convert HTML to wikitext.' );
		} );

		return wikitext;
	}
};
} )( jQuery, mediaWiki );
