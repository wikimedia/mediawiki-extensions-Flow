( function ( mw, $ ) {
	/**
	 * @param {string} schemaName Canonical schema name.
	 * @param {Object} [eventInstance] Shared event instance data.
	 * @return {FlowEventLog}
	 * @constructor
	 */
	function FlowEventLog( schemaName, eventInstance ) {
		this.schemaName = schemaName;
		this.eventInstance = eventInstance || {};

		/**
		 * @param {Object} eventInstance Additional event instance data for this
		 *   particular event.
		 * @return {jQuery.Promise}
		 */
		function logEvent( eventInstance ) {
			// Ensure eventLog & this schema exist, or return a stub deferred
			if ( !mw.eventLog || !mw.eventLog.schemas[ this.schemaName ] ) {
				return $.Deferred().promise();
			}

			return mw.eventLog.logEvent(
				this.schemaName,
				$.extend( this.eventInstance, eventInstance )
			);
		}
		this.logEvent = logEvent;
	}

	var FlowEventLogRegistry = {
		funnels: {},

		/**
		 * Generates a unique id.
		 *
		 * @return {string}
		 */
		generateFunnelId: mw.user.generateRandomSessionId
	};

	// Export
	/**
	 * EventLogging wrapper
	 * @type {FlowEventLog}
	 */
	mw.flow.EventLog = FlowEventLog;

	mw.flow.EventLogRegistry = FlowEventLogRegistry;
}( mw, jQuery ) );
