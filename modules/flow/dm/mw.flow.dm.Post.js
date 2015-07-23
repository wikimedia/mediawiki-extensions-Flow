( function ( $ ) {
	/**
	 * Flow Post
	 *
	 * @constructor
	 *
	 * @extends mw.flow.dm.RevisionedContent
	 * @mixins mw.flow.dm.List
	 *
	 * @param {string} id Post Id
	 * @param {Object} [revisionData] API data to build post with
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.dm.Post = function mwFlowDmPost( id, revisionData, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.dm.Post.parent.call( this, config );

		// Mixin constructor
		mw.flow.dm.List.call( this, config );

		this.setId( id );
		this.populate( revisionData || {} );

		// Configuration
		this.highlighted = !!config.highlighted;

		// Store comparable hash
		this.storeComparableHash();
	};

	/* Initialization */

	OO.inheritClass( mw.flow.dm.Post, mw.flow.dm.RevisionedContent );
	OO.mixinClass( mw.flow.dm.Post, mw.flow.dm.List );

	/* Events */

	/**
	 * Change of topic summary
	 *
	 * @event summaryChange
	 * @param {string} summary New summary
	 */

	/**
	 * Topic moderation state has changed.
	 * Topic is either moderated, changed its moderation
	 * status or reason, or is no longer moderated.
	 *
	 * @event moderated
	 * @param {boolean} moderated Topic is moderated
	 * @param {string} moderationState Moderation state
	 * @param {string} moderationReason Moderation reason
	 * @param {Object} moderator Moderator
	 */

	/* Static methods */

	/**
	 * Get the post revision by its topic Id from the topiclist
	 * api response.
	 *
	 * @param {Object} topiclist API data for topiclist
	 * @param {string} postId Post id
	 * @return {Object} Revision data
	 */
	mw.flow.dm.Post.static.getPostRevision = function ( topiclist, postId ) {
		var pid = OO.getProp( topiclist, 'posts', postId );

		if ( pid[0] ) {
			return topiclist.revisions[pid[0]];
		}
		return {};
	};

	/**
	 * Create a hierarchical construct of replies based on the parent reply list.
	 *
	 * @param {Object} topiclist API response for topic list
	 * @param {string[]} parentReplyId Ids of the parent posts
	 * @return {mw.flow.dm.Post[]} Array of posts
	 */
	mw.flow.dm.Post.static.createTopicReplyTree = function ( topiclist, parentReplyId ) {
		var i, len, post, postRevision, replies,
			result = [];

		for ( i = 0, len = parentReplyId.length; i < len; i++ ) {
			postRevision = mw.flow.dm.Post.static.getPostRevision( topiclist, parentReplyId[i] );
			post = new mw.flow.dm.Post( parentReplyId[i], postRevision );
			// Populate sub-posts
			replies = this.createTopicReplyTree( topiclist, post.getReplyIds() );
			post.addItems( replies );
			result.push( post );
		}
		return result;
	};

	/* Methods */

	/**
	 * Get a hash object representing the current state
	 * of the Topic
	 *
	 * @return {Object} Hash object
	 */
	mw.flow.dm.Post.prototype.getHashObject = function () {
		return $.extend(
			{
				moderated: this.isModerated(),
				moderationReason: this.getModerationReason(),
				moderationState: this.getModerationState(),
				moderator: this.getModerator()
			},
			// Parent
			mw.flow.dm.Post.parent.prototype.getHashObject.call( this )
		);
	};

	/**
	 * Populate the topic information from API data.
	 *
	 * @param {Object} data API data
	 */
	mw.flow.dm.Post.prototype.populate = function ( data ) {
		this.setModerated( !!data.isModerated, data.moderateReason, data.moderateState, data.moderator );

		// Store reply Ids
		this.replyIds = data.replies || [];

		// Parent method
		mw.flow.dm.RevisionedContent.prototype.populate.call( this, data );
	};

	mw.flow.dm.Post.prototype.getReplyIds = function () {
		return this.replyIds;
	};

	/**
	 * Check if topic is moderated
	 * @return {boolean} Topic is moderated
	 */
	mw.flow.dm.Post.prototype.isModerated = function () {
		return this.moderated;
	};

	/**
	 * Toggle the moderated state of a topic
	 * @param {boolean} moderated Topic is moderated
	 * @param {string} moderationState Moderation state
	 * @param {string} moderationReason Moderation reason
	 * @param {Object} moderator Moderator
	 * @fires moderated
	 */
	mw.flow.dm.Post.prototype.setModerated = function ( moderated, moderationState, moderationReason, moderator ) {
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
	mw.flow.dm.Post.prototype.getModerationReason = function () {
		return this.moderationReason;
	};

	/**
	 * Set topic moderation reason
	 *
	 * @private
	 * @return {string} Moderation reason
	 */
	mw.flow.dm.Post.prototype.setModerationReason = function ( reason ) {
		this.moderationReason = reason;
	};

	/**
	 * Get topic moderation state
	 *
	 * @return {string} Moderation state
	 */
	mw.flow.dm.Post.prototype.getModerationState = function () {
		return this.moderationState;
	};

	/**
	 * Set topic moderation state
	 *
	 * @private
	 * @param {string} state Moderation state
	 */
	mw.flow.dm.Post.prototype.setModerationState = function ( state ) {
		this.moderationState = state;
	};

	/**
	 * Get topic moderator
	 *
	 * @return {Object} Moderator
	 */
	mw.flow.dm.Post.prototype.getModerator = function () {
		return this.moderator;
	};

	/**
	 * Get topic moderator
	 *
	 * @private
	 * @param {Object} mod Moderator
	 */
	mw.flow.dm.Post.prototype.setModerator = function ( mod ) {
		this.moderator = mod;
	};

	/**
	 * Get the topic summary
	 *
	 * @return {string} Topic summary
	 */
	mw.flow.dm.Post.prototype.getSummary = function () {
		return this.summary;
	};

	/**
	 * Get the topic summary
	 *
	 * @param {string} Topic summary
	 * @fires summary
	 */
	mw.flow.dm.Post.prototype.setSummary = function ( summary ) {
		this.summary = summary;
		this.emit( 'summaryChange', this.summary );
	};

}( jQuery ) );
