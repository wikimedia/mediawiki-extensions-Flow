/**
 * Flow RevisionedContent mixin
 * Must be mixed into an element that already mixes in OO.EventEmitter
 *
 * @mixin
 * @abstract
 * @constructor
 *
 * @param {Object} [config] Configuration options
 */
mw.flow.dm.RevisionedContent = function FlowRevisionedContent( config ) {
	// Configuration initialization
	config = config || {};

	// Initialize properties
	this.content = null;
	this.contentFormat = null;
	this.author = null;
	this.creator = null;
	this.lastUpdated = null;
	this.timestamp = null;
	this.articleTitle = null;
	this.changeType = null;
	this.workflowId = null;
	this.revisionId = null;
	this.previousRevisionId = null;
	this.originalContent = null;
	this.links = null;
	this.actions = null;
	this.watched = null;
	this.watchable = null;
	this.lastEditId = null;
	this.lastEditUser = null;
};

/**
 * Get a hash object representing the current state
 * of the Topic
 *
 * @return {Object} Hash object
 */
mw.flow.dm.RevisionedContent.prototype.getHash = function () {
	return {
		id: this.getId(),

		content: this.getContent(),
		contentFormat: this.getContentFormat(),
		author: this.getAuthor(),
		creator: this.getCreator(),
		lastUpdated: this.getLastUpdate(),
		timestamp: this.getTimestamp(),
		articleTitle: this.getArticleTitle(),
		changeType: this.getChangeType(),

		workflowId: this.getWorkflowId(),
		revisionId: this.getRevisionId(),
		previousRevisionId: this.getPreviousRevisionId(),
		originalContent: this.isOriginalContent(),
		watched: this.isWatched(),
		watchable: this.isWatchable()
	};
};

/**
 * Populate the revision object with available data.
 * @param {Object} data API data
 */
mw.flow.dm.RevisionedContent.prototype.populate = function ( data ) {
	data.content = data.content || {};

	this.content = data.content.content;
	this.contentFormat = data.content.format;
	this.author = data.author;
	this.creator = data.creator;
	this.lastUpdated = data.last_updated;
	this.timestamp = data.timestamp;

	this.articleTitle = data.articleTitle;

	this.changeType = data.changeType;
	this.workflowId = data.workflowId;
	this.revisionId = data.revisionId;
	this.previousRevisionId = data.previousRevisionId;
	this.originalContent = !!data.isOriginalContent;

	this.setLastEditId( data.lastEditId );
	this.setLastEditUser( data.lastEditUser );

	this.links = data.links;
	this.actions = data.actions;

	this.watched = !!data.isWatched;
	this.watchable = data.watchable;
};

/**
 * Get revision author
 *
 * @return {string} Revision author
 */
mw.flow.dm.RevisionedContent.prototype.getAuthor = function () {
	return this.author;
};

/**
 * Set revision author
 *
 * @param {string} author Revision author
 */
mw.flow.dm.RevisionedContent.prototype.setAuthor = function ( author ) {
	this.author = author;
};

/**
 * Get revision creator
 *
 * @return {string} Revision creator
 */
mw.flow.dm.RevisionedContent.prototype.getCreator = function () {
	return this.creator;
};

/**
 * Set revision creator
 *
 * @param {string} creator Revision creator
 */
mw.flow.dm.RevisionedContent.prototype.setCreator = function ( creator ) {
	this.creator = creator;
};

/**
 * Get content
 *
 * @return {string} Content; can be in wikitext, html, fixed-html
 * or plaintext format.
 */
mw.flow.dm.RevisionedContent.prototype.getContent = function () {
	return this.content;
};

/**
 * Set content
 *
 * @param {string} content Content
 */
mw.flow.dm.RevisionedContent.prototype.setContent = function ( content ) {
	this.content = content;
};

/**
 * Get content format
 *
 * @return {string} Content format: 'wikitext', 'html', 'fixed-html'
 * or 'plaintext'.
 */
mw.flow.dm.RevisionedContent.prototype.getContentFormat = function () {
	return this.contentFormat;
};

/**
 * Set content format
 *
 * @param {string} contentFormat Content format
 */
mw.flow.dm.RevisionedContent.prototype.setContent = function ( contentFormat ) {
	this.contentFormat = contentFormat;
};

/**
 * Get topic last update
 *
 * @return {string} Topic last update
 */
mw.flow.dm.RevisionedContent.prototype.getLastUpdate = function () {
	return this.lastUpdate;
};

/**
 * Set topic last update
 *
 * @param {string} lastUpdate Topic last update
 */
mw.flow.dm.RevisionedContent.prototype.setLastUpdate = function ( lastUpdate ) {
	this.lastUpdate = lastUpdate;
};

/**
 * Get revsion timestamp
 *
 * @return {number} Topic timestamp
 */
