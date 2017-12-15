import React from 'react'
import ReactDOM from 'react-dom'
import Board from './app'
import { convert } from './api'
import Controller from './controller'

// console.log( 'RAW:', mw.config.get( 'wgFlowData' ) )

const preRendered = mw.config.get( 'wgFlowPreRendered' ),
	wgFlowData = mw.config.get( 'wgFlowData' ),
	state = preRendered ? wgFlowData : convert( wgFlowData )

const controller = new Controller( state )

const hydrate = ( state ) => {
	console.time( 'client hydrate' )
	ReactDOM.hydrate( 
		<Board description={state.description} topics={state.topics} />,
		document.getElementById( 'fleact-root' )
	)
	console.timeEnd( 'client hydrate' )
}

const render = ( state ) => {
	console.time( 'client render' )
	ReactDOM.render( 
		<Board description={state.description} topics={state.topics} />,
		document.getElementById( 'fleact-root' )
	)
	console.timeEnd( 'client render' )
}

if ( preRendered ) {
	hydrate( controller.getState() )
} else {
	render( controller.getState() )
}

controller.onStateChange( ( state ) => render( state ) )

$( '#fleact-root' ).on( 'click', '[data-action]', function ( e ) {
	e.preventDefault();
	let action = $( this ).data( 'action' )
	let params = $( this ).data( 'action-params' )
	if ( controller[ action ] ) {
		controller[ action ]( params )
	} else {
		console.log( 'Unrecognized controller action:', action )
	}
} )

console.log( 'client initialization: done' )
