/*!
 * Contains loadMore, jumpToTopic, and topic titles list functionality.
 */

( function ( $, mw, moment ) {
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
			apiPreHandlers: {},
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
		var flowBoard = this, apiParameters,
			// Scrolls to the given topic, but disables infinite scroll loading while doing so
			_scrollWithoutInfinite = function () {
				var $renderedTopic = flowBoard.renderedTopics[ topicId ];

				if ( $renderedTopic && $renderedTopic.length ) {
					flowBoard.infiniteScrollDisabled = true;

					// We want to get out of the way of the affixed navigation, but
					// without showing or triggering the infinite scroll above the new
					// topic.  With:
					// .scrollTop( $renderedTopic.offset().top - $( '.flow-board-navigation' ).height() )
					// the 'load more' above becomes visible.
					$( 'html, body' ).scrollTop( $renderedTopic.offset().top - 10 );

					// Focus on given topic
					$renderedTopic.click().focus();

					delete flowBoard.infiniteScrollDisabled;
				} else {
					flowBoard.debug( 'Rendered topic not found when attempting to scroll!' );
				}
			};

		// 1. Topic is already on the page; just scroll to it
		if ( flowBoard.renderedTopics[ topicId ] ) {
			_scrollWithoutInfinite();
			return;
		}

		// 2a. Topic is not rendered; do we know about this topic ID?
		if ( flowBoard.topicTitlesById[ topicId ] === undefined ) {
			// We don't. Abort!
			return flowBoard.debug( 'Unknown topicId', arguments );
		}

		// 2b. Load that topic and jump to it
		apiParameters = {
			action: 'flow',
			submodule: 'view-topiclist',
			'vtloffset-dir': 'fwd', // @todo support "middle" dir
			'vtlinclude-offset': true,
			vtlsortby: this.topicIdSort
		};

		if ( this.topicIdSort === 'newest' ) {
			apiParameters['vtloffset-id'] = topicId;
		} else {
			// TODO: It would seem to be safer to pass 'offset-id' for both (what happens
			// if there are two posts at the same timestamp?).  (Also, that would avoid needing
			// the timestamp in the TOC-only API response).  However,
			// apparently, we must pass 'offset' for 'updated' order to get valid
			// results (e.g. by passing offset-id for 'updated', it doesn't even include
			// the item requested despite include-offset).  However, the server
			// does not throw an exception for 'offset-id' + 'sortby'='updated', which it
			// should if this analysis is correct.

			apiParameters.vtloffset = moment.utc( this.updateTimestampsByTopicId[ topicId ] ).format( 'YYYYMMDDHHmmss' );
		}

		flowBoard.Api.apiCall( apiParameters )
			// TODO: Finish this error handling or remove the empty functions.
			// Remove the load indicator
			.always( function () {
				// @todo support for multiple indicators on same target
				//$target.removeClass( 'flow-api-inprogress' );
				//$this.removeClass( 'flow-api-inprogress' );
			} )
			// On success, render the topic
			.done( function( data ) {
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
				flowBoard.debug( true, 'Failed to load topics: ' + code );
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
	// API pre-handlers
	//

	/**
	 * On before board reloading (eg. change sort).
	 * This method only clears the storage in preparation for it to be reloaded.
	 * @param {Event} event
	 * @param {Object} info
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 */
	function flowBoardComponentLoadMoreFeatureBoardApiPreHandler( event, info ) {
		// Backup the topic data
		info.component.renderedTopicsBackup = info.component.renderedTopics;
		info.component.topicTitlesByIdBackup = info.component.topicTitlesById;
		info.component.orderedTopicIdsBackup = info.component.orderedTopicIds;
		// Reset the topic data
		info.component.renderedTopics = {};
		info.component.topicTitlesById = {};
		info.component.orderedTopicIds = [];
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiPreHandlers.board = flowBoardComponentLoadMoreFeatureBoardApiPreHandler;

	//
	// API callback handlers
	//

	/**
	 * On failed board reloading (eg. change sort), restore old data.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	function flowBoardComponentLoadMoreFeatureBoardApiCallback( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Failed; restore the topic data
			info.component.renderedTopics = info.component.renderedTopicsBackup;
			info.component.topicTitlesById = info.component.topicTitlesByIdBackup;
			info.component.orderedTopicIds = info.component.orderedTopicIdsBackup;
		}

		// Delete the backups
		delete info.component.renderedTopicsBackup;
		delete info.component.topicTitlesByIdBackup;
		delete info.component.orderedTopicIdsBackup;
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiHandlers.board = flowBoardComponentLoadMoreFeatureBoardApiCallback;

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
		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		var $this = $( this ),
			$target = info.$target,
			flowBoard = info.component,
			scrollTarget = $this.data( 'flow-scroll-target' ),
			$scrollContainer = $.findWithParent( $this, $this.data( 'flow-scroll-container' ) ),
			topicsData = data.flow[ 'view-topiclist' ].result.topiclist,
			readingTopicPosition;

		if ( scrollTarget === 'window' && flowBoard.readingTopicId ) {
			// Store the current position of the topic you are reading
			readingTopicPosition = { id: flowBoard.readingTopicId };
			// Where does the topic start?
			readingTopicPosition.topicStart = flowBoard.renderedTopics[ readingTopicPosition.id ].offset().top;
			// Where am I within the topic?
			readingTopicPosition.topicPlace = $( window ).scrollTop() - readingTopicPosition.topicStart;
		}

		// Render topics
		_flowBoardComponentLoadMoreFeatureRenderTopics(
			flowBoard,
			topicsData,
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

			if ( readingTopicPosition ) {
				readingTopicPosition.anuStart = flowBoard.renderedTopics[ readingTopicPosition.id ].offset().top;
				if ( readingTopicPosition.anuStart > readingTopicPosition.topicStart ) {
					// Looks like the topic we are reading got pushed down. Let's jump to where we were before
					scrollTarget.scrollTop( readingTopicPosition.anuStart + readingTopicPosition.topicPlace );
				}
			}
		} else {
			scrollTarget = $.findWithParent( this, scrollTarget );
		}

		/*
		 * Fire infinite scroll check again - if no (or few) topics were
		 * added (e.g. because they're moderated), we should immediately
		 * fetch more instead of waiting for the user to scroll again (when
		 * there's no reason to scroll)
		 */
		_flowBoardComponentLoadMoreFeatureInfiniteScrollCheck.call( flowBoard, $scrollContainer, scrollTarget );
		return $.Deferred().resolve().promise();
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiHandlers.loadMoreTopics = flowBoardComponentLoadMoreFeatureTopicsApiCallback;

	/**
	 * Loads up the topic titles list.
	 * Saves the topic titles to topicTitlesById and orderedTopicIds, and adds timestamps
	 * to updateTimestampsByTopicId.
	 *
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

		// Iterate over every topic
		for ( ; i < topicsData.roots.length; i++ ) {
			// Get the topic ID
			topicId = topicsData.roots[ i ];
			// Get the revision ID
			revisionId = topicsData.posts[ topicId ][0];

			if ( $.inArray( topicId, flowBoard.orderedTopicIds ) === -1 ) {
				// Append to the end, we will sort after the insert loop.
				flowBoard.orderedTopicIds.push( topicId );
			}

			if ( flowBoard.topicTitlesById[ topicId ] === undefined ) {
				// Store the title from the revision object
				flowBoard.topicTitlesById[ topicId ] = topicsData.revisions[ revisionId ].content.content;
			}

			if ( flowBoard.updateTimestampsByTopicId[ topicId ] === undefined ) {
				flowBoard.updateTimestampsByTopicId[ topicId ] = topicsData.revisions[ revisionId ].last_updated;
			}
		}

		_flowBoardSortTopicIds( flowBoard );

		// we need to re-trigger scroll.flow-load-more if there are not enough items in the
		// toc for it to scroll and trigger on its own. Without this TOC never triggers
		// the initial loadmore to expand from the number of topics on page to topics
		// available from the api.
		if ( this.$loadMoreNodes ) {
			this.$loadMoreNodes
				.filter( '[data-flow-api-handler=topicList]' )
				.trigger( 'scroll.flow-load-more', { forceNavigationUpdate: true } );
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
		var scrollTargetSelector = $button.data( 'flow-scroll-target' ),
			$target,
			scrollContainerSelector = $button.data( 'flow-scroll-container' ),
			$scrollContainer = $.findWithParent( $button, scrollContainerSelector ),
			board = this;

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
		if ( $scrollContainer.data( 'scrollIsBound' ) ) {
			return;
		}
		$scrollContainer.data( 'scrollIsBound', true );

		// Bind the event for this
		if ( scrollTargetSelector === 'window' ) {
			this.on( 'windowScroll', function () {
				_flowBoardComponentLoadMoreFeatureInfiniteScrollCheck.call( board, $scrollContainer, $( window ) );
			} );
		} else {
			$target = $.findWithParent( $button, scrollTargetSelector );
			$target.on( 'scroll.flow-load-more', $.throttle( 50, function ( evt ) {
				_flowBoardComponentLoadMoreFeatureInfiniteScrollCheck.call( board, $scrollContainer, $target );
			} ) );
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
		// @todo this puts the wrong order
		if ( this.topicTitlesById[ currentTopicId ] === undefined ) {
			this.topicTitlesById[ currentTopicId ] = $topicTitle.data( 'flow-topic-title' );

			if ( $.inArray( currentTopicId, this.orderedTopicIds ) === -1 ) {
				this.orderedTopicIds.push( currentTopicId );
				_flowBoardSortTopicIds( this );
			}
		}
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.topicTitle = flowBoardComponentLoadMoreFeatureElementLoadTopicTitle;

	//
	// Private functions
	//


	/**
	 * Re-sorts the orderedTopicIds after insert
	 *
	 * @param {Object} flowBoard
	 */
	function _flowBoardSortTopicIds( flowBoard ) {
		if ( flowBoard.topicIdSortCallback ) {
			// Custom sorts
			flowBoard.orderedTopicIds.sort( flowBoard.topicIdSortCallback );
		} else {
			// Default sort, takes advantage of topic ids monotonically increasing
			// which allows for the newest sort to be the default utf-8 string sort
			// in reverse.
			// TODO: This can be optimized (to avoid two in-place operations that affect
			// the whole array by doing a descending sort (with a custom comparator)
			// rather than sorting then reversing.
			flowBoard.orderedTopicIds.sort().reverse();
		}
	}

	/**
	 * Called on scroll. Checks to see if a FlowBoard needs to have more content loaded.
	 * @param {jQuery} $searchContainer Container to find 'load more' buttons in
	 * @param {jQuery} $calculationContainer Container to do scroll calculations on (height, scrollTop, offset, etc.)
	 */
	function _flowBoardComponentLoadMoreFeatureInfiniteScrollCheck( $searchContainer, $calculationContainer ) {
		if ( this.infiniteScrollDisabled ) {
			// This happens when the topic navigation is used to jump to a topic
			// We should not infinite-load anything when we are scrolling to a topic
			return;
		}

		var calculationContainerHeight = $calculationContainer.height(),
			calculationContainerScroll = $calculationContainer.scrollTop(),
			calculationContainerThreshold = ( $calculationContainer.offset() || { top: calculationContainerScroll } ).top;

		// Find load more buttons within our search container, and they must be visible
		$searchContainer.find( this.$loadMoreNodes ).filter( ':visible' ).each( function () {
			var $this = $( this ),
				nodeOffset = $this.offset().top,
				nodeHeight = $this.outerHeight( true );

			// First, is this element above or below us?
			if ( nodeOffset <= calculationContainerThreshold ) {
				// Top of element is above the viewport; don't use it.
				return;
			}

			// Is this element in the viewport?
			if ( nodeOffset - nodeHeight <= calculationContainerThreshold + calculationContainerHeight ) {
				// Element is almost in viewport, click it.
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
		$insertAt = false; // @todo debug

		/** @private
		 */
		function _createRevPagination( $target ) {
			if ( !topicsData.links.pagination.fwd && !topicsData.links.pagination.rev ) {
				return;
			}

			if ( !topicsData.links.pagination.rev && topicsData.links.pagination.fwd ) {
				// This is a fix for the fact that a "rev" is not available here (TODO: Why not?)
				// We can create one by overriding dir=rev
				topicsData.links.pagination.rev = $.extend( true, {}, topicsData.links.pagination.fwd, { title: 'rev' } );
				topicsData.links.pagination.rev.url = topicsData.links.pagination.rev.url.replace( '_offset-dir=fwd', '_offset-dir=rev' );
			}

			$allRendered = $allRendered.add(
				$( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
					'flow_load_more',
					{
						loadMoreObject: topicsData.links.pagination.rev,
						loadMoreApiHandler: 'loadMoreTopics',
						loadMoreTarget: scrollTarget,
						loadMoreContainer: scrollContainer,
						loadMoreTemplate: scrollTemplate
					}
				) ).children()
					.insertBefore( $target.first() )
			);
		}

		/** @private
		 */
		function _createFwdPagination( $target ) {
			if ( forceShowLoadMore || topicsData.links.pagination.fwd ) {
				// Add the load more to the end of the stack
				$allRendered = $allRendered.add(
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
						.insertAfter( $target.last() )
				);
			}
		}

		/**
		 * Renders topics by IDs from topicsData, and returns the elements.
		 * @param {Array} toRender List of topic IDs in topicsData
		 * @returns {jQuery}
		 * @private
		 */
		function _render( toRender ) {
			var rootsBackup = topicsData.roots,
				$newTopics;

			// Temporarily set roots to our subset to be rendered
			topicsData.roots = toRender;

			try {
				$newTopics = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
					scrollTemplate,
					topicsData
				) ).children();
			} catch( e ) {
				flowBoard.debug( true, 'Failed to render new topic' );
				$newTopics = $();
			}

			topicsData.roots = rootsBackup;

			return $newTopics;
		}

		var i, j, $topic, topicId,
			$allRendered = $( [] ),
			toInsert = [];

		for ( i = 0; i < topicsData.roots.length; i++ ) {
			topicId = topicsData.roots[ i ];

			if ( flowBoard.renderedTopics[ topicId ] ) {
				// already rendered, update?
			} else {
				flowBoard.renderedTopics[ topicId ] = _render( [ topicId ] );
				$allRendered.push( flowBoard.renderedTopics[ topicId ][0] );
				toInsert.push( topicId );
				if ( $.inArray( topicId, flowBoard.orderedTopicIds ) === -1 ) {
					flowBoard.orderedTopicIds.push( topicId );
				}
				// @todo this is already done elsewhere, but it runs after insert
				// to the DOM instead of before.  Not sure how to fix ordering.
				if ( !flowBoard.updateTimestampsByTopicId[ topicId ] ) {
					flowBoard.updateTimestampsByTopicId[ topicId ] = topicsData.revisions[topicsData.posts[topicId][0]].last_updated;
				}
			}
		}

		if ( toInsert.length ) {
			_flowBoardSortTopicIds( flowBoard );

			// This uses the assumption that there will be at least one pre-existing
			// topic above the topics to be inserted.  This should hold true as the
			// initial page load starts at the begining.
			for ( i = 1; i < flowBoard.orderedTopicIds.length; i++ ) {
				// topic is not to be inserted yet.
				if ( $.inArray( flowBoard.orderedTopicIds[ i ], toInsert ) === -1 ) {
					continue;
				}

				// find the most recent topic in the list that exists and insert after it.
				for ( j = i - 1; j >= 0; j-- ) {
					$topic = flowBoard.renderedTopics[ flowBoard.orderedTopicIds[ j ] ];
					if ( $topic && $topic.length && $.contains( document.body, $topic[0] ) ) {
						break;
					}
				}

				// Put the new topic after the found topic above it
				if ( j >= 0 ) {
					$topic.after( flowBoard.renderedTopics[ flowBoard.orderedTopicIds[ i ] ] );
				} else {
					// @todo log something about failures?
				}
			}

			// This works because orderedTopicIds includes not only the topics on
			// page but also the ones loaded by the toc.  If these topics are due
			// to a jump rather than forward auto-pagination the prior topic will
			// not be rendered.
			i = $.inArray( topicsData.roots[0], flowBoard.orderedTopicIds );
			if ( i > 0 && flowBoard.renderedTopics[ flowBoard.orderedTopicIds[ i - 1 ] ] === undefined ) {
				_createRevPagination( flowBoard.renderedTopics[ topicsData.roots[0] ] );
			}
			// Same for forward pagination, if we jumped and then scrolled backwards the
			// topic after the last will already be rendered, and forward pagination
			// will not be necessary.
			i = $.inArray( topicsData.roots[ topicsData.roots.length - 1 ], flowBoard.orderedTopicIds );
			if ( i === flowBoard.orderedTopicIds.length - 1 || flowBoard.renderedTopics[ flowBoard.orderedTopicIds[ i + 1 ] ] === undefined ) {
				_createFwdPagination( flowBoard.renderedTopics[ topicsData.roots[ topicsData.roots.length - 1 ] ] );
			}
		}

		// Run loadHandlers
		flowBoard.emitWithReturn( 'makeContentInteractive', $allRendered );
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentLoadMoreFeatureMixin );
}( jQuery, mediaWiki, moment ) );
