/*!
 * Expose some functionality on the board object that is needed for VisualEditor.
 */

( function ( $, mw ) {
	/**
	 * FlowBoardComponentVisualEditorFeatureMixin
	 *
	 * @this FlowBoardComponent
	 * @constructor
	 *
	 */
	function FlowBoardComponentVisualEditorFeatureMixin( $container ) {
	}

	// This is not really VE-specific, but I'm not sure where best to put it.
	// Also, should we pre-compute this in a loadHandler?
	/**
	 * Finds topic authors for the given node
	 *
	 * @return Array List of usernames
	 */
	function flowVisualEditorGetTopicPosters( $node ) {
		var $topic = $node.closest( '.flow-topic' ),
		duplicatedArray, topicPostersObject, topicPostersArray, poster, i;

		// TODO: Use OO.unique when https://gerrit.wikimedia.org/r/#/c/195971/
		// makes it to core in a few days.  Could also use a data attribute to avoid trim.
		duplicatedArray = $topic.find( '.flow-author .mw-userlink' ).map(
			function () {
				return $.trim( $( this ).text() );
			}
		);

		topicPostersObject = {};
		for ( i = 0; i < duplicatedArray.length; i++ ) {
			topicPostersObject[duplicatedArray[i]] = true;
		}

		topicPostersArray = [];
		for ( poster in topicPostersObject ) {
			topicPostersArray.push( poster );
		}

		return topicPostersArray;
	}

	FlowBoardComponentVisualEditorFeatureMixin.prototype.getTopicPosters = flowVisualEditorGetTopicPosters;

	mw.flow.mixinComponent( 'board', FlowBoardComponentVisualEditorFeatureMixin );
}( jQuery, mediaWiki ) );
