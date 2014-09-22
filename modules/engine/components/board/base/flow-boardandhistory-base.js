/*!
 *
 */

( function ( $, mw ) {
	/**
	 *
	 * @param {jQuery} $container
	 * @return {FlowBoardAndHistoryComponentBase|bool}
	 * @constructor
	 */
	function FlowBoardAndHistoryComponentBase( $container ) {
		if ( this.constructor.static.name === 'boardAndHistoryBase' ) {
			throw new Error( 'Cannot use FlowBoardAndHistoryComponentBase directly');
		}
	}
	OO.initClass( FlowBoardAndHistoryComponentBase );

	// Register
	mw.flow.registerComponent( 'boardAndHistoryBase', FlowBoardAndHistoryComponentBase );

	//
	// Methods
	//

	/**
	 * Sets up the board and base properties on this class.
	 * Returns either FALSE for failure, or jQuery object of old nodes that were replaced.
	 * @return {Boolean|jQuery}
	 */
	FlowBoardAndHistoryComponentBase.prototype.reinitializeContainer = function ( $container ) {
		if ( $container === false ) {
			return false;
		}

		$container = $( $container );
		var $retObj = $();

		// Progressively enhance the board and its forms
		// @todo Needs a ~"liveUpdateComponents" method, since the functionality in makeContentInteractive needs to also run when we receive new content or update old content.
		// @todo move form stuff
		this.emitWithReturn( 'makeContentInteractive', this );

		// Bind any necessary event handlers to this board
		this.bindContainerHandlers( this );

		// Initialize editors, turning them from textareas into editor objects
		if ( typeof this.editorTimer === 'undefined' ) {
			/*
			 * When this method is first run, all page elements are initialized.
			 * We probably don't need editor immediately, so defer loading it
			 * to speed up the rest of the work that needs to be done.
			 */
			this.editorTimer = setTimeout( $.proxy( function ( $container ) { this.emitWithReturn( 'initializeEditors', $container ); }, this, $container ), 20000 );
		} else {
			/*
			 * Subsequent calls here (e.g. when rendering the edit header form)
			 * should immediately initialize the editors!
			 */
			clearTimeout( this.editorTimer );
			this.emitWithReturn( 'initializeEditors', $container );
		}

		// Restore the last state
		this.constructor.static.HistoryEngine.restoreLastState();

		return $retObj;
	};

	/**
	 * Binds event handlers to individual boards
	 * @param {FlowBoardAndHistoryComponentBase} flowBoard
	 */
	FlowBoardAndHistoryComponentBase.prototype.bindContainerHandlers = function ( flowBoard ) {
		// Load the collapser state from localStorage
		this.collapserState( flowBoard );
	};

	//
	// Static methods
	//

	/**
	 * Return true page is in topic namespace,
	 * and if $el is given, that if $el is also within .flow-post.
	 * @param {jQuery} [$el]
	 * @returns {boolean}
	 */
	function flowBoardInTopicNamespace( $el ) {
		return inTopicNamespace && ( !$el || $el.closest( '.flow-post' ).length === 0 );
	}
	FlowBoardAndHistoryComponentBase.static.inTopicNamespace = flowBoardInTopicNamespace;

	var inTopicNamespace = ( mw.config.get( 'wgNamespaceNumber' ) || -1 ) === ( mw.config.get( 'wgNamespaceIds' ) || {} ).topic;

	// Register
	//mw.flow.extendComponent( 'board', FlowBoardAndHistoryComponentBase );
	//mw.flow.extendComponent( 'boardHistory', FlowBoardAndHistoryComponentBase );
}( jQuery, mediaWiki ) );