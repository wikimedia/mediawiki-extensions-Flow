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
		 */
		function logEvent( eventInstance ) {
			mw.track(
				'event.' + this.schemaName,
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
