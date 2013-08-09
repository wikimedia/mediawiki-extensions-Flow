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
		},

		'reply' : function( workflowId, replyTo, content ) {
			var deferredObject = $.Deferred();

			mw.flow.api.executeAction(
				{
					'workflow' : workflowId
				},
				'reply',
				{ 'topic_list' :
					{
						'replyTo' : replyTo,
						'content' : content
					}
				}, true
			).done( function(data) {
				if ( data.flow['reply'].errors ) {
					deferredObject.reject( 'block-errors', data.flow['reply'].errors );
					return;
				}

				if (
					! data.flow ||
					! data.flow.reply ||
					! data.flow.reply.result ||
					! data.flow.reply.result['topic_list']
				) {
					deferredObject.reject( 'invalid-result', 'Unable to find appropriate section in result' );
					return;
				}
				var output = data.flow.reply.result['topic_list'];

				deferredObject.resolve( output, data );
			} )
			.fail( function( ) {
				deferredObject.reject.apply( this, arguments );
			} );

			return deferredObject.promise();
		}
	}
};
});
})( jQuery, mediaWiki );