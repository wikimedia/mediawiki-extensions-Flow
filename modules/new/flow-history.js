/*!
 * Implements the history state management in FlowBoard.HistoryEngine.
 */
( function () {
	window.mw = window.mw || {}; // mw-less testing
	mw.flow = mw.flow || {}; // create mw.flow globally

	function FlowHistoryStateManager( FlowStorageEngine ) {
		/**
		 * Called on load of a page (ideally). It will get the last known state of the page,
		 * and trigger necessary events.
		 */
		this.restoreLastState = function () {

		};

		/**
		 * To be called when the current page's state has changed. This adjusts the current state,
		 * and does not add a new state into the browser history.
		 */
		this.updateState = function ( stateData ) {

		};

		/**
		 * To be called when the current page's state has changed, but in this scenario, an entirely
		 * new page state will be stored in the browser's history. Use sparingly.
		 */
		this.addState = function ( stateData ) {

		};
	}

	mw.flow.FlowHistoryStateManager = FlowHistoryStateManager;
}() );