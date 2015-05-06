/**
 * Flow Topic
 *
 * @constructor
 *
 * @extends mw.flow.dm.Item
 * @mixins mw.flow.dm.List
 *
 * @param {string} id Topic Id
 * @param {Object} data API data to build topic with
 * @param {Object} [config] Configuration options
 */
mw.flow.dm.Topic = function mwFlowDmTopic( id, data, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.dm.Topic.super.call( this, config );

	// Mixin constructor
	mw.flow.dm.List.call( this, config );
	mw.flow.dm.RevisionedContent.call( this, config );

	this.setId( id );
	this.populate( data );

	// Configuration
	this.highlighted = !!config.highlighted;
	this.stub = true;

	// Store comparable hash
	this.storeComparableHash();
};

/* Initialization */

OO.inheritClass( mw.flow.dm.Topic, mw.flow.dm.Item );
OO.mixinClass( mw.flow.dm.Topic, mw.flow.dm.List );
OO.mixinClass( mw.flow.dm.Topic, mw.flow.dm.RevisionedContent );

/**
 * Get a hash object representing the current state
 * of the Topic
 *
 * @return {Object} Hash object
 */
mw.flow.dm.Topic.prototype.getHash = function () {
	return $.extend(
		{
			stub: this.isStub(),
			moderated: this.isModerated(),
			moderationReason: this.getModerationReason(),
			moderationState: this.getModerationState(),
			moderator: this.getModerator()
		},
		// Mixin
		mw.flow.dm.RevisionedContent.call( this )
	);
};

/**
 * Populate the topic information from API data.
 *
 * @param {Object} data API data
 */
mw.flow.dm.Topic.prototype.populate = function ( data ) {
	// Parent method
	mw.flow.dm.RevisionedContent.prototype.populate.call( this, data );

	this.summary = data.summary && data.summary.revision && data.summary.revision.content;

	this.moderated = !!data.isModerated;
	this.moderationReason = data.moderateReason;
	this.moderationState = data.moderateState;
	this.moderator = data.moderator;

	// TODO: These should be added as dm.Post objects
	this.replies = data.replies;
};

/**
 * Check if a topic is a stub
 * @return {Boolean} Topic is a stub
 */
mw.flow.dm.Topic.prototype.isStub = function () {
	return this.stub;
};

/**
 * Unstub a topic when all available information exists on it
 */
mw.flow.dm.Topic.prototype.unStub = function () {
	this.stub = false;
	this.emit( 'stub', false );
};

/**
 * Check if topic is moderated
 * @return {boolean} Topic is moderated
 */
mw.flow.dm.Topic.prototype.isModerated = function () {
	return this.moderated;
};

/**
 * Toggle the moderated state of a topic
 * @param {boolean} [moderate] Moderate the topic
 * @fires moderated
 */
mw.flow.dm.Topic.prototype.toggleModerated = function ( moderate ) {
	this.moderated = moderate || !this.moderated;
	if ( !this.moderated ) {
		this.setModerationReason( '' );
	}
	this.emit( 'moderated', this.moderated );
};

/**
 * Get topic moderation reason
 *
 * @return {string} Moderation reason
 */
mw.flow.dm.Topic.prototype.getModerationReason = function () {
	return this.moderationReason;
};

/**
 * Set topic moderation reason
 *
 * @return {string} Moderation reason
 */
mw.flow.dm.Topic.prototype.setModerationReason = function ( reason ) {
	this.moderationReason = reason;
};

/**
 * Get topic moderation state
 *
 * @return {string} Moderation state
 */
mw.flow.dm.Topic.prototype.getModerationState = function () {
	return this.moderationState;
};

/**
 * Set topic moderation state
 *
 * @return {string} Moderation state
 */
mw.flow.dm.Topic.prototype.setModerationReason = function ( state ) {
	this.moderationState = state;
};

/**
 * Get topic moderator
 *
 * @return {mw.User} Moderator
 */
mw.flow.dm.Topic.prototype.getModerator = function () {
	return this.moderator;
};

/**
 * Get topic moderator
 *
 * @param {mw.User} mod Moderator
 */
mw.flow.dm.Topic.prototype.setModerator = function ( mod ) {
	this.moderator = mod;
};

/**
 * Get the topic summary
 *
 * @return {string} Topic summary
 */
mw.flow.dm.Topic.prototype.getSummary = function () {
	return this.summary;
};

/**
 * Get the topic summary
 *
 * @param {string} Topic summary
 * @fires summary
 */
mw.flow.dm.Topic.prototype.setSummary = function ( summary ) {
	this.summary = summary;
	this.emit( 'summary', this.summary );
};
