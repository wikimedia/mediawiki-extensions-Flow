( function ( $, mw, OO, ve ) {
	'use strict';

	/**
	 * Creates an input widget with auto-completion for users to be mentioned
	 *
	 * @class
	 * @extends OO.ui.TextInputWidget
	 * @mixins OO.ui.LookupElement
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @param {string[]} [config.topicPosters] Array of usernames representing posters to this thread,
	 *   without duplicates.
	 */
	mw.flow.ve.ui.MentionTargetInputWidget = function FlowVeUiMentionTargetInputWidget( config ) {
		mw.flow.ve.ui.MentionTargetInputWidget.parent.call(
			this,
			$.extend(
				{ placeholder: mw.msg( 'flow-ve-mention-placeholder' ) },
				config
			)
		);

		// Mixin constructor
		OO.ui.LookupElement.call( this, $.extend( { allowSuggestionsWhenEmpty: true }, config ) );

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

	mw.flow.ve.ui.MentionTargetInputWidget.prototype.isValid = function () {
		var api = new mw.Api(),
			dfd = $.Deferred(),
			promise = dfd.promise(),
			username = this.getValue(),
			widget = this,
			isValid;

		if ( $.trim( username ) === '' ) {
			dfd.resolve( false );
			return promise;
		}

		username = username[0].toUpperCase() + username.slice( 1 );
		if ( this.isUsernameValidCache[username] !== undefined ) {
			return this.isUsernameValidCache[username];
		}

		// Note that we delete this below if it turns out to get an error.
		this.isUsernameValidCache[username] = promise;

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
				// This is the normal path for either existent or non-existent users.
				isValid = resp.query.users[0].missing === undefined;
				dfd.resolve( isValid );
			} else {
				// This means part of the response is missing, which again shouldn't
				// happen (it could for empty string user, but we're not supposed to
				// send the request at all then). See explanation under fail.
				dfd.resolve( true );
				delete widget.isUsernameValidCache[username];
			}
		} ).fail( function () {
			// This should only happen on error cases.  Even if the user doesn't exist,
			// we should still enter done.  Since this is an unforseen error, return true
			// so we don't block submission, and evict cache.
			dfd.resolve( true );
			delete widget.isUsernameValidCache[username];
		} );

		return promise;
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
		var matches,
			abortObject = { abort: $.noop },
			dfd = $.Deferred(),
			lowerValue = this.value.toLowerCase();

		matches = $.grep( this.loggedInTopicPosters, function ( poster ) {
			return poster.toLowerCase().indexOf( lowerValue ) >= 0;
		} );

		dfd.resolve( matches );
		return dfd.promise( abortObject );
	};

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
		var user, i,
			items = [];

		for ( i = 0; i < users.length; i++ ) {
			user = users[i];

			items.push( new OO.ui.MenuOptionWidget( {
				data: user,
				label: user
			} ) );
		}

		return items;
	};

	// Based on ve.ui.MWLinkTargetInputWidget.prototype.initializeLookupMenuSelection
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
}( jQuery, mediaWiki, OO, ve ) );
