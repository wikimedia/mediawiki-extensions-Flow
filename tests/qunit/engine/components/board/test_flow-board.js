( function ( $ ) {
QUnit.module( 'ext.flow: Flow board' );

QUnit.test( 'Check Flow is running', 1, function() {
	strictEqual( 1, 1, 'Test to see if Flow has a qunit test.' );
} );

QUnit.module( 'ext.flow: FlowBoardComponent', {
	setup: function() {
		var stub, events;

		this.$el = $( '<div class="flow-component" data-flow-component="board">' );
		this.component = mw.flow.initComponent( this.$el );
		stub = this.sandbox.stub( this.component.Api, 'apiCall' );

		stub.withArgs( {
			action: 'flow',
			submodule: 'view-topic',
			workflow: 's18cjkj1bs3rkt13',
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
									'4': {
										content: {
											format: 'html',
											content: 'Hi'
										},
										changeType: "lock-topic",
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
			workflow: 't18cjkj1bs3rkt13',
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
									'4': {
										changeType: "restore-topic",
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

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.lockTopic - perform unlock', 2, function( assert ) {
	var
		$topic = $( '<div class="flow-topic" data-flow-id="s18cjkj1bs3rkt13">' ).
			addClass( 'flow-topic-moderatestate-lock flow-topic-moderated' ).
			appendTo( this.$el ),
		$titleBar = $( '<div class="flow-topic-titlebar">' ).appendTo( $topic ),
		info = { status: 'done', $target: $topic };

	this.triggerEvent( 'apiHandlers', 'lockTopic', $titleBar, info );
	$topic = this.$el.children( '.flow-topic' );
	assert.strictEqual( $topic.hasClass( 'flow-topic-moderated' ), false, 'No longer has the moderated state.' );
	assert.strictEqual( $topic.hasClass( 'flow-topic-moderatestate-lock' ), false, 'No longer has the moderated lock state.' );
} );

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.lockTopic - perform lock', 2, function( assert ) {
	var
		$topic = $( '<div class="flow-topic" data-flow-id="t18cjkj1bs3rkt13">' ).
			appendTo( this.$el ),
		$titleBar = $( '<div class="flow-topic-titlebar">' ).appendTo( $topic ),
		info = { status: 'done', $target: $topic };

	this.triggerEvent( 'apiHandlers', 'lockTopic', $titleBar, info );
	$topic = this.$el.children( '.flow-topic' );
	assert.strictEqual( $topic.hasClass( 'flow-topic-moderated' ), true, 'Has the moderated state.' );
	assert.strictEqual( $topic.hasClass( 'flow-topic-moderatestate-lock' ), true, 'Has the moderated lock state.' );
} );

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.preview', 3, function( assert ) {
	var $container = this.$el,
		$form = $( '<form>' ).appendTo( $container ),
		$input = $( '<input value="HEADING">' ).appendTo( $form ),
		$textarea = $( '<textarea data-flow-preview-template="flow_post">text</textarea>' ).appendTo( $form ),
		$btn = $( '<button name="preview">' ).
			appendTo( $form ),
		info = {
			$target: $textarea,
			status: 'done'
		},
		data = {
			'flow-parsoid-utils': {
				format: 'html',
				content: 'hello'
			}
		};

	this.triggerEvent( 'apiHandlers', 'preview', $btn, info, data );

	// check all is well.
	assert.strictEqual( $container.find( '.flow-preview-warning' ).length, 1, 'There is a preview warning.' );
	assert.strictEqual( $textarea.hasClass( 'flow-preview-target-hidden' ), true, 'Textarea is hidden.' );
	assert.strictEqual( $input.hasClass( 'flow-preview-target-hidden' ), true, 'Input is hidden.' );
} );

QUnit.test( 'FlowBoardComponent.UI.events.apiHandlers.preview (summary)', 3, function( assert ) {
	var $container = this.$el,
		$form = $( '<form>' ).appendTo( $container ),
		$textarea = $( '<textarea data-flow-preview-template="flow_topic_titlebar_summary" data-flow-preview-node="summary">text</textarea>' ).appendTo( $form ),
		$btn = $( '<button name="preview">' ).
			appendTo( $form ),
		info = {
			$target: $textarea,
			status: 'done'
		},
		data = {
			'flow-parsoid-utils': {
				format: 'html',
				content: 'hello'
			}
		};

	this.triggerEvent( 'apiHandlers', 'preview', $btn, info, data );

	// check all is well.
	assert.strictEqual( $container.find( '.flow-preview-warning' ).length, 1,
		'There is a preview warning.' );
	assert.strictEqual( $container.find( '.flow-topic-summary' ).length, 1, 'Summary visible.' );
	assert.strictEqual( $.trim( $container.find( '.flow-topic-summary' ).text() ),
		'hello', 'Check content of summary.' );
} );

} ( jQuery ) );
