( function ( $, mw ) {
$( function () {
mw.flow = {
	'api' : {
		/**
		 * Execute a Flow action, fires API call.
		 *
		 * @param {object} workflowParam API parameters
		 * @param {string} action Action performed
		 * @param {object} options Additional API parameters (API key 'params')
		 * @param {bool} render
		 * @return {Deferred}
		 */
		'executeAction' : function ( workflowParam, action, options, render ) {
			var api = new mw.Api(),
				deferredObject = $.Deferred();

			api
				.post(
					{
						'action' : 'tokens',
						'type' : 'flow'
					}
				)
				.done( function ( data ) {
					var request = {
						'action' : 'flow',
						'flowaction' : action,
						'params' : $.toJSON( options ),
						'token' : data.tokens.flowtoken
					};

					request = $.extend( request, workflowParam );

					if ( render ) {
						request.render = true;
					}

					api.post( request )
						.done( function () {
							deferredObject.resolve.apply( this, arguments );
						} )
						.fail( function () {
							deferredObject.reject.apply( this, arguments );
						} );
				} )
				.fail( function () {
					deferredObject.reject.apply( this, arguments );
				} );

			return deferredObject.promise();
		},

		/**
		 * @param {string} pageName
		 * @param {string} workflowId
		 * @param {object} options API parameters
		 * @return {Deferred}
		 */
		'read' : function ( pageName, workflowId, options ) {
			var api = new mw.Api();

			return api.get(
				{
					'action' : 'query',
					'list' : 'flow',
					'flowpage' : pageName,
					'flowworkflow' : workflowId,
					'flowparams' : $.toJSON( options )
				}
			);
		},

		/**
		 * @param {string} pageName
		 * @param {string} topicId
		 * @param {string} blockName
		 * @param {object} options API parameters
		 * @return {Deferred}
		 */
		'readBlock' : function ( pageName, topicId, blockName, options ) {
			var deferredObject = $.Deferred();

			mw.flow.api.read( pageName, topicId, options )
				.done( function ( output ) {
					// Immediate failure modes
					if (
						!output.query ||
						!output.query.flow ||
						output.query.flow._element !== 'block'
					) {
						deferredObject.fail( 'invalid-result', 'Unable to understand the API result' );
						return;
					}

					$.each( output.query.flow, function ( index, block ) {
						// Looping through each block
						if ( block['block-name'] === blockName ) {
							// Return this block
							deferredObject.resolve( block );
						}
					} );

					deferredObject.fail( 'invalid-result', 'Unable to find the '+
						blockName+' block in the API result' );
				} )
				.fail( function () {
					deferredObject.fail( arguments );
				} );

			return deferredObject.promise();
		},

		/**
		 * @param {string} pageName
		 * @param {string} workflowId
		 * @param {object} options API parameters
		 * @return {Deferred}
		 */
		'readTopicList' : function ( pageName, workflowId, options ) {
			return mw.flow.api.readBlock( pageName, workflowId, 'topic_list', options );
		},

		/**
		 * @param {string} pageName
		 * @param {string} topicId
		 * @param {object} options API parameters
		 * @return {Deferred}
		 */
		'readTopic' : function ( pageName, topicId, options ) {
			return mw.flow.api.readBlock( pageName, topicId, 'topic', options );
		},

		'readSummary' : function( pageName, workflowId, options ) {
			return mw.flow.api.readBlock( pageName, workflowId, 'summary', options );
		},

		/**
		 * @param {string} actionName
		 * @param {object} parameterList
		 * @param {function} promiseFilterCallback
		 * @return {function}
		 */
		'generateTopicAction' : function ( actionName, parameterList, promiseFilterCallback ) {
			return function ( workflowId ) {
				var deferredObject = $.Deferred(),
					requestParams = {},
					paramIndex = 1,
					requestArguments = arguments,
					newDeferredObject;

				$.each( parameterList, function ( key, value ) {
					requestParams[value] = requestArguments[paramIndex];
					paramIndex++;
				} );

				mw.flow.api.executeAction(
					{
						'workflow' : workflowId
					},
					actionName,
					{ 'topic' :
						requestParams
					}, true
				).done( function ( data ) {
					var output;

					if ( data.flow[actionName].errors ) {
						deferredObject.reject( 'block-errors', data.flow[actionName].errors );
						return;
					}

					if (
						!data.flow ||
						!data.flow[actionName] ||
						!data.flow[actionName].result ||
						!data.flow[actionName].result.topic
					) {
						deferredObject.reject( 'invalid-result', 'Unable to find appropriate section in result' );
						return;
					}
					output = data.flow[actionName].result.topic;

					deferredObject.resolve( output, data );
				} )
				.fail( function () {
					deferredObject.reject.apply( this, arguments );
				} );

				if ( promiseFilterCallback ) {
					newDeferredObject = promiseFilterCallback( deferredObject.promise() );
					if ( newDeferredObject ) {
						deferredObject = newDeferredObject;
					}
				}

				return deferredObject.promise();
			};
		},

		/**
		 * @param {object} workflowParam
		 * @param {string} title
		 * @param {string} content
		 * @return {Deferred}
		 */
		'newTopic' : function ( workflowParam, title, content ) {
			var deferredObject = $.Deferred();

			mw.flow.api.executeAction(
				workflowParam,
				'new-topic',
				{ 'topic_list' :
					{
						'topic' : title,
						'content' : content
					}
				}, true
			).done( function ( data ) {
				var output;

				if ( data.flow['new-topic'].errors ) {
					deferredObject.reject( 'block-errors', data.flow['new-topic'].errors );
					return;
				}

				if (
					!data.flow ||
					!data.flow['new-topic'] ||
					!data.flow['new-topic'].result ||
					!data.flow['new-topic'].result.topic_list
				) {
					deferredObject.reject( 'invalid-result', 'Unable to find appropriate section in result' );
					return;
				}
				output = data.flow['new-topic'].result.topic_list;

				deferredObject.resolve( output, data );
			} )
			.fail( function () {
				deferredObject.reject.apply( this, arguments );
			} );

			return deferredObject.promise();
		}
	}
};

/**
 * @param {string} workflowId
 * @return {Deferred}
 */
mw.flow.api.reply = mw.flow.api.generateTopicAction(
	'reply',
	[
		'replyTo',
		'content'
	]
);

/**
 * @param {string} workflowId
 * @return {Deferred}
 */
mw.flow.api.changeTitle = mw.flow.api.generateTopicAction(
	'edit-title',
	[
		'content'
	]
);

/**
 * @param {string} workflowId
 * @return {Deferred}
 */
mw.flow.api.editPost = mw.flow.api.generateTopicAction(
	'edit-post',
	[
		'postId',
		'content'
	]
);
} );
} )( jQuery, mediaWiki );
