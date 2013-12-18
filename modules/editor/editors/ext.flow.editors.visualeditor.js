( function ( $, mw ) {
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
		mw.loader.using( this.getModules(), this.init.bind( this, content || '' ) );
	};

	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var {string}
	 */
	mw.flow.editors.visualeditor.format = 'html';

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
		var $veNode;

		// add i18n messages to VE
		window.ve.init.platform.addMessages( mw.messages.values );

		$.removeSpinner( 'flow-editor-loading' );
		var $focussedElement = $( ':focus' );

		// init ve, save target object
		this.target = new window.ve.init.sa.Target(
			// ve does not "convert" textareas = bind to new div
			$( '<div>' ).insertAfter( this.$node ),
			window.ve.createDocumentFromHtml( content || '' )
		);

		$veNode = this.target.surface.$element.find( '.ve-ce-documentNode' );

		// focus VE instance if textarea had focus
		if ( !$focussedElement.length || this.$node.is( $focussedElement ) ) {
			this.focus();
		} else {
			$focussedElement.focus();
		}

		$veNode.addClass( 'mw-ui-input' );

		// simulate a keyup event on the original node, so the validation code will
		// pick up changes in the new node
		$veNode.keyup( function () {
			this.$node.keyup();
		}.bind( this ) );

		$.each( this.initCallbacks, function( k, callback ) {
			callback.apply( this );
		}.bind( this ) );
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
		var
			// core setup
			core =
				mw.config.get( 'wgVisualEditorConfig' ).enableExperimentalCode ?
					['ext.visualEditor.experimental'] :
					['ext.visualEditor.core'],

			// standalone target
			standalone = ['ext.visualEditor.standalone'],

			// data module
			messages = ['ext.visualEditor.data'],

			// icons
			icons =
				document.createElementNS && document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' ).createSVGRect ?
					['ext.visualEditor.viewPageTarget.icons-vector', 'ext.visualEditor.icons-vector'] :
					['ext.visualEditor.viewPageTarget.icons-raster', 'ext.visualEditor.icons-raster'],

			// plugins
			plugins = mw.config.get( 'wgVisualEditorConfig' ).pluginModules || [],

			// site & user
			specific = ['site', 'user'];

		return [].concat( core, standalone, messages, icons, plugins, specific );
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.visualeditor.prototype.getRawContent = function () {
		// If we haven't fully loaded yet, just return nothing.
		if ( ! this.target ) {
			return '';
		}

		// get document from ve
		var model = this.target.surface.getModel(),
			doc = window.ve.dm.converter.getDomFromModel( model );

		// document content will include html, head & body nodes; get only content inside body node
		return $( window.ve.properOuterHtml( model.getDocument().documentElement ) ).wrapAll( '<div>' ).parent().html();
	};

	mw.flow.editors.visualeditor.isSupported = function() {
		return mw.user.options.get( 'visualeditor-enable' ) ? true : false;
	};

	mw.flow.editors.visualeditor.prototype.focus = function() {
		if ( ! this.target ) {
			this.initCallbacks.push( function() {
				this.focus();
			} );
			return;
		}
		this.target.surface.$element.find( '.ve-ce-documentNode' ).focus();
	};

	mw.flow.editors.visualeditor.prototype.moveCursorToEnd = function () {
		if ( ! this.target ) {
			this.initCallbacks.push( function() {
				this.moveCursorToEnd();
			} );
			return;
		}

		var data = this.target.surface.getModel().getDocument().data,
			cursorPos = data.getNearestContentOffset( data.getLength(), -1 );

		this.target.surface.getModel().setSelection( new ve.Range( cursorPos ) );
	};
} ( jQuery, mediaWiki ) );
