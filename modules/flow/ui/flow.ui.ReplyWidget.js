/**
 * Reply widget.
 *
 * A form for replying to a post.
 *
 * @param {Object} config Configuration options
 */
flow.ui.ReplyWidget = function FlowUiReplyWidget( config ) {
	// Parent constructor
	OO.ui.Widget.call( this, config );

	// Initialization
	this.$element.addClass( 'flow-ui-replyWidget' );
};

/* Inheritance */

OO.inheritClass( flow.ui.ReplyWidget, OO.ui.Widget );

/* Methods */
