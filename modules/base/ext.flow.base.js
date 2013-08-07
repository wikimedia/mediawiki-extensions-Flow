( function($, mw) {
$( function() {
mw.flow = {
	'api' : {
		'executeAction' : function( workflowId, action, options ) {
			var api = new mw.Api();

			return api.post( {
				'action' : 'flow',
				'workflow' : workflowId,
				'flowaction' : action,
				'params' : $.toJSON( options )
			} );
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