/*!
 * Contains board navigation header, which affixes to the viewport on scroll.
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for the board header itself.
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
	 * Toggle between possible editors
	 *
	 * @param {???}
	 */
	FlowBoardComponentSwitchEditorFeatureMixin.UI.events.interactiveHandlers.switchEditor = function ( event ) {
		var desiredEditor,
			$this = $( this ),
			$target = $this.findWithParent( $this.data( 'flow-target' ) ),
			editorList = mw.config.get( 'wgFlowEditorList' ),
			currentEditorIndex = editorList.indexOf( mw.user.options.get( 'flow-editor' ) );

		event.preventDefault();

		if ( !$target.length ) {
			mw.flow.debug( '[switchEditor] No target located' );
			return;
		}

		// currently there are only two possible editors.  If we add more in the future
		// this will need to be more robust.
		if ( currentEditorIndex + 1 >= editorList.length ) {
			desiredEditor = editorList[0];
		} else {
			desiredEditor = editorList[currentEditorIndex + 1];
		}

		mw.flow.editor.switchEditor( $target, desiredEditor )
			.then(
				function() {
					// update the user preferences
					new mw.Api().postWithToken( 'options', {
						action: 'options',
						change: 'flow-editor=' + desiredEditor
					} );
					// ensure we also see that preference in the current page
					mw.user.options.set( 'flow-editor', desiredEditor );
				},
				function() {
					mw.flow.debug( '[switchEditor] oh noes!' );
				}
			)
	};

	/**
	 * Hide switchEditor controls on load if only one editor is available.
	 *
	 * @todo should this take into account the isSupported() method on those editors?
	 *  possibly the code isn't even loaded yet.
	 *
	 * @param {???} event
	 */
	FlowBoardComponentSwitchEditorFeatureMixin.UI.events.loadHandlers.switchEditor = function ( event ) {
		if ( mw.config.get( 'wgFlowEditorList' ).length === 1 ) {
			$( this ).hide();
		}
	};

	//
	// Prototype methods
	//

	//
	// API pre-handlers
	//

	//
	// On element-click handlers
	//

	//
	// On element-load handlers
	//

	//
	// Private functions
	//

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentSwitchEditorFeatureMixin );
}( jQuery, mediaWiki ) );
