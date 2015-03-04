/*!
 * @todo break this down into mixins for each callback section (eg. post actions, read topics)
 */

( function ( $, mw ) {
	/**
	 * Binds API events to FlowBoardComponent
	 * @param {jQuery} $container
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentApiEventsMixin( $container ) {
		// Bind event callbacks
		this.bindNodeHandlers( FlowBoardComponentApiEventsMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentApiEventsMixin );

	/** Event handlers are stored here, but are registered in the constructor */
	FlowBoardComponentApiEventsMixin.UI = {
		events: {
			globalApiPreHandlers: {},
			apiPreHandlers: {},
			apiHandlers: {}
		}
	};

	//
	// pre-api callback handlers, to do things before the API call
	//

	/**
	 * Textareas are turned into editor objects, so we can't rely on
	 * textareas to properly return the real content we're looking for (the
	 * real editor can be anything, depending on the type of editor)
	 *
	 * @param {Event} event
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.globalApiPreHandlers.prepareEditor = function ( event ) {
		var $textareas = $( this ).closest( 'form' ).find( 'textarea' ),
			override = {};

		$textareas.each( function() {
			var $editor = $( this );

			// Doublecheck that this textarea is actually an editor instance
			// (the editor may have added a textarea itself...)
			if ( mw.flow.editor && mw.flow.editor.exists( $editor ) ) {
				override[$editor.attr( 'name' )] = mw.flow.editor.getRawContent( $editor );
				override.flow_format = mw.flow.editor.getFormat();
			}

			// @todo: we have to make sure we get rid of all unwanted data
			// in the form (whatever "editor instance" may have added)
			// because we'll $form.serializeArray() to get the content.
			// This is currently not an issue since we only have "none"
			// editor type, which just uses the existing textarea. Someday,
			// however, we may have VE (or wikieditor or ...) which could
			// add its own nodes, which may be picked up by serializeArray()
		} );

		return override;
	};

	/**
	 * When presented with an error conflict, the conflicting content can
	 * subsequently be re-submitted (to overwrite the conflicting content)
	 * This will prepare the data-to-be-submitted so that the override is
	 * submitted against the most current revision ID.
	 * @param {Event} event
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.globalApiPreHandlers.prepareEditConflict = function ( event ) {
		var $form = $( this ).closest( 'form' ),
			prevRevisionId = $form.data( 'flow-prev-revision' );

		if ( !prevRevisionId ) {
			return {};
		}

		// Get rid of the temp-saved new revision ID
		$form.removeData( 'flow-prev-revision' );

		/*
		 * This is prev_revision in "generic" form. Each Flow API has its
		 * own unique prefix, which (in FlowApi.prototype.getQueryMap) will
		 * be properly applied for the respective API call; e.g.
		 * epprev_revision (for edit post)
		 */
		return {
			flow_prev_revision: prevRevisionId
		};
	};

	/**
	 * Before activating header, sends an overrideObject to the API to modify the request params.
	 * @param {Event} event
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateEditHeader = function () {
		return {
			submodule: 'view-header', // href submodule is edit-header
			vhcontentFormat: 'wikitext' // href does not have this param
		};
	};

	/**
	 * Before activating topic, sends an overrideObject to the API to modify the request params.
	 *
	 * @param {Event} event
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateEditTitle = function ( event ) {
		// Use view-post API for topic as well; we only want this on
		// particular (title) post revision, not the full topic
		return {
			submodule: "view-post",
			vppostId: $( this ).closest( '.flow-topic' ).data( 'flow-id' ),
			vpcontentFormat: "wikitext"
		};
	};

	/**
	 * Before activating post, sends an overrideObject to the API to modify the request params.
	 * @param {Event} event
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateEditPost = function ( event ) {
		return {
			submodule: 'view-post',
			vppostId: $( this ).closest( '.flow-post' ).data( 'flow-id' ),
			vpcontentFormat: 'wikitext'
		};
	};

	/**
	 * Adjusts query params to use global watch action, and specifies it should use a watch token.
	 * @param {Event} event
	 * @returns {Function}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.watchItem = function ( event ) {
		return function ( queryMap ) {
			var params = {
				action: 'watch',
				titles: queryMap.page,
				_internal: {
					tokenType: 'watch'
				}
			};
			if ( queryMap.submodule === 'unwatch' ) {
				params.unwatch = 1;
			}
			return params;
		};
	};

	/**
	 * Before activating summarize topic, sends an overrideObject to the
	 * API to modify the request params.
	 * @param {Event} event
	 * @param {Object} info
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateSummarizeTopic = function ( event, info ) {
		if ( info.$target.find( 'form' ).length ) {
			// Form already open; cancel the old form
			var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );
			flowBoard.emitWithReturn( 'cancelForm', info.$target );
			return false;
		}

		return {
			// href submodule is edit-topic-summary
			submodule: 'view-topic-summary',
			// href does not have this param
			vtscontentFormat: 'wikitext'
		};
	};

	/**
	 * Before activating lock/unlock edit form, sends an overrideObject
	 * to the API to modify the request params.
	 * @param {Event} event
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateLockTopic = function ( event ) {
		return {
			// href submodule is lock-topic
			submodule: 'view-post',
			// href does not have this param
			vpcontentFormat: 'wikitext',
			// request just the data for this topic
			vppostId: $( this ).data( 'flow-id' )
		};
	};

	//
	// api callback handlers
	//

	/**
	 * On complete board reprocessing through view-topiclist (eg. change topic sort order), re-render any given blocks.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.board = function ( info, data, jqxhr ) {
		var $rendered,
			flowBoard = info.component,
			dfd = $.Deferred();

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return dfd.reject().promise();
		}

		$rendered = $(
			flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_block_loop',
				{ blocks: data.flow[ 'view-topiclist' ].result }
			)
		).children();

		// Run this on a short timeout so that the other board handler in FlowBoardComponentLoadMoreFeatureMixin can run
		// TODO: Using a timeout doesn't seem like the right way to do this.
		setTimeout( function () {
			// Reinitialize the whole board with these nodes
			flowBoard.reinitializeContainer( $rendered );
			dfd.resolve();
		}, 50 );

		return dfd.promise();
	};

	/**
	 * @returns {$.Promise}

		return $.Deferred().resolve().promise();
	 * Renders the editable board header with the given API response.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateEditHeader = function ( info, data, jqxhr ) {
		var $rendered,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) ),
			$oldBoardNodes;

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		// Change "header" to "header_edit" so that it loads up flow_block_header_edit
		data.flow[ 'view-header' ].result.header.type = 'header_edit';

		$rendered = $(
			flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_block_loop',
				{ blocks: data.flow[ 'view-header' ].result }
			)
		).children();

		// Set the cancel callback on this form so that it returns the old content back if needed
		flowBoard.emitWithReturn( 'addFormCancelCallback', $rendered.find( 'form' ), function () {
			flowBoard.reinitializeContainer( $oldBoardNodes );
		} );

		// Reinitialize the whole board with these nodes, and hold onto the replaced header
		$oldBoardNodes = flowBoard.reinitializeContainer( $rendered );

		return $.Deferred().resolve().promise();
	};

	/**
	 * After submit of the board header edit form, process the new header data.
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Deferred}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitHeader = function ( info, data, jqxhr ) {
		var $rendered,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().reject();
		}

		$rendered = $(
			flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_block_loop',
				{ blocks: data.flow[ 'edit-header' ].result }
			)
		).children();

		// Reinitialize the whole board with these nodes
		flowBoard.reinitializeContainer( $rendered );

		return $.Deferred().resolve();
	};

	/**
	 * Renders the editable lock/unlock text area with the given API response.
	 * Allows a user to lock or unlock an entire topic.
	 * @param {Object} info
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateLockTopic = function ( info, data ) {
		var result, revision, postId, revisionId,
			$target = info.$target,
			$old = $target,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		$( this ).closest( '.flow-menu' ).removeClass( 'focus' );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		// FIXME: API should take care of this for me.
		result = data.flow[ 'view-post' ].result.topic;
		postId = result.roots[0];
		revisionId = result.posts[postId];
		revision = result.revisions[revisionId];

		// Enable the editable summary
		$target = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_topic_titlebar_lock.partial', revision
		) ).children();

		// Ensure that on a cancel the form gets destroyed.
		flowBoard.emitWithReturn( 'addFormCancelCallback', $target.find( 'form' ), function () {
			// xxx: Can this use replaceWith()? If so, use it because it saves the browser
			// from having to reflow the document view twice (once with both elements on the
			// page and then again after its removed, which causes bugs like losing your
			// scroll offset on long pages).
			$target.before( $old ).remove();
		} );

		// Replace the old one
		$old.before( $target ).detach();

		flowBoard.emitWithReturn( 'makeContentInteractive', $target );

		// Focus on first form field
		$target.find( 'input, textarea' ).filter( ':visible:first' ).focus();

		return $.Deferred().resolve().promise();
	};

	/**
	 * After submit of the lock/unlock topic form, process the new summary data and re-render
	 * the title bar.
	 * @param {String} status
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.lockTopic = function ( info, data ) {
		var $replacement,
			$target = info.$target,
			$this = $( this ),
			$deferred = $.Deferred(),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this ),
			flowId = $this.closest( '.flow-topic' ).data( 'flow-id' );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $deferred.reject().promise();
		}

		// We couldn't make lock-topic to return topic data after a successful
		// post submission because lock-topic is used for no-js support as well.
		// If we make it return topic data, that means it has to return wikitext format
		// for edit form in no-js mode.  This is a performance problem for wikitext
		// conversion since topic data returns all children data as well.  So we need to
		// make lock-topic return a single post for topic then fire
		// another request to topic data in html format
		//
		// @todo the html could json encode the parameters including topics, the js
		// could then import that and continuously update it with new revisions from
		// api calls.  Rendering a topic would then just be pointing the template at
		// the right part of that data instead of requesting it.
		flowBoard.Api.apiCall( {
			action: 'flow',
			submodule: 'view-topic',
			workflow: flowId,
			// Flow topic title, in Topic:<topicId> format (2600 is topic namespace id)
			page: mw.Title.newFromText( flowId, 2600 ).getPrefixedDb()
			// @todo fixme
			// - mw.Title.newFromText can return null. If you're not going to check its return
			//   value, use 'new mw.Title' instead so that you get an exception for 'invalid title'
			//   instead of an exception for 'property of null'.
			// - The second parameter to mw.Title is 'defaultNamespace' not 'namespace'.
			//   E.g. mw.Title.newFromText( 'User:Example', 6 ) -> 'User:Example', not 'File:
			//   If you need to prefix/enforce a namespace, use the canonical prefix instead.
		} ).done( function( result ) {
			// Update view of the full topic
			$replacement = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_topiclist_loop.partial',
				result.flow['view-topic'].result.topic
			) ).children();

			$target.replaceWith( $replacement );
			flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );

			$deferred.resolve();
		} ).fail( function( code, result ) {
			/*
			 * At this point, the lock/unlock actually worked, but failed
			 * fetching the new data to be displayed.
			 */
			flowBoard.emitWithReturn( 'removeError', $target );
			var errorMsg = flowBoard.constructor.static.getApiErrorMessage( code, result );
			errorMsg = mw.msg( 'flow-error-fetch-after-open-lock', errorMsg );
			flowBoard.emitWithReturn( 'showError', $target, errorMsg );

			$deferred.reject();
		} );

		return $deferred.promise();
	};

	/**
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitTopicTitle = function( info, data, jqxhr ) {
		var
			topicData,
			rootId,
			revisionId,
			$this = $( this ),
			$topic = info.$target,
			$oldTopicTitleBar, $newTopicTitleBar,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		$oldTopicTitleBar = $topic.find( '.flow-topic-titlebar' );
		topicData = data.flow['edit-title'].result.topic;
		rootId = topicData.roots[0];
		revisionId = topicData.posts[rootId][0];
		$newTopicTitleBar = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_topic_titlebar.partial',
			topicData.revisions[revisionId]
		) ).children();

		$oldTopicTitleBar
			.replaceWith( $newTopicTitleBar );

		flowBoard.emitWithReturn( 'makeContentInteractive', $newTopicTitleBar );

		$newTopicTitleBar.conditionalScrollIntoView();

		return $.Deferred().resolve().promise();
	};

	/**
	 * After submit of the topic title edit form, process the response.
	 *
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitEditPost = function( info, data, jqxhr ) {
		var result;

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		result = data.flow['edit-post'].result.topic;
		// clear out submitted data, otherwise it would re-trigger an edit
		// form in the refreshed topic
		result.submitted = {};

		_flowBoardComponentRefreshTopic( info.$target, result );

		return $.Deferred().resolve().promise();
	};

	/**
	 * After submitting a new topic, process the response.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.newTopic = function ( info, data, jqxhr ) {
		var result, fragment,
			schemaName = $( this ).data( 'flow-eventlog-schema' ),
			funnelId = $( this ).data( 'flow-eventlog-funnel-id' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		flowBoard.logEvent( schemaName, { action: 'save-success', funnelId: funnelId } );

		result = data.flow['new-topic'].result.topiclist;

		// render only the new topic
		result.roots = [result.roots[0]];
		fragment = mw.flow.TemplateEngine.processTemplateGetFragment( 'flow_topiclist_loop.partial', result );

		flowBoard.emitWithReturn( 'cancelForm', $( this ).closest( 'form' ) );

		// Everything must be reset before re-initializing
		// @todo un-hardcode
		flowBoard.reinitializeContainer(
			flowBoard.$container.find( '.flow-topics' ).prepend( fragment )
		);

		// remove focus - title input field may still have focus
		// (submitted via enter key), which it needs to lose:
		// the form will only re-activate if re-focused
		document.activeElement.blur();

		return $.Deferred().resolve().promise();
	};

	/**
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitReply = function ( info, data, jqxhr ) {
		var $form = $( this ).closest( 'form' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form ),
			schemaName = $( this ).data( 'flow-eventlog-schema' ),
			funnelId = $( this ).data( 'flow-eventlog-funnel-id' );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		flowBoard.logEvent( schemaName, { action: 'save-success', funnelId: funnelId } );

		// Execute cancel callback to destroy form
		flowBoard.emitWithReturn( 'cancelForm', $form );

		// Target should be flow-topic
		_flowBoardComponentRefreshTopic( info.$target, data.flow.reply.result.topic );

		return $.Deferred().resolve().promise();
	};

	/**
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.watchItem = function ( info, data, jqxhr ) {
		var watchUrl, unwatchUrl,
			watchType, watchLinkTemplate, $newLink,
			$target = $( this ),
			$tooltipTarget = $target.closest( '.flow-watch-link' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $tooltipTarget ),
			isWatched = false,
			url = $( this ).prop( 'href' ),
			links = {};

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		if ( $tooltipTarget.is( '.flow-topic-watchlist' ) ) {
			watchType = 'topic';
			watchLinkTemplate = 'flow_topic_titlebar_watch';
		}

		if ( data.watch[0].watched !== undefined ) {
			unwatchUrl = url.replace( 'watch', 'unwatch' );
			watchUrl = url;
			isWatched = true;
		} else {
			watchUrl = url.replace( 'unwatch', 'watch' );
			unwatchUrl = url;
		}
		links['unwatch-' + watchType] = { url : unwatchUrl };
		links['watch-' + watchType] = { url : watchUrl };

		// Render new icon
		// This will hide any tooltips if present
		$newLink = $(
			flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				watchLinkTemplate,
				{
					isWatched: isWatched,
					links: links,
					watchable: true
				}
			)
		).children();
		$tooltipTarget.replaceWith( $newLink );

		if ( data.watch[0].watched !== undefined ) {
			// Successful watch: show tooltip
			flowBoard.emitWithReturn( 'showSubscribedTooltip', $newLink.find( '.wikiglyph' ), watchType );
		}

		return $.Deferred().resolve().promise();
	};

	/**
	 * Activate the editable summarize topic form with given api request
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateSummarizeTopic = function ( info, data, jqxhr ) {
		var $target = info.$target,
			$old = $target,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		// Create the new topic_summary_edit template
		$target = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_block_topicsummary_edit',
			data.flow[ 'view-topic-summary' ].result.topicsummary
		) ).children();

		// On cancel, put the old topicsummary back
		flowBoard.emitWithReturn( 'addFormCancelCallback', $target.find( 'form' ), function() {
			$target.before( $old ).remove();
		} );

		// Replace the old one
		$old.before( $target ).detach();

		flowBoard.emitWithReturn( 'makeContentInteractive', $target );

		// Focus on first form field
		$target.find( 'input, textarea' ).filter( ':visible:first' ).focus();

		return $.Deferred().resolve().promise();
	};

	/**
	 * After submit of the summarize topic edit form, process the new topic summary data.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.summarizeTopic = function ( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		_flowBoardComponentRefreshTopic(
			info.$target,
			data.flow['edit-topic-summary'].result.topic,
			'.flow-topic-titlebar'
		);

		return $.Deferred().resolve().promise();
	};

	/**
	 * Shows the form for editing a topic title, it's not already showing.
	 *
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateEditTitle = function ( info, data, jqxhr ) {
		var flowBoard, $form, cancelCallback,
			$link = $( this ),
			activeClass = 'flow-topic-title-activate-edit',
			rootBlock = data.flow['view-post'].result.topic,
			revision = rootBlock.revisions[rootBlock.posts[rootBlock.roots[0]]];

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		$form = info.$target.find( 'form' );

		if ( $form.length === 0 ) {
			// Add class to identify title is being edited (so we can hide the
			// current title in CSS)
			info.$target.addClass( activeClass );

			cancelCallback = function() {
				$form.remove();
				info.$target.removeClass( activeClass );
			};

			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $link );
			$form = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_edit_topic_title.partial',
				{
					'actions' : {
						'edit' : {
							'url' : $link.attr( 'href' )
						}
					},
					'content': {
						'content' : revision.content.content
					},
					'revisionId' : revision.revisionId
				}
			) ).children();

			flowBoard.emitWithReturn( 'addFormCancelCallback', $form, cancelCallback );
			$form
				.data( 'flow-initial-state', 'hidden' )
				.prependTo( info.$target );
		}

		$form.find( '.mw-ui-input' ).focus();

		return $.Deferred().resolve().promise();
	};

	/**
	 * Renders the editable post with the given API response.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateEditPost = function ( info, data, jqxhr ) {
		var $rendered, rootBlock,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) ),
			$post = info.$target;

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		// The API returns with the entire topic, but we only want to render the edit form
		// for a singular post
		rootBlock = data.flow['view-post'].result.topic;
		$rendered = $(
			flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_edit_post_ajax.partial',
				{
					revision: rootBlock.revisions[rootBlock.posts[rootBlock.roots[0]]],
					rootBlock: rootBlock
				}
			)
		).children();

		// Set the cancel callback on this form so that it returns to the post
		flowBoard.emitWithReturn( 'addFormCancelCallback',
			$rendered.find( 'form' ).addBack( 'form' ),
			function () {
				$rendered.replaceWith( $post );
			}
		);

		$post.replaceWith( $rendered );
		$rendered.find( 'textarea' ).conditionalScrollIntoView().focus();

		return $.Deferred().resolve().promise();
	};

	/**
	 * Callback from the topic moderation dialog.
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.moderateTopic = _genModerateHandler(
		'moderate-topic',
		function ( $target, revision, apiResult ) {
			var $replacement,
				flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );
			if ( revision.isModerated && !flowBoard.constructor.static.inTopicNamespace( $target ) ) {
				$replacement = $( $.parseHTML( mw.flow.TemplateEngine.processTemplate(
					'flow_moderate_topic_confirmation.partial',
					revision
				) ) );

				$target.closest( '.flow-topic' ).replaceWith( $replacement );
				flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );
			} else {
				_flowBoardComponentRefreshTopic( $target, apiResult );
			}
		}
	);

	/**
	 * Callback from the post moderation dialog.
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.moderatePost = _genModerateHandler(
		'moderate-post',
		function ( $target, revision, apiResult ) {
			var $replacement,
				flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

			if ( revision.isModerated ) {
				$replacement = $( $.parseHTML( flowBoard.constructor.static.TemplateEngine.processTemplate(
					'flow_moderate_post_confirmation.partial',
					revision
				) ) );
				$target.closest( '.flow-post-main' ).replaceWith( $replacement );
				flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );
			} else {
				_flowBoardComponentRefreshTopic( $target, apiResult );
			}
		}
	);

	//
	// Private functions
	//

	/**
	 * Generate a moderation handler callback
	 *
	 * @param {string} Action to expect in api response
	 * @param {Function} Method to call on api success
	 */
	function _genModerateHandler( action, successCallback ) {
		/**
		 * After submit of a moderation form, process the response.
		 *
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 * @returns {$.Promise}
		 */
		return function ( info, data, jqxhr ) {
			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return $.Deferred().reject().promise();
			}

			var result = data.flow[action].result.topic,
				$this = $( this ),
				$form = $this.closest( 'form' ),
				id = result.submitted.postId || result.postId || result.roots[0],
				flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this );

			successCallback.call(
				this,
				$form.data( 'flow-dialog-owner' ) || $form,
				result.revisions[result.posts[id]],
				result
			);

			flowBoard.emitWithReturn( 'cancelForm', $form );

			return $.Deferred().resolve().promise();
		};
	}

	/**
	 * Refreshes the titlebar of a topic given an API response.
	 * @param  {jQuery} $targetElement An element in the topic.
	 * @param  {Object} apiResult      Plain object containing the API response to build from.
	 * @param  {String} [selector]     Select specific element to replace
	 */
	function _flowBoardComponentRefreshTopic( $targetElement, apiResult, selector ) {
		var $target = $targetElement.closest( '.flow-topic' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $targetElement ),
			$newContent = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_topiclist_loop.partial',
				apiResult
			) ).children();

		if ( selector ) {
			$newContent = $newContent.find( selector );
			$target = $target.find( selector );
		}

		$target.replaceWith( $newContent );
		// Run loadHandlers
		flowBoard.emitWithReturn( 'makeContentInteractive', $newContent );
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentApiEventsMixin );
}( jQuery, mediaWiki ) );
