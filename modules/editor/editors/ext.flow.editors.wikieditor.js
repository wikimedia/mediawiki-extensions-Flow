( function ( $, mw ) {
	'use strict';

	/**
	 * @param {jQuery} $node
	 * @param {string} [content='']
	 */
	mw.flow.editors.wikieditor = function ( $node, content ) {
		this.$node = $node;

		// load dependencies & init editor
		mw.loader.using( this.getModules(), $.proxy( this.init, this, content || '' ) );
	};

	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var {string}
	 */
	mw.flow.editors.wikieditor.format = 'wikitext';

	/**
	 * Callback function, executed after all VE dependencies have been loaded.
	 *
	 * @param {string} [content='']
	 */
	mw.flow.editors.wikieditor.prototype.init = function ( content ) {
		var config = mw.config.get( 'wgWikiEditorEnabledModules' );

		this.$node.val( content || '' );

		this.$node.wikiEditor();

		if ( config.toolbar ) {
			this.$node.wikiEditor( 'addModule', $.wikiEditor.modules.toolbar.config.getDefaultConfig() );
		}
		if ( config.dialogs ) {
			this.$node.wikiEditor( 'addModule', $.wikiEditor.modules.dialogs.config.getDefaultConfig() );
		}
	};

	mw.flow.editors.wikieditor.prototype.destroy = function () {
		$.wikiEditor.instances.splice( $.inArray( this.$node, $.wikiEditor.instances ), 1 );
		this.$node.removeData( 'wikiEditor-context' );
		this.$node.replaceAll( this.$node.closest( '.wikiEditor-ui' ) );
	};

	/**
	 * Get all resourceloader modules that should be loaded.
	 *
	 * @return {array}
	 */
	mw.flow.editors.wikieditor.prototype.getModules = function () {
		return [
			'jquery.wikiEditor',

			'jquery.wikiEditor.toolbar',
			'jquery.wikiEditor.toolbar.config',

			'jquery.wikiEditor.dialogs',
			'jquery.wikiEditor.dialogs.config'
		];
	};

	/**
	 * @return {string}
	 */
	mw.flow.editors.wikieditor.prototype.getRawContent = function () {
		return this.$node.val();
	};
} ( jQuery, mediaWiki ) );
