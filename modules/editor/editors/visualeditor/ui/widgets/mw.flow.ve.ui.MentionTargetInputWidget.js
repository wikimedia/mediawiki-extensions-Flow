( function ( $, mw, OO, ve ) {
	'use strict';

	/**
	 * Creates an input widget with auto-completion for users to be mentioned
	 *
	 * @class
	 * @extends oo.ui.TextInputWidget
	 * @mixins OO.ui.LookupElement
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @param {Array} [config.topicPosters] Array of usernames representing posters to this thread,
	 *   without duplicates.
	 */
	mw.flow.ve.ui.MentionTargetInputWidget = function FlowVeUiMentionTargetInputWidget( config ) {
		var flowBoard;

		mw.flow.ve.ui.MentionTargetInputWidget.parent.call( this, config );

		// Mixin constructor
		config.allowSuggestionsWhenEmpty = true;
		OO.ui.LookupElement.call( this, config );

		// Properties
		// Exclude anonymous users, since they do not receive pings.
		this.loggedInTopicPosters = $.grep( config.topicPosters || [], function ( poster ) {
			return !mw.util.isIPAddress( poster, false );
		} );
		this.username = null;
		// Username to validity promise (promise resolves with true/false for existent/non-existent
		this.isUsernameValidCache = {};

		this.$element.addClass( 'flow-ve-ui-mentionTargetInputWidget' );
		this.lookupMenu.$element.addClass( 'flow-ve-ui-mentionTargetInputWidget-menu' );
	};

	OO.inheritClass( mw.flow.ve.ui.MentionTargetInputWidget, OO.ui.TextInputWidget );

	OO.mixinClass( mw.flow.ve.ui.MentionTargetInputWidget, OO.ui.LookupElement );

	// TODO: Is this worth the API delay?  If so, it should be cached, and ideally
	// reuse data from the suggestions.  this.lookupCache is similar, but not quite the same
	// thing, so we'll probably want our own cache object.
	//
	// TODO: How does this actually affect the UI?  It doesn't block the input button
	// yet, so I still need to wire that up.
	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.isValid = function () {
		var api = new mw.Api(),
			dfd = $.Deferred(),
			username = this.getValue(),
			isValid;

		if ( $.trim( username ) === '' ) {
			dfd.resolve( false );
			return dfd.promise();
		}

		username = username[0].toUpperCase() + username.slice( 1 );
		if ( this.isUsernameValidCache[username] !== undefined ) {
			return this.isUsernameValidCache[username];
		}

		this.isUsernameValidCache[username] = dfd.promise();

		api.get( {
			action: 'query',
			list: 'users',
			ususers: username
		} ).done( function ( resp ) {
			if (
				resp &&
				resp.query &&
				resp.query.users &&
				resp.query.users.length > 0
			) {
				// This is the normal path for either existent or non-existent users,
				// so we cache it.
				isValid = resp.query.users[0].missing === undefined;
				dfd.resolve( isValid );
			} else {
				// This means part of the response is missing, which again shouldn't
				// happen (it could for empty string user, but we're not supposed to
				// send the request at all then). See explanation under fail.
				dfd.resolve( true );
			}
		} ).fail( function () {
			// This should only happen on error cases.  Even if the user doesn't exist,
			// we should still enter done.  Since this is an unforseen error, return true
			// so we don't block submission.
			dfd.resolve( true );
		} );

		return this.isUsernameValidCache[username];
	};

	/**
	 * Gets a promise representing the auto-complete.
	 * Right now, the auto-complete is based on the users who have already posted to the topic.
	 *
	 * It does a case-insensitive search for a string (anywhere in the poster's username)
	 * matching what the user has typed in so far.
	 *
	 * E.g. if one of the posters is "Mary Jane Smith", that will be a suggestion if the user has
	 * entered e.g. "Mary", "jane", or 'Smi'.
	 *
	 * @method
	 * @returns {jQuery.Promise}
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupRequest = function () {
		var abortObject = { abort: $.noop }, dfd = $.Deferred(),
			lowerValue = this.value.toLowerCase(), matches;

		matches = $.grep( this.loggedInTopicPosters, function ( poster ) {
			return poster.toLowerCase().indexOf( lowerValue ) >= 0;
		} );

		dfd.resolve( matches );
		return dfd.promise( abortObject );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupCacheDataFromResponse = function ( data ) {
		return data;
	};

	/**
	 * Converts the raw data to UI objects
	 *
	 * @param Array list of users
	 * @return {OO.ui.MenuOptionWidget[]} Menu items
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupMenuOptionsFromData = function ( users ) {
		var items = [], user;

		for ( var i = 0; i < users.length; i++ ) {
			user = users[i];

			items.push( new OO.ui.MenuOptionWidget( {
				$: this.lookupMenu.$,
				data: user,
				label: user
			} ) );
		}

		return items;
	};

	// Based on ve.ui.MWLinkTargetInputWidget.prototype.initializeLookupMenuSelection
	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.initializeLookupMenuSelection = function () {
		var item;
		if ( this.username ) {
			this.lookupMenu.selectItem( this.lookupMenu.getItemFromData( this.username ) );
		}

		item = this.lookupMenu.getSelectedItem();
		if ( !item ) {
			OO.ui.LookupElement.prototype.initializeLookupMenuSelection.call( this );
		}

		item = this.lookupMenu.getSelectedItem();
		if ( item ) {
			this.username = item.getData();
		}
	};
} ( jQuery, mediaWiki, OO, ve ) );
