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
 * Note: for IE6-7 compatibility, initStorer requires a function called domReady, or uses jQuery(document).ready if available.<br/>
 * <br/>
 * Here is a cat. =^.^= His name is Frisbee.
 * <br/>
 *
 * @copyright Viafoura, Inc. <viafoura.com>
 * @author Shahyar G <github.com/shahyar>, originally for <github.com/viafoura>
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
 * @author Shahyar G <github.com/shahyar>, originally for Viafoura, Inc. <viafoura.com>
 * @param {Function} [callback]
 * @param {Object} [params]
 *                 {String}  [prefix='']                 automatic key prefix for sessionStorage and localStorage
 *                 {String}  [default_domain='']         default domain for cookies
 *                 {String}  [default_path='']           default path for cookies
 *                 {Boolean} [no_cookie_fallback=false]  If true, do not use cookies as fallback for localStorage
 * @return {Object} {cookieStorage, localStorage, memoryStorage, sessionStorage}
 * @version 0.1.1
 */
function initStorer(callback, params) {
    "use strict";

    var _TESTID            = '__SG__',
        top                = window,
        PREFIX             = (params = Object.prototype.toString.call(callback) === "[object Object]" ? callback : (params || {})).prefix || '',
        NO_COOKIE_FALLBACK = params.no_cookie_fallback || false,
        _callbackNow       = true,
        cookieStorage, localStorage, memoryStorage, sessionStorage;

    if (params === callback) {
        // Allow passing params without callback
        callback = null;
    }

    // get top within cross-domain limit if we're in an iframe
    try { while (top !== top.top) { top = top.top; } } catch (e) {}

    /**
     * Returns result.value if result has ._end key, or returns result entirely otherwise.
     * Returns null when: result is null or undefined, or end && end > current timestamp.
     * @param {String|Number|Date|null|undefined} end
     * @param {*} result
     * @param {Function} remove_callback
     * @param {String} remove_callback_key
     * @returns {*}
     * @private
     */
    function _checkEnd(end, result, remove_callback, remove_callback_key) {
        if (result === null || result === undefined || (end && parseInt(+new Date() / 1000, 10) > parseInt(end, 10))) {
            // Remove this key from the data set
            remove_callback(remove_callback_key);
            // Return nothing
            return null;
        }
        // Return the actual data
        return result._end !== undefined ? result.value : result;
    }

    /**
     * Parses str into JSON object, but also handles backwards compatibility with 0.0.4 when data was not automatically
     * JSONified. If data._end exists, also runs _checkEnd. When not a valid JSON object, returns str back.
     * @param {String|*} str
     * @param {Function} [remove_callback]
     * @param {String} [callback_key]
     * @returns {*}
     * @private
     */
    function _getJSON(str, remove_callback, callback_key) {
        try {
            var obj = str && JSON.parse(str);
            if (obj) {
                // Backwards compatibility for 0.0.4, when _end did not exist
                if (obj._end !== undefined) {
                    // Check for expiry
                    return _checkEnd(obj._end, obj.value, remove_callback, callback_key);
                }
                return obj;
            }
        } catch (e) {}

        // Non-JSON data (0.0.4)
        return str;
    }

    /**
     * Puts data and end (standardized to seconds) in an object, and returns it for use.
     * If end is valid and end > now, data = null, and remove_callback is called,
     * otherwise, set_callback is called.
     * @param {Object|*} data
     * @param {String|Number|Date} [end]
     * @param {Function} [set_callback]
     * @param {Function} [remove_callback]
     * @param {String} [callback_key]
     * @param {Boolean} [json]
     * @returns {*}
     * @private
     */
    function _storeEnd(data, end, set_callback, remove_callback, callback_key, json) {
        var now = parseInt(+new Date() / 1000, 10);

        switch (typeof end) {
            case "number":
                // Max-age, although we allow end=0 to mimic 0 for cookies
                end = end && parseInt(now + end, 10);
                break;
            case "string":
                // timestamp or Date string
                end = end.length > 4 && "" + parseInt(end, 10) === end ? parseInt(end, 10) : parseInt(+new Date(end) / 1000, 10);
                break;
            case "object":
                if (end.toGMTString) {
                    // Date object
                    end = parseInt(+end / 1000, 10);
                }
                break;
            default:
                end = null;
        }

        data = { value: end && now > end ? null : data, _end: end || null };

        if (data.value === null || data.value === undefined) {
            // Automatically expire this item
            remove_callback && remove_callback(callback_key);
        } else if (json) {
            // Set the data with JSON
            set_callback && set_callback(callback_key, JSON.stringify(data._end ? data : data.value));
        } else {
            // Set the data
            set_callback && set_callback(callback_key, data._end ? data : data.value);
        }

        return data;
    }

    /**
     * Clears expired data from each storage subsystem.
     * @private
     */
    function _clearExpired() {
        var i, j, key;
        // Iterate over every storage subsystem
        for (i in _returnable) {
            // Ignore memoryStorage, as it doesn't have anything to expire
            if (_returnable.hasOwnProperty(i) && i.charAt(0) !== '_' && _returnable[i].STORE_TYPE !== 'memoryStorage') {
                j = 0;
                // Iterate over every key in this subsystem
                while ((key = _returnable[i].key(j++))) {
                    // getItem automatically handles removing expired items
                    _returnable[i].getItem(key);
                }
            }
        }
    }

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
            setItem: function (key, value, end) {
                return StoreRef.setItem(key, value, end);
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
     * Amends getItem and setItem to support expiry times for HTML5 Storage.
     * @param {Object|Storage} StoreRef
     * @return {Object}
     * @private
     */
    function _adjustHTML5Storage(StoreRef) {
        var _getItem = StoreRef.getItem,
            _setItem = StoreRef.setItem,
            _removeItem = StoreRef._removeItem || StoreRef.removeItem,
            _removeItemCallback = function (key) {
                _removeItem(key);
            };

        StoreRef.getItem       = function (key) {
            return _getJSON(_getItem(key), _removeItemCallback, key);
        };
        StoreRef.setItem       = function (key, data, end) {
            return _storeEnd(
                data,
                end,
                function (key, value) {
                    _setItem(key, value);
                },
                _removeItemCallback,
                key,
                true
            );
        };

        return StoreRef;
    }

    /**
     *
     * @param {Object|Storage} StoreRef
     * @returns {*}
     * @private
     */
    function _assignPrefix(StoreRef) {
        // Use the rest of the object natively without a prefix
        // memoryStorage doesn't need prefixes
        if (!PREFIX || StoreRef.STORE_TYPE === 'memoryStorage') {
            return StoreRef;
        }

        // Rewire functions to use a prefix and avoid collisions
        // @todo Rewire length for prefixes as well
        StoreRef._getItem    = StoreRef.getItem;
        StoreRef._setItem    = StoreRef.setItem;
        StoreRef._removeItem = StoreRef.removeItem;
        StoreRef._key        = StoreRef.key;

        /** Variable # of items in Storage.
         * @const int length
         * @memberof sessionStorage
         * @memberof localStorage */

        /**
         * Returns an item from the current type of Storage.
         * @param {String} key
         * @returns {*}
         * @memberof sessionStorage
         * @memberof localStorage
         */
        StoreRef.getItem    = function (key) {
            return StoreRef._getItem(PREFIX + key);
        };

        /**
         * Sets an item in the current type of Storage.
         * end is expiry: Number = seconds from now, String = date string for Date(), or Date object.
         * @param {String} key
         * @param {*} data
         * @param {int|String|Date} [end]
         * @memberof sessionStorage
         * @memberof localStorage
         */
        StoreRef.setItem    = function (key, data, end) {
            return StoreRef._setItem(PREFIX + key, data, end);
        };

        /**
         * Removes key from the current Storage instance, if it has been set.
         * @param {String} key
         * @memberof sessionStorage
         * @memberof localStorage
         */
        StoreRef.removeItem = function (key) {
            return StoreRef._removeItem(PREFIX + key);
        };

        StoreRef._key        = StoreRef.key;
        /**
         * Gets the key (if any) at index, from the current Storage instance.
         * @param {int} index
         * @returns {String|null}
         * @memberof sessionStorage
         * @memberof localStorage
         */
        StoreRef.key        = function (index) {
            if ((index = StoreRef._key(index)) !== undefined && index !== null) {
                // Chop off the index
                return index.indexOf(PREFIX) === 0 ? index.substr(PREFIX.length) : index;
            }
            return null;
        };

        if (StoreRef.STORE_TYPE !== 'cookieStorage') {
            // cookieStorage has its own clear which supports prefixes
            /**
             * Removes all the current keys from this Storage instance.
             * @memberof sessionStorage
             * @memberof localStorage
             */
            StoreRef.clear      = function () {
                for (var i = StoreRef.length, key; i--;) {
                    if ((key = StoreRef._key(i)).indexOf(PREFIX) === 0) {
                        StoreRef._removeItem(key);
                    }
                }
            };
        } else {
            // cookieStorage is the only one which implements hasItem
            if (StoreRef.hasItem) {
                StoreRef._hasItem = StoreRef.hasItem;
                StoreRef.hasItem = function (key) {
                    return StoreRef._hasItem(PREFIX + key);
                };
            }
        }

        return StoreRef;
    }

    /**
     * Returns memoryStorage on failure
     * @param {String} [cookie_prefix] An additional prefix, useful for isolating fallbacks for local/sessionStorage.
     * @return {cookieStorage|memoryStorage}
     */
    function _createCookieStorage(cookie_prefix) {
        cookie_prefix        = (cookie_prefix || '');
        var _cookiergx       = new RegExp("(?:^|;)\\s*" + cookie_prefix + PREFIX + "[^=;]+\\s*(?:=[^;]*)?", "g"),
            _nameclean       = new RegExp("^;?\\s*" + cookie_prefix + PREFIX),
            _cookiergxGlobal = new RegExp("(?:^|;)\\s*[^=;]+\\s*(?:=[^;]*)?", "g"),
            _namecleanGlobal = new RegExp("^;?\\s*"),
            _expire          = (new Date(1979)).toGMTString(),
            /**
             * @namespace cookieStorage
             * @memberof Storer
             * @public
             * @global
             */
            _cookieStorage   = {
            /** @const String STORE_TYPE
             * @default "cookieStorage"
             * @memberof cookieStorage */
            STORE_TYPE: 'cookieStorage',
            /** Default domain to use in cookieStorage.setItem (set by initStorer)
             * @const String DEFAULT_DOMAIN
             * @memberof cookieStorage */
            DEFAULT_DOMAIN: escape(params.default_domain || ''),
            /** Default path to use in cookieStorage.setItem (set by initStorer)
             * @const String DEFAULT_PATH
             * @memberof cookieStorage */
            DEFAULT_PATH: escape(params.default_path || ''),

            /** Variable # of items in storage
             * @const int length
             * @memberof cookieStorage */
            length: 0,

            /**
             * Returns the cookie key at idx.
             * @param {int} idx
             * @param {Boolean} [global=false] Omits prefix.
             * @return {*}
             * @memberof cookieStorage
             */
            key: function (idx, global) {
                var cookies = _cookieStorage.getAll(false, global);
                return cookies[idx] ? cookies[idx].key : undefined;
            },

            /**
             * Clears all cookies for this prefix.
             * @param {Boolean} [global=false] true omits the prefix, and erases all cookies
             * @memberof cookieStorage
             */
            clear: function (global) {
                var cookies = _cookieStorage.getAll(false, global),
                    i = cookies.length;

                while (i--) {
                    // Don't use static _removeItemFn reference, because cookieStorage.clear is not handled by _assignPrefix
					_cookieStorage.removeItem(cookies[i].key);
                }
            },

            /**
             * Returns an Array of Objects of key-value pairs, or an Object with properties-values plus length (as_object).
             * @param {Boolean} [as_object=false] true returns a single object of key-value pairs
             * @param {Boolean} [global=false] true gets all cookies, omitting the default prefix
             * @return {Object[]|Object}
             * @memberof cookieStorage
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
             * Get a cookie by name.
             * @param {String} key
             * @param {Boolean} [global=false] true omits the prefix, and searches for a match "globally"
             * @return {String}
             * @memberof cookieStorage
             */
            getItem: function (key, global) {
                if (!key || !_hasItemFn(key, global)) {
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
             * @param {Boolean} [global=false] true omits prefix, defines the cookie "globally"
             * @return {Boolean}
             * @memberof cookieStorage
             **/
            setItem: function (key, value, end, path, domain, is_secure, global) {
                if (!key || key === 'expires' || key === 'max-age' || key === 'path' || key === 'domain' || key === 'secure') {
                    return false;
                }

                var sExpires = "",
                    store_end = _storeEnd(value, end);
                if (store_end._end !== null) {
                    sExpires = "; expires=" + (new Date(store_end._end * 1000)).toGMTString();
                }

                if (store_end.value !== null && value !== undefined && value !== null) {
                    domain = (domain = typeof domain === 'string' ? escape(domain) : _cookieStorage.DEFAULT_DOMAIN) ? '; domain=' + domain : '';
                    path   = (path   = typeof path   === 'string' ? escape(path)   : _cookieStorage.DEFAULT_PATH)   ? '; path=' + path : '';
                    document.cookie = escape((global ? '' : cookie_prefix) + key) + '=' + escape(value) + sExpires + domain + path + (is_secure ? '; secure' : '');

                    _updateLength();
                    return true;
                }

                return _removeItemFn(key, domain, path, is_secure, global);
            },

            /**
             * Get a cookie by name
             * @param {String} key
             * @param {String} [path]
             * @param {String} [domain]
             * @param {Boolean} [is_secure]
             * @param {Boolean} [global=false] Omits prefix.
             * @memberof cookieStorage
             */
            removeItem: function (key, domain, path, is_secure, global) {
                if (!key || !_hasItemFn(key, global)) {
                    return;
                }

                domain = (domain = typeof domain === 'string' ? escape(domain) : _cookieStorage.DEFAULT_DOMAIN) ? '; domain=' + domain : '';
                path   = (path   = typeof path   === 'string' ? escape(path)   : _cookieStorage.DEFAULT_PATH)   ? '; path=' + path : '';
                document.cookie = escape((global ? '' : cookie_prefix) + key) + '=; expires=' + _expire + domain + path + (is_secure ? '; secure' : '');

                _updateLength();
            },

            /**
             * Returns true if a cookie with that name was found, false otherwise
             * @param {String} key
             * @param {Boolean} [global=false] Omits prefix.
             * @param {Boolean}
             * @memberof cookieStorage
             */
            hasItem: function (key, global) {
                return (new RegExp('(?:^|;) *' + escape((global ? '' : cookie_prefix) + key) + '=')).test(document.cookie);
            }
        },
        // Keep backups of these functions, as they may be overriden by _assignPrefix
            _removeItemFn = _cookieStorage.removeItem,
            _hasItemFn = _cookieStorage.hasItem;

        /**
         * Updates cookieStorage.length on update
         * @private
         */
        function _updateLength() {
            _cookieStorage.length = _cookieStorage.getAll().length;
        }

        _cookieStorage.setItem(_TESTID, 4);
        if (_cookieStorage.getItem(_TESTID) == 4) {
            _cookieStorage.removeItem(_TESTID);
            return _assignPrefix(_cookieStorage);
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
            /** @const String STORE_TYPE
             * @default "memoryStorage"
             * @memberof memoryStorage */
            STORE_TYPE: 'memoryStorage',

            /** Variable # of items in storage
             * @const int length
             * @memberof memoryStorage */
            length: 0,

            /**
             * Get key name by id
             * @param {int} i
             * @return {String|null}
             * @memberof memoryStorage
             */
            key: function (i) {
                return _keys[i];
            },

            /**
             * Get an item
             * @param {String} key
             * @return {*}
             * @memberof memoryStorage
             */
            getItem: function (key) {
                return _checkEnd(_data[key] && _data[key]._end, _data[key], _memoryStorage.removeItem, key);
            },

            /**
             * Set an item
             * @param {String} key
             * @param {String} data
             * @param {String|Number|Date} [end]
             * @memberof memoryStorage
             */
            setItem: function (key, data, end) {
                if (data !== null && data !== undefined) {
                    _ikey[key] === undefined && (_ikey[key] = (_memoryStorage.length = _keys.push(key)) - 1);
                    return (_data[key] = _storeEnd(data, end)).value;
                }
                return _memoryStorage.removeItem(key);
            },

            /**
             * Removes an item
             * @param {String} key
             * @return {Boolean}
             * @memberof memoryStorage
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
             * @memberof memoryStorage
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
                 * @private
                 */
                decode: function (key, data) {
                    return this.encode(key, data);
                },

                /** RC4.encode(key:String, data:String):String
                 * @description     encode a data string using provided key
                 * @param   {String} key    key to use for this encoding
                 * @param   {String} data   data to encode
                 * @return  {String}        encoded data. Will require same key to be decoded
                 * @private
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
                 * @private
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
         * Cannot be accessed directly, and in fact appears as Storer.sessionStorage when in use.
         * You can, however, know that it is in use when sessionStorage.STORE_TYPE === 'name'.
         * @namespace nameStorage
         */
            _nameStorage = {
                /** @const String STORE_TYPE
                 * @default "name"
                 * @memberof nameStorage */
                STORE_TYPE: 'name',

                /** Number of items in storage */
                length: 0,

                /**
                 * Get an item key by its index
                 * @param {int} index
                 * @return {String|null} key
                 */
                key: function (index) {
                    return _dataArray[index];
                },

                /**
                 * Get an item by its key
                 * @param {String} key
                 * @return {String|null} data
                 */
                getItem: function (key) {
                    return _checkEnd(_dataObject[key] && _dataObject[key]._end, _dataObject[key], _removeItemFn, key);
                },

                /**
                 * Set an item by key
                 * @param {String} key
                 * @param {String} data
                 * @param {String|Number|Date} [end]
                 */
                setItem: function (key, data, end) {
                    var store_end = _storeEnd(data, end)._end;

                    if (store_end.value === null) {
                        return _removeItemFn(key);
                    }

                    if (_dataObject[key]) {
                        // Update an existing key's value
                        _dataObject[key].value = data;
                        _dataObject[key]._end = store_end._end;
                    } else {
                        // Store this item by its key
                        _dataObject[key] = {
                            value: data,
                            // For new items, increment the length property
                            index: (_nameStorage.length = _dataArray.push(key)) - 1,
                            _end: store_end._end
                        };
                    }

                    if (!store_end._end) {
                        // Save some space
                        delete _dataObject[key]._end;
                    }

                    is_opera && _write();

                    return data;
                },

                /**
                 * Remove an item by key
                 * @param {String} key
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
            },
            // Keep backups of these functions, as they may be overriden by _assignPrefix
            _removeItemFn = _nameStorage.removeItem;

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
    if (callback) {
        // Create a callback wrapper to empty expired data preemptively
        callback = (function (callback) {
            return function () {
                callback(_returnable);
                setTimeout(_clearExpired, 100); // delay expiration
            };
        }(callback));
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
     * @mixes localStorage
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
                // jshint -W055
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
                    _sessionStorage = _adjustHTML5Storage(_createDOMStorage('sessionStorage', _sessionStorage));
                } else if (!_tmp || Object.prototype.toString.apply(Storage.prototype) === '[object StoragePrototype]') {
                    // Safari throws a type error when extending with Storage
                    _sessionStorage = _adjustHTML5Storage(_createReferencedStorage('sessionStorage', _sessionStorage));
                } else {
                    _sessionStorage = _adjustHTML5Storage(_tmp);
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

        // cookieStorage already calls _assignPrefix
        return _sessionStorage.STORE_TYPE === 'cookieStorage' ? _sessionStorage : _assignPrefix(_sessionStorage);
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
                // jshint -W055
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
                    _localStorage = _adjustHTML5Storage(_createDOMStorage('localStorage', _localStorage));
                } else if (!_tmp || Object.prototype.toString.apply(Storage.prototype) === '[object StoragePrototype]') {
                    // Safari throws a type error when extending with Storage
                    _localStorage = _adjustHTML5Storage(_createReferencedStorage('localStorage', _localStorage));
                } else {
                    // Spec
                    _localStorage = _adjustHTML5Storage(_tmp);
                }
            } catch (e) {
                _localStorage = null;
            }
        }

        // Did not work, try alternatives...
        // Try userData first
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
                            /**
                             * Cannot be accessed directly, and in fact appears as Storer.localStorage when in use.
                             * You can, however, know that it is in use when localStorage.STORE_TYPE === 'userData'.
                             * @namespace userDataStorage */
                            userData = {
                                /** @const String STORE_TYPE
                                 * @default "userData"
                                 * @memberof userDataStorage */
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
                                 * @return {*}
                                 */
                                getItem: function (key) {
                                    var esckey = _esc(key);
                                    return _checkEnd(el.getAttribute('_end_' + esckey), el.getAttribute(esckey), _removeItemFn, key);
                                },

                                /**
                                 * Sets key to data
                                 * @param {String} key
                                 * @param {String} data
                                 * @param {String|Number|Date} [end]
                                 */
                                setItem: function (key, data, end) {
                                    if (data !== null && data !== undefined) {
                                        var esckey = _esc(key),
                                            store_end = _storeEnd(data, end);
                                        if (store_end.value !== null) {
                                            el.setAttribute(esckey, data);
                                            if (!store_end._end) {
                                                // Save some space
                                                el.removeAttribute('_end_' + esckey);
                                            } else {
                                                el.setAttribute('_end_' + esckey, "" + store_end._end);
                                            }
                                            _ikey[key] === undefined && (_ikey[key] = (userData.length = _keys.push(key)) - 1);
                                            el.save(_PREFIX + _NAME);
                                            return (_data[key] = store_end.value);
                                        }
                                    }
                                    return _removeItemFn(key);
                                },

                                /**
                                 * Removes item at key
                                 * @param {String} key
                                 */
                                removeItem: function (key) {
                                    var esckey = _esc(key);
                                    el.removeAttribute(esckey);
                                    el.removeAttribute('_end_' + esckey);
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
                            },
                            // Keep backups of these functions, as they may be overriden by _assignPrefix
                            _removeItemFn = userData.removeItem,
                            _hasItemFn = userData.hasItem;

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
                                _returnable.localStorage = localStorage = _localStorage = NO_COOKIE_FALLBACK ? _createMemoryStorage() : _createCookieStorage('localStorage');
                                callback && callback(_returnable);
                            }
                        });

                        return userData;
                    } catch (e) {}
                }
            }());
        }
        if (!_localStorage) {
            // Try cookie or memory
            _localStorage = NO_COOKIE_FALLBACK ? _createMemoryStorage() : _createCookieStorage('localStorage');
        }

        // cookieStorage already calls _assignPrefix
        return _localStorage.STORE_TYPE === 'cookieStorage' ? _localStorage : _assignPrefix(_localStorage);
    }());

    _callbackNow && callback && callback(_returnable);

    return _returnable;
}

window.mediaWiki = window.mediaWiki || {};
mediaWiki.flow = mediaWiki.flow || {};
mediaWiki.flow.vendor = mediaWiki.flow.vendor || {};
mediaWiki.flow.vendor.initStorer = initStorer;