/**
 * Flow Post.
 *
 * @extends flow.ui.Item
 *
 * @param {flow.dm.Post} model Post model
 * @param {Object} [config] Configuration options
 */
flow.ui.Post = function FlowUiPost( model, config ) {
	// Parent constructor
	flow.ui.Item.call( this, model, config );

	// Properties
	this.$creator = this.$( '<div>' );
	this.$content = this.$( '<div>' );
	this.$timestamp = this.$( '<div>' );
	this.replyButton = new OO.ui.ButtonWidget( { '$': this.$, 'label': 'reply' } );

	// Events
	this.model.connect( this, { 'change': 'setup' } );

	// Initialization
	this.$creator.addClass( 'flow-ui-post-creator' );
	this.$content.addClass( 'flow-ui-post-content' );
	this.$timestamp.addClass( 'flow-ui-post-timestamp' );
	this.$element
		.addClass( 'flow-ui-post' )
		.append( this.$creator, this.$content, this.$timestamp, this.replyButton.$element );
	this.setup();
};

/* Inheritance */

OO.inheritClass( flow.ui.Post, flow.ui.Item );

/* Methods */

/**
 * Setup post.
 */
flow.ui.Post.prototype.setup = function () {
	this.$creator.text( this.model.getCreator() );
	this.$content.text( this.model.getContent() );
	this.$timestamp.text( this.model.getTimestamp() );
};
