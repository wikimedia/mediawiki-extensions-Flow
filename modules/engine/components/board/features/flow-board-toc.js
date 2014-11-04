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
	// Prototype methods
	//

	/**
	 *
	 * @param {Object} data
	 * @param {Object} data.roots
	 */
	function flowBoardComponentTocFeatureMixinAddTocItems( data ) {

	}
	FlowBoardComponentTocFeatureMixin.prototype.addTocItems = flowBoardComponentTocFeatureMixinAddTocItems;

	//
	// API pre-handlers
	//

	/**
	 *
	 * @param {Event} event
	 * @param {Object} info
	 * @param {jQuery} info.$target
	 */
	function flowBoardComponentTocFeatureMixinTopicListApiPreHandler( event, info ) {
		// Also open the menu on this node
		$( this ).trigger( 'click', { interactiveHandler: 'menuToggle' } );
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
			template = $this.data( 'flow-template' );

		// Prevent this API call from happening again
		$this.data( 'flow-interactive-handler', 'menuToggle' );

		// Insert the new topic titles
		info.$target.append(
			$( mw.flow.TemplateEngine.processTemplateGetFragment(
				template,
				data.flow[ 'view-topiclist' ].result.topiclist
			) ).children()
		);
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
