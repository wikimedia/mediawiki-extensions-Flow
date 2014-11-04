/*!
 * Contains loadMore, jumpToTopic, and topic titles list functionality.
 */

( function ( $, mw ) {
	/**
	 * Bind UI events and infinite scroll handler for load more and titles list functionality.
	 * @param {jQuery} $container
	 * @this FlowBoardComponent
	 * @constructor
	 */
	function FlowBoardComponentLoadMoreFeatureMixin( $container ) {
		/** Stores a reference to each topic element currently on the page */
		this.renderedTopics = {};
		/** Stores a list of all topics titles by ID */
		this.topicTitlesById = {};
		/** Stores a list of all topic IDs in order */
		this.orderedTopicIds = [];

		this.bindNodeHandlers( FlowBoardComponentLoadMoreFeatureMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentLoadMoreFeatureMixin );

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
	 * 1. If topic is rendered, scrolls to it.
	 * 2. Otherwise, we load the topic itself
	 * 3b. When the user scrolls up, we begin loading the topics in between.
	 * @param {String} topicId
	 */
	function flowBoardComponentLoadMoreFeatureJumpTo( topicId ) {
		/** @type FlowBoardComponent*/
		var flowBoard = this,
			// Scrolls to the given topic, but disables infinite scroll loading while doing so
			_scrollWithoutInfinite = function () {
				flowBoard.infiniteScrollDisabled = true;
				flowBoard.renderedTopics[ topicId ].conditionalScrollIntoView().queue( function ( next ) {
					delete flowBoard.infiniteScrollDisabled;

					// Focus on given topic
					flowBoard.$board.find('.flow-topic').filter('[data-flow-id=' + topicId + ']' ).click().focus();

					// jQuery.dequeue
					next();
				});
			};

		// 1. Topic is already on the page; just scroll to it
		if ( flowBoard.renderedTopics[ topicId ] ) {
			_scrollWithoutInfinite();
			return;
		}

		// 2a. Topic is not rendered; do we know about this topic ID?
		if ( !flowBoard.topicTitlesById[ topicId ] ) {
			// We don't. Abort!
			return flowBoard.debug( 'Unknown topicId', arguments );
		}

		// 2b. Load that topic and jump to it
		flowBoard.Api.apiCall( {
			action: 'flow',
			submodule: 'view-topiclist',
			'vtloffset-dir': 'fwd', // @todo support "middle" dir
			'vtlinclude-offset': true,
			'vtllimit': 1, // @todo remove this and use the default
			'vtloffset-id': topicId
		} )
			// Remove the load indicator
			.always( function () {
				// @todo support for multiple indicators on same target
				//$target.removeClass( 'flow-api-inprogress' );
				//$this.removeClass( 'flow-api-inprogress' );
			} )
			// On success, render the topic
			.done( function( data ) {
				/*
				 * @param {FlowBoardComponent} flowBoard
				 * @param {Object} topicsData
				 * @param {boolean} [forceShowLoadMore]
				 * @param {jQuery} [$insertAt]
				 * @param {String} [scrollTarget]
				 * @param {String} [scrollContainer]
				 * @param {String} [scrollTemplate]
				 * */
				_flowBoardComponentLoadMoreFeatureRenderTopics(
					flowBoard,
					data.flow[ 'view-topiclist' ].result.topiclist,
					false,
					null,
					'',
					'',
					'flow_topiclist_loop' // @todo clean up the way we pass these 3 params ^
				);

				_scrollWithoutInfinite();
			} )
			// On fail, render an error
			.fail( function( code, data ) {
				// Failed fetching the new data to be displayed.
				// @todo render the error at topic position and scroll to it
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
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 */
	function flowBoardComponentLoadMoreFeatureTopicsApiCallback( info, data, jqxhr ) {
		var $this = $( this ),
			$target = info.$target,
			flowBoard = info.component,
			scrollTarget = $this.data( 'flow-scroll-target' ),
			$scrollContainer = $.findWithParent( $this, $this.data( 'flow-scroll-container' ) || scrollTarget );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return;
		}

		// Render topics
		_flowBoardComponentLoadMoreFeatureRenderTopics(
			flowBoard,
			data.flow[ 'view-topiclist' ].result.topiclist,
			flowBoard.$container.find( flowBoard.$loadMoreNodes ).last()[ 0 ] === this, // if this is the last load more button
			$target,
			scrollTarget,
			$this.data( 'flow-scroll-container' ),
			$this.data( 'flow-template' )
		);

		// Remove the old load button (necessary if the above load_more template returns nothing)
		$target.remove();

		if ( scrollTarget === 'window' ) {
			scrollTarget = $( window );
		} else {
			scrollTarget = $.findWithParent( this, scrollTarget );
		}

		/*
		 * Fire infinite scroll check again - if no (or few) topics were
		 * added (e.g. because they're moderated), we should immediately
		 * fetch more instead of waiting for the user to scroll again (when
		 * there's no reason to scroll)
		 */
		_flowBoardComponentLoadMoreFeatureInfiniteScrollCheck.call( flowBoard, scrollTarget, $scrollContainer );
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiHandlers.loadMoreTopics = flowBoardComponentLoadMoreFeatureTopicsApiCallback;

	/**
	 * Saves the topic titles to topicTitlesById and orderedTopicIds.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 */
	function flowBoardComponentLoadMoreFeatureTopicListApiCallback( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return;
		}

		var i = 0,
			topicsData = data.flow[ 'view-topiclist' ].result.topiclist,
			topicId, revisionId,
			flowBoard = info.component;

		for ( ; i < topicsData.roots.length; i++ ) {
			// Get the topic ID
			topicId = topicsData.roots[ i ];
			// Get the revision ID
			revisionId = topicsData.posts[ topicId ][0];

			// Store the title from the revision object
			flowBoard.topicTitlesById[ topicId ] = topicsData.revisions[ revisionId ].content.content;
			// Add it in order
			flowBoard.orderedTopicIds.push( topicId );
		}
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiHandlers.topicList = flowBoardComponentLoadMoreFeatureTopicListApiCallback;

	//
	// On element-load handlers
	//

	/**
	 * Stores the load more button for use with infinite scroll.
	 * @example <button data-flow-scroll-target="< ul"></button>
	 * @param {jQuery} $button
	 */
	function flowBoardComponentLoadMoreFeatureElementLoadCallback( $button ) {
		var target = $button.data( 'flow-scroll-target' ),
			$container = $.findWithParent( $button, $button.data( 'flow-scroll-container' ) || target );

		if ( !this.$loadMoreNodes ) {
			// Create a new $loadMoreNodes list
			this.$loadMoreNodes = $();
		} else {
			// Remove any loadMore nodes that are no longer in the body
			this.$loadMoreNodes = this.$loadMoreNodes.filter( function () {
				var $this = $( this );

				// @todo unbind scroll handlers
				if ( !$this.closest( 'body' ).length ) {
					// Get rid of this and its handlers
					$this.remove();
					// Delete from list
					return false;
				}

				return true;
			} );
		}

		// Store this new loadMore node
		this.$loadMoreNodes = this.$loadMoreNodes.add( $button );

		// Make sure we didn't already bind to this element's scroll previously
		if ( $container.data( 'scrollIsBound' ) ) {
			return;
		}
		$container.data( 'scrollIsBound', true );

		// Bind the event for this
		if ( target === 'window' ) {
			this.on( 'windowScroll', _flowBoardComponentLoadMoreFeatureInfiniteScrollCheck, [ $container ] );
		} else {
			target = $.findWithParent( $button, target );
			target.on( 'scroll.flow', [ $container ], $.throttle( 50, $.proxy( _flowBoardComponentLoadMoreFeatureInfiniteScrollCheck, this ) ) );
		}
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.loadMore = flowBoardComponentLoadMoreFeatureElementLoadCallback;

	/**
	 * Stores a list of all topics currently visible on the page.
	 * @param {jQuery} $topic
	 */
	function flowBoardComponentLoadMoreFeatureElementLoadTopic( $topic ) {
		var self = this,
			currentTopicId = $topic.data( 'flow-id' );

		// Store this topic by ID
		this.renderedTopics[ currentTopicId ] = $topic;

		// Remove any topics that are no longer on the page, just in case
		$.each( this.renderedTopics, function ( topicId, $topic ) {
			if ( !$topic.closest( self.$board ).length ) {
				delete self.renderedTopics[ topicId ];
			}
		} );
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.topic = flowBoardComponentLoadMoreFeatureElementLoadTopic;

	/**
	 * Stores a list of all topics titles currently visible on the page.
	 * @param {jQuery} $topicTitle
	 */
	function flowBoardComponentLoadMoreFeatureElementLoadTopicTitle( $topicTitle ) {
		var currentTopicId = $topicTitle.closest( '[data-flow-id]' ).data( 'flowId' );

		// If topic doesn't exist in topic titles list, add it (only happens at page load)
		if ( !this.topicTitlesById[ currentTopicId ] ) {
			this.topicTitlesById[ currentTopicId ] = $topicTitle.data( 'flow-topic-title' );
			this.orderedTopicIds.push( currentTopicId );
		}
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.topicTitle = flowBoardComponentLoadMoreFeatureElementLoadTopicTitle;

	//
	// Private functions
	//

	/**
	 * Called on scroll. Checks to see if a FlowBoard needs to have more content loaded.
	 * @param {jQuery} $container
	 * @param {Event} event
	 */
	function _flowBoardComponentLoadMoreFeatureInfiniteScrollCheck( $container, event ) {
		if ( this.infiniteScrollDisabled ) {
			// This happens when the topic navigation is used to jump to a topic
			// We should not infinite-load anything when we are scrolling to a topic
			return;
		}

		// Might be passed via $.on data
		if ( !event ) {
			event = $container;
			$container = event.data[0];
		}

		// This is probably window, but for TOC it is another scrollable container
		var $scrollContainer = $( event.currentTarget ),
			threshold = $scrollContainer.height(),
			scrollTop = $scrollContainer.scrollTop() + ( $scrollContainer.offset() || { top: 0 } ).top;

		// Find load more buttons within our search container, and they must be visible
		$container.find( this.$loadMoreNodes ).filter( ':visible' ).each( function () {
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
	 * @param {String} [scrollTarget]
	 * @param {String} [scrollContainer]
	 * @param {String} [scrollTemplate]
	 * @private
	 */
	function _flowBoardComponentLoadMoreFeatureRenderTopics( flowBoard, topicsData, forceShowLoadMore, $insertAt, scrollTarget, scrollContainer, scrollTemplate ) {
		if ( !topicsData.roots.length ) {
			flowBoard.debug( 'No topics returned from API', arguments );
			return;
		}

		var i = 0,
			$prevExist, $nextExist, found, $newTopics = $(),
			// We need to find a specific topic ID within our list of known topic IDs
			findTopicId = topicsData.roots[ 0 ],
			wgFlowDefaultLimit = mw.config.get( 'wgFlowDefaultLimit' );

		if ( topicsData.submitted[ 'offset-dir' ] === 'rev' ) {
			// In reverse mode, we need to find the last item
			findTopicId = topicsData.roots[ topicsData.roots - 1 ];
		}

		// With the load more button, we already know where to put these topics
		if ( !$insertAt ) {
			// But for jumpTo, we don't, so we use topicsList to find out where they will go
			for ( ; i < flowBoard.orderedTopicIds.length; i++ ) {
				if ( flowBoard.renderedTopics[ flowBoard.orderedTopicIds[i] ] ) {
					if ( found ) {
						// We found an insertion location after the given topic
						$nextExist = flowBoard.renderedTopics[ flowBoard.orderedTopicIds[i] ];
						break; // we already have the topic and somewhere to insert it
					} else {
						// We found an insertion location before the given topic
						$prevExist = flowBoard.renderedTopics[ flowBoard.orderedTopicIds[i] ];
					}
				}

				if ( flowBoard.orderedTopicIds[ i ] === findTopicId ) {
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
			return !flowBoard.renderedTopics[ topicId ];
		} );

		// Render the new topics

		// See bug 61097, Catch any random javascript error from
		// parsoid so they don't break and stop the page
		try {
			$newTopics = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				scrollTemplate,
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
						{
							loadMoreObject: topicsData.links.pagination.fwd,
							loadMoreApiHandler: 'loadMoreTopics',
							loadMoreTarget: scrollTarget,
							loadMoreContainer: scrollContainer,
							loadMoreTemplate: scrollTemplate
						}
					) ).children()
				);
			}

			// Drop in
			( $insertAt || $prevExist ).after( $newTopics );
		} else if ( $nextExist ) {
			// ...before
			// Load more rev
			if ( !topicsData.links.pagination.rev && topicsData.links.pagination.fwd ) {
				// This is a fix for the fact that a "rev" is not available here
				// We can create one by overriding dir=rev
				topicsData.links.pagination.rev = $.extend( topicsData.links.pagination.fwd, {} );
				topicsData.links.pagination.rev.title = 'rev';
				topicsData.links.pagination.rev.url = topicsData.links.pagination.rev.url.replace( 'dir=fwd', 'dir=rev' );
			}
			if ( topicsData.links.pagination.rev ) {
				// Add the load more to the top of the stack
				$newTopics = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
						'flow_load_more',
						{
							loadMoreObject: topicsData.links.pagination.rev,
							loadMoreApiHandler: 'poop', //'loadMoreTopics',
							loadMoreTarget: scrollTarget,
							loadMoreContainer: scrollContainer,
							loadMoreTemplate: scrollTemplate
						}
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
