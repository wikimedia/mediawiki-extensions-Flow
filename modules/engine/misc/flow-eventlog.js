( function ( mw, $ ) {
	/**
	 * @param {String} schemaName Canonical schema name.
	 * @param {Object} [eventInstance] Shared event instance data.
	 * @returns {FlowEventLog}
	 * @constructor
	 */
	function FlowEventLog( schemaName, eventInstance ) {
		this.schemaName = schemaName;
		this.eventInstance = eventInstance || {};

		/**
		 * @param {object} eventInstance Additional event instance data for this
		 *   particular event.
		 * @returns {$.Deferred}
		 */
		function logEvent( eventInstance ) {
console.log($.extend( this.eventInstance, eventInstance )); // @todo: remove this line
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
		 * @returns {string}
		 */
		generateFunnelId: mw.user.generateRandomSessionId
	};

	// Export
	mw.flow.FlowEventLog = FlowEventLog;
	mw.flow.FlowEventLogRegistry = FlowEventLogRegistry;
}( mw, jQuery ) );
