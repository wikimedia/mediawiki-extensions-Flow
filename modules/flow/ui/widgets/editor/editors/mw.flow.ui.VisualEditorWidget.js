( function ( $ ) {
	/**
	 * Flow visualeditor editor widget
	 *
	 * @class
	 * @extends mw.flow.ui.AbstractEditorWidget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.VisualEditorWidget = function mwFlowUiVisualEditorWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.VisualEditorWidget.parent.call( this, config );

		this.switchable = config.switchable;
		this.placeholder = config.placeholder;

		this.$element.addClass( 'flow-ui-visualEditorWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.VisualEditorWidget, mw.flow.ui.AbstractEditorWidget );

	/* Static Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.static.format = 'html';

	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.load = function () {
		var widget = this;
		return mw.loader.using( 'ext.flow.visualEditor' )
			.then( function () {
				// HACK add i18n messages to VE
				ve.init.platform.addMessages( mw.messages.values );

				widget.target = new mw.flow.ve.Target();
				widget.$element.append( widget.target.$element );
			} );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.setup = function ( content ) {
		var dmDoc, surface,
			htmlDoc = ve.createDocumentFromHtml( content );
		ve.init.mw.Target.static.fixBase( htmlDoc );
		dmDoc = ve.dm.converter.getModelFromDom( htmlDoc, {
			lang: mw.config.get( 'wgVisualEditor' ).pageLanguageCode,
			dir: mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		} );
		surface = this.target.addSurface( dmDoc, { placeholder: this.placeholder } );
		this.target.setSurface( surface );

		// Add directionality class
		surface.getView().getDocument().getDocumentNode().$element.addClass(
			'mw-content-' + mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		);

		// Relay events
		surface.getView().connect( this, {
			focusin: [ 'emit', 'focusin' ],
			focusout: [ 'emit', 'focusout' ]
		} );
		surface.getModel().connect( this, { documentUpdate: [ 'emit', 'change' ] } );
		surface.connect( this, { switchEditor: [ 'emit', 'switch' ] } );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.focus = function () {
		if ( this.target ) {
			this.target.getSurface().getView().focus();
		}
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.getContent = function () {
		var doc, html;

		// If we haven't fully loaded yet, just return nothing.
		if ( !this.target ) {
			return '';
		}

		// get document from ve
		doc = ve.dm.converter.getDomFromModel( this.target.getSurface().getModel().getDocument() );

		// document content will include html, head & body nodes; get only content inside body node
		html = ve.properInnerHtml( doc.body );
		return html;
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.destroy = function () {
		if ( this.target ) {
			this.target.getSurface().getView().disconnect( this );
			this.target.getSurface().getModel().disconnect( this );
			this.target.getSurface().disconnect( this );
			this.target.destroy();
		}
	};
}( jQuery ) );
