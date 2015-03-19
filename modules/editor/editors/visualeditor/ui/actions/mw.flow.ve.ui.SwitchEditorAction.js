( function ( mw, OO, ve ) {

mw.flow.ve.ui.SwitchEditorAction = function MwFlowVeUiSwitchEditorAction( surface ) {
	// Parent constructor
	ve.ui.Action.call( this, surface );
};

/* Inheritance */

OO.inheritClass( mw.flow.ve.ui.SwitchEditorAction, ve.ui.Action );

/* Static Properties */

mw.flow.ve.ui.SwitchEditorAction.static.name = 'flowSwitchEditor';

/**
 * List of allowed methods for the action.
 *
 * @static
 * @property
 */
mw.flow.ve.ui.SwitchEditorAction.static.methods = [ 'switch' ];

/* Methods */

mw.flow.ve.ui.SwitchEditorAction.prototype.switch = function ( name, data, action ) {
	var $node = this.surface.$element.closest( 'form' ).find( 'textarea' );

	mw.flow.editor.switchEditor( $node, 'none' );
};

ve.ui.actionFactory.register( mw.flow.ve.ui.SwitchEditorAction );

}( mediaWiki, OO, ve ) );
