/*!
 *
 */

window.mw = window.mw || {}; // mw-less testing

( function ( mw, $ ) {
	mw.flow = mw.flow || {}; // create mw.flow globally

	// @todo this stuff
	var _apiCache = {},
		_apiQueue = [],
	// Stores information about most API requests, and helps in completing the request data
		actionInfo = {
			'new-topic': {
				write: true
			}
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
		 * @param {String} action
		 * @param {Object|String} [params] May be a JSON object string
		 * @param {String} [pageName]
		 * @param {String} [workflowId]
		 * @returns {$.Deferred}
		 */
		function flowApiCall( action, params, pageName, workflowId ) {
			params = params || {};
			pageName = pageName || this.pageName || mw.config.get( 'wgPageName' );
			workflowId = workflowId || this.workflowId;

			var $deferred = $.Deferred(),
				mwApi = new mw.Api();

			if ( !action ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing action', arguments );
				return $deferred.rejectWith({ error: 'Missing action' });
			}
			if ( !pageName ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing pageName', [mw.config.get( 'wgPageName' )], arguments );
				return $deferred.rejectWith({ error: 'Missing pageName' });
			}
			if ( !workflowId ) {
				mw.flow.debug( '[FlowAPI] apiCall error: missing workflowId', arguments );
				return $deferred.rejectWith({ error: 'Missing workflowId' });
			}

			return mwApi.get( {
				'action'       : 'query',
				'list'         : 'flow',
				'flowpage'     : pageName || '',
				'flowworkflow' : workflowId || '',
				'flowaction'   : action || 'view',
				'flowparams'   : typeof params === 'string' ? params : $.toJSON( params )
			} );
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
	 * With a url (a://b.c/d?e=f&g#h) will return an object of key-value pairs ({e:'f', g:''}).
	 * @param {String} url
	 * @returns {Object}
	 */
	function flowApiGetQueryMap( url ) {
		var query,
			queryMap = {},
			queries, i = 0, split;

		query = url.split('#')[0].split('?').slice(1).join('?');
		if ( query ) {
			for ( queries = query.split(/&(?:amp;)?/gi); i < queries.length; i++ ) {
				split = queries[i].split('=');
				queryMap[split[0]] = split.slice(1).join('='); // if extra = are present
			}
		}

		return queryMap;
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
	function flowApiRequestFromForm( button ) {
		var queryMap,
			$deferred = $.Deferred(),
			$form = button.closest( 'form' ),
			method = $form.attr( 'method' ) || 'GET',
			formData = $form.serializeArray(),
			url = $form.attr( 'action' );


		if ( $form.length === 0 ) {
			return $deferred.rejectWith( { error: 'No form located' } );
		}

		if ( !( queryMap = flowApiGetQueryMap( url ) ) ) {
			return $deferred.rejectWith( { error: 'Invalid form action' } );
		}

		// join the query map into formData
		$.extend( formData, queryMap );

		if ( !( queryMap.action ) ) {
			return $deferred.rejectWith( { error: 'Unknown action for form' } );
		}

		// @todo this issues a GET, but we want `method`
		return this.apiCall( queryMap.action, queryMap );
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

		if ( !( queryMap = flowApiGetQueryMap( anchor.href ) ) ) {
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
		return this.apiCall( 'view', queryMap );
	}

	FlowAPI.prototype.requestFromAnchor = flowApiRequestFromAnchor;

	// Export
	mw.flow.FlowAPI = FlowAPI;
}( mw, jQuery ) );
