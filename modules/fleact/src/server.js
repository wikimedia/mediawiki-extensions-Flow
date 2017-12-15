import React from 'react'
import ReactDOMServer from 'react-dom/server'
import Board from './app'
import { convert } from './api'
import Controller from './controller'

global.renderFleactServer = function ( apiResponse, action ) {
	const board = convert( apiResponse )
	const controller = new Controller( board )

	if ( action === 'edit-header' ) {
		controller.editHeaderReady( board.description.view )
	}
	const state = controller.getState()

	const markup = ReactDOMServer.renderToString(
		<Board description={state.description} topics={state.topics} />
	)

	return { state: JSON.stringify( state ), markup }
}
