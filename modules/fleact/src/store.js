import { combineReducers, createStore } from 'redux'

const description = ( state=null, action ) => {
	if ( action.type === 'edit-header-prepare' ) {
		return Object.assign( {}, state, {
			edit: { 
				pending: true
			}
		} )
	}

	if ( action.type === 'edit-header-cancel' ) {
		return Object.assign( {}, state, {
			edit: false
		} )
	}

	if ( action.type === 'edit-header-ready' ) {
		return Object.assign( {}, state, {
			edit: action.description
		} )
	}

	if ( action.type === 'edit-header-save' ) {
		return Object.assign( {}, state, {
			edit: {
				pending: true
			}
		} )
	}

	if ( action.type === 'edit-header-confirmed' ) {
		return Object.assign( {}, state, {
			edit: false,
			view: action.description
		} )
	}
	return state
}

const topics = ( state=[], action ) => state

export default function initStore ( state ) {
	return createStore( combineReducers( { description, topics } ), state )
}
