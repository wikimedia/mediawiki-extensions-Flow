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
	 *
	 * @param {Event} event
	 * @param {Object} info
	 * @param {jQuery} info.$target
	 * @param {FlowBoardComponent} info.component
	 */
	function flowBoardComponentTocFeatureMixinTopicListApiPreHandler( event, info ) {
		var $this = $( this ),
			isLoadMoreButton = $this.data( 'flow-load-handler' ) === 'loadMore';

		if ( !isLoadMoreButton ) {
			// Also open the menu on this node
			$this.trigger( 'click', { interactiveHandler: 'menuToggle' } );
		}

		// Send some overrides to this API request
		var overrides = {
			topiclist_sortby: info.component.$board.data( 'flow-sortby' ),
			topiclist_toconly: true
		};

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

		var $this = $( this ),
			template = $this.data( 'flow-template' ),
			$kids = $( mw.flow.TemplateEngine.processTemplateGetFragment(
				template,
				data.flow['view-topiclist'].result.topiclist
			) ).children(),
			isLoadMoreButton = $this.data( 'flow-load-handler' ) === 'loadMore';

		if ( isLoadMoreButton ) {
			// Insert the new topic titles
			info.$target.replaceWith( $kids );
		} else {
			// Prevent this API call from happening again
			$this.data( 'flow-interactive-handler', 'menuToggle' );

			// Insert the new topic titles
			info.$target.append( $kids );
		}

		info.component.emitWithReturn( 'makeContentInteractive', $kids );

		if ( isLoadMoreButton ) {
			// Remove the old load button (necessary if the above load_more template returns nothing)
			$this.remove();
		} else if ( info.component.readingTopicId ) {
			// Scroll to the active item
			info.$target.animate( {
				scrollTop: info.$target.find( 'a[data-flow-id=' + info.component.readingTopicId + ']' ).offset().top - info.$target.offset().top
			} );
		}

		// Triggers load more if we didn't load enough content to fill the viewport
		info.$target.trigger( 'scroll.flow' );
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

	/**
	 * For
	 * @param {jQuery} $topicTitle
	 */
	function flowBoardComponentTocFeatureElementLoadTopicTitle( $topicTitle ) {
		var currentTopicId = $topicTitle.closest( '[data-flow-id]' ).data( 'flowId' );

		// If topic doesn't exist in topic titles list, add it (only happens at page load)
		if ( !this.topicTitlesById[ currentTopicId ] ) {
			this.topicTitlesById[ currentTopicId ] = $topicTitle.data( 'flow-topic-title' );
			this.orderedTopicIds.push( currentTopicId );
		}
	}
	FlowBoardComponentTocFeatureMixin.UI.events.loadHandlers.topicTitle = flowBoardComponentTocFeatureElementLoadTopicTitle;

	//
	// Private functions
	//

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentTocFeatureMixin );
}( jQuery, mediaWiki ) );
