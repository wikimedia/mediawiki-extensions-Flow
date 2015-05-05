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
		mw.flow.ve.ui.MentionTargetInputWidget.parent.call( this, config );

		// Mixin constructor
		OO.ui.LookupElement.call( this, $.extend( { allowSuggestionsWhenEmpty: true }, config ) );

		// Properties
		// Exclude anonymous users, since they do not receive pings.
		this.loggedInTopicPosters = $.grep( config.topicPosters || [], function ( poster ) {
			return !mw.util.isIPAddress( poster, false );
		} );
		this.username = null;

		this.$element.addClass( 'flow-ve-ui-mentionTargetInputWidget' );
		this.lookupMenu.$element.addClass( 'flow-ve-ui-mentionTargetInputWidget-menu' );
	};

	OO.inheritClass( mw.flow.ve.ui.MentionTargetInputWidget, OO.ui.TextInputWidget );

	OO.mixinClass( mw.flow.ve.ui.MentionTargetInputWidget, OO.ui.LookupElement );

	mw.flow.ve.ui.MentionTargetInputWidget.prototype.isValid = function () {
		return $.Deferred().resolve( !!mw.Title.newFromText( this.value, 2 ) );
	};

	/**
	 * Gets a promise representing the auto-complete.
	 * The auto-complete is based on the users who have already posted to the topic
	 * and on an API call.
	 *
	 * For users who have posted to the topic, we do a case-insensitive search for a string
	 * (anywhere in the poster's username) matching what the user has typed in so far.
	 * E.g. if one of the posters is "Mary Jane Smith", that will be a suggestion if the user has
	 * entered e.g. "Mary", "jane", or 'Smi'.
	 *
	 * For the API call, the best we have is a prefix search.
	 *
	 * @method
	 * @return {jQuery.Promise}
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupRequest = function () {
		var xhr,
			lowerValue = this.value.toLowerCase(),
			initialUpperValue = this.value.charAt( 0 ).toUpperCase() + this.value.slice( 1 ),
			localMatches = $.grep( this.loggedInTopicPosters, function ( poster ) {
				return poster.toLowerCase().indexOf( lowerValue ) >= 0;
			} );

		if ( this.value === '' ) {
			return $.Deferred()
				.resolve( {
					localMatches: localMatches,
					apiMatches: [] }
				)
				.promise( { abort: $.noop } );
		}

		xhr = new mw.Api().get( {
			action: 'query',
			list: 'allusers',
			auprefix: initialUpperValue,
			aulimit: 5
		} );
		return xhr
			.then( function ( data ) {
				var i, len,
					users = OO.getProp( data, 'query', 'allusers' ) || [],
					apiMatches = [];
				for ( i = 0, len = users.length; i < len; i++ ) {
					if ( localMatches.indexOf( users[i].name ) === -1 ) {
						apiMatches.push( users[i].name );
					}
				}
				return {
					localMatches: localMatches,
					apiMatches: apiMatches
				};
			} )
			.promise( { abort: xhr.abort } );
	};

	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupCacheDataFromResponse = function ( data ) {
		return data;
	};

	/**
	 * Converts the raw data to UI objects
	 *
	 * @param {Object} data Raw data
	 * @param {string[]} data.localMatches Users in the current conversation
	 * @param {string[]} data.apiMatches Users from the API
	 * @return {OO.ui.MenuOptionWidget[]} Menu items
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupMenuOptionsFromData = function ( data ) {
		return $.map( data.localMatches.concat( data.apiMatches ), function ( username ) {
			return new OO.ui.MenuOptionWidget( {
				data: username,
				label: username
			} );
		} );
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
