/*!
 *
 */

window.mw = window.mw || {}; // mw-less testing

( function ( mw, $ ) {
	mw.flow = mw.flow || {}; // create mw.flow globally

	var apiTransformMap = {
		// Map of API submodule name, block name, and prefix name
		'moderate-post': [ 'topic_', 'mp' ],
		'new-topic': [ 'topiclist_', 'nt' ],
		'edit-header': [ 'header_' , 'eh' ],
		'edit-post': [ 'topic_', 'ep' ],
		'reply': [ 'topic_', 'rep' ],
		'moderate-topic': [ 'topic_', 'mt' ],
		'edit-title': [ 'topic_', 'et' ],
		// 'close-open-topic': [ 'topic_', 'cot' ], // @todo this is both topic and topicsummary
		'topiclist-view': [ 'topiclist_', 'vtl' ],
		'post-view': [ 'topic', 'vp' ],
		'topic-view': [ 'topic', 'vt' ],
		'header-view': [ 'header_', 'vh' ],
		'topic-summary-view': [ 'topicsummary_', 'vts' ],
		'edit-topic-summary': [ 'topicsummary_', 'ets' ]
	};

	/**
	 * Handles Flow API calls. Each FlowComponent has its own instance of FlowAPI as component.API,
	 * so that it can store a workflowId and pageName permanently for simplicity.
	 * @param {String} [workflowId]
	 * @param {String} [pageName]
	 * @returns {FlowAPI}
	 * @constructor
	 */
	function FlowAPI( storageEngine, workflowId, pageName ) {
		this.StorageEngine = storageEngine;
		this.workflowId = workflowId;
		this.pageName = pageName;

		/**
		 * Makes the actual API call and returns
		 * @param {Object|String} [params] May be a JSON object string
		 * @param {String} [pageName]
		 * @returns {$.Deferred}
		 */
		function flowApiCall( params, method ) {
			params = params || {};
			// Server is using page instead of title
			params.page = params.page || this.pageName || mw.config.get( 'wgPageName' );
			// Don't pass invalid workflowid to server
			if ( !params.workflow && this.workflowId && this.workflowId.substring( 0, 15 ) !== 'flow-generated-') {
				params.workflow = this.workflowId;
			}
			method = method ? method.toUpperCase() : 'GET';

			var $deferred = $.Deferred(),
				mwApi = new mw.Api();

			if ( !params.action ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing action string', arguments );
				return $deferred.rejectWith({ error: 'Invalid action' });
			}
			if ( !params.page ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing page string', [ mw.config.get( 'wgPageName' ) ], arguments );
				return $deferred.rejectWith({ error: 'Invalid title' });
			}
			// A workflow is not required for brand new board, but check to make sure
			//if ( !params.workflow ) {
			//	mw.flow.debug( '[FlowAPI] apiCall error: missing workflow ID', arguments );
			//	return $deferred.rejectWith({ error: 'Invalid workflow' });
			//}

			if ( method === 'POST' ) {
				return mwApi.postWithToken( 'edit', params );
			} else if ( method !== 'GET' ) {
				return $deferred.rejectWith({ error: "Unknown submission method: " + method });
			} else {
				return mwApi.get( params );
			}
		}

		this.apiCall = flowApiCall;
	}

	/** @type {Storer} */
	FlowAPI.prototype.StorageEngine = null;
	/** @type {String} */
	FlowAPI.prototype.pageName = null;
	/** @type {String} */
	FlowAPI.prototype.workflowId = null;
	/** @type {String} */
	FlowAPI.prototype.defaultSubmodule = null;

	/**
	 * Sets the fixed pageName for this API instance.
	 * @param {String} pageName
	 */
	function flowApiSetPageName( pageName ) {
		this.pageName = pageName;
	}

	FlowAPI.prototype.setPageName = flowApiSetPageName;

	/**
	 * Sets the fixed workflowId for this API instance.
	 * @param {String} workflowId
	 */
	function flowApiSetWorkflowId( workflowId ) {
		this.workflowId = workflowId;
	}

	FlowAPI.prototype.setWorkflowId = flowApiSetWorkflowId;

	/**
	 * Transforms URL request parameters into API params
	 * @todo fix it server-side so we don't need this client-side
	 * @param {Object} queryMap
	 * @returns {Object}
	 */
	function flowApiTransformMap( queryMap ) {
		var key,
			map = apiTransformMap[queryMap.submodule];
		if ( !map ) {
			return queryMap;
		}
		for ( key in queryMap ) {
			if ( queryMap.hasOwnProperty( key ) ) {
				if ( key.indexOf( map[0] ) === 0 ) {
					queryMap[ key.replace( map[0], map[1] ) ] = queryMap[ key ];
					delete queryMap[ key ];
				}
				if ( key.indexOf( 'flow_' ) === 0 ) {
					queryMap[ key.replace( 'flow_', map[1] ) ] = queryMap[ key ];
					delete queryMap[ key ];
				}
			}
		}

		return queryMap;
	}


	/**
	 * Sets the fixed defaultSubmodule for this API instance.
	 * @param {String} defaultSubmodule
	 */
	function flowApiSetDefaultSubmodule( defaultSubmodule ) {
		this.defaultSubmodule = defaultSubmodule;
	}

	FlowAPI.prototype.setDefaultSubmodule = flowApiSetDefaultSubmodule;

	/**
	 * With a url (a://b.c/d?e=f&g#h) will return an object of key-value pairs ({e:'f', g:''}).
	 * @param {String} url
	 * @param {Object} [queryMap]
	 * @returns {Object}
	 */
	function flowApiGetQueryMap( url, queryMap ) {
		var query,
			queries, i = 0, split;

		queryMap = queryMap || {};

		// Parse the URL query params
		query = url.split( '#' )[ 0 ].split( '?' ).slice( 1 ).join( '?' );
		if ( query ) {
			for ( queries = query.split( /&(?:amp;)?/gi ); i < queries.length; i++ ) {
				split = queries[ i ].split( '=' );

				if ( split[ 0 ] === 'action' ) {
					// Submodule is the action
					split[ 0 ] = 'submodule';
				}
				if ( split[ 0 ] === 'title' ) {
					// Server is using page
					split[ 0 ] = 'page';
				}

				queryMap[ split[ 0 ] ] = split.slice( 1 ).join( '=' ); // if extra = are present
			}
		}

		// Use the default submodule if no action in URL
		queryMap.submodule = queryMap.submodule || this.defaultSubmodule;
		// Default action is flow
		queryMap.action = queryMap.action || 'flow';

		// Use the API map to transform this data if necessary, eg.
		return flowApiTransformMap( queryMap );
	}

	FlowAPI.prototype.getQueryMap = flowApiGetQueryMap;

	/**
	 * Using a given form, parses its action, serializes the data, and sends it as GET or POST depending on form method.
	 * With button, its name=value is serialized in. If button is an Event, it will attempt to find the clicked button.
	 * Additional params can be set with data-flow-api-params on both the clicked button or the form.
	 * @param {Event|Element} [button]
	 * @param {Object|Function} [override]
	 * @return {$.Deferred}
	 */
	function flowApiRequestFromForm( button, override ) {
		var i,
			$deferred = $.Deferred(),
			$form = $( button ).closest( 'form' ),
			method = $form.attr( 'method' ) || 'GET',
			formData = $form.serializeArray(),
			url = $form.attr( 'action' ),
			queryMap = { submodule: $form.data( 'flow-api-action' ) }; // usually null


		if ( $form.length === 0 ) {
			return $deferred.rejectWith( { error: 'No form located' } );
		}

		for ( i = 0; i < formData.length; i++ ) {
			// skip wpEditToken, its handle independantly
			if ( formData[i].name !== 'wpEditToken' ) {
				queryMap[formData[i].name] = formData[i].value;
			}
		}

		if ( !( queryMap = this.getQueryMap( url, queryMap ) ) ) {
			return $deferred.rejectWith( { error: 'Invalid form action' } );
		}

		if ( override ) {
			switch ( typeof override ) {
				// If given an override object, extend our queryMap with it
				case 'object':
					$.extend( queryMap, override );
					break;
				// If given an override function, call it and make it return the new queryMap
				case 'function':
					queryMap = override( queryMap );
					break;
			}
		}

		if ( !( queryMap.action ) ) {
			return $deferred.rejectWith( { error: 'Unknown action for form' } );
		}

		return this.apiCall( queryMap, method );
	}

	FlowAPI.prototype.requestFromForm = flowApiRequestFromForm;

	/**
	 * Using a given anchor, parses its URL and sends it as a GET (default) or POST depending on data-flow-api-method.
	 * Additional params can be set with data-flow-api-params.
	 * @param {Element} anchor
	 * @param {Object|Function} [override]
	 * @return {$.Deferred}
	 */
	function flowApiRequestFromAnchor( anchor, override ) {
		var $anchor = $( anchor ),
			$deferred = $.Deferred(),
			queryMap = { submodule: $anchor.data( 'flow-api-action' ) }, // usually null
			prevApiCall, newApiCall;

		// This method only works on anchors with HREF
		if ( !$anchor.is( 'a' ) ) {
			mw.flow.debug( '[FlowAPI] requestFromAnchor error: not an anchor', arguments );
			return $deferred.rejectWith( { error: 'Not an anchor' } );
		}

		// Build the query map from this anchor's HREF
		if ( !( queryMap = this.getQueryMap( anchor.href, queryMap ) ) ) {
			mw.flow.debug( '[FlowAPI] requestFromAnchor error: invalid href', arguments );
			return $deferred.rejectWith( { error: 'Invalid href' } );
		}

		if ( override ) {
			switch ( typeof override ) {
				// If given an override object, extend our queryMap with it
				case 'object':
					$.extend( queryMap, override );
					break;
				// If given an override function, call it and make it return the new queryMap
				case 'function':
					queryMap = override( queryMap );
					break;
			}
		}

		// If this anchor already has a request in flight, abort it
		prevApiCall = $anchor.data( 'flow-api-query-temp-' + queryMap.action + '-' + queryMap.submodule );
		if ( prevApiCall ) {
			if ( prevApiCall.abort ) {
				prevApiCall.abort();
			}
			mw.flow.debug( '[FlowAPI] apiCall abort request in flight: ' + 'flow-api-query-temp-' + queryMap.action + '-' + queryMap.submodule, arguments );
		}

		// Make the request
		newApiCall = this.apiCall( queryMap, 'GET' );

		// Store this request on the node if it needs to be aborted
		$anchor.data(
			'flow-api-query-temp-' + queryMap.action + '-' + queryMap.submodule,
			newApiCall
		);

		// Remove the request on success
		newApiCall.always( function () {
			$anchor.removeData( 'flow-api-query-temp-' + queryMap.action + '-' + queryMap.submodule );
		} );

		// Return jqXHR
		return newApiCall;
	}

	FlowAPI.prototype.requestFromAnchor = flowApiRequestFromAnchor;

	// Export
	mw.flow.FlowAPI = FlowAPI;
}( mw, jQuery ) );
