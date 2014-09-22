/*!
 * Implements element on-load callbacks for FlowBoardComponent
 */

( function ( $, mw ) {
	/**
	 * Binds element load handlers for FlowBoardComponent
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentLoadEventsMixin( $container ) {
		this.bindNodeHandlers( FlowBoardComponentLoadEventsMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentLoadEventsMixin );

	FlowBoardComponentLoadEventsMixin.UI = {
		events: {
			loadHandlers: {}
		}
	};

	//
	// On element-load handlers
	//

	/**
	 * Loads the last collapser states from sessionStorage and either expands or collapses based on
	 * what the user last did with a given element.
	 * @param {jQuery} $topic
	 */
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.collapserState = function ( $topic ) {
		var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $topic ),
			// Get last collapse state from sessionStorage
			stateForTopic, classForTopic,
			states = mw.flow.StorageEngine.sessionStorage.getItem( 'collapserStates' ) || {},
			topicId = $topic.data('flow-id'),
			STORAGE_TO_CLASS = {
				// Conserve space in browser storage
				'+': 'flow-element-expanded',
				'-': 'flow-element-collapsed'
			};

		// Don't apply to titlebars in the topic namespace
		if ( flowBoard.constructor.static.inTopicNamespace( $topic ) ) {
			return;
		}

		stateForTopic = states[ topicId ];
		if ( stateForTopic ) {
			// This item has an visibility override previously, so reapply the class

			classForTopic = STORAGE_TO_CLASS[stateForTopic];

			if ( classForTopic === 'flow-element-expanded' ) {
				// Remove flow-element-collapsed first (can be set on server for moderated), so it
				// doesn't clash.
				$topic.removeClass( 'flow-element-collapsed' );
			}

			$topic.addClass( classForTopic );
		}

		// Mark them as active to allow specific styling for active elements
		$topic.addClass( 'flow-element-collapsible-active' );
	};

	/**
	 * Replaces $time with a new time element generated by TemplateEngine
	 * @param {jQuery} $time
	 */
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.timestamp = function ( $time ) {
		$time.replaceWith(
			mw.flow.TemplateEngine.callHelper(
				'timestamp',
				parseInt( $time.attr( 'datetime' ), 10) * 1000,
				$time.data( 'time-str' ),
				$time.data( 'time-ago-only' ) === "1",
				$time.text()
			)
		);
	};

	/**
	 * Stores the load more button for use with infinite scroll.
	 * @param {jQuery} $button
	 */
	FlowBoardComponentLoadEventsMixin.UI.events.loadHandlers.loadMore = function ( $button ) {
		var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $button );

		if ( !flowBoard.$loadMoreNodes ) {
			// Create a new $loadMoreNodes list
			flowBoard.$loadMoreNodes = $();
		} else {
			// Remove any loadMore nodes that are no longer in the body
			flowBoard.$loadMoreNodes = flowBoard.$loadMoreNodes.filter( function () {
				return $( this ).closest( 'body' ).length;
			} );
		}

		// Store this new loadMore node
		flowBoard.$loadMoreNodes = flowBoard.$loadMoreNodes.add( $button );
	};

	//
	// Private functions
	//

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentLoadEventsMixin );
}( jQuery, mediaWiki ) );