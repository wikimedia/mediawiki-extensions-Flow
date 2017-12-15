
const formatUser = ( rawUser ) => {
	return {
		name: rawUser.name,
		talkUrl: rawUser.links.talk.url
	}
}

const formatPost = ( postId, data ) => {
	var post = data.revisions[ data.posts[ postId ] ]
	return {
		id: postId,
		content: post.content.content,
		user:  formatUser( post.creator ),
		ts: post.timestamp,
		replies: post.replies.map( ( postId ) => formatPost( postId, data ) )
	}
}

const formatDescription = ( rawDesc ) => {
	return {
		content: rawDesc.revision.content.content,
		revisionId: rawDesc.revision.revisionId,
		user: formatUser( rawDesc.revision.author ),
		actions: rawDesc.revision.actions,
		links: rawDesc.revision.links,
		editToken: rawDesc.editToken
	}
}

export function convert ( apiResponse ) {
	const data = apiResponse[ 'blocks' ],
		description = formatDescription( data[ 'header' ] ),
		topicList = data[ 'topiclist' ],
		topics = topicList ? topicList.roots.map( ( root ) => formatPost( root, topicList ) ) : []

	return { description: { view: description }, topics }
}

export function getHeader ( format='wikitext' ) {
	return new mw.Api().get( {
		action: 'flow',
		submodule: 'view-header',
		page: mw.config.get( 'wgPageName' ),
		vhformat: format
	} ).then( function ( response ) {
		return formatDescription( response.flow['view-header']['result']['header'] )
	} )
}

export function saveHeader ( content, previousRevisionId ) {
	return new mw.Api().postWithToken('csrf', {
		action: 'flow',
		submodule: 'edit-header',
		page: mw.config.get( 'wgPageName' ),
		ehformat: 'wikitext',
		ehcontent: content,
		ehprev_revision: previousRevisionId
	} )
}
