import React from 'react'
import ReactDOM from 'react-dom'
import Board from './app'
import { convert } from './api'
import Controller from './controller'

// console.log( mw.config.get( 'wgFlowData' ) )
const state = convert( mw.config.get( 'wgFlowData' ) )

const controller = window.controller = new Controller( state )

const render = ( state ) => {
	console.time( 'fleact render' )
	ReactDOM.render(
		<Board description={state.description} topics={state.topics} />,
		document.getElementById( 'fleact-root' )
	)
	console.timeEnd( 'fleact render' )
}

$( '.flow-component' ).replaceWith( $( '<div>' ).attr( 'id', 'fleact-root' ) )
render( controller.getState() )
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
