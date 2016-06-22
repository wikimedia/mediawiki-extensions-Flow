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
			mw.loader.getState( 'ext.visualEditor.core' ) &&
			mw.config.get( 'wgFlowEditorList' ).indexOf( 'visualeditor' ) !== -1 &&
			window.VisualEditorSupportCheck && VisualEditorSupportCheck()
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

					widget.target = ve.init.mw.targetFactory.create( 'flow' );
					widget.$element.append( widget.target.$element );
				} );
		}
		return this.loadPromise;
	};

	/**
	 * Create a VE surface with the provided content in it.
	 *
	 * @param {string} content HTML to put in the surface (body only)
	 */
	mw.flow.ui.VisualEditorWidget.prototype.createSurface = function ( content ) {
		var widget = this,
			deferred = $.Deferred();

		this.target.loadHtml( content );
		this.target.once( 'surfaceReady', function () {
			var surface = widget.target.getSurface();

			surface.setPlaceholder( widget.placeholder );
			// Relay events
			surface.getModel().connect( widget, { documentUpdate: [ 'emit', 'change' ] } );
			surface.connect( widget, { switchEditor: [ 'emit', 'switch' ] } );
			deferred.resolve();
		} );
		return deferred.promise();
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
		if ( this.target ) {
			this.target.getSurface().getModel().selectLastContentOffset();
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
