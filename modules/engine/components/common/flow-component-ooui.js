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

	/**
	 * Respond to topic save button click and add a topic
	 */
	mw.flow.ooui.prototype.onNewTopicSaveButtonClick = function () {
		var api = new mw.Api(),
			$form = $( '.flow-newtopic-form' ),
			$boardTopics = $form.closest( 'div.flow-board .flow-topics' ),
			$textarea = $form.find( '.flow-editor textarea' ),
			// TODO: Work with actual data model instead of fetching
			// data from the DOM
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form ),
			topic = $form.find( 'input[name=topiclist_topic]' ).val(),
			content = mw.flow.editor.getRawContent( $textarea ),
			format = mw.flow.editor.getFormat( $textarea ) || 'wikitext',
			buttonData = this.widgets['flow-newtopic-save'].getData();

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
				return flowBoard.Api.apiCall( {
					action: 'flow',
					submodule: 'view-topic',
					page: result.flow['new-topic'].committed.topiclist['topic-page']
				} );
			} )
			.then(
				// Success
				function ( result ) {
					// TODO: OOUIfy the entire thing!
					// Update view of the full topic
					var schemaName = $( this ).data( 'flow-eventlog-schema' ),
						funnelId = $( this ).data( 'flow-eventlog-funnel-id' ),
						$stub = $( '<div class="flow-topic"><div></div></div>' ).prependTo( flowBoard.$container.find( '.flow-topics' ) ),
						$target = $stub.closest( '.flow-topic' ),
						$newtopic = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
						'flow_topiclist_loop.partial',
						result.flow['view-topic'].result.topic
					) ).children();

					flowBoard.logEvent( schemaName, { action: 'save-success', funnelId: funnelId } );

					flowBoard.emitWithReturn( 'cancelForm', $( this ).closest( 'form' ) );

					// remove focus - title input field may still have focus
					// (submitted via enter key), which it needs to lose:
					// the form will only re-activate if re-focused
					document.activeElement.blur();

					$target = $stub.find( 'div' );
					$target.replaceWith( $newtopic );
					// Run loadHandlers
					flowBoard.emitWithReturn( 'makeContentInteractive', $newtopic );
				},
				// Failure
				function ( code, result ) {
					var errorMsg = flowBoard.constructor.static.getApiErrorMessage( code, result );
					errorMsg = mw.msg( 'flow-error-fetch-after-open-lock', errorMsg );

					flowBoard.emitWithReturn( 'removeError', $boardTopics );
					flowBoard.emitWithReturn( 'showError', $boardTopics, errorMsg );
				}
			);
	};

}( jQuery, mediaWiki ) );
