( function ( mw, OO, ve ) {

/**
 * Action to switch from VisualEditor to the Wikitext editing interface
 * within Flow.
 *
 * @class
 * @extends ve.ui.Action
 *
 * @constructor
 * @param {ve.ui.Surface} surface Surface to act on
 */
mw.flow.ve.ui.SwitchEditorAction = function MwFlowVeUiSwitchEditorAction( surface ) {
	// Parent constructor
	ve.ui.Action.call( this, surface );
};

/* Inheritance */

OO.inheritClass( mw.flow.ve.ui.SwitchEditorAction, ve.ui.Action );

/* Static Properties */

/**
 * Name of this action
 *
 * @static
 * @property
 */
mw.flow.ve.ui.SwitchEditorAction.static.name = 'flowSwitchEditor';

/**
 * List of allowed methods for the action.
 *
 * @static
 * @property
 */
mw.flow.ve.ui.SwitchEditorAction.static.methods = [ 'switch' ];

/* Methods */

/**
 * Switch to wikitext editing.
 *
 * @method
 */
mw.flow.ve.ui.SwitchEditorAction.prototype.switch = function () {
	var $editor = this.surface.$element.closest( '.flow-editor' );
	if ( $editor.length ) {
		// Old editor
		mw.flow.editor.switchEditor( $editor.find( 'textarea' ), 'none' );
	} else {
		// New editor
		this.surface.emit( 'switchEditor' );
	}
};

ve.ui.actionFactory.register( mw.flow.ve.ui.SwitchEditorAction );

}( mediaWiki, OO, ve ) );
