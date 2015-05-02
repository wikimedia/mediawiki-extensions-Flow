( function ( $ ) {
QUnit.module( 'ext.flow: Flow board' );

QUnit.test( 'Check Flow is running', 1, function () {
	strictEqual( 1, 1, 'Test to see if Flow has a qunit test.' );
} );

QUnit.module( 'ext.flow: FlowBoardComponent', {
	setup: function () {
		var stub, events;

		this.$el = $( '<div class="flow-component" data-flow-component="board">' );
		this.component = mw.flow.initComponent( this.$el );
		stub = this.sandbox.stub( this.component.Api, 'apiCall' );

		stub.withArgs( {
			action: 'flow',
			submodule: 'view-topic',
			page: 'Topic:S18cjkj1bs3rkt13'
		} ).returns(
			$.Deferred().resolve( {
				flow: {
					'view-topic': {
						result: {
							topic: {
								roots: [ 's18cjkj1bs3rkt13' ],
								posts: {
									s18cjkj1bs3rkt13: '4'
								},
								revisions: {
									4: {
										content: {
											format: 'html',
											content: 'Hi'
										},
										changeType: 'lock-topic',
										isModerated: false
									}
								}
							}
						}
					}
				}
			} )
		);
		stub.withArgs( {
			action: 'flow',
			submodule: 'view-topic',
			page: 'Topic:T18cjkj1bs3rkt13'
		} ).returns(
			$.Deferred().resolve( {
				flow: {
					'view-topic': {
						result: {
							topic: {
								roots: [ 't18cjkj1bs3rkt13' ],
								posts: {
									t18cjkj1bs3rkt13: '4'
								},
								revisions: {
									4: {
										changeType: 'restore-topic',
										content: {
											format: 'html',
											content: 'Hi'
										},
										isModerated: true,
										moderateState: 'lock'
									}
								}
							}
						}
					}
				}
			} )
		);

		events = this.component.UI.events;
		// This method is used to directly trigger callback methods
		// It's needed, because the element we are triggering it from doesn't necessarily
		// have the required data- attributes to cause the correct workflow
		// @todo Correct these tests to test with real elements and their data attribs
		this.triggerEvent = function ( handlerType, callbackName, context, args ) {
			var returns = [];
			args = Array.prototype.slice.call( arguments, 3 );

			$.each( events[ handlerType ][ callbackName ], function ( i, callbackFn ) {
				returns.push( callbackFn.apply( context, args ) );
			} );

			return returns;
		};
	}
} );

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.lockTopic - perform unlock', 2, function ( assert ) {
	var
		$el = this.$el,
		$topic = $( '<div class="flow-topic" data-flow-id="s18cjkj1bs3rkt13">' ). addClass( 'flow-topic-moderatestate-lock flow-topic-moderated' ). appendTo( $el ),
		$titleBar = $( '<div class="flow-topic-titlebar">' ).appendTo( $topic ),
		info = { status: 'done', $target: $topic },
		returns;

	returns = this.triggerEvent( 'apiHandlers', 'lockTopic', $titleBar, info );
	returns[0].done( function () {
		$topic = $el.children( '.flow-topic' );
		assert.strictEqual( $topic.hasClass( 'flow-topic-moderated' ), false, 'No longer has the moderated state.' );
		assert.strictEqual( $topic.hasClass( 'flow-topic-moderatestate-lock' ), false, 'No longer has the moderated lock state.' );
	} );
} );

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.lockTopic - perform lock', 2, function ( assert ) {
	var
		$el = this.$el,
		$topic = $( '<div class="flow-topic" data-flow-id="t18cjkj1bs3rkt13">' ). appendTo( $el ),
		$titleBar = $( '<div class="flow-topic-titlebar">' ).appendTo( $topic ),
		info = { status: 'done', $target: $topic },
		returns;

	returns = this.triggerEvent( 'apiHandlers', 'lockTopic', $titleBar, info );
	returns[0].done( function () {
		$topic = $el.children( '.flow-topic' );
		assert.strictEqual( $topic.hasClass( 'flow-topic-moderated' ), true, 'Has the moderated state.' );
		assert.strictEqual( $topic.hasClass( 'flow-topic-moderatestate-lock' ), true, 'Has the moderated lock state.' );
	} );
} );

}( jQuery ) );
