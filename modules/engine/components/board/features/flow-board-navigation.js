/*!
 * Contains board navigation header, which affixes to the viewport on scroll.
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for the board header itself.
	 * @class
	 * @constructor
	 * @param {jQuery} $container
	 * @this FlowComponent
	 */
	function FlowBoardComponentBoardHeaderFeatureMixin( $container ) {
		// Bind element handlers
		this.bindNodeHandlers( FlowBoardComponentBoardHeaderFeatureMixin.UI.events );

		/** {string} topic ID currently being read in viewport */
		this.readingTopicId = null;

		/** {Object} Map from topic id to its last update timestamp for sorting */
		this.updateTimestampsByTopicId = {};
	}
	OO.initClass( FlowBoardComponentBoardHeaderFeatureMixin );

	FlowBoardComponentBoardHeaderFeatureMixin.UI = {
		events: {
			apiPreHandlers: {},
			apiHandlers: {},
			interactiveHandlers: {},
			loadHandlers: {}
		}
	};

	//
	// Prototype methods
	//

	//
	// API pre-handlers
	//

	//
	// On element-click handlers
	//

	//
	// On element-load handlers
	//

	/**
	 * Bind the navigation header bar to the window.scroll event.
	 * @param {jQuery} $boardNavigation
	 */
	function flowBoardLoadEventsBoardNavigation( $boardNavigation ) {
		// initialize the board topicId sorting callback.  This expects to be rendered
		// as a sibling of the topiclist component.  The topiclist component includes
		// information about how it is currently sorted, so we can maintain that in the
		// TOC. This is typically either 'newest' or 'updated'.
		this.topicIdSort = $boardNavigation.siblings( '[data-flow-sortby]' ).data( 'flow-sortby' );
		this.updateTopicIdSortCallback();

	}
	FlowBoardComponentBoardHeaderFeatureMixin.UI.events.loadHandlers.boardNavigation = flowBoardLoadEventsBoardNavigation;

	//
	// Private functions
	//

	/**
	 * Initialize the topic id sort callback
	 */
	function _flowBoardUpdateTopicIdSortCallback() {
		if ( this.topicIdSort === 'newest' ) {
			// the sort callback takes advantage of the default utf-8
			// sort in this case
			this.topicIdSortCallback = undefined;
		} else if ( this.topicIdSort === 'updated' ) {
			this.topicIdSortCallback = flowBoardTopicIdGenerateSortRecentlyActive( this );
		} else {
			throw new Error( 'this.topicIdSort has an invalid value' );
		}
	}
	FlowBoardComponentBoardHeaderFeatureMixin.prototype.updateTopicIdSortCallback = _flowBoardUpdateTopicIdSortCallback;

	/**
	 * Generates Array#sort callback for sorting a list of topic ids
	 * by the 'recently active' sort order. This is a numerical
	 * comparison of related timestamps held within the board object.
	 * Also note that this is a reverse sort from newest to oldest.
	 * @param {Object} board Object from which to source
	 *  timestamps which map from topicId to its last updated timestamp
	 * @return {Function} Sort callback
	 * @return {string} return.a
	 * @return {string} return.b
	 * @return {number} return.return Per Array#sort callback rules
	 */
	function flowBoardTopicIdGenerateSortRecentlyActive( board ) {
		return function ( a, b ) {
			var aTimestamp = board.updateTimestampsByTopicId[a],
				bTimestamp = board.updateTimestampsByTopicId[b];

			if ( aTimestamp === undefined && bTimestamp === undefined ) {
				return 0;
			} else if ( aTimestamp === undefined ) {
				return 1;
			} else if ( bTimestamp === undefined ) {
				return -1;
			} else {
				return bTimestamp - aTimestamp;
			}
		};
	}

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentBoardHeaderFeatureMixin );
}( jQuery, mediaWiki ) );
