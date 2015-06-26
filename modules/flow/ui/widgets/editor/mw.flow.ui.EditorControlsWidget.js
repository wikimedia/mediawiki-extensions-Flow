( function ( $ ) {
	/**
	 * Flow editor controls widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [termsMsgKey='flow-terms-of-use-edit'] A i18n message key
	 *  for the footer message.
	 */
	mw.flow.ui.EditorControlsWidget = function mwFlowUiEditorControlsWidget( config ) {
		var $buttons = $( '<div>' )
				.addClass( 'flow-ui-editorControlsWidget-buttons' ),
			$label = $( '<div>' )
				.addClass( 'flow-ui-editorControlsWidget-label' );

		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorControlsWidget.parent.call( this, config );

		this.termsMsgKey = config.termsMsgKey || 'flow-terms-of-use-edit';

		this.termsLabel = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-editorControlsWidget-termsLabel' ]
		} );
		this.setTermsLabel( mw.msg( this.termsMsgKey ) );

		this.saveButton = new OO.ui.ButtonWidget( {
			flags: [ 'primary', 'constructive' ],
			label: mw.msg( 'flow-newtopic-save' ),
			classes: [ 'flow-ui-editorControlsWidget-saveButton' ]
		} );

		this.cancelButton = new OO.ui.ButtonWidget( {
			flags: 'destructive',
			framed: false,
			label: mw.msg( 'flow-cancel' ),
			classes: [ 'flow-ui-editorControlsWidget-cancelButton' ]
		} );

		$label.append( this.termsLabel.$element );

		$buttons.append(
			this.cancelButton.$element,
			this.saveButton.$element
		);

		// Events
		this.saveButton.connect( this, { click: [ 'emit', 'save' ] } );
		this.cancelButton.connect( this, { click: [ 'emit', 'cancel' ] } );

		// Initialize
		this.$element
			.append( $label, $buttons )
			.addClass( 'flow-ui-editorControlsWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorControlsWidget, OO.ui.Widget );

	/**
	 * Set the content of the terms label
	 *
	 * @param {string} msg Terms message
	 */
	mw.flow.ui.EditorControlsWidget.prototype.setTermsLabel = function ( msg ) {
		this.termsLabel.setLabel( msg );
	};

	/**
	 * Set the widget as disabled
	 *
	 * @param {boolean} disabled Widget is disabled
	 */
	mw.flow.ui.EditorControlsWidget.prototype.setDisabled = function ( disabled ) {
		if ( this.cancelButton && this.saveButton ) {
			this.cancelButton.setDisabled( !!disabled );
			this.saveButton.setDisabled( !!disabled );
		}
	};

}( jQuery ) );
