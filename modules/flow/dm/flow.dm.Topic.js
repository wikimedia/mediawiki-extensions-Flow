/**
 * Flow Topic.
 *
 * @extends flow.dm.List
 *
 * @param {Object} data API data to build topic with
 */
flow.dm.Topic = function FlowDmTopic( data ) {
	// Parent constructor
	flow.dm.List.call( this );

	// Events
	this.connect( this, { 'add': 'onItemsChange', 'remove': 'onItemsChange' } );

	// Properties
	this.id = data.id;
	this.permalink = data.permalink;
	this.setup();
};

/* Inheritance */

OO.inheritClass( flow.dm.Topic, flow.dm.List );

/* Events */

/**
 * @event change Topic has changed
 */

/* Methods */

/**
 * Handle item change events.
 */
flow.dm.Topic.prototype.onItemsChange = function () {
	this.setup();
	this.emit( 'change' );
};

/**
 * Setup topic.
 */
flow.dm.Topic.prototype.setup = function () {
	// Clear caches
	this.title = null;
	this.participants = null;
	this.timestamp = null;
};

/**
 * Get topic ID.
 *
 * @returns {string} UUID
 */
flow.dm.Topic.prototype.getId = function () {
	return this.id;
};

/**
 * Get topic permalink.
 *
 * @returns {string} UUID
 */
flow.dm.Topic.prototype.getPermalink = function () {
	return this.permalink;
};

/**
 * Get topic title.
 *
 * Title is derrived from the first post.
 *
 * @returns {string|undefined} Title or undefined if no posts exist.
 */
flow.dm.Topic.prototype.getTitle = function () {
	if ( this.title === null ) {
		this.title = this.items.length ? this.items[0].getContent() : undefined;
	}

	return this.title;
};

/**
 * Get participants.
 *
 * Participant names are derrived from posts.
 *
 * @returns {string[]} List of participant usernames
 */
flow.dm.Topic.prototype.getParticipants = function () {
	var i, len, creator, first, second, last;

	if ( this.participants === null ) {
		for ( i = 0, len = this.items.length; i < len; i++ ) {
			creator = this.items[i].getCreator();
			if ( !first ) {
				first = creator;
			} else if ( !second ) {
				if ( creator !== first ) {
					second = creator;
				}
			} else {
				if ( creator !== first && creator !== second ) {
					last = creator;
				}
			}
		}
		this.participants = [];
		if ( first ) {
			this.participants.push( first );
		}
		if ( second ) {
			this.participants.push( second );
		}
		if ( last ) {
			this.participants.push( last );
		}
	}

	return this.participants;
};

/**
 * Get latest timestamp.
 *
 * Latest timestamp is derrived from posts.
 *
 * @returns {number|undefined} Latest timestamp, undefined if no posts exist.
 */
flow.dm.Topic.prototype.getTimestamp = function () {
	var i, len, current, max;

	if ( this.timestamp === null ) {
		for ( i = 0, len = this.items.length; i < len; i++ ) {
			current = this.items[i].getTimestamp();
			if ( !max || current > max ) {
				max = current;
			}
		}
		this.timestamp = max;
	}

	return this.timestamp;
};
