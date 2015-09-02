( function ( $ ) {
	/**
	 * Dialog for confirming with the user if they reall want to cancel
	 * the edit.
	 *
	 * @class
	 * @extends OO.ui.MessageDialog
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.CancelConfirmDialog = function MwFlowUICancelConfirmDialog( config ) {
		// Parent constructor
		mw.flow.ui.CancelConfirmDialog.parent.call( this, config );
	};

	/* Inheritance */

	OO.inheritClass( mw.flow.ui.CancelConfirmDialog, OO.ui.MessageDialog );

	/* Static Properties */

	mw.flow.ui.CancelConfirmDialog.static.name = 'cancelconfirm';

	mw.flow.ui.CancelConfirmDialog.static.verbose = true;

	mw.flow.ui.CancelConfirmDialog.static.size = 'small';

	mw.flow.ui.CancelConfirmDialog.static.icon = 'help';

	mw.flow.ui.CancelConfirmDialog.static.title =
		mw.msg( 'flow-dialog-cancelconfirm-title' );

	mw.flow.ui.CancelConfirmDialog.static.message =
		mw.msg( 'flow-dialog-cancelconfirm-message' );

	mw.flow.ui.CancelConfirmDialog.static.actions = [
		{ action: 'discard', label: mw.msg( 'flow-dialog-cancelconfirm-discard' ), flags: [ 'primary', 'destructive' ] },
		{ action: 'keep', label: mw.msg( 'flow-dialog-cancelconfirm-keep' ), flags: 'safe' }
	];

	/* Registration */

	mw.flow.ui.windowFactory.register( mw.flow.ui.CancelConfirmDialog );

}( jQuery ) );
