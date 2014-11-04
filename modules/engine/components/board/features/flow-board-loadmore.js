/*!
 * Contains loadMore and jumpToTopic functionality.
 */

( function ( $, mw ) {
	/**
	 *
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentLoadMoreFeatureMixin( $container ) {
		this.bindNodeHandlers( FlowBoardComponentLoadMoreFeatureMixin.UI.events );
		this.on( 'scroll', _flowBoardComponentLoadMoreFeatureInfiniteScrollCheck );
	}
	OO.initClass( FlowBoardComponentLoadMoreFeatureMixin );

	/** Stores a reference of all topics currently on the page */
	FlowBoardComponentLoadMoreFeatureMixin.prototype.loadedTopics = {};
	/** Stores a list of all topic IDs we know about, in their sorted order */
	FlowBoardComponentLoadMoreFeatureMixin.prototype.topicsList = [];

	FlowBoardComponentLoadMoreFeatureMixin.UI = {
		events: {
			apiHandlers: {},
			loadHandlers: {}
		}
	};

	//
	// Prototype methods
	//

	/**
	 * Scrolls up or down to a specific topic, and loads any topics it needs to.
	 * 1. If topic is loaded, scrolls to it.
	 * 2. If topic is not loaded, but is within TOPICS_PER_PAGE of the last loaded topic, we load and scroll.
	 * 3. If topic is not loaded and is beyond TOPICS_PER_PAGE, we load it and jump to it (no scroll).
	 * 3b. When the user scrolls up, we begin loading the topics in between.
	 * @param {String} topicId
	 */
	function flowBoardComponentLoadMoreFeatureJumpTo( topicId ) {
		var flowBoard = this,
			// Scrolls to the given topic, but disables infinite scroll loading while doing so
			_scrollWithoutInfinite = function () {
				flowBoard.infiniteScrollDisabled = true;
				flowBoard.loadedTopics[ topicId ].conditionalScrollIntoView().queue( function ( next ) {
					delete flowBoard.infiniteScrollDisabled;

					// jQuery.dequeue
					next();
				});
			};

		if ( flowBoard.loadedTopics[ topicId ] ) {
			// 1. Topic is already on the page; just scroll to it
			_scrollWithoutInfinite();
			return;
		}

		// 2. @todo this requires the topics list API

		// 3. Topic is beyond; load only that topic and jump to it
		flowBoard.Api.apiCall( {
			action: 'flow',
			submodule: 'view-topiclist',
			'vtloffset-dir': 'fwd', // @todo support "middle" dir
			'vtlinclude-offset': true,
			'vtllimit': 1, // @todo remove this and use the default
			'vtloffset-id': topicId
		} ).done( function( data ) {
			_flowBoardComponentLoadMoreFeatureRenderTopics(
				flowBoard,
				data.flow['view-topiclist'].result.topiclist
			);
		} ).fail( function( code, data ) {
			// Failed fetching the new data to be displayed.
			// @todo how do we render this?
			// $target = ????
			// flowBoard.emitWithReturn( 'removeError', $target );
			// var errorMsg = flowBoard.constructor.static.getApiErrorMessage( code, result );
			// errorMsg = mw.msg( '????', errorMsg );
			// flowBoard.emitWithReturn( 'showError', $target, errorMsg );
		} );
	}
	FlowBoardComponentLoadMoreFeatureMixin.prototype.jumpToTopic = flowBoardComponentLoadMoreFeatureJumpTo;

	//
	// API callback handlers
	//

	/**
	 * Loads more content
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	function flowBoardComponentLoadMoreFeatureApiCallback( info, data, jqxhr ) {
		var $target = info.$target,
			flowBoard = info.component;

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		// Render topics
		_flowBoardComponentLoadMoreFeatureRenderTopics(
			flowBoard,
			data.flow['view-topiclist'].result.topiclist,
			flowBoard.$container.find( flowBoard.$loadMoreNodes ).last()[ 0 ] === this, // if this is the last load more button
			$target
		);

		// Remove the old load button (necessary if the above load_more template returns nothing)
		$target.remove();

		/*
		 * Fire infinite scroll check again - if no (or few) topics were
		 * added (e.g. because they're moderated), we should immediately
		 * fetch more instead of waiting for the user to scroll again (when
		 * there's no reason to scroll)
		 */
		flowBoard.emitWithReturn( 'scroll' );
		return $.Deferred().resolve().promise();
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiHandlers.loadMore = flowBoardComponentLoadMoreFeatureApiCallback;

	//
	// On element-load handlers
	//

	/**
	 * Stores the load more button for use with infinite scroll.
	 * @param {jQuery} $button
	 */
	function flowBoardComponentLoadMoreFeatureElementLoadCallback( $button ) {
		if ( !this.$loadMoreNodes ) {
			// Create a new $loadMoreNodes list
			this.$loadMoreNodes = $();
		} else {
			// Remove any loadMore nodes that are no longer in the body
			this.$loadMoreNodes = this.$loadMoreNodes.filter( function () {
				return $( this ).closest( 'body' ).length;
			} );
		}

		// Store this new loadMore node
		this.$loadMoreNodes = this.$loadMoreNodes.add( $button );
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.loadMore = flowBoardComponentLoadMoreFeatureElementLoadCallback;

	/**
	 * Stores a list of all topics currently visible on the page.
	 * @param {jQuery} $topic
	 */
	function flowBoardComponentLoadMoreFeatureElementLoadTopic( $topic ) {
		var self = this;

		// Store this topic by ID
		this.loadedTopics[ $topic.data( 'flowId' ) ] = $topic;

		// Remove any topics that are no longer on the page, just in case
		$.each( this.loadedTopics, function ( topicId, $topic ) {
			if ( !$topic.closest( self.$board ).length ) {
				delete self.loadedTopics[ topicId ];
			}
		} );
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.topic = flowBoardComponentLoadMoreFeatureElementLoadTopic;

	//
	// Private functions
	//

	/**
	 * Called on window.scroll. Checks to see if a FlowBoard needs to have more content loaded.
	 * @param {Event} event
	 */
	function _flowBoardComponentLoadMoreFeatureInfiniteScrollCheck( event ) {
		if ( this.infiniteScrollDisabled ) {
			// This happens when the topic navigation is used to jump to a topic
			// We should not infinite-load anything when we are scrolling to a topic
			return;
		}

		var $window = $( window ),
			threshold = $window.height(),
			scrollTop = $window.scrollTop();

		// Check each loadMore button
		( this.$loadMoreNodes || $() ).each( function () {
			var $this = $( this ),
				offsetTop = $this.offset().top,
				offsetBottom = offsetTop + $this.outerHeight();

			// First, is this element above or below us?
			if ( offsetBottom < scrollTop ) {
				// The element is above the viewport; don't use it.
				return;
			}

			// Is this element in the viewport, or at most within the next viewport frame?
			if ( scrollTop + threshold * 2 > offsetTop ) {
				// Yes, trigger it.
				$( this ).trigger( 'click' );
			}
		} );
	}

	/**
	 * Renders and inserts a list of new topics.
	 * @param {FlowBoardComponent} flowBoard
	 * @param {Object} topicsData
	 * @param {boolean} [forceShowLoadMore]
	 * @param {jQuery} [$insertAt]
	 * @private
	 */
	function _flowBoardComponentLoadMoreFeatureRenderTopics( flowBoard, topicsData, forceShowLoadMore, $insertAt ) {
		if ( !topicsData.roots.length ) {
			flowBoard.debug( 'No topics returned from API', arguments );
			return;
		}

		var i = 0,
			$prevExist, $nextExist, found, $newTopics = $(),
			// We need to find a specific topic ID within our list of known topic IDs
			findTopicId = topicsData.roots[0];

		if ( topicsData.submitted[ 'offset-dir' ] === 'rev' ) {
			// In reverse mode, we need to find the last item
			findTopicId = topicsData.roots[ topicsData.roots - 1 ];
		}

		// With the load more button, we already know where to put these topics
		if ( !$insertAt ) {
			// But for jumpTo, we don't, so we use topicsList to find out where they will go
			for ( ; i < flowBoard.topicsList.length; i++ ) {
				if ( flowBoard.loadedTopics[flowBoard.topicsList[i]] ) {
					if ( found ) {
						// We found an insertion location after the given topic
						$nextExist = flowBoard.loadedTopics[flowBoard.topicsList[i]];
						break; // we already have the topic and somewhere to insert it
					} else {
						// We found an insertion location before the given topic
						$prevExist = flowBoard.loadedTopics[flowBoard.topicsList[i]];
					}
				}

				if ( flowBoard.topicsList[i] === findTopicId ) {
					// We found this topic in our list of known topics
					found = true;

					if ( $prevExist ) {
						break; // We have somewhere to insert this
					}
				}
			}
		}

		// Don't render any topics that we already have on the page
		// @todo implement live topic updating
		topicsData.roots = $.grep( topicsData.roots, function( topicId ){
			return !flowBoard.loadedTopics[ topicId ];
		} );

		// Render the new topics

		// See bug 61097, Catch any random javascript error from
		// parsoid so they don't break and stop the page
		try {
			$newTopics = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_topiclist_loop',
				topicsData
			) ).children();
		} catch( e ) {
			// nothing to do, just silently ignore the external error
			return;
		}

		// Insert the new topics...
		if ( $insertAt || $prevExist ) {
			// ...after
			// Load more fwd
			if ( forceShowLoadMore || topicsData.links.pagination.fwd ) {
				// Add the load more to the end of the stack
				$newTopics = $newTopics.add(
					$( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
						'flow_load_more',
						{ loadMoreObject: topicsData.links.pagination.fwd }
					) ).children()
				);
			}

			// Drop in
			( $insertAt || $prevExist ).after( $newTopics );
		} else if ( $nextExist ) {
			// ...before
			// Load more rev
			if ( topicsData.links.pagination.rev ) {
				// Add the load more to the top of the stack
				$newTopics = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
						'flow_load_more',
						{ loadMoreObject: topicsData.links.pagination.rev }
					) ).children()
					.add( $newTopics );
			}

			// Drop in
			$nextExist.before( $newTopics );
		} else {
			// ...or not at all
			flowBoard.debug( 'Could not find somewhere to insert topics', arguments );
			return;
		}

		// Run loadHandlers
		flowBoard.emitWithReturn( 'makeContentInteractive', $newTopics );
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentLoadMoreFeatureMixin );
}( jQuery, mediaWiki ) );
