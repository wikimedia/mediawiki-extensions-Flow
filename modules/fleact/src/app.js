import React, { Component } from 'react';

const Content = ( props ) => (
  <div dangerouslySetInnerHTML={ { __html: props.html } } />
)

class Editor extends Component {
  constructor( props ) {
    super( props )
    this.state = { content: props.content }
    this.handleChange = this.handleChange.bind( this )
  }
  handleChange( e ) {
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
          onChange={ this.handleChange }
          rows="5" />
        <a href={ this.props.cancelUrl } data-action={ this.props.cancelAction }>Cancel</a>&nbsp;
        <input type="submit" value="Save"
          data-action={ this.props.saveAction }
          data-action-params={ JSON.stringify( { content: this.state.content, previousRevisionId: this.props.previousRevisionId } ) } />
      </form>
    )
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
    <a href={ props.description.actions.edit.url } data-action="editHeaderPrepare">
      { props.description.actions.edit.text }
    </a>
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
      About this board
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
    <hr />
    <pre>{ JSON.stringify( { description: props.description, topics: props.topics }, null, '\t' ) }</pre>
  </div>
)

export default Board;
