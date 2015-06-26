( function ( $ ) {
	/**
	 * Flow visualeditor editor widget
	 *
	 * @class
	 * @extends mw.flow.ui.AbstractEditorWidget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [content] An initial content for the textarea
	 */
	mw.flow.ui.VisualEditorWidget = function mwFlowUiVisualEditorWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.VisualEditorWidget.parent.call( this, config );

		this.$element.addClass( 'flow-ui-visualEditorWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.VisualEditorWidget, mw.flow.ui.AbstractEditorWidget );

	/* Static Methods */

	/**
	 * Type of content to use
	 *
	 * @var {string}
	 */
	mw.flow.ui.VisualEditorWidget.static.format = 'html';

	/**
	 * Name of this editor
	 *
	 * @var string
	 */
	mw.flow.ui.VisualEditorWidget.static.name = 'visualeditor';

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.static.isSupported = function () {
		var isMobileTarget = ( mw.config.get( 'skin' ) === 'minerva' );

		return !!(
			!isMobileTarget &&

			// ES5 support, from es5-skip.js
			( function () {
				'use strict';
				return !this && !!Function.prototype.bind;
			}() ) &&

			// Since VE commit e2fab2f1ebf2a28f18b8ead08c478c4fc95cd64e, SVG is required
			document.createElementNS &&
			document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' ).createSVGRect &&

			// ve needs to be turned on as a valid editor
			mw.config.get( 'wgFlowEditorList' ).indexOf( 'visualeditor' ) !== -1
		);
	};

	/* Methods */
	/**
	 * List of callbacks to execute when VE is fully loaded
	 */
	mw.flow.ui.VisualEditorWidget.prototype.initCallbacks = [];

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.initialize = function ( content ) {
		return mw.loader.using( 'ext.flow.visualEditor' )
			.then( this.initVE.bind( this, content || '' ) );
	};

	/**
	 * Initialize VisualEditor. This only runs after all dependencies are
	 * already loaded.
	 *
	 * @param {string} [content] Content to display in the editor
	 */
	mw.flow.ui.VisualEditorWidget.prototype.initVE = function ( content ) {
		var htmlDoc, $documentNode;

		// ve.createDocumentFromHtml documents support for an empty string
		// to create an empty document, but does not mention other falsy values.
		content = content || '';

		// add i18n messages to VE
		ve.init.platform.addMessages( mw.messages.values );

		this.target = new mw.flow.ve.Target();

		// Fix missing base URL
		htmlDoc = ve.createDocumentFromHtml( content ); // HTMLDocument
		ve.init.mw.Target.static.fixBase( htmlDoc );

		// Based on ve.init.mw.Target.prototype.setupSurface
		this.dmDoc = ve.dm.converter.getModelFromDom( htmlDoc, {
			lang: mw.config.get( 'wgVisualEditor' ).pageLanguageCode,
			dir: mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		} );

		// attach VE to DOM
		this.surface = this.target.addSurface( this.dmDoc );
		this.target.setSurface( this.surface );
		this.$element.append( this.target.$element );

		// this.$node
		// 	.hide()
		// 	.removeClass( 'oo-ui-texture-pending' )
		// 	.prop( 'disabled', false );

		// Add appropriately mw-content-ltr or mw-content-rtl class
		$documentNode = this.surface.getView().getDocument().getDocumentNode().$element;
		$documentNode.addClass(
			'mw-content-' + mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		);

		// Pass surface focus state to parent
		this.surface.getView()
			.on( 'focus', this.target.$element.addClass.bind( this.target.$element, 'flow-ui-focused' ) )
			.on( 'blur', this.target.$element.removeClass.bind( this.target.$element, 'flow-ui-focused' ) );

		// focus VE instance if textarea had focus
		// if ( !$focusedElement.length || this.$node.is( $focusedElement ) ) {
		// 	surface.getView().focus();
		// }

		// $veNode = this.urface.$element.find( '.ve-ce-documentNode' );

		// // HACK: simulate a keyup event on the original node, so the validation code will
		// // pick up changes in the new node
		// $veNode.keyup( $.proxy( function () {
		// 	this.$node.keyup();
		// }, this ) );

		this.surface.getModel().connect( this, { documentUpdate: [ 'emit', 'change' ] } );
		this.surface.connect( this, { switchEditor: [ 'emit', 'switch' ] } );

		$.each( this.initCallbacks, $.proxy( function ( k, callback ) {
			callback.apply( this );
		}, this ) );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.reloadContent = function () {
		return $.Deferred().resolve().promise(); // TESTING ONLY
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.focus = function () {
		if ( !this.target ) {
			this.initCallbacks.push( function () {
				this.focus();
			} );
			return;
		}

		this.target.surface.getView().focus();
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.getRawContent = function () {
		var doc, html;

		// If we haven't fully loaded yet, just return nothing.
		if ( !this.target ) {
			return '';
		}

		// get document from ve
		doc = ve.dm.converter.getDomFromModel( this.dmDoc );

		// document content will include html, head & body nodes; get only content inside body node
		html = ve.properInnerHtml( $( doc.documentElement ).find( 'body' )[0] );
		return html;
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.destroy = function () {
		if ( this.target ) {
			this.target.destroy();
		}
	};
}( jQuery ) );
