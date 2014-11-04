/*!
 * Contains miscellaneous functionality needed for FlowBoardComponents.
 * @todo Find a better place for this code.
 */

( function ( $, mw ) {
	/**
	 *
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentMiscMixin( $container ) {
	}
	OO.initClass( FlowBoardComponentMiscMixin );

	//
	// Methods
	//

	/**
	 * Removes the preview and unhides the form fields.
	 * @param {jQuery} $cancelButton
	 * @return {bool} true if success
	 * @todo genericize into FlowComponent
	 */
	function flowBoardComponentResetPreview( $cancelButton ) {
		var $form = $cancelButton.closest( 'form' ),
			$button = $form.find( '[name=preview]' ),
			oldData = $button.data( 'flow-return-to-edit' );

		if ( oldData ) {
			// We're in preview mode. Revert it back.
			$button.text( oldData.text );

			// Show the inputs again
			$form.find( '.flow-preview-target-hidden' ).removeClass( 'flow-preview-target-hidden' ).focus();

			// Remove the preview
			oldData.$nodes.remove();

			// Remove this reset info
			$button.removeData( 'flow-return-to-edit' );

			return true;
		}
		return false;
	}
	FlowBoardComponentMiscMixin.prototype.resetPreview = flowBoardComponentResetPreview;

	/**
	 * This will trigger an eventLog call to the given schema with the given
	 * parameters (along with other info about the user & page.)
	 * A unique funnel ID will be created for all new EventLog calls.
	 *
	 * There may be multiple subsequent calls in the same "funnel" (and share
	 * same info) that you want to track. It is possible to forward funnel data
	 * from one node to another once the first has been clicked. It'll then
	 * log new calls with the same data (schema & entrypoint) & funnel ID as the
	 * initial logged event.
	 *
	 * @param {string} schemaName
	 * @param {object} data Data to be logged
	 * @param {string} data.action Schema's action parameter. Always required!
	 * @param {string} [data.entrypoint] Schema's entrypoint parameter (can be
	 *   omitted if already logged in funnel - will inherit)
	 * @param {string} [data.funnelId] Schema's funnelId parameter (can be
	 *   omitted if starting new funnel - will be generated)
	 * @param {jQuery} [$forward] Nodes to forward funnel to
	 * @returns {object} Logged data
	 */
	function logEvent( schemaName, data, $forward ) {
		var // Get existing (forwarded) funnel id, or generate a new one if it does not yet exist
			funnelId = data.funnelId || mw.flow.FlowEventLogRegistry.generateFunnelId(),
			// Fetch existing EventLog object for this funnel (if any)
			eventLog = mw.flow.FlowEventLogRegistry.funnels[funnelId];

		// Optional argument, may not want/need to forward funnel to other nodes
		$forward = $forward || $();

		if ( !eventLog ) {
			// Add some more data to log!
			data = $.extend( data, {
				isAnon: mw.user.isAnon(),
				sessionId: mw.user.sessionId(),
				funnelId: funnelId,
				pageNs: mw.config.get( 'wgNamespaceNumber' ),
				pageTitle: ( new mw.Title( mw.config.get( 'wgPageName' ) ) ).getMain()
			} );

			// A funnel with this id does not yet exist, create it!
			eventLog = new mw.flow.EventLog( schemaName, data );

			// Store this particular eventLog - we may want to log more things
			// in this funnel
			mw.flow.FlowEventLogRegistry.funnels[funnelId] = eventLog;
		}

		// Log this action
		eventLog.logEvent( { action: data.action } );

		// Forward the event
		this.forwardEvent( $forward, schemaName, funnelId );

		return data;
	}
	FlowBoardComponentMiscMixin.prototype.logEvent = logEvent;

	/**
	 * Forward funnel data to other places.
	 *
	 * @param {jQuery} $forward Nodes to forward funnel to
	 * @param {string} schemaName
	 * @param {string} funnelId Schema's funnelId parameter
	 */
	function forwardEvent( $forward, schemaName, funnelId ) {
		// Not using data() - it somehow gets lost on some nodes
		$forward.attr( {
			'data-flow-eventlog-schema': schemaName,
			'data-flow-eventlog-funnel-id': funnelId
		} );
	}
	FlowBoardComponentMiscMixin.prototype.forwardEvent = forwardEvent;

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentMiscMixin );
}( jQuery, mediaWiki ) );
