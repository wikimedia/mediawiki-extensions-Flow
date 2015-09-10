/*!
 * @todo break this down into mixins for each callback section (eg. post actions, read topics)
 */

( function ( $, mw ) {
	/**
	 * Binds API events to FlowBoardComponent
	 * @class
	 * @extends FlowComponent
	 * @constructor
	 * @param {jQuery} $container
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

	/** @class FlowBoardComponentApiEventsMixin.UI.events.globalApiPreHandlers */

	/**
	 * Textareas are turned into editor objects, so we can't rely on
	 * textareas to properly return the real content we're looking for (the
	 * real editor can be anything, depending on the type of editor)
	 *
	 * @param {Event} event
	 * @param {Object} info
	 * @param {Object} queryMap
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.globalApiPreHandlers.prepareEditor = function ( event, info, queryMap ) {
		var $textareas = $( this ).closest( 'form' ).find( 'textarea' ),
			override = {};

		$textareas.each( function () {
			var $editor = $( this );

			// Doublecheck that this textarea is actually an editor instance
			// (the editor may have added a textarea itself...)
			if ( mw.flow.editor && mw.flow.editor.exists( $editor ) ) {
				override[$editor.attr( 'name' )] = mw.flow.editor.getRawContent( $editor );
				override.flow_format = mw.flow.editor.getFormat( $editor );
			}

			// @todo: we have to make sure we get rid of all unwanted data
			// in the form (whatever "editor instance" may have added)
			// because we'll $form.serializeArray() to get the content.
			// This is currently not an issue since we only have "none"
			// editor type, which just uses the existing textarea. Someday,
			// however, we may have VE (or wikieditor or ...) which could
			// add its own nodes, which may be picked up by serializeArray()
		} );

		return $.extend( {}, queryMap, override );
	};

	/**
	 * When presented with an error conflict, the conflicting content can
	 * subsequently be re-submitted (to overwrite the conflicting content)
	 * This will prepare the data-to-be-submitted so that the override is
	 * submitted against the most current revision ID.
	 * @param {Event} event
	 * @param {Object} info
	 * @param {Object} queryMap
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.globalApiPreHandlers.prepareEditConflict = function ( event, info, queryMap ) {
		var $form = $( this ).closest( 'form' ),
			prevRevisionId = $form.data( 'flow-prev-revision' );

		if ( !prevRevisionId ) {
			return queryMap;
		}

		// Get rid of the temp-saved new revision ID
		$form.removeData( 'flow-prev-revision' );

		/*
		 * This is prev_revision in "generic" form. Each Flow API has its
		 * own unique prefix, which (in FlowApi.prototype.getQueryMap) will
		 * be properly applied for the respective API call; e.g.
		 * epprev_revision (for edit post)
		 */
		return $.extend( {}, queryMap, {
			flow_prev_revision: prevRevisionId
		} );
	};

	/** @class FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers */

	/**
	 * Before activating header, sends an overrideObject to the API to modify the request params.
	 * @param {Event} event
	 * @param {Object} info
	 * @param {Object} queryMap
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateEditHeader = function ( event, info, queryMap ) {
		return $.extend( {}, queryMap, {
			submodule: 'view-header', // href submodule is edit-header
			vhformat: mw.flow.editor.getFormat() // href does not have this param
		} );
	};

	/**
	 * Before activating topic, sends an overrideObject to the API to modify the request params.
	 *
	 * @param {Event} event
	 * @param {Object} info
	 * @param {Object} queryMap
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateEditTitle = function ( event, info, queryMap ) {
		// Use view-post API for topic as well; we only want this on
		// particular (title) post revision, not the full topic
		return $.extend( {}, queryMap, {
			submodule: 'view-post',
			vppostId: $( this ).closest( '.flow-topic' ).data( 'flow-id' ),
			vpformat: mw.flow.editor.getFormat()
		} );
	};

	/**
	 * Adjusts query params to use global watch action, and specifies it should use a watch token.
	 * @param {Event} event
	 * @param {Object} info
	 * @param {Object} queryMap
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.watchItem = function ( event, info, queryMap ) {
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

	/**
	 * Before activating summarize topic, sends an overrideObject to the
	 * API to modify the request params.
	 * @param {Event} event
	 * @param {Object} info
	 * @param {Object} queryMap
	 * @return {Object}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiPreHandlers.activateSummarizeTopic = function ( event, info, queryMap ) {
		if ( info.$target.find( 'form' ).length ) {
			// Form already open; cancel the old form
			var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );
			flowBoard.emitWithReturn( 'cancelForm', info.$target );
			return false;
		}

		return $.extend( {}, queryMap, {
			// href submodule is edit-topic-summary
			submodule: 'view-topic-summary',
			// href does not have this param
			vtsformat: mw.flow.editor.getFormat()
		} );
	};

	//
	// api callback handlers
	//

	/** @class FlowBoardComponentApiEventsMixin.UI.events.apiHandlers */

	/**
	 * On complete board reprocessing through view-topiclist (eg. change topic sort order), re-render any given blocks.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.board = function ( info, data, jqxhr ) {
		var $rendered,
			flowBoard = info.component,
			dfd = $.Deferred();

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return dfd.resolve().promise();
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
	 * Renders the editable board header with the given API response.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateEditHeader = function ( info, data, jqxhr ) {
		var $rendered,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) ),
			$oldBoardNodes;

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().resolve().promise();
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

		mw.flow.editor.focus( $rendered.find( 'textarea' ) );

		return $.Deferred().resolve().promise();
	};

	/**
	 * After submit of the board header edit form, process the new header data.
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitHeader = function ( info, data, jqxhr ) {
		var $rendered,
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		return flowBoard.Api.apiCall( {
			action: 'flow',
			submodule: 'view-header',
			page: mw.config.get( 'wgPageName' )
		} ).done( function ( result ) {
			// SUPERHACK: Add an indicator for handlebars to know to load the partial from
			// the "old" system. This will go away when the OOUI widget in JS is operational
			result.flow['view-header'].result.header.oldSystem = true;

			// Render
			$rendered = $(
				flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
					'flow_block_loop',
					{ blocks: result.flow['view-header'].result }
				)
			).children();

			// Reinitialize the whole board with these nodes
			flowBoard.reinitializeContainer( $rendered );
		} );
	};

	/**
	 * Lock/unlock a topic, update the UI and trigger summarize.
	 * @param {string} status
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.lockAndSummarizeTopic = function ( info, data ) {
		var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) ),
			workflowId = $( this ).closest( '.flow-topic' ).data( 'flow-id' );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		function summarize( result ) {
			var root = result.topic.roots[0],
				revId = result.topic.posts[root][0],
				topic = result.topic.revisions[revId];

			return flowBoard.Api.apiCall( {
				action: 'flow',
				submodule: 'view-topic-summary',
				// Flow topic title, in Topic:<topicId> format (2600 is topic namespace id)
				page: ( new mw.Title( workflowId, 2600 ) ).getPrefixedDb(),
				vtsformat: mw.flow.editor.getFormat()
			} ).then( function ( data ) {
				var topicSummary = data.flow['view-topic-summary'].result.topicsummary;

				// skip summarize step when a topic with NO summary has been reopened
				if ( !topic.isLocked && !_hasSummary( topicSummary ) ) {
					return $.Deferred().resolve().promise();
				}

				return _activateSummarizeTopic(
					result.$topic.find( '.flow-topic-titlebar .flow-topic-summary-container' ),
					flowBoard,
					topicSummary,
					topic.isLocked ? 'lock' : 'unlock'
				);
			} );
		}

		return _flowBoardComponentRefreshTopic( info.$target, workflowId ).then( summarize );
	};

	/**
	 * Checks if a topic summary object actually contains a summary.
	 * @param topicSummary
	 * @return {boolean}
	 * @private
	 */
	function _hasSummary( topicSummary ) {
		return !!( topicSummary &&
				topicSummary.revision &&
				topicSummary.revision.content &&
				topicSummary.revision.content.content );
	}

	/**
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitTopicTitle = function ( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Error will be displayed by default & edit conflict handled, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		return _flowBoardComponentRefreshTopic(
			info.$target,
			data.flow['edit-title'].workflow,
			'.flow-topic-titlebar'
		);
	};

	/**
	 * After submitting a new topic, process the response.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.newTopic = function ( info, data, jqxhr ) {
		var schemaName = $( this ).data( 'flow-eventlog-schema' ),
			funnelId = $( this ).data( 'flow-eventlog-funnel-id' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) ),
			$stub;

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		flowBoard.logEvent( schemaName, { action: 'save-success', funnelId: funnelId } );

		flowBoard.emitWithReturn( 'cancelForm', $( this ).closest( 'form' ) );

		// remove focus - title input field may still have focus
		// (submitted via enter key), which it needs to lose:
		// the form will only re-activate if re-focused
		document.activeElement.blur();

		// _flowBoardComponentRefreshTopic relies on finding a .flow-topic node
		// to replace, so let's pretend to have one here!
		$stub = $( '<div class="flow-topic"><div></div></div>' ).prependTo( flowBoard.$container.find( '.flow-topics' ) );
		return _flowBoardComponentRefreshTopic( $stub.find( 'div' ), data.flow['new-topic'].committed.topiclist['topic-id'] );
	};

	/**
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.submitReply = function ( info, data, jqxhr ) {
		var $form = $( this ).closest( 'form' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form ),
			schemaName = $( this ).data( 'flow-eventlog-schema' ),
			funnelId = $( this ).data( 'flow-eventlog-funnel-id' );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		flowBoard.logEvent( schemaName, { action: 'save-success', funnelId: funnelId } );

		// Execute cancel callback to destroy form
		flowBoard.emitWithReturn( 'cancelForm', $form );

		// Target should be flow-topic
		// @todo: add 3rd argument (target selector); there's no need to refresh entire topic
		return _flowBoardComponentRefreshTopic( info.$target, data.flow.reply.workflow );
	};

	/**
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
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
			return $.Deferred().resolve().promise();
		}

		if ( $tooltipTarget.is( '.flow-topic-watchlist' ) ) {
			watchType = 'topic';
			watchLinkTemplate = 'flow_topic_titlebar_watch.partial';
		}

		if ( data.watch[0].watched !== undefined ) {
			unwatchUrl = url.replace( 'watch', 'unwatch' );
			watchUrl = url;
			isWatched = true;
		} else {
			watchUrl = url.replace( 'unwatch', 'watch' );
			unwatchUrl = url;
		}
		links['unwatch-' + watchType] = { url: unwatchUrl };
		links['watch-' + watchType] = { url: watchUrl };

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
			flowBoard.emitWithReturn( 'showSubscribedTooltip', $newLink.find( '.mw-ui-icon' ), watchType );
		}

		return $.Deferred().resolve().promise();
	};

	/**
	 * Activate the editable summarize topic form with given api request
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateSummarizeTopic = function ( info, data, jqxhr ) {
		var flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $( this ) );

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		return _activateSummarizeTopic(
			info.$target,
			flowBoard,
			data.flow[ 'view-topic-summary' ].result.topicsummary,
			'summarize'
		);
	};

	/**
	 * After submit of the summarize topic edit form, process the new topic summary data.
	 * @param {Object} info
	 * @param {string} info.status "done" or "fail"
	 * @param {jQuery} info.$target
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.summarizeTopic = function ( info, data, jqxhr ) {
		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		return _flowBoardComponentRefreshTopic(
			info.$target,
			data.flow['edit-topic-summary'].workflow,
			'.flow-topic-titlebar'
		);
	};

	/**
	 * Shows the form for editing a topic title, it's not already showing.
	 *
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @return {jQuery.Promise}
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.activateEditTitle = function ( info, data, jqxhr ) {
		var flowBoard, $form, cancelCallback,
			$link = $( this ),
			activeClass = 'flow-topic-title-activate-edit',
			rootBlock = data.flow['view-post'].result.topic,
			revision = rootBlock.revisions[rootBlock.posts[rootBlock.roots[0]]];

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().resolve().promise();
		}

		$form = info.$target.find( 'form' );

		if ( $form.length === 0 ) {
			// Add class to identify title is being edited (so we can hide the
			// current title in CSS)
			info.$target.addClass( activeClass );

			cancelCallback = function () {
				$form.remove();
				info.$target.removeClass( activeClass );
			};

			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $link );
			$form = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_edit_topic_title.partial',
				{
					actions: {
						edit: {
							url: $link.attr( 'href' )
						}
					},
					content: {
						content: revision.content.content
					},
					revisionId: revision.revisionId
				}
			) ).children();

			flowBoard.emitWithReturn( 'addFormCancelCallback', $form, cancelCallback );
			$form.prependTo( info.$target );
		}

		$form.find( '.mw-ui-input' ).focus();

		return $.Deferred().resolve().promise();
	};

	/**
	 * Callback from the topic moderation dialog.
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.moderateTopic = _genModerateHandler(
		'moderate-topic',
		function ( flowBoard, revision ) {
			var $replacement, $target;

			if ( !revision.isModerated ) {
				return;
			}

			$target = flowBoard.$container.find( '#flow-topic-' + revision.postId );
			if ( flowBoard.constructor.static.inTopicNamespace( $target ) ) {
				return;
			}

			$replacement = $( $.parseHTML( mw.flow.TemplateEngine.processTemplate(
				'flow_moderate_topic_confirmation.partial',
				revision
			) ) );

			$target.replaceWith( $replacement );
			flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );
		}
	);

	/**
	 * Callback from the post moderation dialog.
	 */
	FlowBoardComponentApiEventsMixin.UI.events.apiHandlers.moderatePost = _genModerateHandler(
		'moderate-post',
		function ( flowBoard, revision ) {
			var $replacement, $target;

			if ( !revision.isModerated ) {
				return;
			}

			$replacement = $( $.parseHTML( flowBoard.constructor.static.TemplateEngine.processTemplate(
				'flow_moderate_post_confirmation.partial',
				revision
			) ) );

			$target = flowBoard.$container.find( '#flow-post-' + revision.postId + ' > .flow-post-main' );
			$target.replaceWith( $replacement );

			flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );
		}
	);

	//
	// Private functions
	//

	/** @class FlowBoardComponentApiEventsMixin */

	/**
	 * Generate a moderation handler callback
	 *
	 * @private
	 * @param {string} action Action to expect in api response
	 * @param {Function} successCallback Method to call on api success
	 * @return {Function} Callback processing the response after submit of a moderation form
	 * @return {Object} return.info `{status: done|fail, $target: jQuery}`
	 * @return {Object} return.data
	 * @return {jqXHR} return.jqxhr
	 * @return {jQuery.Promise} return.return
	 */
	function _genModerateHandler( action, successCallback ) {
		return function ( info, data, jqxhr ) {
			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return $.Deferred().resolve().promise();
			}

			var $this = $( this ),
				$form = $this.closest( 'form' ),
				revisionId = data.flow[action].committed.topic['post-revision-id'],
				$target = $form.data( 'flow-dialog-owner' ) || $form,
				flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this );

			// @todo: add 3rd argument (target selector); there's no need to refresh entire topic if only post was moderated
			return _flowBoardComponentRefreshTopic( $target, data.flow[action].workflow )
				.done( function ( result ) {
					successCallback( flowBoard, result.flow['view-topic'].result.topic.revisions[revisionId] );
				} )
				.done( function () {
					// we're done here, close moderation pop-up
					flowBoard.emitWithReturn( 'cancelForm', $form );
				} );
		};
	}

	/**
	 * Refreshes (part of) a topic.
	 *
	 * @private
	 * @param  {jQuery} $targetElement An element in the topic.
	 * @param  {string} workflowId     Plain object containing the API response to build from.
	 * @param  {string} [selector]     Select specific element to replace
	 * @return {jQuery.Promise}
	 */
	function _flowBoardComponentRefreshTopic( $targetElement, workflowId, selector ) {
		var $target = $targetElement.closest( '.flow-topic' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $targetElement );

		$targetElement.addClass( 'flow-api-inprogress' );
		return flowBoard.Api.apiCall( {
			action: 'flow',
			submodule: 'view-topic',
			// Flow topic title, in Topic:<topicId> format (2600 is topic namespace id)
			page: ( new mw.Title( workflowId, 2600 ) ).getPrefixedDb()
		} ).done( function ( result ) {
			// Update view of the full topic
			var $replacement = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				'flow_topiclist_loop.partial',
				result.flow['view-topic'].result.topic
			) ).children();

			if ( selector ) {
				$replacement = $replacement.find( selector );
				$target = $target.find( selector );
			}

			$target.replaceWith( $replacement );
			// Run loadHandlers
			flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );

			// make new topic and $element accessible to downstream handlers
			result.$topic = $replacement;
			result.topic = result.flow['view-topic'].result.topic;

			// HACK: Emit an event here so that the flow data model can update
			// itself based on the API response
			flowBoard.emit( 'refreshTopic', workflowId, result );
		} ).fail( function ( code, result ) {
			var errorMsg = flowBoard.constructor.static.getApiErrorMessage( code, result );
			errorMsg = mw.msg( 'flow-error-fetch-after-open-lock', errorMsg );

			flowBoard.emitWithReturn( 'removeError', $target );
			flowBoard.emitWithReturn( 'showError', $target, errorMsg );
		} ).always( function () {
			$targetElement.removeClass( 'flow-api-inprogress' );
		} );
	}

	// HACK expose this so flow-initialize.js can rerender topics when it needs to
	FlowBoardComponentApiEventsMixin.prototype.flowBoardComponentRefreshTopic = _flowBoardComponentRefreshTopic;

	function _activateSummarizeTopic( $target, flowBoard, topicSummary, action ) {
		var $old = $target;

		// Create the new flow_block_topicsummary_edit template
		$target = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_block_topicsummary_edit',
			$.extend( topicSummary, { action: action } )
		) ).children();

		// On cancel, put the old topicsummary back
		flowBoard.emitWithReturn( 'addFormCancelCallback', $target.find( 'form' ), function () {
			$target.before( $old ).remove();
		} );

		// Replace the old one
		$old.before( $target ).detach();

		flowBoard.emitWithReturn( 'makeContentInteractive', $target );

		// Focus on first form field
		$target.find( 'input, textarea' ).filter( ':visible:first' ).focus();

		return $.Deferred().resolve().promise();
	}

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'board', FlowBoardComponentApiEventsMixin );
}( jQuery, mediaWiki ) );
