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
		'topic-summary-view': [ 'topicsummary_', 'vts' ]
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
		function flowApiCall( params, pageName ) {
			params = params || {};
			params.title = params.title || this.pageName || mw.config.get( 'wgPageName' );
			params.workflow = params.workflow || this.workflowId;

			var $deferred = $.Deferred(),
				mwApi = new mw.Api();

			if ( !params.action || params.action !== 'flow' ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing action string, or action string !== "flow"', arguments );
				return $deferred.rejectWith({ error: 'Invalid action' });
			}
			if ( !params.title ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing title string', [ mw.config.get( 'wgPageName' ) ], arguments );
				return $deferred.rejectWith({ error: 'Invalid title' });
			}
			if ( !params.workflow ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing workflow ID', arguments );
				return $deferred.rejectWith({ error: 'Invalid workflow' });
			}

			return mwApi.get(
				params
			);
		}

		this.apiCall = flowApiCall;
	}

	/** @type {Storer} */
	FlowAPI.prototype.StorageEngine = null;
	/** @type {String} */
	FlowAPI.prototype.pageName = null;
	/** @type {String} */
	FlowAPI.prototype.workflowId = null;

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
		var map = apiTransformMap[ queryMap.submodule ];
		if ( !map ) {
			return queryMap;
		}
		for ( var key in queryMap ) {
			if ( queryMap.hasOwnProperty( key ) ) {
				if ( key.indexOf( map[0] ) === 0 ) {
					queryMap[ key.replace( map[0], map[1] ) ] = queryMap[ key ];
					delete queryMap[ key ];
				}
			}
		}

		return queryMap;
	}

	/**
	 * With a url (a://b.c/d?e=f&g#h) will return an object of key-value pairs ({e:'f', g:''}).
	 * @param {String} url
	 * @returns {Object}
	 */
	function flowApiGetQueryMap( url ) {
		var query,
			queryMap = {},
			queries, i = 0, split;

		// Parse the URL query params
		query = url.split('#')[0].split('?').slice(1).join('?');
		if ( query ) {
			for ( queries = query.split(/&(?:amp;)?/gi); i < queries.length; i++ ) {
				split = queries[i].split('=');
				queryMap[split[0]] = split.slice(1).join('='); // if extra = are present
			}
		}

		// Submodule is the action
		queryMap.submodule = queryMap.action;
		// and the API action is always flow
		queryMap.action    = 'flow';

		// Use the API map to transform this data if necessary, eg.
		return flowApiTransformMap( queryMap );
	}

	FlowAPI.prototype.getQueryMap = flowApiGetQueryMap;

	/**
	 * Using a given form, parses its action, serializes the data, and sends it as GET or POST depending on form method.
	 * With button, its name=value is serialized in. If button is an Event, it will attempt to find the clicked button.
	 * Additional params can be set with data-flow-api-params on both the clicked button or the form.
	 * @param {Element} form
	 * @param {Event|Element} [button]
	 * @return {$.Deferred}
	 */
	function flowApiRequestFromForm( form, button ) {
		var method = method || 'get',
			formData = form ? $( form ).serializeArray() : [];
	}

	FlowAPI.prototype.requestFromForm = flowApiRequestFromForm;

	/**
	 * Using a given anchor, parses its URL and sends it as a GET (default) or POST depending on data-flow-api-method.
	 * Additional params can be set with data-flow-api-params.
	 * @param {Element} anchor
	 * @return {$.Deferred}
	 */
	function flowApiRequestFromAnchor( anchor ) {
		var $anchor = $( anchor ),
			$deferred = $.Deferred(),
			queryMap;

		if ( !$anchor.is( 'a' ) ) {
			mw.flow.debug( '[FlowAPI] requestFromAnchor error: not an anchor', arguments );
			return $deferred.rejectWith( { error: 'Not an anchor' } );
		}

		if ( !( queryMap = this.getQueryMap( anchor.href ) ) ) {
			mw.flow.debug( '[FlowAPI] requestFromAnchor error: invalid href', arguments );
			return $deferred.rejectWith( { error: 'Invalid href' } );
		}

		//?action=query
		//&format=json
		//&list=flow
		//&flowpage=Talk:Flow
		//&flowworkflow=rj3tafdbsz4kqu4a
		//&flowaction=view
		//&flowparams=
		/*{
			"topiclist": {
				"offset-dir": "fwd",
				"offset-id": "rkqk4sf0rt7q3856",
				"limit": 10,
				"render": true
			}
		}
		*/
		return this.apiCall( queryMap );
	}

	FlowAPI.prototype.requestFromAnchor = flowApiRequestFromAnchor;

	// Export
	mw.flow.FlowAPI = FlowAPI;
}( mw, jQuery ) );
