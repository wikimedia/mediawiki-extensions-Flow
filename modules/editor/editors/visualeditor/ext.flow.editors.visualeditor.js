( function ( $, mw, OO, ve ) {
	'use strict';

	/**
	 * @class
	 * @extends mw.flow.editors.AbstractEditor
	 * @constructor
	 * @param {jQuery} $node Node to replace with a VisualEditor
	 * @param {string} [content='']
	 */
	mw.flow.editors.visualeditor = function ( $node, content ) {
		// Parent constructor
		mw.flow.editors.visualeditor.parent.call( this );

		// node the editor is associated with.
		this.$node = $node;

		// HACK: make textarea look pending in case we didn't come from an editor switch
		// Once this is an OO.ui.TextInputWidget we'll be able to use real PendingElement
		// functionality for this
		$node
			.prop( 'disabled', true )
			.addClass( 'oo-ui-texture-pending' );

		// load dependencies & init editor
		mw.loader.using( 'ext.flow.visualEditor', $.proxy( this.init, this, content || '' ) );
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
		var $veNode, htmlDoc, surface, $documentNode,
			$focusedElement = $( ':focus' );

		// ve.createDocumentFromHtml documents support for an empty string
		// to create an empty document, but does not mention other falsy values.
		content = content || '';

		// add i18n messages to VE
		ve.init.platform.addMessages( mw.messages.values );

		this.target = ve.init.mw.targetFactory.create( 'flow' );

		// Fix missing base URL
		htmlDoc = ve.createDocumentFromHtml( content ); // HTMLDocument
		ve.init.mw.ArticleTarget.static.fixBase( htmlDoc );

		// Based on ve.init.mw.ArticleTarget.prototype.setupSurface
		this.dmDoc = ve.dm.converter.getModelFromDom( htmlDoc, {
			lang: mw.config.get( 'wgVisualEditor' ).pageLanguageCode,
			dir: mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		} );

		// attach VE to DOM
		surface = this.target.addSurface( this.dmDoc, { placeholder: this.$node.attr( 'placeholder' ) } );
		this.target.setSurface( surface );
		this.target.$element.insertAfter( this.$node );

		this.$node
			.hide()
			.removeClass( 'oo-ui-texture-pending' )
			.prop( 'disabled', false );

		// Add appropriately mw-content-ltr or mw-content-rtl class
		$documentNode = surface.getView().getDocument().getDocumentNode().$element;
		$documentNode.addClass(
			'mw-content-' + mw.config.get( 'wgVisualEditor' ).pageLanguageDir
		);

		// Pass surface focus state to parent
		surface.getView()
			.on( 'focus', $.proxy( function () {
				this.target.$element.addClass( 'flow-ui-focused' );
			}, this ) )
			.on( 'blur', $.proxy( function () {
				this.target.$element.removeClass( 'flow-ui-focused' );
			}, this ) );

		// focus VE instance if textarea had focus
		if ( !$focusedElement.length || this.$node.is( $focusedElement ) ) {
			surface.getView().focus();
		}

		$veNode = surface.$element.find( '.ve-ce-documentNode' );

		// HACK: simulate a keyup event on the original node, so the validation code will
		// pick up changes in the new node
		$veNode.keyup( $.proxy( function () {
			this.$node.keyup();
		}, this ) );

		surface.getModel().connect( this, { documentUpdate: [ 'emit', 'change' ] } );

		$.each( this.initCallbacks, $.proxy( function ( k, callback ) {
			callback.apply( this );
		}, this ) );
	};

	mw.flow.editors.visualeditor.prototype.destroy = function () {
		if ( this.target ) {
			this.target.destroy();
		}

		// re-display original node
		this.$node.show();
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
		html = ve.properInnerHtml( $( doc.documentElement ).find( 'body' )[ 0 ] );
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

	mw.flow.editors.visualeditor.prototype.focus = function () {
		if ( !this.target ) {
			this.initCallbacks.push( function () {
				this.focus();
			} );
			return;
		}

		this.target.surface.getView().focus();
	};

	mw.flow.editors.visualeditor.prototype.moveCursorToEnd = function () {
		if ( !this.target ) {
			this.initCallbacks.push( function () {
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
	 * Type of content to use
	 *
	 * @var {string}
	 */
	mw.flow.editors.visualeditor.static.format = 'html';

	/**
	 * Name of this editor
	 *
	 * @var string
	 */
	mw.flow.editors.visualeditor.static.name = 'visualeditor';

	// Static methods

	mw.flow.editors.visualeditor.static.isSupported = function () {
		var isMobileTarget = ( mw.config.get( 'skin' ) === 'minerva' );

		return !!(
			!isMobileTarget &&
			mw.loader.getState( 'ext.visualEditor.core' ) &&
			mw.config.get( 'wgFlowEditorList' ).indexOf( 'visualeditor' ) !== -1 &&
			window.VisualEditorSupportCheck && VisualEditorSupportCheck()
		);
	};

}( jQuery, mediaWiki, OO, ve ) );
