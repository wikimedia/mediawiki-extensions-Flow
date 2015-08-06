( function ( $ ) {
	/**
	 * Flow reply widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {string} topicId The id of the topic this reply belongs to
	 * @param {string} replyTo The id this reply is a child of
	 * @param {Object} [config] Configuration object
	 */
	mw.flow.ui.NewTopicWidget = function mwFlowUiNewTopicWidget( page, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.NewTopicWidget.parent.call( this, config );

		this.page = page;
		this.expanded = false;

		this.title = new OO.ui.TextInputWidget( {
			placeholder: mw.msg( 'flow-newtopic-content-placeholder', this.page ),
			multiline: false,
			classes: [ 'flow-ui-newTopicWidget-title' ]
		} );

		this.editor = new mw.flow.ui.EditorWidget( {
			placeholder: mw.msg( 'flow-newtopic-content-placeholder' ),
			saveMsgKey: 'flow-newtopic-save',
			classes: [ 'flow-ui-newTopicWidget-editor' ]
		} );
		this.editor.toggle( false );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-newTopicWidget-error flow-error errorbox' ]
		} );
		this.error.toggle( false );

		this.api = new mw.flow.dm.APIHandler( this.page );

		// Events
		this.editor.connect( this, {
			saveContent: 'onEditorSave',
			cancel: 'onEditorCancel'
		} );
		this.title.$element.on( 'focusin', this.onTitleFocusIn.bind( this ) );

		this.$element
			.addClass( 'flow-ui-newTopicWidget' )
			.append(
				this.error.$element,
				this.title.$element,
				this.editor.$element
			);

	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.NewTopicWidget, OO.ui.Widget );

	mw.flow.ui.NewTopicWidget.prototype.onTitleFocusIn = function () {
		if ( !this.isExpanded() ) {
			// Expand the editor
			this.toggleExpanded( true );
			this.editor.activate();
			this.title.focus();
		}
	};

	/**
	 * Respond to editor save event
	 *
	 * @param {string} content Content
	 * @param {string} format Content format
	 */
	mw.flow.ui.NewTopicWidget.prototype.onEditorSave = function ( content, format ) {
		var widget = this,
			title = this.title.getValue();

		this.error.toggle( false );
		this.editor.pushPending();
		this.title.pushPending();
		this.title.setDisabled( true );
		this.api.saveNewTopic( title, content, format )
			.then( function ( topicId ) {
				widget.toggleExpanded( false );
				widget.emit( 'save', topicId );
			} )
			.then( null, function ( errorCode, errorObj ) {
				var $errorMessage = $( '<span>' ).text( errorObj.error.info );
				widget.error.setLabel( $errorMessage );
				widget.error.toggle( true );
			} )
			.always( function () {
				widget.editor.popPending();
				widget.title.popPending();
				widget.title.setDisabled( false );
			} );
	};

	/**
	 * Respond to editor cancel event
	 */
	mw.flow.ui.NewTopicWidget.prototype.onEditorCancel = function () {
		// Hide the editor
		this.toggleExpanded( false );
	};

	/**
	 * Get the expanded state of the widget
	 * @return {boolean} expanded Widget is expanded
	 */
	mw.flow.ui.NewTopicWidget.prototype.isExpanded = function () {
		return this.expanded;
	};

	/**
	 * Toggle the expanded state of the widget
	 * @param {boolean} expanded Widget is expanded
	 */
	mw.flow.ui.NewTopicWidget.prototype.toggleExpanded = function ( expanded ) {
		this.expanded = expanded !== undefined ? expanded : !this.expanded;

		this.editor.toggle( this.expanded );
		// Hide errors
		this.error.toggle( false );

		if ( !this.expanded ) {
			// Reset the title
			this.title.setValue( '' );
		}
	};
}( jQuery ) );
