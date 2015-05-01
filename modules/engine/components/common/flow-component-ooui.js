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

		// Page title
		this.title = mw.Title.newFromText( mw.config.get( 'wgPageName' ) );

		// New topic save button
		this.widgets['flow-newtopic-save'] = OO.ui.infuse( 'flow-newtopic-save' );
		this.widgets['flow-newtopic-save'].connect( this, { click: this.onNewTopicSaveButtonClick } );

		// New topic cancel
		this.widgets['flow-newtopic-cancel'] = OO.ui.infuse( 'flow-newtopic-cancel' );
		this.widgets['flow-newtopic-cancel'].connect( this, { click: this.onCancelNewTopicButtonClick } );

		// TODO: When these two fields are OOUI'fied, change the events to 'change'
		$( '.flow-newtopic-form .flow-editor textarea' ).on( 'change keypress keyup', this.onNewTopicFieldsChange.bind( this ) );
		$( '.flow-newtopic-form input[name=topiclist_topic]' ).on( 'change keypress keyup', this.onNewTopicFieldsChange.bind( this ) );

		// Remove the form action
		this.widgets['flow-newtopic-save'].$element.closest( 'form' ).attr( 'action', '' );
	};
	OO.initClass( mw.flow.ooui );

	/**
	 * Respond to change in any of the new topic form inputs
	 */
	mw.flow.ooui.prototype.onNewTopicFieldsChange = function () {
		var $textarea = $( '.flow-newtopic-form .flow-editor textarea' ),
			$subject = $( '.flow-newtopic-form input[name=topiclist_topic]' );

		this.widgets['flow-newtopic-save'].setDisabled(
			$textarea.val().length === 0 ||
			$subject.val().length === 0
		);
	};

	/**
	 * Respond to new topic cancel button click and add a topic
	 */
	mw.flow.ooui.prototype.onCancelNewTopicButtonClick = function () {
		var $form = $( '.flow-newtopic-form' ),
			$button = this.widgets[ 'flow-newtopic-cancel' ].$element,
			$textarea = $form.find( '.flow-editor textarea' ),
			$subject = $form.find( 'input[name=topiclist_topic]' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form );

		if ( $button.length ) {
			// Clear contents to not trigger the "are you sure you want to
			// discard your text" warning
			$subject.val( '' );
			$textarea.val( $textarea.defaultValue );
			// $form.find( 'textarea, :text' ).each( function() {
			// 	$( this ).val( this.defaultValue );
			// } );

			// remove focus - title input field may still have focus
			// (submitted via enter key), which it needs to lose:
			// the form will only re-activate if re-focused
			document.activeElement.blur();

			// Shrink the form
			$textarea.addClass( 'flow-input-compressed' );
			if ( mw.flow.editor && mw.flow.editor.exists( $textarea ) ) {
				mw.flow.editor.destroy( $textarea );
			}
			// Run loadHandlers
			flowBoard.emitWithReturn( 'makeContentInteractive', $( '.flow-component' ) );//$newtopic );

		}
	};

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
					$form.find( 'input[name=topiclist_topic]' ).val( '' );

					$target = $stub.find( 'div' );
					$target.replaceWith( $newtopic );

					// Run loadHandlers
					flowBoard.emitWithReturn( 'makeContentInteractive', $( '.flow-component' ) );//$newtopic );
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
