( function($, mw) {
$( function() {
mw.flow = {
	'api' : {
		'executeAction' : function( workflowId, action, options, render ) {
			var api = new mw.Api();
			var deferredObject = $.Deferred();

			api.post(
				{
					'action' : 'flow',
					'workflow' : workflowId,
					'flowaction' : action,
					'gettoken' : 'gettoken'
				}
			)
				.done( function(data) {
					request = {
						'action' : 'flow',
						'workflow' : workflowId,
						'flowaction' : action,
						'params' : $.toJSON( options ),
						'token' : data.flow.token
					};

					if ( render ) {
						request.render = true;
					}

					api.post( request )
						.done( function( data, textStatus, xhr ) {
							deferredObject.resolve( data, textStatus, xhr );
						} )
						.fail( function( xhr, textStatus, errorThrown ) {
							deferredObject.reject( xhr, textStatus, errorThrown );
						} );
				} )
				.fail(function( xhr, textStatus, errorThrown ) {
					deferredObject.reject( xhr, textStatus, errorThrown );
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

		'newTopic' : function( workflowId, title, content ) {
			var deferredObject = $.Deferred();

			mw.flow.api.executeAction(
				workflowId,
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
			.fail( function(arg1, arg2, arg3 ) {
				deferredObject.reject( arg1, arg2, arg3 );
			} );

			return deferredObject.promise();
		},

		'reply' : function( workflowId, replyTo, content ) {
			var deferredObject = $.Deferred();

			mw.flow.api.executeAction(
				workflowId,
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
			.fail( function(arg1, arg2, arg3 ) {
				deferredObject.reject( arg1, arg2, arg3 );
			} );

			return deferredObject.promise();
		}
	}
};
});
})( jQuery, mediaWiki );