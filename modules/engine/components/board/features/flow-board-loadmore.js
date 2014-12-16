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
		var flowBoard = this,
			// Scrolls to the given topic, but disables infinite scroll loading while doing so
			_scrollWithoutInfinite = function () {
				flowBoard.infiniteScrollDisabled = true;

				$( 'html, body' ).scrollTop(
					flowBoard.renderedTopics[ topicId ].offset().top -
					parseInt( flowBoard.renderedTopics[ topicId ].css( "border-top-width" ) ) -
					parseInt( flowBoard.renderedTopics[ topicId ].css( "margin-top" ) ) -
					parseInt( flowBoard.renderedTopics[ topicId ].css( "padding-top" ) )
				);

				// Focus on given topic
				flowBoard.$board.find('.flow-topic').filter('[data-flow-id=' + topicId + ']' ).click().focus();

				delete flowBoard.infiniteScrollDisabled;
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
		flowBoard.Api.apiCall( {
			action: 'flow',
			submodule: 'view-topiclist',
			'vtloffset-dir': 'fwd', // @todo support "middle" dir
			'vtlinclude-offset': true,
			'vtllimit': 1, // @todo remove this and use the default
			'vtloffset-id': topicId
		} )
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
			flowBoard = info.component,
			unknownTopicIds = [];


		// Iterate over every topic
		for ( ; i < topicsData.roots.length; i++ ) {
			// Get the topic ID
			topicId = topicsData.roots[ i ];
			// Get the revision ID
			revisionId = topicsData.posts[ topicId ][0];

			if ( flowBoard.topicTitlesById[ topicId ] === undefined ) {
				// Store the title from the revision object
				flowBoard.topicTitlesById[ topicId ] = topicsData.revisions[ revisionId ].content.content;
				// Hold onto the new topic IDs to be inserted
				unknownTopicIds.push( topicId );
			} else if ( unknownTopicIds.length ) {
				// This is a known topic ID; merge the other topics at this location
				// Start changing orderedTopicIds at index i, remove 0 elements, add all of the unknownTopicIds.
				unknownTopicIds.unshift( i, 0 );
				flowBoard.orderedTopicIds.splice.apply( flowBoard.orderedTopicIds, unknownTopicIds );
				unknownTopicIds = [];
			}
		}

		if ( unknownTopicIds.length ) {
			// Add any other topic IDs at the end
			flowBoard.orderedTopicIds.push.apply( flowBoard.orderedTopicIds, unknownTopicIds );
		}

		// we need to re-trigger scroll.flow if there are not enough items in the
		// toc for it to scroll and trigger on its own. Without this TOC never triggers
		// the initial loadmore to expand from the number of topics on page to topics
		// available from the api.
		if ( this.$loadMoreNodes ) {
			this.$loadMoreNodes
				.filter( '[data-flow-api-handler=topicList]' )
				.trigger( 'scroll.flow', { forceNavigationUpdate: true } );
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
			$target.on( 'scroll.flow', $.throttle( 50, function ( evt ) {
				_flowBoardComponentLoadMoreFeatureInfiniteScrollCheck.call( board, $scrollContainer, $target );
				evt.stopPropagation();
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
			}
		}
	}
	FlowBoardComponentLoadMoreFeatureMixin.UI.events.loadHandlers.topicTitle = flowBoardComponentLoadMoreFeatureElementLoadTopicTitle;

	//
	// Private functions
	//

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
				$newTopics = $();
			}

			topicsData.roots = rootsBackup;

			return $newTopics;
		}

		/**
		 * Updates the topic objects and arrays on this object to reflect the new information.
		 * @param {String[]} topicIdsToRender
		 * @param {String} [insertAtTopicId]
		 * @param {String} [insertAt] before or after
		 * @private
		 */
		function _updateIdTables( topicIdsToRender, insertAtTopicId, insertAt ) {
			var arrayIndex, i;

			// Remove any topic IDs that we already have in the page
			for ( i = 0; i < topicIdsToRender.length; i++ ) {
				if ( flowBoard.topicTitlesById[ topicIdsToRender[ i ] ] ) {
					topicIdsToRender.splice( i, 1 );
					i--;
				}
			}

			// renderedTopics is set by topic loadHandler
			// topicTitlesById is set by topicTitle loadHandler
			if ( !insertAtTopicId ) {
				// Concat new topics at end
				flowBoard.orderedTopicIds.push.apply( flowBoard.orderedTopicIds, topicIdsToRender );
			} else {
				// Find topic position and merge in place
				arrayIndex = $.inArray( insertAtTopicId, flowBoard.orderedTopicIds );
				topicIdsToRender.unshift( insertAt === 'after' ? arrayIndex + 1 : arrayIndex, 0 );
				flowBoard.orderedTopicIds.splice.apply( flowBoard.orderedTopicIds, topicIdsToRender );
			}
		}

		var toRender = [],
			insertAtTopicId,
			topicId,
		// Currently rendered topics in queue to be inserted
			$rendered,
		// All new topics rendered
			$allRendered = $(),
			i,
			arrayIndex,
			sortBy = topicsData.sortby || 'newest', // updated or newest
			reverseDirection = topicsData.submitted[ 'offset-dir' ] === 'rev',
		// Requested offset datetime as timestamp
			firstTopicTime,
		// Iterated topic timestamp
			topicTime,
		// Best time difference found
			bestTopicTimeDifference = null,
		// Best matching topic found
			$bestTopic;

		for ( i = 0; i < topicsData.roots.length; i++ ) {
			topicId = topicsData.roots[ i ];

			if ( flowBoard.renderedTopics[ topicId ] ) {
				// This topic was already rendered. Render any topics before it.
				$insertAt = flowBoard.renderedTopics[ topicId ];
				insertAtTopicId = topicId;

				// Render any topics from before this point
				if ( toRender.length ) {
					$rendered = _render( toRender ).insertBefore( $insertAt );
					_updateIdTables( toRender, insertAtTopicId, 'before' );
					$allRendered = $allRendered.add( $rendered );
					toRender = [];
				}

				// Remove now-rendered topics and the current topic (which was already rendered)
				topicsData.roots.splice( 0, i + 1 );
				// Reset the index
				i = -1;

				continue;
			}

			// Add this topic to the list of topics to render
			toRender.push( topicId );
		}

		// Do we have more topics to render?
		if ( toRender.length ) {
			$rendered = _render( toRender );
			$allRendered = $allRendered.add( $rendered );

			if ( $insertAt ) {
				// Easy solution; insert all these topics after a known, rendered topic.  We had a set of results in
				// which at least one topic in the middle was already on the page, so we can render around it.
				$rendered.insertAfter( $insertAt );
				_updateIdTables( toRender, insertAtTopicId, 'after' );
			} else {
				// The less easy solution; find out where these topics go...

				// Find the first topic to be rendered in our current list of topic IDs
				arrayIndex = $.inArray( toRender[ 0 ], flowBoard.orderedTopicIds );

				// Four possible options for finding where to drop these in
				if ( arrayIndex > -1 ) {
				/**
				 * 1. Find by known order in topic list
				 */
					// Try to find a preceding topic
					for ( i = arrayIndex; --i > 0; ) {
						topicId = flowBoard.orderedTopicIds[ i ];

						// Seems we found the closest topic before
						if ( flowBoard.renderedTopics[ topicId ] ) {
							$insertAt = flowBoard.renderedTopics[ topicId ];
							insertAtTopicId = topicId;

							$rendered.insertAfter( $insertAt );

							_updateIdTables( toRender, insertAtTopicId, 'after' );

							if ( i !== arrayIndex - 1 && i > 0 ) {
								// If we had to look further than 1 topic, a load more button needs to be inserted before
								_createRevPagination( $rendered );
							}

							$rendered = null; // used later

							break;
						}
					}

					// Try to find a subsequent topic
					// Is this necessary, or is the 'preceding' version enough (the initial topics of the current sort order are always loaded at page load)?
					for ( i = arrayIndex; ++i < flowBoard.orderedTopicIds.length; ) {
						topicId = flowBoard.orderedTopicIds[ i ];

						// Seems we found the closest topic after
						if ( flowBoard.renderedTopics[ topicId ] ) {
							if ( $rendered ) {
								// If we didn't already insert our topics in the previous loop, do it now
								$insertAt = flowBoard.renderedTopics[ topicId ];
								insertAtTopicId = topicId;

								$rendered.insertBefore( $insertAt );

								_updateIdTables( toRender, insertAtTopicId, 'before' );
							}

							if ( i !== arrayIndex + 1 ) {
								// If we had to look further than 1 topic, a load more button needs to be inserted after
								_createFwdPagination( $rendered );
							}
							break;
						}

						// If we already inserted the topics in the previous loop, and we haven't found the next sibling yet...
						if ( !$rendered ) {
							// ...then insert a load more link after, and stop the loop
							_createFwdPagination( $allRendered );
							break;
						}
					}
				} else if ( topicsData.submitted.offset ) {
				/**
				 * 2. Find by date
				 */
					// Requested offset datetime as timestamp
					// TODO: Use http://momentjs.com/docs/#/parsing/string-format/ instead.
					firstTopicTime = ( new Date( topicsData.submitted.offset.replace(/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/, '$1-$2-$3T$4:$5:$6') ) ).getTime();

					for ( i = 0; i < flowBoard.orderedTopicIds.length; i++ ) {
						topicId = flowBoard.orderedTopicIds[ i ];

						if ( flowBoard.renderedTopics[ topicId ] ) {
							topicTime = sortBy === 'newest' ? mw.flow.uuidToTime( topicId ) : flowBoard.renderedTopics[ topicId ].data( 'flow-topic-timestamp-updated' );

							if ( bestTopicTimeDifference === null || Math.abs( topicTime - firstTopicTime ) < Math.abs( bestTopicTimeDifference ) ) {
								bestTopicTimeDifference = topicTime - firstTopicTime;
								$bestTopic = flowBoard.renderedTopics[ topicId ];
								insertAtTopicId = topicId;
							} else {
								// The previous diff we found was smaller than this one, so it's the closest option
								break;
							}
						}
					}

					if ( !$bestTopic ) {
						flowBoard.debug( true, 'Failed to find topics insertion location by offset time', arguments );
					} else if ( bestTopicTimeDifference < 0 || ( bestTopicTimeDifference === 0 && reverseDirection ) ) {
						// 'rev' topics, insert before the topic it had offset from
						$rendered.insertBefore( $bestTopic );

						_updateIdTables( toRender, insertAtTopicId, 'before' );
						_createRevPagination( $rendered );
					} else {
						// 'fwd' topics, insert after the topic it had offset from
						$rendered.insertAfter( $bestTopic );

						_updateIdTables( toRender, insertAtTopicId, 'after' );
						_createFwdPagination( $rendered );
					}
				} else if ( topicsData.submitted[ 'offset-id' ] ) {
				/**
				 * 3. Find by sibling ID
				 */
					topicId = topicsData.submitted[ 'offset-id' ];

					if ( flowBoard.renderedTopics[ topicId ] ) {
						$insertAt = flowBoard.renderedTopics[ topicId ];
						insertAtTopicId = topicId;

						if ( reverseDirection ) {
							// rev mode, topics go before offset
							$rendered.insertBefore( $insertAt );

							_updateIdTables( toRender, insertAtTopicId, 'before' );
							_createRevPagination( $rendered );
						} else {
							// fwd mode, topics go after offset
							$rendered.insertAfter( $insertAt );

							_updateIdTables( toRender, insertAtTopicId, 'after' );
							_createFwdPagination( $rendered );
						}
					} else {
						flowBoard.debug( true, 'Failed to find topics insertion location by offset ID', arguments );
						return;
					}
				} else {
				/**
				 * 4. Just drop in at the end of our current topics
				 */
					// Find the last rendered topic
					for ( i = flowBoard.orderedTopicIds.length; i--; ) {
						topicId = flowBoard.orderedTopicIds[ i ];

						if ( flowBoard.renderedTopics[ topicId ] ) {
							$insertAt = flowBoard.renderedTopics[ topicId ];
							insertAtTopicId = topicId;

							$rendered.insertAfter( $insertAt );

							_updateIdTables( toRender, insertAtTopicId, 'after' );

							$rendered = null;

							break;
						}
					}

					// For some reason, we STILL didn't insert these topics. Most likely there are no topics on the page.
					if ( $rendered ) {
						// Do a hacky insert with find
						flowBoard.$board.find( '.flow-topics' ).append( $rendered );

						_updateIdTables( toRender );
						_createRevPagination( $rendered );
						_createFwdPagination( $rendered );
					}
				}
			}
		}

		// Run loadHandlers
		flowBoard.emitWithReturn( 'makeContentInteractive', $allRendered );
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentLoadMoreFeatureMixin );
}( jQuery, mediaWiki ) );
