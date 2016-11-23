/*!
 * Expose some functionality on the board object that is needed for VisualEditor.
 */

( function ( $, mw, OO ) {
	/**
	 * FlowBoardComponentVisualEditorFeatureMixin
	 *
	 * @this FlowBoardComponent
	 * @constructor
	 */
	function FlowBoardComponentVisualEditorFeatureMixin() {
	}

	// This is not really VE-specific, but I'm not sure where best to put it.
	// Also, should we pre-compute this in a loadHandler?
	/**
	 * Finds topic authors for the given node
	 *
	 * @param {jQuery} $node
	 * @return {string[]} List of usernames
	 */
	function flowVisualEditorGetTopicPosters( $node ) {
		var $topic = $node.closest( '.flow-topic' ),
			duplicatedArray;

		// Could use a data attribute to avoid trim.
		duplicatedArray = $.map( $topic.find( '.flow-author .mw-userlink' ).get(), function ( el ) {
			return $.trim( $( el ).text() );
		} );
		return OO.unique( duplicatedArray );
	}

	FlowBoardComponentVisualEditorFeatureMixin.prototype.getTopicPosters = flowVisualEditorGetTopicPosters;

	mw.flow.mixinComponent( 'board', FlowBoardComponentVisualEditorFeatureMixin );
}( jQuery, mediaWiki, OO ) );
