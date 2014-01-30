( function ( $, mw ) {

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
				.get(
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
		 * @return {jQuery.Deferred}
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
						deferredObject.reject( 'invalid-result', 'Unable to understand the API result' );
						return;
					}

					$.each( output.query.flow, function ( index, block ) {
						// Looping through each block
						if ( block['block-name'] === blockName ) {
							// Return this block
							deferredObject.resolve( block );
						}
					} );

					deferredObject.reject( 'invalid-result', 'Unable to find the '+
						blockName+' block in the API result' );
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
		'readTopicList' : function ( pageName, workflowId, options ) {
			return mw.flow.api.readBlock( pageName, workflowId, 'topic_list', options );
		},

		/**
		 * @param {string} pageName
		 * @param {string} topicId
		 * @param {object} options API parameters
		 * @return {jQuery.Deferred}
		 */
		'readTopic' : function ( pageName, topicId, options ) {
			return mw.flow.api.readBlock( pageName, topicId, 'topic', options );
		},

		'readHeader' : function( pageName, workflowId, options ) {
			return mw.flow.api.readBlock( pageName, workflowId, 'header', options );
		},

		generateTopicAction: function( actionName, parameterList, promiseFilterCallback ) {
			var params = $.makeArray( arguments ),
				innerAction;
			params.unshift( 'topic' );
			innerAction = mw.flow.api.generateBlockAction.apply( this, params );

			return function( topicId ) {
				var actionParams = $.makeArray( arguments );
				topicId = actionParams.shift();
				actionParams.unshift( { 'workflow' : topicId } );
				return innerAction.apply( this, actionParams );
			};
		},

		/**
		 * @param {string} actionName
		 * @param {object} parameterList
		 * @param {function} promiseFilterCallback
		 * @return {function}
		 */
		'generateBlockAction' : function ( blockName, actionName, parameterList, promiseFilterCallback ) {
			return function ( workflowSpec ) {
				var deferredObject = $.Deferred(),
					requestParams = {},
					paramIndex = 1,
					requestArguments = arguments,
					newDeferredObject,
					realParams = {};

				$.each( parameterList, function ( key, value ) {
					requestParams[value] = requestArguments[paramIndex];
					paramIndex++;
				} );

				realParams[blockName] = requestParams;

				mw.flow.api.executeAction(
					workflowSpec,
					actionName,
					realParams,
					true
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
						!data.flow[actionName].result[blockName]
					) {
						deferredObject.reject( 'invalid-result', 'Unable to find appropriate section in result' );
						return;
					}
					output = data.flow[actionName].result[blockName];

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
		}
	}
};

// Define some higher-level functions using mw.flow.api "primitives".
mw.flow.api.newTopic = mw.flow.api.generateBlockAction(
	'topic_list',
	'new-topic',
	[
		'topic',
		'content'
	]
);

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

mw.flow.api.editHeader = mw.flow.api.generateBlockAction(
	'header',
	'edit-header',
	[
		'content',
		'prev_revision'
	]
);

mw.flow.api.moderatePost = mw.flow.api.generateTopicAction(
	'moderate-post',
	[
		'postId',
		'moderationState',
		'reason'
	]
);

mw.flow.api.moderateTopic = mw.flow.api.generateTopicAction(
	'moderate-topic',
	[
		'moderationState',
		'reason'
	]
);

// random onclick handler moved from html template
mw.flow.notImplemented = function() {
	alert( '@todo: Not yet implemented!' );
	return false;
};

// Simple flow exception
mw.flow.exception = function( code, data ) {
	this.code = code;
	this.data = data;
};

mw.flow.exception.prototype.getCode = function() {
	return this.code;
};

mw.flow.exception.prototype.getData = function() {
	return this.data;
};

mw.flow.exception.prototype.getErrorInfo = function() {
	return [this.code, this.data];
};

} )( jQuery, mediaWiki );
