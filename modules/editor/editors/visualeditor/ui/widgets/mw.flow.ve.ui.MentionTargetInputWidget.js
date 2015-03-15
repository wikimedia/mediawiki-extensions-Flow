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
	 * @param {Array} [config.topicAuthors] List of authors in this topic, for use in auto-completion
	 */
	mw.flow.ve.ui.MentionTargetInputWidget = function FlowVeuiMentionTargetInputWidget( config ) {
		mw.flow.ve.ui.MentionTargetInputWidget.parent.call( this, config );

		// Mixin constructor
		OO.ui.LookupElement.call( this, config );

		// Properties
		this.topicAuthors = config.topicAuthors || [];
		this.username = null;

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
			username = this.getValue();

		if ( $.trim( username ) === '' ) {
			dfd.resolve( false );
			return dfd.promise();
		}

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
				if ( resp.query.users[0].missing === undefined ) {
					dfd.resolve( true );
				} else {
					dfd.resolve( false );
				}
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

		return dfd.promise();
	};

	/**
	 * Gets a promise representing the auto-complete
	 *
	 * @method
	 * @returns {jQuery.Promise}
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupRequest = function () {
		var abortObject = { abort: $.noop }, dfd = $.Deferred();

		// Mock for now
		if ( this.value.startsWith( 'A' ) ) {
			dfd.resolve( [
				'Astronaut',
				'American',
				'Academic'
			] );
		} else {
			dfd.resolve( [] );
		}

		return dfd.promise( abortObject );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ve.ui.MentionTargetInputWidget.prototype.getLookupCacheDataFromResponse = function ( data ) {
		// Since getLookupRequest will combine multiple data sources (topic authors and
		// general straight prefix auto-complete), I don't think we can use the getLookupRequest/
		// getLookupCacheDataFromResponse separation, so this is just an identity.
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
