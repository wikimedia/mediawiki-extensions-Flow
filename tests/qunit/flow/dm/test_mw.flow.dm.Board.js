QUnit.module( 'ext.flow.dm mw.flow.dm.Board' );

/* Tests */

QUnit.test( 'Create board', function ( assert ) {
	var i, ilen, result, board,
		executeOperation = function ( obj, operation, params ) {
			return obj[operation].apply( obj, params );
		},
		expectCount = 0,
		cases = [
			{
				method: 'getHashObject',
				expected: {
					id: 'xxx123xxx',
					isDeleted: false,
					pagePrefixedDb: 'Special:FlowTestBoardPage',
					topicCount: 0,
					description: {
						// Empty description revision
						author: null,
						changeType: null,
						content: null,
						contentFormat: null,
						creator: null,
						id: null,
						lastUpdate: null,
						originalContent: true,
						previousRevisionId: null,
						revisionId: null,
						timestamp: null,
						watchable: true,
						watched: false,
						workflowId: null
					}
				},
				msg: 'Get current board initial state'
			},
			{
				method: 'storeComparableHash'
			},
			{
				method: 'addItems',
				params: [
					[
						new mw.flow.dm.Topic( 'topic1', {
							content: {
								content: 'Topic 1',
								format: 'plaintext'
							},
							last_updated: '123123123'
						} ),
						new mw.flow.dm.Topic( 'topic2', {
							content: {
								content: 'Topic 2',
								format: 'plaintext'
							},
							last_updated: '123123321'
						} ),
						new mw.flow.dm.Topic( 'topic3', {
							content: {
								content: 'Topic 3',
								format: 'plaintext'
							},
							last_updated: '123123543'
						} )
					]
				]
			},
			{
				method: 'getItemCount',
				expected: 3,
				msg: 'Add topics.'
			},
			{
				method: 'setDescription',
				params: [
					new mw.flow.dm.BoardDescription( {
						content: {
							content: '<h1>This is a test board description.</h1>',
							format: 'html'
						}
					} )
				]
			},
			{
				method: 'getHashObject',
				expected: {
					id: 'xxx123xxx',
					isDeleted: false,
					pagePrefixedDb: 'Special:FlowTestBoardPage',
					topicCount: 3,
					description: {
						author: null,
						changeType: null,
						content: '<h1>This is a test board description.</h1>',
						contentFormat: 'html',
						creator: null,
						id: null,
						lastUpdate: null,
						originalContent: true,
						previousRevisionId: null,
						revisionId: null,
						timestamp: null,
						watchable: true,
						watched: false,
						workflowId: null
					}
				},
				msg: 'Get current board state with description'
			},
			{
				method: 'hasBeenChanged',
				expected: true,
				msg: 'Check if board has changed since breakpoint'
			}
		];

	board = new mw.flow.dm.Board( {
		id: 'xxx123xxx',
		pageTitle: ( new mw.Title( 'Special:FlowTestBoardPage' ) ),
		isDeleted: false
	} );

	expectCount = cases.length;
	for ( i = 0, ilen = cases.length; i < ilen; i++ ) {
		result = executeOperation( board, cases[i].method, cases[i].params || [] );
		if ( cases[ i ].expected !== undefined ) {
			// Test
			assert.deepEqual( result, cases[i].expected, cases[i].msg );
		} else {
			expectCount--;
		}
	}

	QUnit.expect( expectCount );
} );
