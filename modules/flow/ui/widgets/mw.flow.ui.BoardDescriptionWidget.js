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

		this.flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );

		config = config || {};

		// Parent constructor
		mw.flow.ui.BoardDescriptionWidget.super.call( this, config );

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

		this.button.connect( this, { click: 'onEditButtonClick' } );
		// HACK: For the moment, call for the 'old' system's editor by
		// mimicking the button behavior
		// this.button.$element
		// 	.data( 'flow-api-handler', 'activateEditHeader' )
		// 	.data( 'flow-api-target', '< .flow-board-header' )
		// 	.data( 'flow-interactive-handler', 'apiRequest' );
		// this.button.$element.on( 'click', flowBoard.emitWithReturn.bind( flowBoard, 'interactiveHandlerFocus', [ $( '.flow-board-header' ) ] ) );

		// Initialize
		this.$element
			.append(
				this.button.$element,
				this.$content
			)
			.addClass( 'flow-ui-boardDescriptionWidget flow-ui-boardDescriptionWidget-nojs' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.BoardDescriptionWidget, OO.ui.Widget );

	/* Methods */

	/**
	 * Respond to edit button click
	 *
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	mw.flow.ui.BoardDescriptionWidget.prototype.onEditButtonClick = function () {

		// Change "header" to "header_edit" so that it loads up flow_block_header_edit
		// data.flow[ 'view-header' ].result.header.type = 'header_edit';
		// $rendered = $(
		// 	this.flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
		// 		'flow_block_loop',
		// 		{ blocks: data.flow[ 'view-header' ].result }
		// 	)
		// ).children();

console.log( 'click' );
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
	 * @param {[type]} model [description]
	 * @return {[type]} [description]
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
