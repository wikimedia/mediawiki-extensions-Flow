( function($, mw) {
$( function() {
mw.flow = {
	'api' : {
		'executeAction' : function( workflowId, action, options ) {
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
					api.post( {
						'action' : 'flow',
						'workflow' : workflowId,
						'flowaction' : action,
						'params' : $.toJSON( options ),
						'token' : data.flow.token
					} )
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
		}
	}
};
});
})( jQuery, mediaWiki );