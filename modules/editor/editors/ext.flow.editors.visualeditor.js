( function( $, mw ) {
/**
 * @param jQuery $node
 * @param string[optional] content
 */
mw.flow.editors.visualeditor = function( $node, content ) {
	/**
	 * Node the editor is associated with.
	 *
	 * @var jQuery
	 */
	this.$node = $node;

	// load dependencies & init editor
	mw.loader.using( this.getModules(), this.init.bind( this, content || '' ) );
};

/**
 * Type of content to use (html or wikitext)
 *
 * @var string
 */
mw.flow.editors.visualeditor.format = 'html';

/**
 * Callback function, executed after all VE dependencies have been loaded.
 *
 * @param string[optional] content
 */
mw.flow.editors.visualeditor.prototype.init = function( content ) {
	var $veNode;

	// add i18n messages to VE
	ve.init.platform.addMessages( mw.messages.values );

	this.$node.hide();

	// init ve, save target object
	this.target = new ve.init.sa.Target(
		// ve does not "convert" textareas = bind to new div
		$( '<div>' ).insertAfter( this.$node ),
		ve.createDocumentFromHtml( content || '' )
	);

	// focus VE instance if textarea had focus
	$veNode = this.target.surface.$.find( '.ve-ce-documentNode' );
	if ( this.$node.is( ':focus' ) ) {
		$veNode.focus();
	}

	// simulate a keyup event on the original node, so the validation code will
	// pick up changes in the new node
	$veNode.keyup( function() {
		this.$node.keyup();
	}.bind( this ) );
};

mw.flow.editors.visualeditor.prototype.destroy = function() {
	this.target.surface.destroy();
	this.target.toolbar.destroy();

	// re-display original node
	this.$node.show();
};

/**
 * Get all resourceloader modules that should be loaded.
 *
 * @return array
 */
mw.flow.editors.visualeditor.prototype.getModules = function() {
	var
		// core setup
		core =
			mw.config.get( 'wgVisualEditorConfig' ).enableExperimentalCode ?
				['ext.visualEditor.experimental'] :
				['ext.visualEditor.core'],

		// standalone target
		standalone = ['ext.visualEditor.standalone'],

		// messages module
		messages = ['ext.visualEditor.specialMessages'],

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
 * @return string
 */
mw.flow.editors.visualeditor.prototype.getRawContent = function() {
	var doc;

	// get document from ve
	doc = this.target.surface.getModel().getDocument();
	doc = ve.dm.converter.getDomFromData( doc.getFullData(), doc.getStore(), doc.getInternalList() );

	// document content will include html, head & body nodes; get only content inside body node
	return $( ve.properOuterHtml( doc.documentElement ) ).wrapAll( '<div>' ).parent().html();
};
} )( jQuery, mediaWiki );
