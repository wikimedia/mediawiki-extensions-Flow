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

/**
 * Get a hash object representing the current state
 * of the Topic
 *
 * @return {Object} Hash object
 */
mw.flow.dm.Topic.prototype.getHash = function () {
	return {
		id: this.getId(),
		stub: this.isStub(),
		content: this.getContent(),
		timestamp: this.getTimestamp(),
		articleTitle: this.getArticleTitle(),
		author: this.getAuthor(),
		creator: this.getCreator(),
		moderated: this.isModerated(),
		moderationReason: this.getModerationReason(),
		moderationState: this.getModerationState(),
		moderator: this.getModerator(),
		watched: this.isWatched(),
		watchable: this.isWatchable(),
		lastUpdate: this.getLastUpdate(),
		summary: this.getSummary()
	};
};

/**
 * Populate the topic information from API data.
 *
 * @param {Object} data API data
 */
mw.flow.dm.Topic.prototype.populate = function ( data ) {
	this.content = data.content;
	this.timestamp = data.timestamp;
	this.articleTitle = data.articleTitle;
	this.author = data.author;
	this.creator = data.creator;

	this.summary = data.summary && data.summary.revision && data.summary.revision.content;

	this.actions = data.actions;

	// I assume this is per post, and not so relevant for a topic
	// as a whole?
	// this.maxThreadingDepth = !!data.isMaxThreadingDepth;

	this.moderated = !!data.isModerated;
	this.moderationReason = data.moderateReason;
	this.moderationState = data.moderateState;
	this.moderator = data.moderator;

	// What is this?
	// this.isOriginalContent = !!data.isOriginalContent;

	this.watched = !!data.isWatched;
	this.lastUpdate = data.last_updated;
	this.watchable = data.watchable;

	// TODO: These should be added as Reply objects
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
};

/**
 * Get topic content
 *
 * @return {string} Topic content
 */
mw.flow.dm.Topic.prototype.getContent = function () {
	return this.content;
};

/**
 * Get topic raw content
 *
 * @return {string} Topic raw content
 */
mw.flow.dm.Topic.prototype.getRawContent = function () {
	return this.content.content;
};

/**
 * Get topic timestamp
 * @return {number} Topic timestamp
 */
mw.flow.dm.Topic.prototype.getTimestamp = function () {
	return this.timestamp;
};

/**
 * Get topic article title
 * @return {string} Article title
 */
mw.flow.dm.Topic.prototype.getArticleTitle = function () {
	return this.articleTitle;
};

/**
 * Get topic author
 *
 * @return {string} Topic author
 */
mw.flow.dm.Topic.prototype.getAuthor = function () {
	return this.author;
};

/**
 * Get topic creator
 *
 * @return {string} Topic creator
 */
mw.flow.dm.Topic.prototype.getCreator = function () {
	return this.creator;
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
 * Check topic watched status
 *
 * @return {boolean} Topic is watched
 */
mw.flow.dm.Topic.prototype.isWatched = function () {
	return this.watched;
};

/**
 * Toggle the watched state of a topic
 * @param {boolean} [watch] Watch the topic
 * @fires watched
 */
mw.flow.dm.Topic.prototype.toggleWatched = function ( watch ) {
	this.watched = watch || !this.watched;

	this.emit( 'watched', this.moderated );
};

/**
 * Check topic watchable status
 *
 * @return {boolean} Topic is watchable
 */
mw.flow.dm.Topic.prototype.isWatchable = function () {
	return this.watchable;
};

/**
 * Toggle the watchable state of a topic
 * @param {boolean} [watchable] Topic is watchable
 * @fires watchable
 */
mw.flow.dm.Topic.prototype.toggleWatchable = function ( watchable ) {
	this.watchable = watchable || !this.watchable;

	this.emit( 'watchable', this.watchable );
};

/**
 * Get topic last update
 *
 * @return {string} Topic last update
 */
mw.flow.dm.Topic.prototype.getLastUpdate = function () {
	return this.lastUpdate;
};

/**
 * Set topic last update
 *
 * @param {string} lastUpdate Topic last update
 */
mw.flow.dm.Topic.prototype.setLastUpdate = function ( lastUpdate ) {
	this.lastUpdate = lastUpdate;
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

/**
 * Get the topic action
 *
 * @return {object} Topic actions
 */
mw.flow.dm.Topic.prototype.getActions = function () {
	return this.actions;
};

/**
 * Get the comparable hash
 *
 * @return {object} Hash
 */
mw.flow.dm.Topic.prototype.getComparableHash = function () {
	return this.comparableHash;
};

/**
 * Store a new comparable hash
 *
 * @param {object} [hash] New hash
 */
mw.flow.dm.Topic.prototype.storeComparableHash = function ( hash ) {
	this.comparableHash = hash || $.extend( {}, this.getHash() );
};

/**
 * Check whether the topic changed since we last saved a comparable hash
 * @return {boolean} Topic has changed
 */
mw.flow.dm.Topic.prototype.hasBeenChanged = function () {
	return !OO.compare( this.comparableHash, this.getHash() );
};
