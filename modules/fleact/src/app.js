import React, { Component } from 'react'

const Content = ( props ) => (
  <div dangerouslySetInnerHTML={ { __html: props.html } } />
)

const enumerateObjectDiff = ( obj1, obj2 ) => {
	let allKeys = Object.keys( obj1 ).concat( Object.keys( obj2 ) )
	return allKeys
		.map( ( key ) => ( { key, value1: obj1[ key ], value2: obj2[ key ] } ) )
		.filter( ( { key, value1, value2 } ) => value1 !== value2 )
}

const filterOutFalsy = ( obj ) => {
	let result = {}
	Object.keys( obj ).forEach( ( key ) => {
		if ( obj[ key ] ) {
			result[ key ] = obj[ key ]
		}
	} )
	return result
}

class OOUIWidget extends Component {
	constructor( props ) {
		super( props )
	}
	shouldComponentUpdate() {
		// this is an "uncontrolled" component
		// it has the following lifecycle:
		//  1- render() generate an empty <div />
		//  2- componentDidMount() instantiate the OOUI Widget and replaces the generated div with the widget's $element
		//  3- componentWillReceiveProps() is called when properties change and this.button needs to be updated
		return false
	}
	componentDidMount() {
		this.widget = new OO.ui[ this.props.type + 'Widget' ]( this.props.config )

		let events = filterOutFalsy( this.props.events )
		if ( events ) {
			this.widget.connect( this, events )
		}

		let otherProps = Object.assign( {}, this.props )
		delete otherProps.type
		delete otherProps.config
		delete otherProps.events

		this.widget.$element.attr( otherProps )

		$( this.el ).replaceWith( this.widget.$element )
	}
	componentWillReceiveProps( nextProps ) {
		// todo: update this.widget with options from nextProps
		enumerateObjectDiff( nextProps.config, this.props.config ).forEach( ( { key, value1, value2 } ) => {
			let setterName = 'set' + key.charAt( 0 ).toUpperCase() + key.slice( 1 )
			if ( typeof this.widget[ setterName ] === 'function' ) {
				this.widget[ setterName ]( value1 );
			} else {
				console.warn( 'Config option "' + key + '" has changed but I don\'t know what to do about it' )
			}
		} )
	}
	render() {
		return <div ref={el => this.el = el} />
	}
}

const consumable = ( obj ) => {
	let originalCopy = Object.assign( {}, obj )
	return new Proxy( originalCopy, {
		get( target, name, receiver ) {
			let value = target[ name ]
			delete target[ name ]
			return value
		}
	} )
}

const OOUIButton = ( props ) => {
	let p = consumable( props )
	return ( <OOUIWidget
		type="Button"
		config={ { label: p.label, flags: p.flags, framed: p.framed } }
		events={ { click: p.onClick } } {...p} /> )
}

class VisualEditor extends Component {
	constructor( props ) {
		super( props )
		this.onSave = this.onSave.bind( this )
	}
	componentDidMount() {
		this.target = ve.init.mw.targetFactory.create( 'flow' )
		this.target.setDefaultMode( 'source' )
		this.target.loadContent( this.props.content )
		$(this.el).prepend( this.target.$element )
	}
	shouldComponentUpdate() {
		return false
	}
	getContent() {
		const dom = this.target.getSurface().getDom();
		let content = '',
			format = ''
		if ( typeof dom === 'string' ) {
			content = dom;
			format = 'wikitext';
		} else {
			// Document content will include html, head & body nodes; get only content inside body node
			content = ve.properInnerHtml( dom.body );
			format = 'html';
		}
		return { content: content, format: format };
	}
	onSave() {
		const content = this.getContent()
		window.controller[ this.props.saveAction ]( {
			content: content.content,
			format: content.format,
			previousRevisionId: this.props.previousRevisionId
		} )
	}
	render() {
		return (
			<div className="flow-ui-editorWidget-editor" ref={el => this.el = el}>
				<div className="flow-ui-editorControlsWidget-buttons">
					<OOUIButton label="Cancel" data-action={ this.props.cancelAction } flags="destructive" framed={ false } />
					<OOUIButton label="Save" flags="progressive" onClick={ this.onSave } />
				</div>
			</div>
		)
	}
}

