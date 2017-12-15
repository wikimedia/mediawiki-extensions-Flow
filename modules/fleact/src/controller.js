import initStore from './store'
import { getHeader, saveHeader } from './api'

export default class Controller {
	constructor( state ) {
		this.store = initStore( state )
	}

	getState() {
		return this.store.getState()
	}

	onStateChange( callback ) {
		this.store.subscribe( () => callback( this.store.getState() ) )
	}

	editHeaderPrepare() {
		this.store.dispatch( { type: 'edit-header-prepare' } )
		getHeader().then( this.editHeaderReady.bind( this ) )
	}

	editHeaderReady( description ) {
		this.store.dispatch( { type: 'edit-header-ready', description } )
	}

	editHeaderCancel() {
		this.store.dispatch( { type: 'edit-header-cancel' } )
	}

	editHeaderConfirmed( description ) {
		this.store.dispatch( { type: 'edit-header-confirmed', description } )
	}

	editHeaderSave( { content, previousRevisionId } ) {
		this.store.dispatch( { type: 'edit-header-save' } )
		saveHeader( content, previousRevisionId )
			.then( getHeader.bind( null, 'fixed-html' ) )
			.then( this.editHeaderConfirmed.bind( this ) )
	}
}
