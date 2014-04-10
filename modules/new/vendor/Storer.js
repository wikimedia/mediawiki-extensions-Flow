/*!
 * Storer.js is a fallback-reliant, HTML5 Storage-based storage system.<br/>
 * <br/>
 * All of its storage subsystems implement getItem, setItem, removeItem, clear, key, and length, as the HTML5 Web
 * Storage specification is written, with some enhancements on them, and slight deviations on memory/cookieStorage.<br/>
 * <br/>
 * It piggybacks on the real HTML5 storage when available, and creates the additional functionality of being able to
 * prepend a 'prefix' to all key names automatically (see initStorer params). This is useful for projects where you
 * would like to use Storage without worrying about name collisions.<br/>
 * <br/>
 * It _always_ returns every type of storage, and falls back to others, as listed below. In the worst-case scenario,
 * all the storage subsystems are instances of memoryStorage, which means no persistance is available, but that no code
 * will break while performing actions on the current page.<br/>
 * <br/>
 * The fallbacks are as follows:<br/>
 *  localStorage   = localStorage   || userData    || cookieStorage || memoryStorage<br/>
 *  sessionStorage = sessionStorage || window.name || memoryStorage<br/>
 *  cookieStorage  = cookieStorage  || memoryStorage<br/>
 *  memoryStorage  = memoryStorage<br/>
 * <br/>
 * cookieStorage also supports an additional 'global' Boolean argument on all of its methods, allowing you to escape
 * out of the 'prefix' defined, so that you may use it to fetch general cookies as well.<br/>
 * <br/>
 * initStorer is called, takes a callback function, which will return the storage subsystems.<br/>
 * This is necessary because the Internet Explorer fallback for localStorage is userData, which needs to be able to
 * insert an element into the document before proceeding. On any modern or non-IE browser, the callback function is
 * triggered synchronously and immediately.<br/>
 * <br/>
 * Note: initStorer requires a function called domReady, or uses jQuery(document).ready if available.<br/>
 * <br/>
 * Here is a cat. =^.^= His name is Frisbee.
 * <br/>
 *
 * @todo cookieStorage is the sole storage subsystem which does not implement "length" and "key".
 * @todo It would be nice to have expiry times on all non-cookieStorage storage subsystems.
 * @todo Allow disabling userData for localStorage, so that the system is completely synchronous in all browsers.
 * @todo Implement automatic JSON stringify/parse if necessary.
 *
 * @copyright Viafoura, Inc. <viafoura.com>
 * @author Shahyar G <github.com/shahyar> for <github.com/viafoura>
 * @license CC-BY 3.0 <creativecommons.org/licenses/by/3.0>: Keep @copyright, @author intact.
 *
 * @example
 * initStorer(function (Storer) {
 *     cookieStorage  = Storer.cookieStorage;
 *     memoryStorage  = Storer.memoryStorage;
 *     sessionStorage = Storer.sessionStorage;
 *     localStorage   = Storer.localStorage;
 * }, { 'prefix': '_MyStorage_' });
 */

/**
 * This will return an object with each of the storage types.
 * The callback will fire when all of the necessary types have been created, although it's really only necessary
 * for Internet Explorer's userData storage, which requires domReady to begin.
 *
 * @author Shahyar G <github.com/shahyar> of Viafoura, Inc. <viafoura.com>
 * @param {Function} [callback]
 * @param {Object} [params]
 *                 {String} [prefix='']         automatic key prefix for sessionStorage and localStorage
 *                 {String} [default_domain=''] default domain for cookies
 *                 {String} [default_path='']   default path for cookies
 * @return {Object} cookieStorage, localStorage, memoryStorage, sessionStorage
 */

