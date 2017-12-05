mw.flowExperimental.ui.ReplyWidget = function MwFlowReplyWidget( config ) {
	config = config || {};

	// Parent constructor
	mw.flowExperimental.ui.ReplyWidget.parent.call( this, config );

	this.input = new OO.ui.MultilineTextInputWidget( {
		placeholder: config.postTitle ? 'Reply to ' + config.postTitle : ''
	} );
	this.submitButton = new OO.ui.ButtonWidget( {
		label: 'Submit',
		flags: [ 'constructive', 'primary' ]
	} );
	this.cancelButton = new OO.ui.ButtonWidget( {
		label: 'Cancel',
		flags: [ 'destructive' ]
	} );

	this.submitButton.connect( this, { click: [ 'emit', 'submit' ] } );
	this.cancelButton.connect( this, { click: [ 'emit', 'cencel' ] } );

	this.$element
		.addClass( 'mw-flow-ui-replyWidget' )
		.append(
			this.input.$element,
			this.submitButton.$element,
			this.cancelButton.$element
		);
};

/* Initialization */

OO.inheritClass( mw.flowExperimental.ui.ReplyWidget, OO.ui.Widget );

mw.flowExperimental.ui.ReplyWidget.prototype.getInputValue = function () {
    return this.input.getValue();
};

mw.flowExperimental.ui.ReplyWidget.prototype.destroy = function () {
	this.disconnect( this );
	this.$element.detach();
};
