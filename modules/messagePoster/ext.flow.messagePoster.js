( function ( $, mw, OO ) {
	/**
	 * This is an implementation of	MessagePoster for Flow boards
	 *
	 * The title can be a non-existent board, but it will only work if Flow is allowed in that
	 * namespace or the user has flow-create-board
	 *
	 * @class
	 * @constructor
	 *
	 * @extends mw.messagePoster.MessagePoster
	 *
	 * @param {mw.Title} title Title of Flow board
	 */
	mw.flow = mw.flow || {};
	mw.flow.MessagePoster =	function MwFlowMessagePoster( title ) {
		// I considered using FlowApi, but most of that functionality is about mapping <form>
		// or <a> tags to AJAX, which is not applicable.  This allows us to keep
		// mediawiki.messagePoster.flow-board light-weight.

		this.api = new mw.Api();
		this.title = title;
	};

	OO.inheritClass(
		mw.flow.MessagePoster,
		mw.messagePoster.MessagePoster
	);

	/**
	 * @inheritdoc
	 */
	mw.flow.MessagePoster.prototype.post = function ( subject, body ) {
		var dfd = $.Deferred(),
			promise = dfd.promise();

		mw.flow.MessagePoster.parent.prototype.post.call( this, subject, body );

		this.api.postWithToken( 'edit', {
			action: 'flow',
			submodule: 'new-topic',
			page: this.title.getPrefixedDb(),
			nttopic: subject,
			ntcontent: body,
			ntformat: 'wikitext',
			ntmetadataonly: 1
		}, {
			// IE 8 seems to have cached some POST requests without this
			cache: false
		} ).done( function ( resp, jqXHR ) {
			dfd.resolve( resp, jqXHR );
		} ).fail( function ( code, details ) {
			dfd.reject( 'api-fail', code, details );
		} );

		return promise;
	};

	mw.messagePoster.factory.register( 'flow-board', mw.flow.MessagePoster );
} ( jQuery, mediaWiki, OO ) );
