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
		this.loadPromise = null;

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

			// ContentEditable support
			'contentEditable' in document.createElement( 'div' ) &&

			// Since VE commit e2fab2f1ebf2a28f18b8ead08c478c4fc95cd64e, SVG is required
			document.createElementNS &&
			document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' ).createSVGRect
		);
	};

	/* Methods */

	/**
	 * Load the VisualEditor code and create this.target.
	 *
	 * It's safe to call this method multiple times, or to call it when loading is already
	 * complete: the same promise will be returned every time.
	 *
	 * @return {jQuery.Promise} Promise resolved when this.target has been created.
	 */
	mw.flow.ui.VisualEditorWidget.prototype.load = function () {
		var widget = this;
		if ( !this.loadPromise ) {
			this.loadPromise = mw.loader.using( 'ext.flow.visualEditor' )
				.then( function () {
					// HACK add i18n messages to VE
					ve.init.platform.addMessages( mw.messages.values );

					widget.target = new mw.flow.ve.Target();
					widget.$element.append( widget.target.$element );
				} );
		}
		return this.loadPromise;
	};

	/**
	 * Create a VE surface with the provided content in it.
	 * @param {string} content HTML to put in the surface
	 */
	mw.flow.ui.VisualEditorWidget.prototype.createSurface = function ( content ) {
		var dmDoc,
			htmlDoc = ve.createDocumentFromHtml( content );
		ve.init.mw.Target.static.fixBase( htmlDoc );
		dmDoc = ve.dm.converter.getModelFromDom( htmlDoc, {
			lang: mw.config.get( 'wgVisualEditor' ).pageLanguageCode,
			dir: mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		} );
		this.surface = this.target.addSurface( dmDoc, { placeholder: this.placeholder } );
		// afterAttach() calls setSurface

		// Add directionality class
		this.surface.getView().getDocument().getDocumentNode().$element.addClass(
			'mw-content-' + mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		);

		// Relay events
		this.surface.getModel().connect( this, { documentUpdate: [ 'emit', 'change' ] } );
		this.surface.connect( this, { switchEditor: [ 'emit', 'switch' ] } );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.setup = function ( content ) {
		return this.load().then( this.createSurface.bind( this, content ) );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.afterAttach = function () {
		this.target.setSurface( this.surface );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.teardown = function () {
		this.target.clearSurfaces();
		return $.Deferred().resolve().promise();
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
	mw.flow.ui.VisualEditorWidget.prototype.moveCursorToEnd = function () {
		if ( !this.target ) {
			return;
		}

		var data = this.target.surface.getModel().getDocument().data,
			cursorPos = data.getNearestContentOffset( data.getLength(), -1 );

		if ( cursorPos !== -1 ) {
			this.target.surface.getModel().setLinearSelection( new ve.Range( cursorPos ) );
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
	mw.flow.ui.VisualEditorWidget.prototype.setContent = function ( content ) {
		this.target.clearSurfaces();
		this.createSurface( content );
		this.target.setSurface( this.surface );
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.prototype.isEmpty = function () {
		return !this.target.getSurface().getModel().getDocument().data.hasContent();
	};

	/**
	 * Check if there are any changes made to the data in the editor
	 *
	 * @return {boolean} The original content has changed
	 */
	mw.flow.ui.VisualEditorWidget.prototype.hasBeenChanged = function () {
		// If we haven't fully loaded yet, just return false
		if ( !this.target ) {
			return false;
		}

		return this.target.getSurface().getModel().hasBeenModified();
	};

	mw.flow.ui.VisualEditorWidget.prototype.setDisabled = function ( disabled ) {
		// Parent method
		mw.flow.ui.VisualEditorWidget.parent.prototype.setDisabled.call( this, disabled );

		if ( this.target ) {
			this.target.setDisabled( !!disabled );
		}
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