mw.flow.dm.RevisionedContent.prototype.getTimestamp = function () {
	return this.timestamp;
};

/**
 * Set revision timestamp
 *
 * @param {number} timestamp Topic timestamp
 */
mw.flow.dm.RevisionedContent.prototype.setTimestamp = function ( timestamp ) {
	this.timestamp = timestamp;
};

/**
 * Get revision article title
 *
 * @return {string} Article title
 */
mw.flow.dm.RevisionedContent.prototype.getArticleTitle = function () {
	return this.articleTitle;
};

/**
 * Get revision article title
 *
 * @param {string} title Article title
 */
mw.flow.dm.RevisionedContent.prototype.setArticleTitle = function ( title ) {
	this.articleTitle = title;
};

/**
 * Get the revision action
 *
 * @return {Object} Revision actions
 */
mw.flow.dm.RevisionedContent.prototype.getActions = function () {
	return this.actions;
};

/**
 * Set the revision action
 *
 * @param {Object} actions Revision actions
 */
mw.flow.dm.RevisionedContent.prototype.setActions = function ( actions ) {
	return this.actions;
};

/**
 * Get the revision change type
 *
 * @return {string} Revision change type
 */
mw.flow.dm.RevisionedContent.prototype.getChangeType = function () {
	return this.changeType;
};

/**
 * Get the revision id
 *
 * @return {string} Revision Id
 */
mw.flow.dm.RevisionedContent.prototype.getRevisionId = function () {
	return this.revisionId;
};

/**
 * Set the revision id
 *
 * @param {string} id Revision Id
 */
mw.flow.dm.RevisionedContent.prototype.setRevisionId = function ( id ) {
	this.revisionId = id;
};
/**
 * Get the revision id
 *
 * @return {string} Previous revision Id
 */
mw.flow.dm.RevisionedContent.prototype.getPreviousRevisionId = function () {
	return this.previousRevisionId;
};

/**
 * Set the previous revision id
 *
 * @param {string} id Previous revision Id
 */
mw.flow.dm.RevisionedContent.prototype.setPreviousRevisionId = function ( id ) {
	this.previousRevisionId = id;
};

/**
 * Get the workflow id
 *
 * @return {string} Workflow Id
 */
mw.flow.dm.RevisionedContent.prototype.getWorkflowId = function () {
	return this.workflowId;
};

/**
 * Set the workflow id
 *
 * @param {string} id Workflow Id
 */
mw.flow.dm.RevisionedContent.prototype.setWorkflowId = function ( id ) {
	this.workflowId = id;
};

/**
 * Get the last edit id
 *
 * @return {string} Last edit id
 */
mw.flow.dm.RevisionedContent.prototype.getLastEditId = function () {
	return this.lastEditId;
};

/**
 * Set the last edit id
 *
 * @param {string} id Last edit id
 */
mw.flow.dm.RevisionedContent.prototype.setLastEditId = function ( id ) {
	this.lastEditId = id;
};

/**
 * Get the last edit user
 *
 * @return {Object} Last edit user
 */
mw.flow.dm.RevisionedContent.prototype.getLastEditUser = function () {
	return this.lastEditUser;
};

/**
 * Set the last edit user
 *
 * @param {Object} user Last edit user
 */
mw.flow.dm.RevisionedContent.prototype.setLastEditUser = function ( user ) {
	this.lastEditUser = user;
};

/**
 * Check topic watched status
 *
 * @return {boolean} Revision is watched
 */
mw.flow.dm.RevisionedContent.prototype.isWatched = function () {
	return this.watched;
};

/**
 * Toggle the watched state of a revision
 *
 * @param {boolean} [watch] Revision is watched
 * @fires watched
 */
mw.flow.dm.RevisionedContent.prototype.toggleWatched = function ( watch ) {
	this.watched = watch || !this.watched;

	this.emit( 'watched', this.watched );
};

/**
 * Check topic originalContent status
 *
 * @return {boolean} Revision is original
 */
mw.flow.dm.RevisionedContent.prototype.isOriginalContent = function () {
	return this.originalContent;
};

/**
 * Toggle the watched state of a revision
 *
 * @param {boolean} [watch] Revision is original
 * @fires originalContent
 */
mw.flow.dm.RevisionedContent.prototype.toggleOriginalContent = function ( originalContent ) {
	this.originalContent = originalContent || !this.originalContent;
};

/**
 * Check topic watchable status
 *
 * @return {boolean} Topic is watchable
 */
mw.flow.dm.RevisionedContent.prototype.isWatchable = function () {
	return this.watchable;
};

/**
 * Toggle the watchable state of a topic
 * @param {boolean} [watchable] Topic is watchable
 * @fires watchable
 */
mw.flow.dm.RevisionedContent.prototype.toggleWatchable = function ( watchable ) {
	this.watchable = watchable || !this.watchable;

	this.emit( 'watchable', this.watchable );
};
