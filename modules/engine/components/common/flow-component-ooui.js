/*!
 * Contains flow-menu functionality.
 */

( function ( $, mw ) {
	/**
	 * Initializes and handles the ooui components
	 *
	 * @this FlowComponentOoui
	 * @constructor
	 */
	mw.flow.ooui = function FlowComponentOoui( config ) {
		config = config || {};

		this.widgets = {};
		this.title = mw.Title.newFromText( mw.config.get( 'wgPageName' ) );

		// New topic save button
		this.widgets['flow-newtopic-save'] = OO.ui.infuse( 'flow-newtopic-save' );
		this.widgets['flow-newtopic-save'].$element.closest( 'form' ).attr( 'action', '' );
		this.widgets['flow-newtopic-save'].connect( this, { click: this.onNewTopicSaveButtonClick } );
	};
	OO.initClass( mw.flow.ooui );

	mw.flow.ooui.prototype.onNewTopicSaveButtonClick = function () {
		var api = new mw.Api(),
//			me = this,
			$form = $( '.flow-newtopic-form' ),
			$target = $form.closest( 'div.flow-board' ),
			$textarea = $form.find( '.flow-editor textarea' ),
			// TODO: Work with actual data model instead of fetching
			// data from the DOM
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form ),
			topic = $form.find( 'input[name=topiclist_topic]' ).val(),
			content = mw.flow.editor.getRawContent( $textarea ),
			format = mw.flow.editor.getFormat( $textarea ) || 'wikitext',
			buttonData = this.widgets['flow-newtopic-save'].getData();
//			workflowId = buttonData.args[1];

		// Post new topic
		// TODO: Use mw.flow.messagePoster here instead
		return api.post( {
			action: 'flow',
			submodule: 'new-topic',
			page: this.title.getPrefixedDb(),
			nttopic: topic,
			ntcontent: content,
			ntformat: format,
			token: buttonData.args[0] // editToken
		} )
			.then( function ( result ) {
				console.log( result.flow['new-topic'].workflow );
//				debugger;
				return flowBoard.Api.apiCall( {
					action: 'flow',
					submodule: 'view-topic',
					// Flow topic title, in Topic:<topicId> format (2600 is topic namespace id)
					// TODO: Use mw.Title.newFromText with a promise rejection fallback
					page: ( new mw.Title( result.flow['new-topic'].workflow, 2600 ) ).getPrefixedDb() // me.title.getPrefixedDb()
				} );
				// return api.get( {
				// 	action: 'flow',
				// 	submodule: 'view-topic',
				// 	// Flow topic title, in Topic:<topicId> format (2600 is topic namespace id)
				// 	// TODO: Use mw.Title.newFromText with a promise rejection fallback
				// 	page:  me.title.getPrefixedDb() //( new mw.Title( workflowId, 2600 ) ).getPrefixedDb()
				// } );
			} )
			.then(
				function ( result ) {
					// Update view of the full topic
					var $replacement = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
						'flow_topiclist_loop.partial',
						result.flow['view-topic'].result.topic
					) ).children();

					$target.replaceWith( $replacement );
					// Run loadHandlers
					flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );
				},
				function ( code, result ) {
					var errorMsg = flowBoard.constructor.static.getApiErrorMessage( code, result );
					errorMsg = mw.msg( 'flow-error-fetch-after-open-lock', errorMsg );

					flowBoard.emitWithReturn( 'removeError', $target );
					flowBoard.emitWithReturn( 'showError', $target, errorMsg );
				}
			);
	};

	mw.flow.ooui.prototype.sendApiRequest = function () {
	};

}( jQuery, mediaWiki ) );
