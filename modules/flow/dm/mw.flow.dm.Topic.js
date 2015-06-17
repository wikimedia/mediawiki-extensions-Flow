( function ( $ ) {
	/**
	 * Flow Topic
	 *
	 * @constructor
	 *
	 * @extends mw.flow.dm.Item
	 * @mixins mw.flow.dm.List
	 *
	 * @param {string} id Topic Id
	 * @param {Object} revisionData API data to build topic with
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.dm.Topic = function mwFlowDmTopic( id, revisionData, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.dm.Topic.super.call( this, config );

		// Mixin constructor
		mw.flow.dm.List.call( this, config );

		this.setId( id );
		this.populate( revisionData );

		// Configuration
		this.highlighted = !!config.highlighted;
		this.stub = true;

		// Store comparable hash
		this.storeComparableHash();
	};

	/* Initialization */

	OO.inheritClass( mw.flow.dm.Topic, mw.flow.dm.RevisionedContent );
	OO.mixinClass( mw.flow.dm.Topic, mw.flow.dm.List );

	/* Events */

	/**
	 * Change of topic summary
	 *
	 * @event summaryChange
	 * @param {string} New summary
	 */

	/* Static methods */

	/**
	 * Get the topic revision connected to the topic id from the
	 * topiclist api response. This connects the topic id to the
	 * post id and then returns the specific available revision.
	 *
	 * @param {Object} topiclist API data for topiclist
	 * @param {string} topicId Topic id
	 * @return {Object} Revision data
	 */
	mw.flow.dm.Topic.static.getTopicRevisionFromApi = function ( topiclist, topicId ) {
		var revisionId = topiclist.posts[ topicId ] && topiclist.posts[ topicId ][ 0 ];

		return topiclist.revisions[ revisionId ];
	};

	/* Methods */

	/**
	 * Get a hash object representing the current state
	 * of the Topic
	 *
	 * @return {Object} Hash object
	 */
	mw.flow.dm.Topic.prototype.getHashObject = function () {
		return $.extend(
			{
				stub: this.isStub(),
				moderated: this.isModerated(),
				moderationReason: this.getModerationReason(),
				moderationState: this.getModerationState(),
				moderator: this.getModerator()
			},
			// Parent
			mw.flow.dm.Topic.super.prototype.getHashObject.call( this )
		);
	};

	/**
	 * Populate the topic information from API data.
	 *
	 * @param {Object} data API data
	 */
	mw.flow.dm.Topic.prototype.populate = function ( data ) {
		this.summary = OO.getProp( data, 'summary', 'revision', 'content' );

		this.setModerated( !!data.isModerated, data.moderateReason, data.moderateState, data.moderator );

		// TODO: These should be added as dm.Post objects
		this.replies = data.replies;

		// Parent method
		mw.flow.dm.RevisionedContent.prototype.populate.call( this, data );

		if ( data.replies !== undefined ) {
			this.unStub();
		}
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
	 * @private
	 */
	mw.flow.dm.Topic.prototype.unStub = function () {
		this.stub = false;
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
	 * @param {boolean} moderated Moderate the topic
	 * @fires moderated
	 */
	mw.flow.dm.Topic.prototype.setModerated = function ( moderated, moderationState, moderationReason, moderator ) {
		if ( this.moderated !== moderated ) {

			this.moderated = moderated;
			this.setModerationReason( moderationReason );
			this.setModerationState( moderationState );
			this.setModerator( moderator );

			// Emit event
			this.emit( 'moderated', this.isModerated(), this.getModerationState(), this.getModerationReason(), this.getModerator() );
		}
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
	 * @private
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
	 * @private
	 * @return {string} Moderation state
	 */
	mw.flow.dm.Topic.prototype.setModerationState = function ( state ) {
		this.moderationState = state;
	};

	/**
	 * Get topic moderator
	 *
	 * @return {Object} Moderator
	 */
	mw.flow.dm.Topic.prototype.getModerator = function () {
		return this.moderator;
	};

	/**
	 * Get topic moderator
	 *
	 * @private
	 * @param {Object} mod Moderator
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
		this.emit( 'summaryChange', this.summary );
	};
}( jQuery ) );
