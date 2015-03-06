( function ( $, mw ) {
	'use strict';

	mw.flow = mw.flow || {}; // create mw.flow globally
	mw.flow.parsoid = {
		/**
		 * @param {string} from Input format: html|wikitext
		 * @param {string} to Desired output format: html|wikitext
		 * @param {string} content Content to convert
		 * @param {string} [title] Page title
		 * @return {jQuery.Promise} Will resolve with converted content as data
		 */
		convert: function ( from, to, content, title ) {
			var deferred = $.Deferred(),
				api = new mw.Api();

			if ( content === '' ) {
				return deferred.resolve( content );
			}

			if ( from === to ) {
				return deferred.resolve( content );
			}

			if ( !title ) {
				title = mw.config.get( 'wgPageName' );
			}

			api.post( {
				action: 'flow-parsoid-utils',
				from: from,
				to: to,
				content: content,
				title: title
			} )
			.done( function ( data ) {
				deferred.resolve( data['flow-parsoid-utils'].content );
			} )
			.fail( function () {
				deferred.reject();
			} );

			return deferred.promise();
		}
	};
} ( jQuery, mediaWiki ) );
