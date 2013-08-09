( function($, mw) {
$( function() {
mw.flow = {
	'api' : {
		'executeAction' : function( workflowParam, action, options, render ) {
			var api = new mw.Api();
			var deferredObject = $.Deferred();

			api.post(
				$.extend(
					{
						'action' : 'flow',
						'flowaction' : action,
						'gettoken' : 'gettoken'
					},
					workflowParam
				)
			)
				.done( function(data) {
					request = {
						'action' : 'flow',
						'flowaction' : action,
						'params' : $.toJSON( options ),
						'token' : data.flow.token
					};

					request = $.extend( request, workflowParam );

					if ( render ) {
						request.render = true;
					}

					api.post( request )
						.done( function( ) {
							deferredObject.resolve.apply( this, arguments );
						} )
						.fail( function( ) {
							deferredObject.reject.apply( this, arguments );
						} );
				} )
				.fail(function( ) {
					deferredObject.reject.apply( this, arguments);
				} );

			return deferredObject.promise();
		},

		'read' : function( pageName, workflowId, options ) {
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

		'generateTopicAction' : function( actionName, parameterList, promiseFilterCallback ) {
			return function( workflowId ) {
				var deferredObject = $.Deferred();

				var requestParams = {};
				var paramIndex = 1;
				var requestArguments = arguments;

				$.each( parameterList, function( key, value ) {
					requestParams[value] = requestArguments[paramIndex];
					paramIndex++;
				} );

				mw.flow.api.executeAction(
					{
						'workflow' : workflowId
					},
					actionName,
					{ 'topic_list' :
						requestParams
					}, true
				).done( function(data) {
					if ( data.flow[actionName].errors ) {
						deferredObject.reject( 'block-errors', data.flow['reply'].errors );
						return;
					}

					if (
						! data.flow ||
						! data.flow[actionName] ||
						! data.flow[actionName].result ||
						! data.flow[actionName].result['topic_list']
					) {
						deferredObject.reject( 'invalid-result', 'Unable to find appropriate section in result' );
						return;
					}
					var output = data.flow[actionName].result['topic_list'];

					deferredObject.resolve( output, data );
				} )
				.fail( function( ) {
					deferredObject.reject.apply( this, arguments );
				} );

				if ( promiseFilterCallback ) {
					var newDeferredObject = promiseFilterCallback( deferredObject.promise() );
					if ( newDeferredObject ) {
						deferredObject = newDeferredObject;
					}
				}

				return deferredObject.promise();
			};
		},

		'newTopic' : function( workflowParam, title, content ) {
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
			).done( function(data) {
				if ( data.flow['new-topic'].errors ) {
					deferredObject.reject( 'block-errors', data.flow['new-topic'].errors );
					return;
				}

				if (
					! data.flow ||
					! data.flow['new-topic'] ||
					! data.flow['new-topic'].result ||
					! data.flow['new-topic'].result['topic_list']
				) {
					deferredObject.reject( 'invalid-result', 'Unable to find appropriate section in result' );
					return;
				}
				var output = data.flow['new-topic'].result['topic_list'];

				deferredObject.resolve( output, data );
			} )
			.fail( function( ) {
				deferredObject.reject.apply( this, arguments );
			} );

			return deferredObject.promise();
		}
	}
};

// Now add all the individual actions
mw.flow.api.reply = mw.flow.api.generateTopicAction(
	'reply',
	[
		'replyTo',
		'content'
	]
);

mw.flow.api.changeTitle = mw.flow.api.generateTopicAction(
	'edit-title',
	[
		'content'
	]
);
});
})( jQuery, mediaWiki );