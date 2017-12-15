/*
	EXTRACTED AND ADAPTED FROM mediawiki/resources/src/mediawiki/mediawiki.js

	Issues:
		* mw.msg ( and the Message "class") is not isolated but part of mediawiki.js
		* It is dependent on jquery
		* It cannot be loaded on the server even when providing jquery
		  because it need a document object

 */

var slice = Array.prototype.slice;
var hasOwn = Object.prototype.hasOwnProperty;

/**
 * Create an object that can be read from or written to via methods that allow
 * interaction both with single and multiple properties at once.
 *
 * @private
 * @class mw.Map
 *
 * @constructor
 */
function Map() {
	this.values = {};
}

Map.prototype = {
	constructor: Map,

	/**
	 * Get the value of one or more keys.
	 *
	 * If called with no arguments, all values are returned.
	 *
	 * @param {string|Array} [selection] Key or array of keys to retrieve values for.
	 * @param {Mixed} [fallback=null] Value for keys that don't exist.
	 * @return {Mixed|Object|null} If selection was a string, returns the value,
	 *  If selection was an array, returns an object of key/values.
	 *  If no selection is passed, a new object with all key/values is returned.
	 */
	get: function ( selection, fallback ) {
		var results, i;
		fallback = arguments.length > 1 ? fallback : null;

		if ( Array.isArray( selection ) ) {
			results = {};
			for ( i = 0; i < selection.length; i++ ) {
				if ( typeof selection[ i ] === 'string' ) {
					results[ selection[ i ] ] = hasOwn.call( this.values, selection[ i ] ) ?
						this.values[ selection[ i ] ] :
						fallback;
				}
			}
			return results;
		}

		if ( typeof selection === 'string' ) {
			return hasOwn.call( this.values, selection ) ?
				this.values[ selection ] :
				fallback;
		}

		if ( selection === undefined ) {
			results = {};
			for ( i in this.values ) {
				results[ i ] = this.values[ i ];
			}
			return results;
		}

		// Invalid selection key
		return fallback;
	},

	/**
	 * Set one or more key/value pairs.
	 *
	 * @param {string|Object} selection Key to set value for, or object mapping keys to values
	 * @param {Mixed} [value] Value to set (optional, only in use when key is a string)
	 * @return {boolean} True on success, false on failure
	 */
	set: function ( selection, value ) {
		var s;

		if ( Object.prototype.toString.call( selection ) === '[object Object]' ) {
			for ( s in selection ) {
				this.values[ s ] = selection[ s ];
			}
			return true;
		}
		if ( typeof selection === 'string' && arguments.length > 1 ) {
			this.values[ selection ] = value;
			return true;
		}
		return false;
	},

	/**
	 * Check if one or more keys exist.
	 *
	 * @param {Mixed} selection Key or array of keys to check
	 * @return {boolean} True if the key(s) exist
	 */
	exists: function ( selection ) {
		var i;
		if ( Array.isArray( selection ) ) {
			for ( i = 0; i < selection.length; i++ ) {
				if ( typeof selection[ i ] !== 'string' || !hasOwn.call( this.values, selection[ i ] ) ) {
					return false;
				}
			}
			return true;
		}
		return typeof selection === 'string' && hasOwn.call( this.values, selection );
	}
};

var format = function ( formatString ) {
	var parameters = slice.call( arguments, 1 );
	return formatString.replace( /\$(\d+)/g, function ( str, match ) {
		var index = parseInt( match, 10 ) - 1;
		return parameters[ index ] !== undefined ? parameters[ index ] : '$' + match;
	} );
};

function escapeCallback( s ) {
	switch ( s ) {
		case '\'':
			return '&#039;';
		case '"':
			return '&quot;';
		case '<':
			return '&lt;';
		case '>':
			return '&gt;';
		case '&':
			return '&amp;';
	}
}

var escape = function ( s ) {
	return s.replace( /['"<>&]/g, escapeCallback );
};

function Message( map, key, parameters ) {
	this.format = 'text';
	this.map = map;
	this.key = key;
	this.parameters = parameters === undefined ? [] : slice.call( parameters );
	return this;
}

Message.prototype = {
	/**
	 * Get parsed contents of the message.
	 *
	 * The default parser does simple $N replacements and nothing else.
	 * This may be overridden to provide a more complex message parser.
	 * The primary override is in the mediawiki.jqueryMsg module.
	 *
	 * This function will not be called for nonexistent messages.
	 *
	 * @return {string} Parsed message
	 */
	parser: function () {
		return format.apply( null, [ this.map.get( this.key ) ].concat( this.parameters ) );
	},

	// eslint-disable-next-line valid-jsdoc
	/**
	 * Add (does not replace) parameters for `$N` placeholder values.
	 *
	 * @param {Array} parameters
	 * @chainable
	 */
	params: function ( parameters ) {
		var i;
		for ( i = 0; i < parameters.length; i++ ) {
			this.parameters.push( parameters[ i ] );
		}
		return this;
	},

	/**
	 * Convert message object to its string form based on current format.
	 *
	 * @return {string} Message as a string in the current form, or `<key>` if key
	 *  does not exist.
	 */
	toString: function () {
		var text;

		if ( !this.exists() ) {
			// Use ⧼key⧽ as text if key does not exist
			// Err on the side of safety, ensure that the output
			// is always html safe in the event the message key is
			// missing, since in that case its highly likely the
			// message key is user-controlled.
			// '⧼' is used instead of '<' to side-step any
			// double-escaping issues.
			// (Keep synchronised with Message::toString() in PHP.)
			return '⧼' + escape( this.key ) + '⧽';
		}

		if ( this.format === 'plain' || this.format === 'text' || this.format === 'parse' ) {
			text = this.parser();
		}

		if ( this.format === 'escaped' ) {
			text = this.parser();
			text = escape( text );
		}

		return text;
	},

	/**
	 * Change format to 'parse' and convert message to string
	 *
	 * If jqueryMsg is loaded, this parses the message text from wikitext
	 * (where supported) to HTML
	 *
	 * Otherwise, it is equivalent to plain.
	 *
	 * @return {string} String form of parsed message
	 */
	parse: function () {
		this.format = 'parse';
		return this.toString();
	},

	/**
	 * Change format to 'plain' and convert message to string
	 *
	 * This substitutes parameters, but otherwise does not change the
	 * message text.
	 *
	 * @return {string} String form of plain message
	 */
	plain: function () {
		this.format = 'plain';
		return this.toString();
	},

	/**
	 * Change format to 'text' and convert message to string
	 *
	 * If jqueryMsg is loaded, {{-transformation is done where supported
		 * (such as {{plural:}}, {{gender:}}, {{int:}}).
		 *
		 * Otherwise, it is equivalent to plain
		 *
	 * @return {string} String form of text message
	 */
	text: function () {
		this.format = 'text';
		return this.toString();
	},

	/**
	 * Change the format to 'escaped' and convert message to string
	 *
	 * This is equivalent to using the 'text' format (see #text), then
	 * HTML-escaping the output.
	 *
	 * @return {string} String form of html escaped message
	 */
	escaped: function () {
		this.format = 'escaped';
		return this.toString();
	},

	/**
	 * Check if a message exists
	 *
	 * @see mw.Map#exists
	 * @return {boolean}
	 */
	exists: function () {
		return this.map.exists( this.key );
	}
};

export function msg( key ) {
	var parameters = slice.call( arguments, 1 );
	if ( !mw.messages ) {
		mw.messages = new Map();
		mw.messages.set( global.messages );
	}
	return new Message( mw.messages, key, parameters ).toString();
}