mediaWiki.flow = mediaWiki.flow || {};
mediaWiki.flow.vendor = mediaWiki.flow.vendor || {};
mediaWiki.flow.vendor.initStorer = function (callback, params) {
	var _TESTID      = '__SG__',
		top          = window,
		PREFIX       = (params = params || {}).prefix || '',
		_callbackNow = true,
		cookieStorage, localStorage, memoryStorage, sessionStorage;

	// get top within cross-domain limit if we're in an iframe
	try { while (top !== top.top) { top = top.top; } } catch (e) {}

	/**
	 * A hack for Safari's inability to extend a class with Storage.
	 * @param {String} name
	 * @param {Storage} StoreRef
	 * @return {Object}
	 */
	function _createReferencedStorage(name, StoreRef) {
		var store = {
			STORE_TYPE: 'ref' + name,
			key: function (key) {
				return StoreRef.key(key);
			},
			getItem: function (key) {
				return StoreRef.getItem(key);
			},
			setItem: function (key, value) {
				return StoreRef.setItem(key, value);
			},
			removeItem: function (key) {
				return StoreRef.removeItem(key);
			},
			clear: function () {
				return StoreRef.clear();
			}
		};
		Object.defineProperty(store, "length", { get: function () { return StoreRef.length; } });
		return store;
	}

	/**
	 * A hack for IE8's inability to extend a class with Storage. We use a DOM property getter to apply length.
	 * @param {String} name
	 * @param {Storage} StoreRef
	 * @return {Object}
	 */
	function _createDOMStorage(name, StoreRef) {
		var store = document.createElement('div');
		store.STORE_TYPE    = 'DOM' + name;
		store.key           = StoreRef.key;
		store.getItem       = StoreRef.getItem;
		store.setItem       = StoreRef.setItem;
		store.removeItem    = StoreRef.removeItem;
		store.clear         = StoreRef.clear;
		Object.defineProperty(store, "length", { get: function () { return StoreRef.length; } });
		return store;
	}

	/**
	 * Returns memoryStorage on failure
	 * @param {String} [cookie_prefix] An additional prefix, useful for isolating fallbacks for local/sessionStorage.
	 * @return {cookieStorage|memoryStorage}
	 */
	function _createCookieStorage(cookie_prefix) {
		cookie_prefix        = (cookie_prefix || '') + PREFIX;
		var _cookiergx       = new RegExp("(?:^|;)\s*" + cookie_prefix + "[^=]+\s*\=[^;]*", "g"),
			_nameclean       = new RegExp("^;?\s*" + cookie_prefix),
			_cookiergxGlobal = new RegExp("(?:^|;)\s*[^=]+\s*\=[^;]*", "g"),
			_namecleanGlobal = new RegExp("^;?\s*"),
			_expire          = (new Date(1979)).toGMTString(),
			_cookieStorage   = {
				STORE_TYPE: 'cookieStorage',
				/** Default domain to use in cookieStorage.setItem
				 * @const String */
				DEFAULT_DOMAIN: escape(params.default_domain || ''),
				/** Default path to use in cookieStorage.setItem
				 * @const String */
				DEFAULT_PATH: escape(params.default_path || ''),

				// @todo property {int} length
				// @todo method {Function} key

				/**
				 * Returns an Array of Objects of key-value pairs, or an Object with properties-values plus length.
				 * @param {Boolean} [as_object=true]
				 * @param {Boolean} [global=false] Omits prefix.
				 * @return {Array|Object}
				 */
				getAll: function (as_object, global) {
					var cleaner = global ? _namecleanGlobal : _nameclean,
						matches = document.cookie.match(global ? _cookiergxGlobal : _cookiergx) || [],
						i = matches.length, _cache;

					if (as_object === true) { // object of properties/values
						for (_cache = {length: i}; i--;) {
							_cache[unescape((matches[i] = matches[i].split('='))[0].replace(cleaner, ''))] = matches[i][1];
						}
					} else { // array of key/value objects
						for (_cache = []; i--;) {
							_cache.push({ key: unescape((matches[i] = matches[i].split('='))[0].replace(cleaner, '')), value: matches[i][1] });
						}
					}
					return _cache;
				},

				/**
				 * Get a cookie by name
				 * @param {String} key
				 * @param {Boolean} [global=false] Omits prefix.
				 * @return {String}
				 */
				getItem: function (key, global) {
					if (!key || !this.hasItem(key, global)) {
						return null;
					}
					return ((global = document.cookie.match(new RegExp('(?:^|;) *' + escape((global ? '' : cookie_prefix) + key) + '=([^;]*)(?:;|$)'))), global && global[0] ? unescape(global[1]) : null);
				},

				/**
				 * cookieStorage.setItem(key, value, end, path, domain, is_secure);
				 * @param {String} key name of the cookie
				 * @param {String} value value of the cookie;
				 * @param {Number|String|Date} [end] max-age in seconds (e.g., 31536e3 for a year) or the
				 *  expires date in GMTString format or in Date Object format; if not specified it will expire at the end of session;
				 * @param {String} [path] e.g., "/", "/mydir"; if not specified, defaults to the current path of the current document location;
				 * @param {String} [domain] e.g., "example.com", ".example.com" (includes all subdomains) or "subdomain.example.com"; if not
				 * specified, defaults to the host portion of the current document location;
				 * @param {Boolean} [is_secure=false] cookie will be transmitted only over secure protocol as https;
				 * @param {Boolean} [global=false] Omits prefix.
				 * @return {Boolean}
				 **/
				setItem: function (key, value, end, path, domain, is_secure, global) {
					if (!key || key === 'expires' || key === 'max-age' || key === 'path' || key === 'domain' || key === 'secure') {
						return false;
					}
					var sExpires = "";
					if (end) {
						switch (typeof end) {
							case "number":
								sExpires = "; max-age=" + end;
								break;
							case "string":
								sExpires = "; expires=" + end;
								break;
							case "object":
								if (end.hasOwnProperty("toGMTString")) {
									sExpires = "; expires=" + end.toGMTString();
								}
								break;
						}
					}
					if (value !== undefined && value !== null) {
						domain = (domain = typeof domain === 'string' ? escape(domain) : _cookieStorage.DEFAULT_DOMAIN) ? '; domain=' + domain : '';
						path   = (path   = typeof path   === 'string' ? escape(path)   : _cookieStorage.DEFAULT_PATH)   ? '; path=' + path : '';
						document.cookie = escape((global ? '' : cookie_prefix) + key) + '=' + escape(value) + sExpires + domain + path + (is_secure ? '; secure' : '');
						return true;
					}
					return _cookieStorage.removeItem(key, domain, path, is_secure, global);
				},

				/**
				 * Get a cookie by name
				 * @param {String} key
				 * @param {String} [path]
				 * @param {String} [domain]
				 * @param {Boolean} [is_secure]
				 * @param {Boolean} [global=false] Omits prefix.
				 * @return {Boolean}
				 */
				removeItem: function (key, domain, path, is_secure, global) {
					if (!key || !this.hasItem(key, global)) {
						return false;
					}
					domain = (domain = typeof domain === 'string' ? escape(domain) : _cookieStorage.DEFAULT_DOMAIN) ? '; domain=' + domain : '';
					path   = (path   = typeof path   === 'string' ? escape(path)   : _cookieStorage.DEFAULT_PATH)   ? '; path=' + path : '';
					document.cookie = escape((global ? '' : cookie_prefix) + key) + '=; expires=' + _expire + domain + path + (is_secure ? '; secure' : '');
					return true;
				},

				/**
				 * Returns true if a cookie with that name was found, false otherwise
				 * @param {String} key
				 * @param {Boolean} [global=false] Omits prefix.
				 * @param {Boolean}
				 */
				hasItem: function (key, global) {
					return (new RegExp('(?:^|;) *' + escape((global ? '' : cookie_prefix) + key) + '=')).test(document.cookie);
				}
			};

		_cookieStorage.setItem(_TESTID, 4);
		if (_cookieStorage.getItem(_TESTID) == 4) {
			_cookieStorage.removeItem(_TESTID);
			return _cookieStorage;
		}
		return _createMemoryStorage();
	}

	/**
	 * Returns a memoryStorage object. This is a constructor to be reused as a fallback on sessionStorage & localStorage
	 * @return {memoryStorage}
	 */
	function _createMemoryStorage() {
		var _data  = {}, // key : data
			_keys  = [], // _keys key : _ikey key
			_ikey  = {}; // _ikey key : _keys key
		/**
		 * @namespace memoryStorage
		 */
		var _memoryStorage = {
			STORE_TYPE: 'memoryStorage',

			/** # of items */
			length: 0,

			/**
			 * Get key name by id
			 * @param {int} i
			 * @return {mixed}
			 */
			key: function (i) {
				return _keys[i];
			},

			/**
			 * Get an item
			 * @param {String} key
			 * @return {mixed}
			 */
			getItem: function (key) {
				return _data[key];
			},

			/**
			 * Set an item
			 * @param {String} key
			 * @param {String} data
			 * @return {String|Boolean}
			 */
			setItem: function (key, data) {
				if (data !== null && data !== undefined) {
					_ikey[key] === undefined && (_ikey[key] = (_memoryStorage.length = _keys.push(key)) - 1);
					return (_data[key] = data);
				}
				return _memoryStorage.removeItem(key);
			},

			/**
			 * Removes an item
			 * @param {String} key
			 * @return {Boolean}
			 */
			removeItem: function (key) {
				var was = _data[key] !== undefined;
				if (_ikey[key] !== undefined) {
					// re-reference all the keys because we've removed an item in between
					for (var i = _keys.length; --i > _ikey[key];) {
						_ikey[_keys[i]]--;
					}
					_keys.splice(_ikey[key], 1);
					delete _ikey[key];
				}
				delete _data[key];
				_memoryStorage.length = _keys.length;
				return was;
			},

			/**
			 * Clears memoryStorage
			 */
			clear: function () {
				for (var i in _data) {
					if (_data.hasOwnProperty(i)) {
						delete _data[i];
					}
				}
				_memoryStorage.length = _keys.length = 0;
				_ikey = {};
			}
		};
		return _memoryStorage;
	}

	/**
	 * Returns a nameStorage object. This constructor is designed to be a fallback for sessionStorage in IE7 and under.
	 * It uses window.name and RC4 encryption on a per-domain basis. Inspired by LSS by Andrea Giammarchi.
	 * @param {DOMWindow} [win=top]
	 * @return {nameStorage}
	 */
	function _createNameStorage(win) {
		if (!win) {
			win = top;
		}

		/** RC4 Stream Cipher
		 *  http://www.wisdom.weizmann.ac.il/~itsik/RC4/rc4.html
		 * -----------------------------------------------
		 * @description     A quick stream cipher to encode & decode any string, using a random key of up to 256 bytes.
		 *
		 * @author          Ported to JavaScript by Andrea Giammarchi
		 * @license         MIT-style license
		 * @blog            http://webreflection.blogspot.com/
		 * @version         1.2.1
		 */
		var RC4 = (function (String, fromCharCode, random) {
				return {
					/** RC4.decode(key:String, data:String):String
					 * @description     given a data string encoded with the same key
					 *                  generates original data string.
					 * @param   {String} key    key precedently used to encode data
					 * @param   {String} data   data encoded using same key
					 * @return  {String}        decoded data
					 */
					decode: function (key, data) {
						return this.encode(key, data);
					},

					/** RC4.encode(key:String, data:String):String
					 * @description     encode a data string using provided key
					 * @param   {String} key    key to use for this encoding
					 * @param   {String} data   data to encode
					 * @return  {String}        encoded data. Will require same key to be decoded
					 */
					encode: function (key, data) {
						for (var length = key.length, len = data.length, decode = [], a = [],
								 i = 0, j = 0, k = 0, l = 0, $;
							 i < 256;
							 i++
							) {
							a[i] = i;
						}
						for (i = 0; i < 256; i++) {
							j = (j + ($ = a[i]) + key.charCodeAt(i % length)) % 256;
							a[i] = a[j];
							a[j] = $;
						}
						for (j = 0; k < len; k++) {
							i = k % 256;
							j = (j + ($ = a[i])) % 256;
							length = a[i] = a[j];
							a[j] = $;
							decode[l++] = data.charCodeAt(k) ^ a[(length + $) % 256];
						}
						return fromCharCode.apply(String, decode);
					},

					/** RC4.key(length:Number):String
					 * @description     generate a random key with arbitrary length
					 * @param   {Number} length The length of the generated key
					 * @return  {String}        a randomly generated key
					 */
					key: function (length) {
						for (var i = 0, key = []; i < length; i++) {
							key[i] = 1 + ((random() * 255) << 0);
						}
						return fromCharCode.apply(String, key);
					}
				};
				// I like to freeze stuff in interpretation time
				// it makes things a bit safer when obtrusive libraries
				// are around
			}(String, String.fromCharCode, Math.random)),
		// Opera will store on every set, because it has no onbeforeunload
			is_opera = Object.prototype.toString.call(window.opera) === "[object Opera]",
		// Key used for this domain
			KEY;
		// Try to fetch an old key
		try {
			KEY = decodeURI(cookieStorage.getItem('.sessionStorageKey'));
		} catch (e) {}
		// Generate an encryption key if we don't have a valid one.
		if (!KEY || KEY.length !== STRENGTH) {
			KEY = RC4.key(STRENGTH);
			cookieStorage.setItem('.sessionStorageKey', encodeURI(KEY));
		}

		// Domain used for prefixing keys
		var DOMAIN  = win.document.domain,
		// Encrypted domain
			EDOMAIN = RC4.encode(KEY, DOMAIN),
		// Start of Header
			SOH = '#' + String.fromCharCode(1) + 'STOR/' + EDOMAIN,
		// End of Transmission
			EOT = EDOMAIN + '/STOR' + String.fromCharCode(4) + '#',
		// Start of Text
			STX = String.fromCharCode(2) + ';',
		// End of Transmission Block
			ETB = String.fromCharCode(23) + ';',
		// End of Text
			ETX = ';' + String.fromCharCode(3),
		// Key strength in bytes (32 = 256-bit)
			STRENGTH = 32,
		// Lengths
			SOHl = SOH.length,
			EOTl = EOT.length,
			STXl = STX.length,
			ETBl = ETB.length,
			ETXl = ETX.length,
		// Data storage by key name
			_dataObject = {},
		// Key storage by index
			_dataArray  = [],

			/**
			 * @namespace NameStorage
			 */
				_nameStorage = {
				STORE_TYPE: 'name',

				/** Number of items in storage */
				length: 0,

				/**
				 * Get an item by its index
				 * @param {int} index
				 * @return {String} key
				 */
				key: function (index) {
					return _dataArray[index];
				},

				/**
				 * Get an item by its key
				 * @param {String} key
				 * @return {String} data
				 */
				getItem: function (key) {
					return _dataObject[key] ? _dataObject[key].value : null;
				},

				/**
				 * Set an item by key
				 * @param {String} key
				 * @param {String} data
				 * @return {String} data
				 */
				setItem: function (key, data) {
					if (_dataObject[key]) {
						// Update an existing key's value
						_dataObject[key].value = data;
					} else {
						// Store this item by its key
						_dataObject[key] = {
							value: data,
							// For new items, increment the length property
							index: (_nameStorage.length = _dataArray.push(key)) - 1
						};
					}

					is_opera && _write();

					return data;
				},

				/**
				 * Remove an item by key
				 * @param {String} key
				 * @return {Boolean}
				 */
				removeItem: function (key) {
					if (_dataObject[key]) {
						// Validity check on _dataArray just in case, to prevent corruption
						if (_dataArray[_dataObject[key].index] === key) {
							// Remove the stored index
							_dataArray.splice(_dataObject[key].index, 1);

							// Update length property
							_nameStorage.length = _dataArray.length;

							// Update all other indices to point to their new locations
							for (var i = _dataObject[key].index, len = _dataArray.length; i < len; i++) {
								_dataObject[_dataArray[i]].index--;
							}
						}

						// Delete the stored data
						delete _dataObject[key];

						is_opera && _write();

						return true;
					}

					return false;
				},

				/**
				 * Completely empies storage
				 */
				clear: function () {
					_dataArray.length = 0;
					_dataObject = {};
					is_opera && _write();
				}
			};

		// Format: FULLCONTENT:    [SOH]CONTENTPIECECONTENTPIECE...[EOT]
		// Format: CONTENTPIECE:   [STX]keylength[:]contentlength[ETB]key[ETB]content[ETX]
		/*
		 win.name = SOH
		 + STX + 5 + ':' + 5 + ETB + 'hello' + ETB + 'world' + ETX
		 + STX + 2 + ':' + 10 + ETB + 'my' + ETB + 'abcd?fhi~k' + ETX
		 + EOT;
		 */

		/**
		 * Writes _dataObject's keys and values to window.name.
		 */
		function _write() {
			var str     = win.name,
				start   = str.indexOf(SOH),
				end     = str.indexOf(EOT),
				i       = _dataArray.length;

			// Remove any previous storage on window.name
			if (start > -1 && end > start) {
				win.name = str.slice(0, start) + str.slice(end + EOTl);
			}

			for (str = ''; i--;) {
				if (_dataObject[_dataArray[i]] && _dataObject[_dataArray[i]].value) {
					//     STX   --------KEY LENGTH---------    :    ----------------CONTENT LENGTH----------------   ETB   -----KEY-----   ETB   -------------CONTENT------------   ETX
					str += STX + ('' + _dataArray[i]).length + ':' + ('' + _dataObject[_dataArray[i]].value).length + ETB + _dataArray[i] + ETB + _dataObject[_dataArray[i]].value + ETX;
				}
			}

			// Encrypt the contents and write it to window.name
			win.name += SOH + encodeURI(RC4.encode(KEY, str)) + EOT;
		}

		/**
		 * This function processes window.name, tries to find matching keys, and stores them.
		 */
		function _initialize() {
			var str     = win.name,
				start   = str.indexOf(SOH),
				end     = str.indexOf(EOT),
				last_index  = 0,
				item_key    = '',
				item_klen   = 0,
				item_clen   = 0;

			if (start > -1 && end > start) {
				// Remove it from the window to append it later. This helps with invalid data.
				// eg. ABC;def;HIJ -> ABCHIJ
				win.name = str.slice(0, start) + str.slice(end + EOTl);

				// Use the rest of the string for storage parsing
				str = RC4.decode(KEY, decodeURI(str.slice(start + SOHl, end)));

				// Find the start of an item
				while ((start = str.indexOf(STX, last_index)) !== -1) {
					last_index = start + STXl; // move index to start of item, past STX

					// Find out how long this item is, and its key name
					if ((end = str.indexOf(ETB, last_index)) !== -1) {
						// [1] content length, [0] key length
						item_klen = str.slice(last_index, end).split(':');
						item_clen = parseInt(item_klen[1], 10);
						item_klen = parseInt(item_klen[0], 10);

						last_index = end + ETBl; // move index to start of item key, past length-ETB

						// Validate: Make sure ETB is immediately after the key
						if ((end = str.indexOf(ETB, last_index)) === last_index + item_klen) {
							// Parse out this item's key
							item_key = str.substr(last_index, item_klen);

							last_index = end + ETBl; // move index to start of item content, past key-ETB

							// Validate: Make sure ETX is immediately after the content
							if ((end = str.indexOf(ETX, last_index)) === last_index + item_clen) {
								// Store this item
								_nameStorage.setItem(item_key, str.substr(last_index, item_clen));
							}
						}
					}
				}
			}

			// _write data onbeforeunload
			if (win.addEventListener) {
				win.addEventListener('beforeunload', _write, true);
			} else if (win.attachEvent) {
				win.attachEvent('onbeforeunload', _write);
			}
		}

		try {
			_initialize();
		} catch (e) {
		}

		return _nameStorage;
	}

	// Return this stuff
	var _returnable = {
		'cookieStorage':        null,
		'localStorage':         null,
		'memoryStorage':        null,
		'sessionStorage':       null,
		'_createCookieStorage': _createCookieStorage,
		'_createMemoryStorage': _createMemoryStorage
	};

	/**
	 * @instanceof cookieStorage
	 */
	_returnable.cookieStorage = cookieStorage = _createCookieStorage();

	/**
	 * @instanceof memoryStorage
	 */
	_returnable.memoryStorage = memoryStorage = _createMemoryStorage();

	/**
	 * @namespace sessionStorage
	 */
	_returnable.sessionStorage = sessionStorage = (function () {
		// Grab sessionStorage from top window
		var _sessionStorage = top.sessionStorage;

		// Try to use original sessionStorage
		if (_sessionStorage) {
			try {
				// Test to make sure it works and isn't full
				_sessionStorage.setItem(_TESTID, 1);
				_sessionStorage.removeItem(_TESTID);

				// Now clone sessionStorage so that we may extend it with our own methods
				var _tmp = function () {
				};
				_tmp.prototype = _sessionStorage;
				_tmp = new _tmp();
				try {
					if (_tmp.getItem) {
						_tmp.setItem(_TESTID, 2);
						_tmp.removeItem(_TESTID);
					}
				} catch (e) {
					// Firefox 14+ throws a security exception when wrapping a native class
					_tmp = null;
				}

				if (_tmp && !_tmp.getItem) {
					// Internet Explorer 8 does not inherit the prototype here. We can hack around it using a DOM object
					_sessionStorage = _createDOMStorage('sessionstorage', _sessionStorage);
				} else if (!_tmp || Object.prototype.toString.apply(Storage.prototype) === '[object StoragePrototype]') {
					// Safari throws a type error when extending with Storage
					_sessionStorage = _createReferencedStorage('sessionstorage', _sessionStorage);
				} else {
					_sessionStorage = _tmp;
				}
			} catch (e) {
				_sessionStorage = null;
			}
		}

		// Build one
		if (!_sessionStorage) {
			try {
				// instantiate nameStorage
				_sessionStorage = _createNameStorage();

				// Test it
				_sessionStorage.setItem(_TESTID, 2);
				if (_sessionStorage.getItem(_TESTID) == 2) {
					_sessionStorage.removeItem(_TESTID);
				} else {
					_sessionStorage = null;
				}
			} catch (e) {
				_sessionStorage = null;
			}

			// Last ditch effort: use memory storage
			if (!_sessionStorage) {
				_sessionStorage = _createMemoryStorage();
			}
		}

		// Rewire functions to use a prefix and avoid collisions
		// @todo Rewire length for prefixes as well
		_sessionStorage._getItem    = _sessionStorage.getItem;
		_sessionStorage._setItem    = _sessionStorage.setItem;
		_sessionStorage._removeItem = _sessionStorage.removeItem;
		_sessionStorage._key        = _sessionStorage.key;

		_sessionStorage.getItem    = function (key) {
			return _sessionStorage._getItem(PREFIX + key);
		};
		_sessionStorage.setItem    = function (key, data) {
			return _sessionStorage._setItem(PREFIX + key, data);
		};
		_sessionStorage.removeItem = function (key) {
			return _sessionStorage._removeItem(PREFIX + key);
		};
		_sessionStorage.key        = function (index) {
			if ((index = _sessionStorage._key(index)) !== undefined && index !== null) {
				// Chop off the index
				return index.indexOf(PREFIX) === 0 ? index.substr(PREFIX.length) : index;
			}
			return null;
		};
		_sessionStorage.clear      = function () {
			for (var i = _sessionStorage.length, j; i--;) {
				if ((j = _sessionStorage._key(i)).indexOf(PREFIX) === 0) {
					_sessionStorage._removeItem(j);
				}
			}
		};
		return _sessionStorage;
	}());

	/**
	 * @namespace localStorage
	 */
	_returnable.localStorage = localStorage = (function () {
		var _localStorage;

		if (top.localStorage || top.globalStorage) {
			try {
				_localStorage = top.localStorage || top.globalStorage[location.hostname];
				_localStorage.setItem(_TESTID, 1);
				_localStorage.removeItem(_TESTID);

				// Now clone sessionStorage so that we may extend it with our own methods
				var _tmp = function () {};
				_tmp.prototype = _localStorage;
				_tmp = new _tmp();
				try {
					if (_tmp.getItem) {
						_tmp.setItem(_TESTID, 2);
						_tmp.removeItem(_TESTID);
					}
				} catch (e) {
					// Firefox 14+ throws a security exception when wrapping a native class
					_tmp = null;
				}

				if (_tmp && !_tmp.getItem) {
					// Internet Explorer 8 does not inherit the prototype here. We can hack around it using a DOM object
					_localStorage = _createDOMStorage('localstorage', _localStorage);
				} else if (!_tmp || Object.prototype.toString.apply(Storage.prototype) === '[object StoragePrototype]') {
					// Safari throws a type error when extending with Storage
					_localStorage = _createReferencedStorage('localstorage', _localStorage);
				} else {
					// Spec
					_localStorage = _tmp;
				}
			} catch (e) {
				_localStorage = null;
			}
		}

		// Did not work, try userData, cookie, or memory:
		if (!_localStorage) {
			_localStorage = (function () {
				/**
				 * @param {String} str
				 * @return {String}
				 */

				var _esc = function (str) {
						return 'PS' + str.replace(_e, '__').replace(_s, '_s');
					},
					_e = /_/g,
					_s = / /g,
					_PREFIX = _esc(PREFIX + 'uData'),
					_NAME = _esc('Storer');

				if (window.ActiveXObject) {
					// Try userData
					try {
						// Data cache
						var _data = {}, // key : data
							_keys = [], // _keys key : _ikey key
							_ikey = {}, // _ikey key : _keys key
							/** @namespace userData */
								userData = {
								STORE_TYPE: 'userData',

								/** # of items */
								length: 0,

								/**
								 * Returns key of i
								 * @param {int} i
								 * @return {String}
								 */
								key: function (i) {
									return _keys[i];
								},

								/**
								 * Gets data of key
								 * @param {String} key
								 * @return {String}
								 */
								getItem: function (key) {
									return el.getAttribute(_esc(key));
								},

								/**
								 * Sets key to data
								 * @param {String} key
								 * @param {String} data
								 * @return {String} data
								 */
								setItem: function (key, data) {
									if (data !== null && data !== undefined) {
										el.setAttribute(_esc(key), data);
										_ikey[key] === undefined && (_ikey[key] = (userData.length = _keys.push(key)) - 1);
										el.save(_PREFIX + _NAME);
										return (_data[key] = data);
									}
									return userData.removeItem(key);
								},

								/**
								 * Removes item at key
								 * @param {String} key
								 * @return {Boolean}
								 */
								removeItem: function (key) {
									el.removeAttribute(_esc(key));
									if (_ikey[key] !== undefined) {
										// re-reference all the keys because we've removed an item in between
										for (var i = _keys.length; --i > _ikey[key];) {
											_ikey[_keys[i]]--;
										}
										_keys.splice(_ikey[key], 1);
										delete _ikey[key];
									}
									el.save(_PREFIX + _NAME);
									userData.length = _keys.length;

									return true;
								},

								/**
								 * Clears all data
								 */
								clear: function () {
									for (var doc = el.xmlDocument,
											 attributes = doc.firstChild.attributes,
											 attr,
											 i = attributes.length;
										 0 <= --i;) {
										attr = attributes[i];
										delete _data[attr.nodeName]; // remove from cache
										el.removeAttribute(attr.nodeName); // use the standard DOM properties to remove the item
										userData.length--;
									}
									el.save(_PREFIX + _NAME);
									userData.length = _keys.length = 0;
									_data = {};
									_ikey = {};
								}
							};

						// Init userData element
						var el = document.createElement('input');
						el.style.display = 'none';
						el.addBehavior('#default#userData');

						var fn = (typeof domReady === 'function' ? domReady : (typeof jQuery !== 'undefined' ? jQuery(document).ready : false));
						_callbackNow = !fn;

						fn && fn(function () {
							try {
								var bod = document.body || document.getElementsByTagName('head')[0];
								bod.appendChild(el);
								el.load(_PREFIX + _NAME);

								// Test
								userData.setItem(_TESTID, 3);
								if (userData.getItem(_TESTID) == 3) {
									userData.removeItem(_TESTID);

									// Good. Parse.
									var attr,
									// the reference to the XMLDocument
										doc = el.xmlDocument,
									// the root element will always be the firstChild of the XMLDocument
										attributes = doc.firstChild.attributes,
										i = -1,
										len = attributes.length;
									while (++i < len) {
										attr = attributes[i];
										if (attr.nodeValue !== undefined && attr.nodeValue !== null) {
											_ikey[attr.nodeName] = _keys.push(attr.nodeName) - 1;
											_data[attr.nodeName] = attr.nodeValue; // use the standard DOM properties to retrieve the key and value
										}
									}

									_returnable.localStorage = localStorage = userData;
									callback && callback(_returnable);
								} else {
									userData = null;
								}
							} catch (e) {
								userData = null;
							}

							if (!userData) {
								_returnable.localStorage = localStorage = _localStorage = _createCookieStorage();
								callback && callback(_returnable);
							}
						});

						return userData;
					} catch (e) {}
				}
			}());
		}

		if (!_localStorage) {
			_localStorage = _createCookieStorage();
		}

		// Use the object natively without a prefix
		if (!PREFIX) {
			return _localStorage;
		}

		// Rewire functions to use a prefix and avoid collisions
		// @todo Rewire length for prefixes as well
		_localStorage._getItem    = _localStorage.getItem;
		_localStorage._setItem    = _localStorage.setItem;
		_localStorage._removeItem = _localStorage.removeItem;
		_localStorage._key        = _localStorage.key;

		_localStorage.getItem    = function (key) {
			return _localStorage._getItem(PREFIX + key);
		};
		_localStorage.setItem    = function (key, data) {
			return _localStorage._setItem(PREFIX + key, data);
		};
		_localStorage.removeItem = function (key) {
			return _localStorage._removeItem(PREFIX + key);
		};
		_localStorage.key        = function (index) {
			if ((index = _localStorage._key(index)) !== undefined && index !== null) {
				// Chop off the index
				return index.indexOf(PREFIX) === 0 ? index.substr(PREFIX.length) : index;
			}
			return null;
		};
		_localStorage.clear      = function () {
			for (var i = _localStorage.length, j; i--;) {
				if ((j = _localStorage._key(i)).indexOf(PREFIX) === 0) {
					_localStorage._removeItem(j);
				}
			}
		};

		return _localStorage;
	}());

	_callbackNow && callback && callback(_returnable);

	return _returnable;
};
