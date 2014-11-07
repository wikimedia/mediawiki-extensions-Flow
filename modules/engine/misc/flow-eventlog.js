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
		this.eventInstance = eventInstance || {};

		/**
		 * @param {Object} eventInstance Additional event instance data for this
		 *   particular event.
		 * @returns {$.Deferred}
		 */
		function logEvent( eventInstance ) {
			if ( mw.eventLog ) {
				return mw.eventLog.logEvent(
					this.schema,
					$.extend( this.eventInstance, eventInstance )
				);
			} else {
				// for compatibility: a deferred that never resolves
				return $.Deferred().promise();
			}
		}

		this.logEvent = logEvent;
	}

	// Export
	mw.flow.FlowEventLog = FlowEventLog;
}( mw, jQuery ) );
