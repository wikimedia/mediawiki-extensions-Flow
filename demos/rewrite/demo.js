/*!
 * Flow standalone demo
 */

var ui,
	board = new flow.dm.Board( {
		'id': '123abc',
		'header': '<i>Header</i> <b>content</b>'
	} ) ,
	topics = [
		new flow.dm.Topic( {
			'id': '234bcd',
			'permalink': 'http://example.com/a'
		} ),
		new flow.dm.Topic( {
			'id': '345cde',
			'permalink': 'http://example.com/b'
		} ),
	],
	posts = [
		new flow.dm.Post( {
			'id': '456def',
			'creator': 'Roan',
			'timestamp': 1390926129,
			'content': 'Flow with a client-side data model'
		} ),
		new flow.dm.Post( {
			'id': '567efg',
			'creator': 'Timo',
			'timestamp': 1390912129,
			'content': 'Sweetness'
		} ),
		new flow.dm.Post( {
			'id': '678fgh',
			'creator': 'Trevor',
			'timestamp': 1390916009,
			'content': 'I am a fan'
		} ),
		new flow.dm.Post( {
			'id': '789ghi',
			'creator': 'Trevor',
			'timestamp': 1390913112,
			'content': 'OoJsUi is nifty'
		} ),
		new flow.dm.Post( {
			'id': '890hij',
			'creator': 'Roan',
			'timestamp': 1390909192,
			'content': 'Indeed, it seems OK'
		} ),
		new flow.dm.Post( {
			'id': '901ijk',
			'creator': 'Timo',
			'timestamp': 1390906162,
			'content': 'Sure, why not?'
		} )
	];

topics[0].addItems( posts.slice( 0, 3 ) );
topics[1].addItems( posts.slice( 3, 6 ) );
board.addItems( topics );

ui = new flow.ui.Board( board );

$( '#board' ).append( ui.$element );
