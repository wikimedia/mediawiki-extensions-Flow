/*!
 * Implements element interactive handler callbacks for FlowBoardComponent
 */

( function () {
	/**
	 * Binds element interactive (click) handlers for FlowBoardComponent
	 *
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentInteractiveEventsMixin() {
		this.bindNodeHandlers( FlowBoardComponentInteractiveEventsMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentInteractiveEventsMixin );

	FlowBoardComponentInteractiveEventsMixin.UI = {
		events: {
			interactiveHandlers: {}
		}
	};

	//
	// interactive handlers
	//

	/**
	 * Toggles collapse state
	 *
	 * @param {Event} event
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers.collapserCollapsibleToggle = function ( event ) {
		const $target = $( this ).closest( '.flow-element-collapsible' ),
			$deferred = $.Deferred(),
			updateTitle = function ( element, state ) {
				const $element = $( element );
				// In case the correct title cannot be found the wrong one must be removed
				$element.attr( 'title', $element.data( state + '-title' ) || null );
			};

		// Ignore clicks on links inside of collapsible areas
		if ( this !== event.target && $( event.target ).is( 'a' ) ) {
			return $deferred.resolve().promise();
		}

		// Ignore clicks on the editor
		if ( $( event.target ).is( '.flow-ui-editorWidget *' ) ) {
			return $deferred.resolve().promise();
		}

		const expand = $target.is( '.flow-element-collapsed' );
		$target.toggleClass( 'flow-element-collapsed', !expand )
			.toggleClass( 'flow-element-expanded', expand );
		updateTitle( this, expand ? 'expanded' : 'collapsed' );

		return $deferred.resolve().promise();
	};

	// @todo remove these data-flow handler forwarder callbacks when data-mwui handlers are implemented
	$( [ 'close', 'prevOrClose', 'nextOrSubmit', 'prev', 'next' ] ).each( ( i, fn ) => {
		// Assigns each handler with the prefix 'modal', eg. 'close' becomes 'modalClose'
		FlowBoardComponentInteractiveEventsMixin.UI.events.interactiveHandlers[ 'modal' + fn.charAt( 0 ).toUpperCase() + fn.slice( 1 ) ] = function ( event ) {
			event.preventDefault();

			// eg. call mw.Modal.close( this );
			mw.Modal[ fn ]( this );
		};
	} );

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentInteractiveEventsMixin );
}() );
