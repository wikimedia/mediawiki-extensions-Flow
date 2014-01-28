/**
 * Flow Topic.
 *
 * @extends flow.ui.List
 *
 * @param {flow.dm.Topic} model Topic model
 * @param {Object} [config] Configuration options
 */
flow.ui.Topic = function FlowUiTopic( model, config ) {
	// Parent constructor
	flow.ui.List.call( this, model, config );

	// Properties
	this.$header = this.$( '<div>' );
	this.$title = this.$( '<div>' );
	this.$participants = this.$( '<div>' );
	this.$timestamp = this.$( '<div>' );
	this.$tally = this.$( '<div>' );
	this.replyButton = new OO.ui.ButtonWidget( { '$': this.$, 'label': 'reply' } );

	// Events
	this.model.connect( this, { 'change': 'setup' } );

	// Initialization
	this.$title.addClass( 'flow-ui-topic-title' );
	this.$participants.addClass( 'flow-ui-topic-participants' );
	this.$timestamp.addClass( 'flow-ui-topic-timestamp' );
	this.$tally.addClass( 'flow-ui-topic-tally' );
	this.$header
		.addClass( 'flow-ui-topic-header' )
		.append( this.$title, this.$participants, this.$timestamp, this.$tally );
	this.$group.addClass( 'flow-ui-topic-posts' );
	this.$element
		.addClass( 'flow-ui-topic' )
		.append( this.$header, this.$group, this.replyButton.$element );
	this.setup();
};

/* Inheritance */

OO.inheritClass( flow.ui.Topic, flow.ui.List );

/* Static Properties */

flow.ui.Topic.static.itemConstructor = flow.ui.Post;

/* Methods */

/**
 * Setup topic.
 */
flow.ui.Topic.prototype.setup = function () {
	this.$title.text( this.model.getTitle() );
	this.$participants.text( this.model.getParticipants().join( ', ' ) );
	this.$timestamp.text( this.model.getTimestamp() );
	this.$tally.text( this.model.getItemCount() + ' comments ' );
};
