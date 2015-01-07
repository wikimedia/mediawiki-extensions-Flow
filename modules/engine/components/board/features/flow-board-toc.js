/*!
 * Contains Table of Contents functionality.
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for TOC in board header.
	 * @param {jQuery} $container
	 * @this FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentTocFeatureMixin( $container ) {
		/** Stores a list of topic IDs rendered in our TOC */
		this.topicIdsInToc = {};
		/** Used to define an offset to optimize fetching of TOC when we already have some items in it */
		this.lastTopicIdInToc = null;

		// Bind element handlers
		this.bindNodeHandlers( FlowBoardComponentTocFeatureMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentTocFeatureMixin );

	FlowBoardComponentTocFeatureMixin.UI = {
		events: {
			apiPreHandlers: {},
			apiHandlers: {},
			interactiveHandlers: {},
			loadHandlers: {}
		}
	};

	//
	// API pre-handlers
	//

	/**
	 * Empties out TOC menu when board is being refreshed.
	 * @param {Event} event
	 * @param {Object} info
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 */
	function flowBoardComponentTocFeatureMixinBoardApiPreHandler( event, info ) {
		info.component.topicIdsInTocBackup = info.component.topicIdsInToc;
		info.component.lastTopicIdInTocBackup = info.component.lastTopicIdInToc;

		info.component.topicIdsInToc = {};
		info.component.lastTopicIdInToc = null;

		if ( info.component.$tocMenu ) {
			info.component.$tocMenuChildBackup = info.component.$tocMenu.children().detach();
			info.component.$tocMenuBackup = info.component.$tocMenu;
			info.component.$tocMenu = null;
		}
	}
	FlowBoardComponentTocFeatureMixin.UI.events.apiPreHandlers.board = flowBoardComponentTocFeatureMixinBoardApiPreHandler;

	/**
	 *
	 * @param {Event} event
	 * @param {Object} info
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 */
	function flowBoardComponentTocFeatureMixinTopicListApiPreHandler( event, info, extraParameters ) {
		var $this = $( this ),
			isLoadMoreButton = $this.data( 'flow-load-handler' ) === 'loadMore';

		if ( !isLoadMoreButton && !( extraParameters || {} ).skipMenuToggle ) {
			// Re-scroll the TOC (in case the scroll that tracks the page scroll failed
			// due to insufficient elements making the desired scrollTop not work (T78572)).
			info.component.scrollTocToActiveItem();

			// Actually open/close the TOC menu on this node.
			$this.trigger( 'click', { interactiveHandler: 'menuToggle' } );
		}

		if ( !isLoadMoreButton && info.component.doneInitialTocApiCall ) {
			// Triggers load more if we didn't load enough content to fill the viewport
			info.$target.trigger( 'scroll.flow-load-more', { forceNavigationUpdate: true } );
			return false;
		}

		// Send some overrides to this API request
		var overrides = {
			topiclist_sortby: info.component.$board.data( 'flow-sortby' ),
			topiclist_limit: 50,
			topiclist_toconly: true
		};

		// @todo verify that this works
		//if ( info.component.lastTopicIdInToc ) {
		//	overrides.topiclist_offset = false;
		//	overrides['topiclist_offset-id'] = info.component.lastTopicIdInToc;
		//}

		if ( !overrides.topiclist_sortby ) {
			delete overrides.topiclist_sortby;
		}

		return overrides;
	}
	FlowBoardComponentTocFeatureMixin.UI.events.apiPreHandlers.topicList = flowBoardComponentTocFeatureMixinTopicListApiPreHandler;

	//
	// API handlers
	//

	/**
	 * Restores TOC stuff if board reload fails.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 */
	function flowBoardComponentTocFeatureMixinBoardApiCallback( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Failed; restore the topic data
			info.component.topicIdsInToc = info.component.topicIdsInTocBackup;
			info.component.lastTopicIdInToc = info.component.lastTopicIdInTocBackup;
			if ( info.component.$tocMenuBackup ) {
				info.component.$tocMenu = info.component.$tocMenuBackup;
			}

			if ( info.component.$tocMenu ) {
				info.component.$tocMenu.append( info.component.$tocMenuChildBackup );
			}
		}

		// Delete the backups
		delete info.component.topicIdsInTocBackup;
		delete info.component.lastTopicIdInTocBackup;
		info.component.$tocMenuChildBackup.remove();
		delete info.component.$tocMenuChildBackup;
		delete info.component.$tocMenuBackup;

		// Allow reopening
		info.component.doneInitialTocApiCall = false;
	}
	FlowBoardComponentTocFeatureMixin.UI.events.apiHandlers.board = flowBoardComponentTocFeatureMixinBoardApiCallback;

	/**
	 * The actual storage is in FlowBoardComponentLoadMoreFeatureMixin.UI.events.apiHandlers.topicList.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 */
	function flowBoardComponentTocFeatureMixinTopicListApiHandler( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return;
		}

		var $kids, i,
			$this = $( this ),
			template = info.component.tocTemplate,
			topicsData = data.flow['view-topiclist'].result.topiclist,
			isLoadMoreButton = $this.data( 'flow-load-handler' ) === 'loadMore';

		// Iterate over every topic
		for ( i = 0; i < topicsData.roots.length; i++ ) {
			// Do this until we find a topic that is not in our TOC
			if ( info.component.topicIdsInToc[ topicsData.roots[ i ] ] ) {
				// And then remove all the ones that are already in our TOC
				topicsData.roots.splice( i, 1 );
				i--;
			} else {
				// For any other subsequent IDs, just mark them as being in the TOC now
				info.component.topicIdsInToc[ topicsData.roots[ i ] ] = true;
			}
		}

		if ( topicsData.roots.length ) {
			// Store the last topic ID for optimal offset use
			info.component.lastTopicIdInToc = topicsData.roots[i];
		} // render even if we have no roots, because another load-more button could appear

		// Render the topic titles
		$kids = $( mw.flow.TemplateEngine.processTemplateGetFragment(
			template,
			topicsData
		) ).children();

		if ( isLoadMoreButton ) {
			// Insert the new topic titles
			info.$target.replaceWith( $kids );
		} else {
			// Prevent this API call from happening again
			info.component.doneInitialTocApiCall = true;

			// Insert the new topic titles
			info.$target.append( $kids );
		}

		info.component.emitWithReturn( 'makeContentInteractive', $kids );

		if ( isLoadMoreButton ) {
			// Remove the old load button (necessary if the above load_more template returns nothing)
			$this.remove();
		}

		// Triggers load more if we didn't load enough content to fill the viewport
		$kids.trigger( 'scroll.flow-load-more', { forceNavigationUpdate: true } );

	}
	FlowBoardComponentTocFeatureMixin.UI.events.apiHandlers.topicList = flowBoardComponentTocFeatureMixinTopicListApiHandler;

	//
	// On element-click handlers
	//

	/**
	 *
	 * @param {Event} event
	 */
	function flowBoardComponentTocFeatureMixinJumpToTopicCallback( event ) {
		var $this = $( this ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this );

		// Load and scroll to topic
		flowBoard.jumpToTopic( $this.data( 'flow-id' ) );
	}
	FlowBoardComponentTocFeatureMixin.UI.events.interactiveHandlers.jumpToTopic = flowBoardComponentTocFeatureMixinJumpToTopicCallback;

	//
	// On element-load handlers
	//

	// This is a confusing name since this.$tocMenu is set to flow-board-toc-list, whereas you
	// would expect flow-board-toc-menu.
	/**
	 * Stores the TOC menu for later use.
	 * @param {jQuery} $tocMenu
	 */
	function flowBoardComponentTocFeatureTocMenuLoadCallback( $tocMenu ) {
		this.$tocMenu = $tocMenu;
		this.tocTarget = $tocMenu.data( 'flow-toc-target' );
		this.tocTemplate = $tocMenu.data( 'flow-template' );
	}
	FlowBoardComponentTocFeatureMixin.UI.events.loadHandlers.tocMenu = flowBoardComponentTocFeatureTocMenuLoadCallback;

	/**
	 * Checks if this title is already in TOC, and if not, adds it to the end of the stack.
	 * @param {jQuery} $topicTitle
	 */
	function flowBoardComponentTocFeatureElementLoadTopicTitle( $topicTitle ) {
		var currentTopicId = $topicTitle.closest( '[data-flow-id]' ).data( 'flowId' ),
			topicData = {
				posts: {},
				revisions: {},
				roots: [ currentTopicId ],
				noLoadMore: true
			},
			$kids, $target;

		if ( !this.topicIdsInToc[ currentTopicId ] ) {
			// If we get in here, this must have been loaded by topics infinite scroll and NOT by jumpTo
			this.topicIdsInToc[ currentTopicId ] = true;
			this.lastTopicIdInToc = currentTopicId;

			// Magic to set the revision data
			topicData.posts[ currentTopicId ] = [ currentTopicId ];
			topicData.revisions[ currentTopicId ] = {
				content: {
					content: $topicTitle.data( 'flow-topic-title' ),
					format: 'plaintext'
				}
			};

			// Render the topic title
			$kids = $( mw.flow.TemplateEngine.processTemplateGetFragment(
				this.tocTemplate,
				topicData
			) ).children();

			// Find out where/how to insert the title
			$target = $.findWithParent( this.$tocMenu, this.tocTarget );
			if ( !$target.length ) {
				this.$tocMenu.append( $kids );
			} else {
				$target.after( $kids );
			}

			this.emitWithReturn( 'makeContentInteractive', $kids );
		}
	}
	FlowBoardComponentTocFeatureMixin.UI.events.loadHandlers.topicTitle = flowBoardComponentTocFeatureElementLoadTopicTitle;

	//
	// Public functions
	//

	/**
	 * Scroll the TOC to the active item
	 */
	function flowBoardComponentTocFeatureScrollTocToActiveItem() {
		// Set TOC active item
		var $tocContainer = this.$tocMenu,
			requestedScrollTop, afterScrollTop; // For debugging

		var $scrollTarget = $tocContainer.find( 'a[data-flow-id]' )
			.removeClass( 'active' )
			.filter( '[data-flow-id=' + this.readingTopicId + ']' )
				.addClass( 'active' )
				.closest( 'li' )
				.next();

		if ( !$scrollTarget.length ) {
			// we are at the last list item; use the current one instead
			$scrollTarget = $scrollTarget.end();
		}
		// Scroll to the active item
		if ( $scrollTarget.length ) {
			requestedScrollTop = $scrollTarget.offset().top - $tocContainer.offset().top + $tocContainer.scrollTop();
			$tocContainer.scrollTop( requestedScrollTop );
			afterScrollTop = $tocContainer.scrollTop();
			// the above may not trigger the scroll.flow-load-more event within the TOC if the $tocContainer
			// does not have a scrollbar. If that happens you could have a TOC without a scrollbar
			// that refuses to autoload anything else. Fire it again(wasteful) untill we find
			// a better way.
			// This does not seem to work for the initial load, that is handled in flow-boad-loadmore.js
			// when it runs this same code.  This seems to be required for subsequent loads after
			// the initial call.
			if ( this.$loadMoreNodes ) {
				this.$loadMoreNodes
					.filter( '[data-flow-api-handler=topicList]' )
					.trigger( 'scroll.flow-load-more', { forceNavigationUpdate: true } );
			}
		}

	}

	FlowBoardComponentTocFeatureMixin.prototype.scrollTocToActiveItem = flowBoardComponentTocFeatureScrollTocToActiveItem;

	//
	// Private functions
	//

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentTocFeatureMixin );
}( jQuery, mediaWiki ) );
