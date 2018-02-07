( function ( mw, OO, ve ) {
	'use strict';

	mw.flow.ve = {
		ui: {}
	};

	/**
	 * Flow-specific target, inheriting from the stand-alone target
	 *
	 * @class
	 * @extends ve.init.mw.Target
	 */
	mw.flow.ve.Target = function FlowVeTarget() {
		mw.flow.ve.Target.parent.call( this, {
			toolbarConfig: { actions: true, position: 'bottom' }
		} );

		this.switchingPromise = null;

		// HACK: stop VE's education popups from appearing (T116643)
		this.dummyToolbar = true;
	};

	OO.inheritClass( mw.flow.ve.Target, ve.init.mw.Target );

	/**
	 * @event switchMode
	 * @param {jQuery.Promise} promise Promise resolved when switch is complete
	 * @param {string} newMode Mode being switched to ('visual' or 'source')
	 */

	/**
	 * @event submit
	 */

	// Static

	mw.flow.ve.Target.static.name = 'flow';

	mw.flow.ve.Target.static.modes = [ 'visual', 'source' ];

	mw.flow.ve.Target.static.toolbarGroups = [
		{
			type: 'list',
			icon: 'textStyle',
			title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
			include: [ 'bold', 'italic' ],
			forceExpand: [ 'bold', 'italic' ]
		},

		{ include: [ 'link' ] },

		{ include: [ 'flowMention' ] }
	];

	// Allow pasting links
	mw.flow.ve.Target.static.importRules = ve.copy( mw.flow.ve.Target.static.importRules );
	mw.flow.ve.Target.static.importRules.external.blacklist = OO.simpleArrayDifference(
		mw.flow.ve.Target.static.importRules.external.blacklist,
		[ 'link/mwExternal' ]
	);

	mw.flow.ve.Target.static.actionGroups = [ {
		type: 'list',
		icon: 'edit',
		title: mw.msg( 'visualeditor-mweditmode-tooltip' ),
		include: [ 'editModeVisual', 'editModeSource' ]
	} ];

	// Methods

	/**
	 * Load content into the editor
	 * @param {string} content HTML or wikitext
	 */
	mw.flow.ve.Target.prototype.loadContent = function ( content ) {
		var doc = this.constructor.static.parseDocument( content, this.getDefaultMode() );
		this.documentReady( doc );
	};

	// These tools aren't available so don't bother generating them
	mw.flow.ve.Target.prototype.generateCitationFeatures = function () {};

	mw.flow.ve.Target.prototype.attachToolbar = function () {
		this.$element.after( this.getToolbar().$element );
	};

	mw.flow.ve.Target.prototype.setDisabled = function ( disabled ) {
		var i, len;
		for ( i = 0, len = this.surfaces.length; i < len; i++ ) {
			this.surfaces[ i ].setDisabled( disabled );
		}
	};

	/**
	 * Switch between visual and source mode.
	 *
	 * If a switch is already in progress, the promise for that switch is returned,
	 * and no new switch is initiated.
	 *
	 * @return {jQuery.Promise} Promise that is resolved when the switch is complete
	 */
	mw.flow.ve.Target.prototype.switchMode = function () {
		var newMode, oldFormat, newFormat, doc, content;

		if ( this.switchingDeferred ) {
			return this.switchingDeferred;
		}

		newMode = this.getDefaultMode() === 'visual' ? 'source' : 'visual';
		oldFormat = newMode === 'visual' ? 'wikitext' : 'html';
		newFormat = newMode === 'visual' ? 'html' : 'wikitext';
		doc = this.getSurface().getDom();
		// When coming from visual mode, getDom() returns an HTMLDocument, otherwise a string
		content = oldFormat === 'html' ? this.getHtml( doc ) : doc;

		this.setDefaultMode( newMode );
		this.switchingDeferred = $.Deferred();
		this.convertContent( content, oldFormat, newFormat )
			.then( this.loadContent.bind( this ) )
			.fail( this.switchingDeferred.reject );

		this.emit( 'switchMode', this.switchingDeferred, newMode );
		return this.switchingDeferred;
	};

	mw.flow.ve.Target.prototype.surfaceReady = function () {
		var deferred;

		// Parent method
		mw.flow.ve.Target.parent.prototype.surfaceReady.apply( this, arguments );

		if ( this.switchingDeferred ) {
			deferred = this.switchingDeferred;
			this.switchingDeferred = null;
			deferred.resolve();
		}

		// Re-emit main surface 'submit' as target 'submit'
		this.getSurface().on( 'submit', this.emit.bind( this, 'submit' ) );
	};

	/**
	 * Convert content from one format to another.
	 *
	 * It's safe to call this function with fromFormat=toFormat: in that case, the returned promise
	 * will be resolved immediately with the original content.
	 *
	 * @param {string} content Content in old format
	 * @param {string} fromFormat Old format name
	 * @param {string} toFormat New format name
	 * @return {jQuery.Promise} Promise resolved with content converted to new format
	 */
	mw.flow.ve.Target.prototype.convertContent = function ( content, fromFormat, toFormat ) {
		if ( fromFormat === toFormat ) {
			return $.Deferred().resolve( content ).promise();
		}
		if (
			content === '' ||
			( fromFormat === 'html' && content === '<!doctype html><html><head></head><body></body></html>' )
		) {
			return $.Deferred().resolve( '' ).promise();
		}

		return new mw.Api().post( {
			action: 'flow-parsoid-utils',
			from: fromFormat,
			to: toFormat,
			content: content,
			title: mw.config.get( 'wgPageName' )
		} )
			.then( function ( data ) {
				var content = data[ 'flow-parsoid-utils' ].content;
				if ( toFormat === 'html' ) {
					// loadContent() expects a full document, but the API only gives us the body content
					content = '<body>' + content + '</body>';
				}
				return content;
			}, function () {
				return mw.msg( 'flow-error-parsoid-failure' );
			} );
	};

	// Registration

	ve.init.mw.targetFactory.register( mw.flow.ve.Target );

}( mediaWiki, OO, ve ) );
