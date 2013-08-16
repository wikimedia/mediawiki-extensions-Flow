( function( $, mw ) {
/**
 * @param jQuery $node
 * @param string[optional] content
 */
mw.flow.editors.ve = function( $node, content ) {
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
mw.flow.editors.ve.contentType = 'html';

/**
 * Callback function, executed after all VE dependencies have been loaded.
 *
 * @param string[optional] content
 */
mw.flow.editors.ve.prototype.init = function( content ) {
	// add i18n messages to VE
	ve.init.platform.addMessages( mw.messages.values );

	// ve does not "convert" textareas = save ve reference to old node, hide node, bind to new div
	this.$node.hide();
	this.$node = $( '<div>' ).insertAfter( this.$node );

	// init ve, focus & save target object
	this.target = new ve.init.sa.Target( this.$node, ve.createDocumentFromHtml( content || '' ) );
	this.target.surface.$.find( '.ve-ce-documentNode' ).focus();
};

mw.flow.editors.ve.prototype.destroy = function() {
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
mw.flow.editors.ve.prototype.getModules = function() {
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
mw.flow.editors.ve.prototype.getContent = function() {
	var doc;

	// get document from ve
	doc = this.target.surface.getModel().getDocument();
	doc = ve.dm.converter.getDomFromData( doc.getFullData(), doc.getStore(), doc.getInternalList() );

	// document content will include html, head & body nodes; get only content inside body node
	return $( ve.properOuterHtml( doc.documentElement ) ).wrapAll( '<div>' ).parent().html();
};
} )( jQuery, mediaWiki );
