QUnit.module( 'ext.flow.dm mw.flow.dm.Topic' );

/* Tests */

QUnit.test( 'Load topics', function ( assert ) {
	var i, j, ilen, jlen, topic, result, operation,
		executeOperation = function ( obj, operation, params ) {
			return obj[operation].apply( obj, params );
		},
		expectCount = 0,
		cases = [
		{
			args: {
				id: 'sgl9yjs9nwgmc7l7',
				data: {
					workflowId: 'sgl9yjs9nwgmc7l7',
					articleTitle: 'Topic:Sgl9yjs9nwgmc7l7',
					revisionId: 'sgl9yjsb80w9oeaz',
					timestamp: '20150503034600',
					changeType: 'new-post',
					dateFormats: [],
					properties: {
						'topic-of-post': {
							plaintext: 'This is the content of the post.'
						}
					},
					isOriginalContent: true,
					isModerated: false,
					links: {
						'topic-history': {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=history',
							title: 'History',
							text: 'History'
						},
						topic: {
							url: '/wiki/index.php/Topic:Sgl9yjs9nwgmc7l7',
							title: 'topic',
							text: 'topic'
						},
						post: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&topic_showPostId=sgl9yjs9nwgmc7l7#flow-post-sgl9yjs9nwgmc7l7',
							title: 'post',
							text: 'post'
						},
						'topic-revision': {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&topic_revId=sgl9yjsb80w9oeaz&action=single-view',
							title: 'topic revision',
							text: 'topic revision'
						},
						'watch-topic': {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=watch',
							title: 'Watch',
							text: 'Watch'
						},
						'unwatch-topic': {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=unwatch',
							title: 'Unwatch',
							text: 'Unwatch'
						}
					},
					actions: {
						reply: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=reply&topic_postId=sgl9yjs9nwgmc7l7#flow-post-sgl9yjs9nwgmc7l7-form-content',
							title: 'Reply',
							text: 'Reply'
						},
						edit: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=edit-title&topic_revId=sgl9yjsb80w9oeaz',
							title: 'Edit title',
							text: 'Edit title'
						},
						hide: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=moderate-topic&topic_moderationState=hide',
							title: 'Hide topic',
							text: 'Hide topic'
						},
						'delete': {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=moderate-topic&topic_moderationState=delete',
							title: 'Delete topic',
							text: 'Delete topic'
						},
						suppress: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=moderate-topic&topic_moderationState=suppress',
							title: 'Suppress topic',
							text: 'Suppress topic'
						},
						summarize: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=edit-topic-summary',
							title: 'Summarize',
							text: 'Summarize'
						},
						lock: {
							url: '/wiki/index.php?title=Topic:Sgl9yjs9nwgmc7l7&action=lock-topic&flow_moderationState=lock',
							title: 'Lock topic',
							text: 'Lock topic'
						}
					},
					size: {
						old: '0',
						'new': '16'
					},
					author: {
						name: '127.0.0.1',
						wiki: 'mediawiki',
						gender: 'unknown',
						links: {
							contribs: {
								url: '/wiki/index.php/Special:Contributions/127.0.0.1',
								title: 'Contributions/127.0.0.1',
								exists: true
							},
							userpage: {
								url: '/wiki/index.php/User:127.0.0.1',
								title: '127.0.0.1',
								exists: false
							},
							talk: {
								url: '/wiki/index.php/User_talk:127.0.0.1',
								title: 'User talk:127.0.0.1',
								exists: true
							},
							block: {
								url: '/wiki/index.php/Special:Block/127.0.0.1',
								title: 'block',
								exists: true
							}
						},
						id: 0
					},
					lastEditUser: {
						name: null,
						wiki: null,
						gender: 'unknown',
						links: [],
						id: null
					},
					lastEditId: null,
					previousRevisionId: null,
					content: {
						content: 'This is the content of the post.',
						format: 'plaintext'
					},
					isWatched: true,
					watchable: true,
					replyToId: null,
					postId: 'sgl9yjs9nwgmc7l7',
					isMaxThreadingDepth: false,
					creator: {
						name: '127.0.0.1',
						wiki: 'mediawiki',
						gender: 'unknown',
						links: {
							contribs: {
								url: '/wiki/index.php/Special:Contributions/127.0.0.1',
								title: 'Contributions/127.0.0.1',
								exists: true
							},
							userpage: {
								url: '/wiki/index.php/User:127.0.0.1',
								title: '127.0.0.1',
								exists: false
							},
							talk: {
								url: '/wiki/index.php/User_talk:127.0.0.1',
								title: 'User talk:127.0.0.1',
								exists: true
							},
							block: {
								url: '/wiki/index.php/Special:Block/127.0.0.1',
								title: 'block',
								exists: true
							}
						},
						id: 0
					},
					replies: [
						'sgl9yjsb82vasga3'
					],
					reply_count: 1,
					last_updated_readable: '03:46, 3 May 2015',
					last_updated: 1430624760000
				}
			},
			operations: [
				{
					method: 'getId',
					expected: 'sgl9yjs9nwgmc7l7',
					msg: 'Get topic id'
				},
				{
					method: 'getContent',
					expected: {
						content: 'This is the content of the post.',
						format: 'plaintext'
					},
					msg: 'Get topic content'
				},
				{
					method: 'getRawContent',
					expected: 'This is the content of the post.',
					msg: 'Get topic raw content'
				},
				{
					method: 'isModerated',
					expected: false,
					msg: 'Check unmoderated topic moderation state'
				},
				{
					method: 'toggleModerated',
					ignoreTest: true,
					params: [ true ]
				},
				{
					method: 'isModerated',
					expected: true,
					msg: 'Moderate topic'
				},
				{
					method: 'isWatched',
					expected: true,
					msg: 'Check watched topic watch state'
				},
				{
					method: 'toggleWatched',
					ignoreTest: true,
					params: [ false ]
				},
				{
					method: 'isWatched',
					expected: false,
					msg: 'Unwatch topic'
				}
			]
		}
	];

	for ( i = 0, ilen = cases.length; i < ilen; i++ ) {
		expectCount += cases[i].operations.length;

		topic = new mw.flow.dm.Topic( cases[i].args.id, cases[i].args.data );

		for ( j = 0, jlen = cases[i].operations.length; j < jlen; j++ ) {
			operation = cases[i].operations[j];
			result = executeOperation( topic, operation.method, operation.params || [] );
			if ( !operation.ignoreTest ) {
				// Test
				assert.deepEqual( result, operation.expected, operation.msg );
			} else {
				expectCount--;
			}
		}
	}

	QUnit.expect( expectCount );
} );
