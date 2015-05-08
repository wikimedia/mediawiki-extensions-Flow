/*!
 * Handlers for the switching the editor from wikitext to visualeditor
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for switching from wikitext to visualeditor
	 *
	 * @param {jQuery} $container
	 * @this FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentSwitchEditorFeatureMixin( $container ) {
		// Bind element handlers
		this.bindNodeHandlers( FlowBoardComponentSwitchEditorFeatureMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentSwitchEditorFeatureMixin );

	FlowBoardComponentSwitchEditorFeatureMixin.UI = {
		events: {
			interactiveHandlers: {},
			loadHandlers: {}
		}
	};

	/**
	 * Toggle between possible editors.
	 *
	 * Currently the only options are visualeditor, and none.  Visualeditor has its own
	 * code for switching, so this is only run by clicking the switch button from 'none'.
	 * If we add more editors later this will have to be revisited.
	 *
	 * @param {Event} event
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentSwitchEditorFeatureMixin.UI.events.interactiveHandlers.switchEditor = function ( event ) {
		var $this = $( this ),
			$target = $this.findWithParent( $this.data( 'flow-target' ) );

		event.preventDefault();

		if ( !$target.length ) {
			mw.flow.debug( '[switchEditor] No target located' );
			return $.Deferred().reject().promise();
		}

		return mw.flow.editor.switchEditor( $target, 'visualeditor' );
	};

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentSwitchEditorFeatureMixin );
}( jQuery, mediaWiki ) );
