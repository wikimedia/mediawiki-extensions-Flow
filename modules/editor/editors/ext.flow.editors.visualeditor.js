
( function ( $, mw, ve ) {
	'use strict';

	/**
	 * @param {jQuery} $node
	 * @param {string} [content='']
	 */
	mw.flow.editors.visualeditor = function ( $node, content ) {
		// node the editor is associated with.
		this.$node = $node;

		// Replace the node with a spinner
		$node.hide();
		$node.injectSpinner( {
			'size' : 'large',
			'type' : 'block',
			'id' : 'flow-editor-loading'
		} );

		// load dependencies & init editor
		mw.loader.using( this.getModules(), $.proxy( this.init, this, content || '' ) );
	};

	OO.inheritClass( mw.flow.editors.visualeditor, mw.flow.editors.AbstractEditor );

	/**
	 * List of callbacks to execute when VE is fully loaded
	 */
	mw.flow.editors.visualeditor.prototype.initCallbacks = [];

	/**
	 * Callback function, executed after all VE dependencies have been loaded.
	 *
	 * @param {string} [content='']
	 */
	mw.flow.editors.visualeditor.prototype.init = function ( content ) {
		var target, surface, $veNode,
			$focusedElement = $( ':focus' );

		// ve.createDocumentFromHtml documents support for an empty string
		// to create an empty document, but does not mention other falsy values.
		content = content || '';

		// add i18n messages to VE
		ve.init.platform.addMessages( mw.messages.values );

		target = new ve.init.sa.Target( 'desktop' );
		surface = target.addSurface(
			ve.dm.converter.getModelFromDom(
				ve.createDocumentFromHtml( content ),
				null,
				mw.config.get( 'wgVisualEditor' ).pageLanguageCode,
				mw.config.get( 'wgVisualEditor' ).pageLanguageDir
			)
		);

		$.removeSpinner( 'flow-editor-loading' );

		// attach VE to DOM
		target.setSurface( surface );
		surface.$element.parent().insertAfter( this.$node );

		// Add appropriately mw-content-ltr or mw-content-rtl class
		surface.getView().getDocument().getDocumentNode().$element.addClass(
			'mw-content-' + mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		);

		// focus VE instance if textarea had focus
		if ( !$focusedElement.length || this.$node.is( $focusedElement ) ) {
			surface.getView().focus();
		}

		// add class to editor, to give it our desired look & feel
		$veNode = surface.$element.find( '.ve-ce-documentNode' );
		$veNode.addClass( 'mw-ui-input' );

		// simulate a keyup event on the original node, so the validation code will
		// pick up changes in the new node
		$veNode.keyup( $.proxy( function () {
			this.$node.keyup();
		}, this ) );

		$.each( this.initCallbacks, $.proxy( function ( k, callback ) {
			callback.apply( this );
		}, this ) );
	};

	mw.flow.editors.visualeditor.prototype.destroy = function () {
		this.target.surface.destroy();
		this.target.toolbar.destroy();

		// re-display original node
		this.$node.show();
	};

	/**
	 * Get all resourceloader modules that should be loaded.
	 *
	 * @return {array}
	 */
	mw.flow.editors.visualeditor.prototype.getModules = function () {
		var core, standalone, messages, icons, plugins, specific;

		// core setup
		core = ['ext.visualEditor.core.desktop'];
		if ( mw.config.get( 'wgVisualEditorConfig' ).enableExperimentalCode ) {
			core.push( 'ext.visualEditor.experimental' );
		}

		// Standalone
		standalone = ['ext.visualEditor.standalone'];

		// data module
		messages = ['ext.visualEditor.data'];

		// icons
		icons = ['ext.visualEditor.icons'];

		// plugins
		plugins = [
			'ext.visualEditor.mwlink',
			'ext.visualEditor.mwgallery',
			'ext.visualEditor.mwtransclusion'
		];

		// site & user
		specific = ['site', 'user'];

		return [].concat(
			core,
			standalone,
			messages,
			icons,
			plugins,
			specific
		);
	};

	/**
	 * Gets HTML of Flow field
	 *
	 * @return {string}
	 */
	mw.flow.editors.visualeditor.prototype.getRawContent = function () {
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
	 * Checks if the document is empty
	 *
	 * @return {boolean} True if and only if it's empty
	 */
	mw.flow.editors.visualeditor.prototype.isEmpty = function () {
		if ( !this.dmDoc ) {
			return true;
		}

		// Per Roan
		return this.dmDoc.data.countNonInternalElements() <= 2;
	};

	mw.flow.editors.visualeditor.prototype.focus = function() {
		if ( !this.target ) {
			this.initCallbacks.push( function() {
				this.focus();
			} );
			return;
		}
		this.target.surface.$element.find( '.ve-ce-documentNode' ).focus();
	};

	mw.flow.editors.visualeditor.prototype.moveCursorToEnd = function () {
		if ( !this.target ) {
			this.initCallbacks.push( function() {
				this.moveCursorToEnd();
			} );
			return;
		}

		var data = this.target.surface.getModel().getDocument().data,
			cursorPos = data.getNearestContentOffset( data.getLength(), -1 );

		this.target.surface.getModel().setSelection( new ve.Range( cursorPos ) );
	};

	// Static fields

	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var {string}
	 */
	mw.flow.editors.visualeditor.static.format = 'html';

	// Static methods

	mw.flow.editors.visualeditor.static.isSupported = function () {
		return !!(
			mw.user.options.get( 'visualeditor-enable' ) &&
			// Since VE commit e2fab2f1ebf2a28f18b8ead08c478c4fc95cd64e, SVG is required
			document.createElementNS &&
			document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' ).createSVGRect
		);
	};

	mw.flow.editors.visualeditor.static.usesPreview = function () {
		return false;
	};
} ( jQuery, mediaWiki, ve ) );
