( function ( $ ) {
	/**
	 * Flow board description widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config]
	 *
	 */
	mw.flow.ui.BoardDescriptionWidget = function mwFlowUiBoardDescriptionWidget( model, config ) {
		var $content = $();

		config = config || {};

		// Parent constructor
		mw.flow.ui.BoardDescriptionWidget.parent.call( this, config );

		this.attachModel( model );

		// Since the content is already displayed, we will "steal" the already created
		// node to avoid having to render it twice.
		// Upon creation of this widget, this should be the rendering of the data
		// that exists in the model. Take care, however, that if this widget is
		// used elsewhere, the model and rendering must be synchronized.
		if ( config.$existing ) {
			$content = config.$existing;
		}

		this.$content = $( '<div>' )
			.addClass( 'flow-ui-boardDescriptionWidget-content' )
			.append( $content );

		this.button = new OO.ui.ButtonWidget( {
			label: mw.msg( 'flow-edit-header-link' ),
			framed: false,
			icon: 'edit',
			flags: 'progressive',
			classes: [ 'flow-ui-boardDescriptionWidget-editButton' ]
		} );

		// Events
		this.button.connect( this, { click: 'onEditButtonClick' } );
		// Initialize
		this.$element
			.append(
				this.button.$element,
				this.$content
			)
			.addClass( 'flow-ui-boardDescriptionWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.BoardDescriptionWidget, OO.ui.Widget );

	/* Methods */

	/**
	 * Respond to edit button click. Switch to the editor widget
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditButtonClick = function () {
console.log( 'edit' );
		// Disable the button
		this.button.setDisabled( true );
	};

	/**
	 * Respond to content change
	 *
	 * @param {string} content Description content
	 * @param {string} contentFormat Description content format
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onContentChange = function ( content, contentFormat ) {
		this.$content.empty().append( $.parseHTML( content ) );
	};

	/**
	 * Attach a model to the widget
	 *
	 * @param {mw.flow.dm.BoardDescription} model Board model
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.attachModel = function ( model ) {
		if ( this.model ) {
			this.model.disconnect( this );
		}

		this.model = model;

		// Events
		this.model.connect( this, {
			contentChange: 'onContentChange'
		} );
	};

}( jQuery ) );