class TextareaEditor extends Component {
	constructor( props ) {
		super( props )
		this.state = { content: props.content }
		this.handleTextareaChange = this.handleTextareaChange.bind( this )
	}
	handleTextareaChange( e ) {
		this.setState( { content: e.target.value } )
	}
	render() {
		return (
			<form method="post" action={ this.props.formActionUrl }>
				<input type="hidden" name="header_prev_revision" value={ this.props.previousRevisionId } />
				<input type="hidden" name="wpEditToken" value={ this.props.editToken } />
				<textarea
					name="header_content"
					value={ this.state.content }
					onChange={ this.handleTextareaChange }
					rows="5" />
						<a href={ this.props.cancelUrl } data-action={ this.props.cancelAction }>Cancel</a>&nbsp;
				<input type="submit" value="Save"
					data-action={ this.props.saveAction }
					data-action-params={ JSON.stringify( { content: this.state.content, previousRevisionId: this.props.previousRevisionId } ) } />
			</form>
		)
	}
}

class Editor extends Component {
	constructor( props ) {
		super( props )
		this.state = { ve: false }
	}
	componentDidMount() {
		//todo: only set {ve: true} if VE is actually available
		this.setState( { ve: true } )
	}
	render() {
		return this.state.ve ?
			<VisualEditor { ...this.props } /> :
			<TextareaEditor { ...this.props } />
	}
}

const User = ( props ) => (
  <div>
    <a href={ props.user.talkUrl }>
      { props.user.name }
    </a>
  </div>
)

const ViewDesc = ( props ) => (
  <div className="flow-board-header-content">
	<OOUIButton label={ props.description.actions.edit.text } framed={ false } flags="progressive" data-action="editHeaderPrepare" />
    <Content html={ props.description.content } />
  </div>
)

const EditDesc = ( props ) => (
  <div>
    {
      props.description.pending ?
        <div>Please wait...</div> :
        <Editor
          content={ props.description.content }
          previousRevisionId={ props.description.revisionId }
          formActionUrl={ props.description.actions.edit.url }
          cancelUrl={ props.description.links.workflow.url }
          editToken={ props.description.editToken }
          cancelAction="editHeaderCancel"
          saveAction="editHeaderSave" />
    }
  </div>
)

const Desc = ( props ) => (
  <div className="flow-board-header">
    <h2 className="flow-board-header-title">
      { mw.msg( 'flow-board-header' ) }
    </h2>
    {
      props.description.edit ?
        <EditDesc description={ props.description.edit } /> :
        <ViewDesc description={ props.description.view } />
    }
    <hr />
    <div className="flow-board-header-footer">
      Text is available under the <a href="https://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-ShareAlike License</a>; additional terms may apply. See <a href="https://wikimediafoundation.org/wiki/Terms_of_Use">Terms of Use</a> for details.
    </div>
  </div>
)

const Comment = ( props ) => (
  <div className="flow-comment" style={ { marginLeft: props.indent*20 } }>
    <User user={ props.comment.user } />
    <Content html={ props.comment.content } />
    <small>{ props.comment.ts }</small>
    { props.comment.replies.map( c => <Comment comment={ c } key={ c.id } indent={ props.indent+1 } /> ) }
  </div>
)

const Topic = ( props ) => (
  <div className="flow-topic">
    <h3><Content html={ props.topic.content } /></h3>
    <small>{ props.topic.replies.length } comment(s) - { props.topic.ts }</small>
    { props.topic.replies.map( c => <Comment comment={ c } key={ c.id } indent={ 0 } /> ) }
  </div>
)

const Board = ( props ) => (
  <div className="flow-board">
    <Desc description={ props.description } />
    { !!props.topics.length && <h2>Topics</h2> }
    { props.topics.map( t => <Topic topic={ t } key={ t.id } /> ) }
  </div>
)

export default Board;
