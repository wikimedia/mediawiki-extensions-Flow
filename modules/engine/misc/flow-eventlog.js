/*!
 *
 */

window.mw = window.mw || {}; // mw-less testing

( function ( mw, $ ) {
	mw.flow = mw.flow || {}; // create mw.flow globally

	/**
	 * @param {String} schemaName Canonical schema name.
	 * @param {Object} [eventInstance] Shared event instance data.
	 * @returns {FlowEventLog}
	 * @constructor
	 */
	function FlowEventLog( schemaName, eventInstance ) {
		this.schemaName = schemaName;

		function loadModules() {
			if ( !mw.config.get( 'wgFlowLogEvents' ) ) {
				return $.Deferred().promise();
			}

			return mw.loader.using( ['ext.eventLogging', 'schema.' + this.schemaName] );
		}

		/**
		 * @param {object} eventInstance
		 */
		function setDefaults( eventInstance ) {
			var schemaName = this.schemaName;
			loadModules.call( this ).done( function() {
				mw.eventLog.setDefaults( schemaName, eventInstance );
			} );
		}
		setDefaults.call( this, eventInstance );

		/**
		 * @param {object} eventInstance Additional event instance data for this
		 *   particular event.
		 * @returns {$.Deferred}
		 */
		function logEvent( eventInstance ) {
			var schemaName = this.schemaName;
			loadModules.call( this ).done( function() {
				return mw.eventLog.logEvent( schemaName, eventInstance );
			} );
		}
		this.logEvent = logEvent;
	}

	var FlowEventLogRegistry = {
		funnels: {},

		/**
		 * Generates a unique id.
		 *
		 * @returns {string}
		 */
		generateFunnelId: function() {
			/**
			 * @param {int} milliseconds
			 */
			var sleep = function( milliseconds )
			{
				var current = new Date(),
					elapsed;

				do {
					elapsed = new Date() - current;
				}
				while ( elapsed < milliseconds );
			};

			/*
			 * Enforce uniqueness: just make sure the timestamp we'll be using
			 * is always unique. Well, in this process at least, in theory, a
			 * user could have multiple tabs open and have id's generated in
			 * both at the exact same millisecond. In practice, unlikely!
			 * This basically just makes sure that we generate unique ids if we
			 * launch multiple funnels at the exact same time (an action could
			 * generate 2 different funnels we might want to track separately)
			 */
			sleep( 1 );

			/*
			 * Generate unique id
			 * This assumes unique session ids per user, and that a user can't
			 * generate an id at exactly the same time in 2 different processes
			 */
			return mw.user.sessionId() + '_' + new Date().getTime();
		}
	};

	// Export
	mw.flow.FlowEventLog = FlowEventLog;
	mw.flow.FlowEventLogRegistry = FlowEventLogRegistry;
}( mw, jQuery ) );
