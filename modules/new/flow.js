/*!
 * Runs Flow code, using methods in FlowUI.
 */

( function ( $ ) {
	// Pretend we got some data and run with it
	var mockData = {
		board: 'ste225fux0r7hi5o',

		workflows: { // boards / topics / replies
			'ste225fux0r7hi5o': {
				header: {
					time_updated: +new Date, // timestamp
					author_id: 'en1', // wiki+id
					content: null, /*'<p>lorry ippy sumsum</p>'*/ // html
					revid: 1
				},
				topics: ['rqx495tvz888x5ur','rqx495tvz888x5uv'], // topic uuids
				topic_count: 3, // total topics on this page including not shown
				sort_order: 'desc', // (might need parsing)
				urls: {
					history: '#'
				}
			},

			'rqx495tvz888x5ur': { // TOPIC uuid
				replies: ['rqx495tvz888x5ut'], // reply uuids
				reply_count: 2,
				author_count: 3,
				state: '',
				start_time: +new Date - 60000000, // parsed from UUID (first 44 bits): parseInt( parseInt( uuid, 36 ).toString( 2 ).substr( 0, 41 ), 2 );
				update_time: +new Date - 10000,
				author_id: 'en1', // wiki+id
				content: 'discussion title', // text
				revid: 3
			},

			'rqx495tvz888x5ut': { // REPLY uuid
				replies: ['rqx495tvz888x5uu'], // reply uuids
				reply_count: 1,
				state: '',
				start_time: +new Date - 60000, // parsed from UUID (first 44 bits)
				update_time: null,
				author_id: 'en1', // wiki+id
				content: '<p>lorem ipsum...</p>', // html
				revid: 0
			},

			'rqx495tvz888x5uu': { // REPLY uuid
				replies: [], // reply uuids
				reply_count: 0,
				state: '',
				start_time: +new Date - 10000, // parsed from UUID (first 44 bits)
				update_time: +new Date - 1000,
				author_id: 'en2', // wiki+id
				content: '<p>dolor sit amet!</p>', // html
				revid: 1
			},

			'rqx495tvz888x5uv': { // TOPIC uuid
				replies: [], // reply uuids
				reply_count: 0,
				state: '',
				start_time: +new Date - 600000000, // parsed from UUID (first 44 bits)
				update_time: null,
				author_id: 'en3', // wiki+id
				content: 'cool story', // text
				revid: 0
			}
		},

		authors: {
			'en1': { // wiki+id
				name: 'shaggy yar',
				gender: 'male',
				wiki: 'enwiki'
			},

			'en2': { // wiki+id
				name: 'lady of the night',
				gender: 'female',
				wiki: 'enwiki'
			},

			'en3': { // wiki+id
				// moderated/redacted user
				name: 'suppressed',
				gender: null,
				wiki: null
			}
		}
	};

	/*
	 * Now do stuff
	 * @todo not like this
	 */
	$( document ).ready( function () {
		$( mw.flow.TemplateEngine.processTemplateGetFragment( 'flow_board', mockData ) ).insertBefore( '#flow_board-partial' );

		mw.flow.initComponent( $( '.flow-component' ) );
	} );
}( jQuery ) );

// temp @todo remove
document.getElementsByTagName('html')[0].className = document.getElementsByTagName('html')[0].className.replace('client-nojs', 'client-js');