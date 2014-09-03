/*
 * Contains the FlowBoardComponent and related functionality.
 */

( function ( $, mw ) {
	/**
	 * Constructor class for instantiating a new Flow board. Returns a FlowBoardComponent object.
	 * Accepts one or more container elements in $container. If multiple, returns an array of FlowBoardComponents.
	 * @example <div class="flow-component" data-flow-component="board" data-flow-id="rqx495tvz888x5ur">...</div>
	 * @param {jQuery} $container
	 * @return {FlowBoardComponent|bool}
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponent( $container ) {
		// Parent FlowComponent constructor
		var parentReturn = this.parent.apply( this, arguments ),
			uri = new mw.Uri( window.location.href );

		delete this.parent;
		if ( parentReturn && parentReturn.constructor ) {
			// If the parent returned an instantiated class (cached), return that
			return parentReturn;
		}

		// Default API submodule for FlowBoard URLs is to fetch a topiclist
		this.API.setDefaultSubmodule( 'view-topiclist' );

		// Set up the board
		if ( this.reinitializeBoard( $container ) === false ) {
			// Failed to init for some reason
			return false;
		}

		// Handle URL parameters
		if ( window.location.hash ) {
			if ( uri.query.fromnotif ) {
				flowHighlightPost( $container, window.location.hash, 'newer' );
			} else {
				flowHighlightPost( $container, window.location.hash );
			}
		}
	}

	// Register this FlowComponent
	mw.flow.registerComponent( 'board', FlowBoardComponent );

	/**
	 * Helper receives
	 * @param {jQuery}
	 * @param {string}
	 * @param {string}
	 * @return {jQuery}
	 */
	function flowHighlightPost( $container, targetSelector, option ) {
		var $target = $container.find( targetSelector ),
			uid = $target.data( 'flow-id' );

		// reset existing highlights
		$container.find( '.flow-post-highlighted' ).removeClass( 'flow-post-highlighted' );

		if ( option === 'newer' ) {
			$target.addClass( 'flow-post-highlight-newer' );
			if ( uid ) {
				$container.find( '.flow-post' ).each( function( idx, el ) {
					var $el = $( el ),
						id = $el.data( 'flow-id' );
					if ( id && id > uid ) {
						$el.addClass( 'flow-post-highlight-newer' );
					}
				} );
			}
		} else {
			$target.addClass( 'flow-post-highlighted' );
		}

		return $target;
	}

	/**
	 * Sets up the board and base properties on this class.
	 * Returns either FALSE for failure, or jQuery object of old nodes that were replaced.
	 * @return {Boolean|jQuery}
	 */
	FlowBoardComponent.prototype.reinitializeBoard = function ( $container ) {
		// Instantiate this FlowBoardComponent
		// First, find our elements at the top level...
		var $header = $container.filter( '.flow-board-header' ),
			$boardNavigation = $container.filter( '.flow-board-navigation' ),
			$topicNavigation = $container.filter( '.flow-topic-navigation' ),
			$board = $container.filter( '.flow-board' ),
			$retObj = $();
		// ...then check at the second level...
		$header = $header.length ? $header : $container.find( '.flow-board-header' );
		$boardNavigation = $boardNavigation.length ? $boardNavigation : $container.find( '.flow-board-navigation' );
		$topicNavigation = $topicNavigation.length ? $topicNavigation : $container.find( '.flow-topic-navigation' );
		$board = $board.length ? $board : $container.find( '.flow-board' );

		// ...and remove any old ones that are in use.
		if ( $header.length ) {
			if ( this.$header ) {
				$retObj = $retObj.add( this.$header.replaceWith( $header ) );
				this.$header.remove();
			}

			this.$header = $header;
		}
		if ( $boardNavigation.length ) {
			if ( this.$boardNavigation ) {
				$retObj = $retObj.add( this.$boardNavigation.replaceWith( $boardNavigation ) );
				this.$boardNavigation.remove();
			}

			this.$boardNavigation = $boardNavigation;
		}
		if ( $board.length ) {
			if ( this.$board ) {
				$retObj = $retObj.add( this.$board.replaceWith( $board ) );
				this.$board.remove();
			}

			this.$board = $board;
		}
		if ( $topicNavigation.length ) {
			if ( this.$topicNavigation ) {
				$retObj = $retObj.add( this.$topicNavigation.replaceWith( $topicNavigation ) );
				this.$topicNavigation.remove();
			}
		}

		this.$topicNavigation = $topicNavigation;

		// Second, verify that this board in fact exists
		if ( !this.$board || !this.$board.length ) {
			// You need a board, dammit!
			this.debug( 'Could not find .flow-board', arguments );
			return false;
		}

		// Progressively enhance the board and its forms
		// @todo Needs a ~"liveUpdateComponents" method, since the functionality in makeContentInteractive needs to also run when we receive new content or update old content.
		// @todo move form stuff
		FlowBoardComponent.UI.makeContentInteractive( this );

		// Bind any necessary event handlers to this board
		FlowBoardComponent.UI.bindBoardHandlers( this );

		// Bind the global event handlers (only happens once per page load, on window/body)
		FlowBoardComponent.UI.bindGlobalHandlers();

		// Initialize editors, turning them from textareas into editor objects
		FlowBoardComponent.UI.initializeEditors( $container );

		// Restore the last state
		this.HistoryEngine.restoreLastState();

		return $retObj;
	};

	/**
	 * UI stuff
	 */
	FlowBoardComponent.UI = {
		/** Event handler callbacks */
		events: {
			/** Callbacks for data-flow-interactive-handler */
			interactiveHandlers: {},
			/** Callbacks for data-flow-load-handler */
			loadHandlers: {},
			/** Handlers run for all api requests */
			globalApiPreHandlers: {},
			/** Validity pre-callback for data-flow-api-handler */
			apiPreHandlers: {},
			/** Callbacks for data-flow-api-handler */
			apiHandlers: {}
		}
	};

	( function () {
		// Store out of global and FlowBoardComponent scope
		var _isGlobalBound = false;


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI pre-api callback handlers, to do things before the API call
		////////////////////

		/**
		 * Before activating header, sends an overrideObject to the API to modify the request params.
		 * @param {Event} event
		 * @return {Object}
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.activateEditHeader = function () {
			return {
				submodule: "view-header", // href submodule is edit-header
				vhcontentFormat: "wikitext" // href does not have this param
			};
		};

		/**
		 * Before activating post, sends an overrideObject to the API to modify the request params.
		 * @param {Event} event
		 * @return {Object}
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.activateEditPost = function ( event ) {
			return {
				submodule: "view-post",
				vppostId: $( this ).closest( '.flow-post' ).data( 'flow-id' ),
				vpcontentFormat: "wikitext"
			};
		};

		/**
		 * Textareas are turned into editor objects, so we can't rely on
		 * textareas to properly return the real content we're looking for (the
		 * real editor can be anything, depending on the type of editor)
		 *
		 * @param {Event} event
		 * @return {Object}
		 */
		FlowBoardComponent.UI.events.globalApiPreHandlers.prepareEditor = function ( event ) {
			var $textareas = $( this ).closest( 'form' ).find( 'textarea' ),
				override = {};

			$textareas.each( function() {
				var $editor = $( this );

				// Doublecheck that this textarea is actually an editor instance
				// (the editor may have added a textarea itself...)
				if ( mw.flow.editor.exists( $editor ) ) {
					override[$editor.attr( 'name' )] = mw.flow.editor.getContent( $editor );
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
		FlowBoardComponent.UI.events.globalApiPreHandlers.prepareEditConflict = function ( event ) {
			var $form = $( this ).closest( 'form' ),
				prevRevisionId = $form.data( 'flow-prev-revision' );

			if ( !prevRevisionId ) {
				return {};
			}

			// Get rid of the temp-saved new revision ID
			$form.removeData( 'flow-prev-revision' );

			/*
			 * This is prev_revision in "generic" form. Each Flow API has its
			 * own unique prefix, which (in FlowAPI.prototype.getQueryMap) will
			 * be properly applied for the respective API call; e.g.
			 * epprev_revision (for edit post)
			 */
			return {
				flow_prev_revision: prevRevisionId
			};
		};

		/**
		 * Adjusts query params to use global watch action, and appends the watch token.
		 * @param {Event} event
		 * @returns {Function}
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.watchItem = function ( event ) {
			return function ( queryMap ) {
				var params = {
					action: 'watch',
					titles: queryMap.page,
					token: mw.user.tokens.get( 'watchToken' )
				};
				if ( queryMap.submodule === 'unwatch' ) {
					params.unwatch = 1;
				}
				return params;
			};
		};

		/**
		 * First, resets the previous preview (if any).
		 * Then, using the form fields, finds the content element to be sent to Parsoid by looking
		 * for one ending in "content", or, failing that, with data-role=content.
		 * @param  {Event} event The event being handled
		 * @return {Function} Callback to modify the API request
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.preview = function ( event ) {
			var $this = $( this ),
				callback;

			callback = function ( queryMap ) {
				var content = null;

				// XXX: Find the content parameter
				$.each( queryMap, function( key, value ) {
					var piece = key.substr( -7 );
					if ( piece === 'content' || piece === 'summary' ) {
						content = value;
						return false;
					}
				} );

				// If we fail to find a content param, look for a field that is the "content" role and use that
				if ( content === null ) {
					content = $this.closest( 'form' ).find( 'input, textarea' ).filter( '[data-role="content"]' ).val();
				}

				queryMap = {
					'action':  'flow-parsoid-utils',
					'from':    'wikitext',
					'to':      'html',
					'content': content,
					'title':   mw.config.get( 'wgPageName' )
				};

				return queryMap;
			};

			// Reset the preview state if already in it
			if ( flowBoardComponentResetPreview( $this ) ) {
				// Special way of cancelling a request, other than returning false outright
				callback._abort = true;
			}

			return callback;
		};

		/**
		 * Before activating summarize topic, sends an overrideObject to the
		 * API to modify the request params.
		 * @param {Event} event
		 * @param {Object} info
		 * @return {Object}
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.activateSummarizeTopic = function ( event, info ) {
			if ( info.$target.find( 'form' ).length ) {
				// Form already open; cancel the old form
				info.$target.find( 'form' ).find( 'button, input, a' ).filter( '[data-flow-interactive-handler="cancelForm"]' ).trigger( 'click' );
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
		 * Before activating close/reopen edit form, sends an overrideObject
		 * to the API to modify the request params.
		 * @param {Event} event
		 * @return {Object}
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.activateCloseOpenTopic = function ( event ) {
			return {
				// href submodule is close-open-topic
				submodule: 'view-post',
				// href does not have this param
				vpcontentFormat: 'wikitext',
				// request just the data for this topic
				vppostId: $( this ).data( 'flow-id' )
			};
		};

		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI api callback handlers
		////////////////////

		/**
		 * On complete board reprocessing through view-topiclist (eg. change topic sort order), re-render any given blocks.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.board = function ( info, data, jqxhr ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				$rendered;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			$rendered = $(
				flowBoard.TemplateEngine.processTemplateGetFragment(
					'flow_block_loop',
					{ blocks: data.flow[ 'view-topiclist' ].result }
				)
			).children();

			// Reinitialize the whole board with these nodes
			flowBoard.reinitializeBoard( $rendered );
		};

		/**
		 *
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.loadMore = function ( info, data, jqxhr ) {
			var $target = $( this ).closest( '.flow-load-more' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $target ),
				$tmp;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			// See bug 61097, Catch any random javascript error from
			// parsoid so they don't break and stop the page
			try {
				// Render topiclist template
				$target.before(
					$tmp = $( flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_topiclist_loop',
						data.flow[ 'view-topiclist' ].result.topiclist
					) ).children()
				);

				// Run loadHandlers
				FlowBoardComponent.UI.makeContentInteractive( $tmp );
			} catch( e ) {
				// nothing to do, just silently ignore the external error
			}

			// Render load more template
			if ( data.flow[ 'view-topiclist'].result.topiclist.links.pagination.fwd ) {
				$target.replaceWith(
					$tmp = $( flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_load_more',
						data.flow[ 'view-topiclist' ].result.topiclist
					) ).children()
				);
			} else {
				$target.replaceWith(
					$tmp = $( flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_no_more',
						{}
					) ).children()
				);
			}

			// Run loadHandlers
			FlowBoardComponent.UI.makeContentInteractive( $tmp );

			// Remove the old load button (necessary if the above load_more template returns nothing)
			$target.remove();

			/*
			 * Fire infinite scroll check again - if no (or few) topics were
			 * added (e.g. because they're moderated), we should immediately
			 * fetch more instead of waiting for the user to scroll again (when
			 * there's no reason to scroll)
			 */
			FlowBoardComponent.UI.infiniteScrollCheck();
		};

		/**
		 * Renders the editable board header with the given API response.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.activateEditHeader = function ( info, data, jqxhr ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				$oldBoardNodes,
				$rendered;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default & edit conflict handled, nothing else to wrap up
				return;
			}

			// Change "header" to "header_edit" so that it loads up flow_block_header_edit
			data.flow[ 'view-header' ].result.header.type = 'header_edit';

			$rendered = $(
				flowBoard.TemplateEngine.processTemplateGetFragment(
					'flow_block_loop',
					{ blocks: data.flow[ 'view-header' ].result }
				)
			).children();

			// Set the cancel callback on this form so that it returns the old content back if needed
			flowBoardComponentAddCancelCallback( $rendered.find( 'form' ), function () {
				flowBoard.reinitializeBoard( $oldBoardNodes );
			} );

			// Reinitialize the whole board with these nodes, and hold onto the replaced header
			$oldBoardNodes = flowBoard.reinitializeBoard( $rendered );
		};

		/**
		 * After submit of the board header edit form, process the new header data.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.submitHeader = function ( info, data, jqxhr ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				$rendered;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default & edit conflict handled, nothing else to wrap up
				return;
			}

			$rendered = $(
				flowBoard.TemplateEngine.processTemplateGetFragment(
					'flow_block_loop',
					{ blocks: data.flow[ 'edit-header' ].result }
				)
			).children();

			// Reinitialize the whole board with these nodes
			flowBoard.reinitializeBoard( $rendered );
		};

		/**
		 * Renders the editable close/open text area with the given API response.
		 * Allows a user to close or reopen an entire topic.
		 * @param {Object} info
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.activateCloseOpenTopic = function ( info, data ) {
			var result, revision, postId, revisionId,
				$target = info.$target,
				$old = $target,
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) );

			$( this ).closest( '.flow-menu' ).removeClass( 'focus' );

			if ( info.status !== 'done' ) {
				// Error will be displayed by default & edit conflict handled, nothing else to wrap up
				return;
			}

			// FIXME: API should take care of this for me.
			result = data.flow[ 'view-post' ].result.topic;
			postId = result.roots[0];
			revisionId = result.posts[postId];
			revision = result.revisions[revisionId];

			// Enable the editable summary
			$target = $( flowBoard.TemplateEngine.processTemplateGetFragment(
				'flow_topic_titlebar_close', revision
			) ).children();

			// Ensure that on a cancel the form gets destroyed.
			flowBoardComponentAddCancelCallback( $target.find( 'form' ), function () {
				$target.before( $old ).remove();
			} );

			// Replace the old one
			$old.before( $target ).detach();

			FlowBoardComponent.UI.makeContentInteractive( $target );

			// Focus on first form field
			$target.find( 'input, textarea' ).filter( ':visible:first' ).focus();
		};

		/**
		 * After submit of the close/open topic form, process the new summary data and re-render
		 * the title bar.
		 * @param {String} status
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.closeOpenTopic = function ( info, data ) {
			var revision,
				$target = info.$target, $topicTitleBar,
				topicId, revisionId,
				self = this,
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				flowId = $( self ).closest( '.flow-topic-titlebar' ).parent().data( 'flow-id' );

			if ( info.status !== 'done' ) {
				// Error will be displayed by default & edit conflict handled, nothing else to wrap up
				return;
			}

			// We couldn't make close-open-topic to return topic data after a successful
			// post submission because close-open-topic is used for no-js support as well.
			// If we make it return topic data, that means it has to return wikitext format
			// for edit form in no-js mode.  This is a performance problem for wikitext
			// conversion since topic data returns all children data as well.  So we need to
			// make close-open-topic return a single post for topic then fire
			// another request to topic data in html format
			//
			// @todo the html could json encode the parameters including topics, the js
			// could then import that and continuously update it with new revisions from
			// api calls.  Rendering a topic would then just be pointing the template at
			// the right part of that data instead of requesting it.
			flowBoard.API.apiCall( {
				action: 'flow',
				submodule: 'view-topic',
				workflow: flowId,
				// Flow topic title, in Topic:<topicId> format (2600 is topic namespace id)
				page: mw.Title.newFromText( flowId, 2600 ).getPrefixedDb()
			} ).done( function( result ) {
				// FIXME: Why doesn't the API return this?
				result = result.flow['view-topic'].result.topic;
				topicId = result.roots[0];
				revisionId = result.posts[topicId];
				revision = result.revisions[revisionId];

				// FIXME: Api should be returning moderation state. Why not?
				revision.isModerated = revision.moderateState === 'close';

				// FIXME: Hackily remove the moderated class (avoids re-rendering entire post)
				$target.parents( '.flow-topic' ).removeClass( 'flow-topic-moderated' );

				// Update view of the title bar
				$topicTitleBar = $(
					flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_topic_titlebar',
						revision
					)
				).children();
				$target.replaceWith( $topicTitleBar );
				FlowBoardComponent.UI.makeContentInteractive( $topicTitleBar );
			} ).fail( function( code, result ) {
				/*
				 * At this point, the open/close actually worked, but failed
				 * fetching the new data to be displayed.
				 */
				FlowBoardComponent.UI.removeError( $target );
				var errorMsg = apiErrorMessage( code, result );
				errorMsg = mw.msg( 'flow-error-fetch-after-open-close', errorMsg );
				FlowBoardComponent.UI.showError( $target, errorMsg );
			} );
		};

		/**
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.submitTopicTitle = function( info, data, jqxhr ) {
			var
				topicData,
				rootId,
				revisionId,
				$this = $( this ),
				$topic = info.$target,
				$oldTopicTitleBar, $newTopicTitleBar,
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $this );
			if ( info.status !== 'done' ) {
				// Error will be displayed by default & edit conflict handled, nothing else to wrap up
				return;
			}

			$oldTopicTitleBar = $topic.find( '.flow-topic-titlebar' );
			topicData = data.flow['edit-title'].result.topic;
			rootId = topicData.roots[0];
			revisionId = topicData.posts[rootId][0];
			$newTopicTitleBar = $( flowBoard.TemplateEngine.processTemplateGetFragment(
				'flow_topic_titlebar',
				topicData.revisions[revisionId]
			) ).children();

			$oldTopicTitleBar
				.replaceWith( $newTopicTitleBar );

			FlowBoardComponent.UI.makeContentInteractive( $newTopicTitleBar );

			$newTopicTitleBar.conditionalScrollIntoView();
		};

		/**
		 * After submit of the topic title edit form, process the response.
		 *
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.submitEditPost = function( info, data, jqxhr ) {
			var result;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default & edit conflict handled, nothing else to wrap up
				return;
			}

			result = data.flow['edit-post'].result.topic;
			// clear out submitted data, otherwise it would re-trigger an edit
			// form in the refreshed topic
			result.submitted = {};

			flowBoardComponentRefreshTopic( info.$target, result );
		};

		/**
		 * Triggers a preview of the given content.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.preview = function( info, data, jqxhr ) {
			var revision, creator,
				$button = $( this ),
				$form = $button.closest( 'form' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $form ),
				$titleField = $form.find( 'input' ).filter( '[data-role=title]' ),
				$previewContainer,
				templateParams,
				$target = info.$target,
				username = mw.user.getName(),
				id = Math.random(),
				previewTemplate = $target.data( 'flow-preview-template' ),
				contentNode = $target.data( 'flow-preview-node' ) || 'content';

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			creator = {
				links: {
					contribs: {
						url: mw.util.getUrl( 'Special:Contributions/' + username ),
						exists: true,
						title: username
					}
				},
				name: username || flowBoard.TemplateEngine.l10n( 'flow-anonymous' )
			};

			revision = {
				postId: id,
				creator: creator,
				replies: [ id ],
				isPreview: true
			};
			templateParams = {};

			// This is for most previews which expect a "revision" key
			revision[contentNode] = {
				content: data['flow-parsoid-utils'].content,
				format: data['flow-parsoid-utils'].format
			};
			// This fixes summarize which expects a key "summary"
			templateParams[contentNode] = revision[contentNode];

			$.extend( templateParams, {
				// This fixes titlebar which expects a key "content" for title
				content: {
					content: $titleField.val() || '',
					format: 'content'
				},
				creator: creator,
				posts: {},
				// @todo don't do these. it's a catch-all for the templates which expect a revision key, and those that don't.
				revision: revision,
				reply_count: 1,
				last_updated: +new Date(),
				replies: [ id ],
				revisions: {}
			} );
			templateParams.posts[id] = { 0: id };
			templateParams.revisions[id] = revision;

			// Render the preview warning
			$previewContainer = $( flowBoard.TemplateEngine.processTemplateGetFragment(
				'flow_preview_warning'
			) ).children();

			// @todo Perhaps this should be done in each template, and not here?
			$previewContainer.addClass( 'flow-preview' );

			// Render this template with the preview data
			$previewContainer = $previewContainer.add(
				$( flowBoard.TemplateEngine.processTemplateGetFragment(
					previewTemplate,
					templateParams
				) ).children()
			);

			// Hide any input fields
			$form.find( 'input, textarea' )
				.addClass( 'flow-preview-target-hidden' );

			// Insert the new preview before the form
			$target
				.parent( 'form' )
				.before( $previewContainer );

			// On cancel, make the preview get removed and reset the form back to its original state
			flowBoardComponentAddCancelCallback( $form, function () {
				flowBoardComponentResetPreview( $button );
			} );

			// Assign the reset-preview information for later use
			$button
				.data( 'flow-return-to-edit', {
					text: $button.text(),
					$nodes: $previewContainer
				} )
				.text( flowBoard.TemplateEngine.l10n('flow-preview-return-edit-post') );
		};

		/**
		 * After submitting a new topic, process the response.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.newTopic = function ( info, data, jqxhr ) {
			var result, html,
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) );

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			result = data.flow['new-topic'].result.topiclist;

			// render only the new topic
			result.roots = [result.roots[0]];
			html = mw.flow.TemplateEngine.processTemplateGetFragment( 'flow_topiclist_loop', result );

			// @todo un-hardcode
			flowBoard.reinitializeBoard(
				flowBoard.$container.find( '.flow-topics' ).prepend( $( html ) )
			);

			$( this ).closest( 'form' )[0].reset();

			// remove focus - title input field may still have focus
			// (submitted via enter key), which it needs to lose:
			// the form will only re-activate if re-focused
			document.activeElement.blur();
		};

		/**
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.submitReply = function ( info, data, jqxhr ) {
			var $form = $( this ).closest( 'form' );

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			// Clear contents to not trigger the "are you sure you want to
			// discard your text" warning
			$form.find( 'textarea, :text' ).each( function() {
				$( this ).val( this.defaultValue );
			} );

			// Trigger a click on cancel to have it destroy the form the way it should
			$form.find( 'button, input, a' ).filter( '[data-flow-interactive-handler="cancelForm"]' ).trigger( 'click' );

			// Target should be flow-topic
			flowBoardComponentRefreshTopic( info.$target, data.flow.reply.result.topic );
		};

		/**
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.watchItem = function ( info, data, jqxhr ) {
			var $target = $( this ),
				$tooltipTarget = $target.closest( '.flow-watch-link' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $tooltipTarget ),
				isWatched = false,
				url = $( this ).prop( 'href' ),
				watchUrl, unwatchUrl,
				watchType, watchLinkTemplate,
				links = {},
				$newLink;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			if ( $tooltipTarget.is( '.flow-topic-watchlist' ) ) {
				watchType = 'topic';
				watchLinkTemplate = 'flow_topic_titlebar_watch';
			} else if ( $tooltipTarget.is( '.flow-board-watch-link' ) ) {
				watchType = 'board';
				watchLinkTemplate = 'flow_board_watch_link';
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
					flowBoard.TemplateEngine.processTemplateGetFragment(
						watchLinkTemplate,
						{
							isWatched: isWatched,
							links: links,
							isBoardPage: watchType === 'board' ? true : false,
							watchable: true
						}
					)
				).children();
			$tooltipTarget.replaceWith( $newLink );

			if ( data.watch[0].watched !== undefined ) {
				// Successful watch: show tooltip
				FlowBoardComponent.UI.showSubscribedTooltip( $newLink.find( '.wikiglyph' ), watchType );
			}
		};

		/**
		 * Activate the editable summarize topic form with given api request
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.activateSummarizeTopic = function ( info, data, jqxhr ) {
			var $target = info.$target,
				$old = $target,
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) );

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			// Create the new topic_summary_edit template
			$target = $( flowBoard.TemplateEngine.processTemplateGetFragment(
				'flow_block_topicsummary_edit',
				data.flow[ 'view-topic-summary' ].result.topicsummary
			) ).children();

			// On cancel, put the old topicsummary back
			flowBoardComponentAddCancelCallback( $target.find( 'form' ), function() {
				$target.before( $old ).remove();
			} );

			// Replace the old one
			$old.before( $target ).detach();

			FlowBoardComponent.UI.makeContentInteractive( $target );

			// Focus on first form field
			$target.find( 'input, textarea' ).filter( ':visible:first' ).focus();
		};

		/**
		 * After submit of the summarize topic edit form, process the new topic summary data.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.summarizeTopic = function ( info, data, jqxhr ) {
			var $this = $( this ),
				$form = $this.closest( 'form' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $this ),
				$target = info.$target;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			$target.replaceWith( $(
				flowBoard.TemplateEngine.processTemplateGetFragment(
					// @todo this should be fixed so that it re-renders the entire flow_topic_titlebar
					'flow_topic_titlebar_summary',
					// @todo the response here doesnt match the standard serialization, typically we
					// get the topic title with summary embedded, but this revision is the actual
					// summary.  As such we have to rename content key to summary
					{ summary: data.flow[ 'edit-topic-summary' ].result.topicsummary.revision.content }
				)
			).children() );

			// Delete the form
			$form.remove();
		};

		/**
		 * Renders the editable post with the given API response.
		 * @param {Object} info (status:done|fail, $target: jQuery)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.activateEditPost = function ( info, data, jqxhr ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				$post = info.$target,
				$rendered,
				rootBlock;

			if ( info.status !== 'done' ) {
				// Error will be displayed by default, nothing else to wrap up
				return;
			}

			// The API returns with the entire topic, but we only want to render the edit form
			// for a singular post
			rootBlock = data.flow['view-post'].result.topic;
			$rendered = $(
				flowBoard.TemplateEngine.processTemplateGetFragment(
					'flow_edit_post_ajax',
					{
						revision: rootBlock.revisions[rootBlock.posts[rootBlock.roots[0]]],
						rootBlock: rootBlock
					}
				)
			).children();

			// Set the cancel callback on this form so that it returns to the post
			flowBoardComponentAddCancelCallback(
				$rendered.find( 'form' ).addBack( 'form' ),
				function () {
					$rendered.replaceWith( $post );
				}
			);

			$post.replaceWith( $rendered );
			$rendered.find( 'textarea' ).conditionalScrollIntoView().focus();
		};

		/**
		 * Generate a moderation handler callback
		 *
		 * @param {string} Action to expect in api response
		 * @param {Function} Method to call on api success
		 */
		function genModerateHandler( action, successCallback ) {
			/**
			 * After submit of a moderation form, process the response.
			 *
			 * @param {Object} info (status:done|fail, $target: jQuery)
			 * @param {Object} data
			 * @param {jqXHR} jqxhr
			 */
			return function ( info, data, jqxhr ) {
				if ( info.status !== 'done' ) {
					// Error will be displayed by default, nothing else to wrap up
					return;
				}

				var result = data.flow[action].result.topic,
					$form = $( this ).closest( 'form' ),
					id = result.postId ? result.postId : result.roots[0];

				successCallback(
					$form.data( 'flow-dialog-owner' ),
					result.revisions[result.posts[id]],
					result
				);

				// @todo cancel dialog
				$form.parent().remove();
			};
		}

		FlowBoardComponent.UI.events.apiHandlers.moderateTopic = genModerateHandler(
			'moderate-topic',
			function ( $target, revision ) {
				var html = mw.flow.TemplateEngine.processTemplate( 'flow_topic', revision ),
					$replacement = $( html ),
					$titlebar = $replacement.find( '.flow-topic-titlebar' );

				$target
					.closest( '.flow-topic' )
					.attr( 'class', $replacement.attr( 'class' ) );

				$target
					.closest( '.flow-topic-titlebar' )
					.replaceWith( $titlebar );

				FlowBoardComponent.UI.makeContentInteractive( $titlebar );
			}
		);

		FlowBoardComponent.UI.events.apiHandlers.moderatePost = genModerateHandler(
			'moderate-post',
			function ( $target, revision, apiResult ) {
				flowBoardComponentRefreshTopic( $target, apiResult );
			}
		);

		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI on-element-load handlers
		////////////////////

		/**
		 * Loads the last collapser states from sessionStorage and either expands or collapses based on
		 * what the user last did with a given element.
		 * @param {jQuery} $topic
		 */
		FlowBoardComponent.UI.events.loadHandlers.collapserState = function ( $topic ) {
			// Get last collapse state from sessionStorage
			var stateForTopic, classForTopic,
				states = mw.flow.StorageEngine.sessionStorage.getItem( 'collapserStates' ) || {},
				topicId = $topic.data('flow-id'),
				STORAGE_TO_CLASS = {
					// Conserve space in browser storage
					'+': 'flow-element-expanded',
					'-': 'flow-element-collapsed'
				};

			stateForTopic =	states[ topicId ];
			if ( stateForTopic ) {
				// This item has an visibility override previously, so reapply the class

				classForTopic = STORAGE_TO_CLASS[stateForTopic];

				if ( classForTopic === 'flow-element-expanded' ) {
					// Remove flow-element-collapsed first (can be set on server for moderated), so it
					// doesn't clash.
					$topic.removeClass( 'flow-element-collapsed' );
				}

				$topic.addClass( classForTopic );
			}
		};

		/**
		 * Replaces $time with a new time element generated by TemplateEngine
		 * @param {jQuery} $time
		 */
		FlowBoardComponent.UI.events.loadHandlers.timestamp = function ( $time ) {
			$time.replaceWith(
				FlowBoardComponent.prototype.TemplateEngine.callHelper(
					'timestamp',
					parseInt( $time.attr( 'datetime' ), 10) * 1000,
					$time.data( 'time-str' ),
					$time.data( 'time-ago-only' ) === "1"
				)
			);
		};

		/**
		 * Stores the load more button for use with infinite scroll.
		 * @param {jQuery} $button
		 */
		FlowBoardComponent.UI.events.loadHandlers.loadMore = function ( $button ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $button );

			if ( !flowBoard.$loadMoreNodes ) {
				// Create a new $loadMoreNodes list
				flowBoard.$loadMoreNodes = $();
			} else {
				// Remove any loadMore nodes that are no longer in the body
				flowBoard.$loadMoreNodes = flowBoard.$loadMoreNodes.filter( function () {
					return $( this ).closest( 'body' ).length;
				} );
			}

			// Store this new loadMore node
			flowBoard.$loadMoreNodes = flowBoard.$loadMoreNodes.add( $button );
		};

		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI event interactive handlers
		////////////////////

		/**
		 * The activateForm handler will expand, scroll to, and then focus onto a form (target = field).
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.activateForm = function ( event ) {
			var $el, $form,
				href = $( this ).prop( 'href' ),
				hash = href.match( /#.+$/ ),
				$target = hash ? $( hash ) : false;

			// If this element is leading to another element on the page, find it.
			if ( !$target || !$target.length ) {
				return;
			}

			$el = $( hash[0] );
			$form = $el.closest( 'form' );

			if ( !$el.length || !$form.length ) {
				return;
			}

			// Is this a hidden form or invisible field? Make it visible.
			FlowBoardComponent.UI.Forms.showForm( $form );

			if ( ! $form.is( ':visible' ) ) {
				FlowBoardComponent.UI.expandTopicIfNecessary( $form.closest( '.flow-topic' ) );
			}

			// Is this a form field? Scroll to the form instead of jumping.
			$form.conditionalScrollIntoView().queue( function ( next ) {
				var $el = $( hash[0] );

				// After scroll, focus onto the form field itself
				if ( $el.is( 'textarea, :text' ) ) {
					$el.focus();
				}

				// jQuery.dequeue
				next();
			});

			// OK, we're done here. Don't use the hard link.
			event.preventDefault();
		};

		/**
		 * Cancels and closes a form. If text has been entered, issues a warning first.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.cancelForm = function ( event ) {
			var $form = $( this ).closest( 'form' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $form ),
				$fields = $form.find( 'textarea, :text' ),
				changedFieldCount = 0;

			event.preventDefault();

			// Check for non-empty fields of text
			$fields.each( function () {
				if ( $( this ).val() !== this.defaultValue ) {
					changedFieldCount++;
					return false;
				}
			} );
			// If all the text fields are empty, OR if the user confirms to close this with text already entered, do it.
			if ( !changedFieldCount || confirm( flowBoard.TemplateEngine.l10n( 'flow-cancel-warning' ) ) ) {
				// Reset the form content
				$form[0].reset();

				// Trigger for flow-actions-disabler
				$form.find( 'textarea, :text' ).trigger( 'keyup' );

				// Hide the form
				FlowBoardComponent.UI.Forms.hideForm( $form );

				// Get rid of existing error messages
				FlowBoardComponent.UI.removeError( $form );

				// Trigger the cancel callback
				if ( $form.data( 'flow-cancel-callback' ) ) {
					$.each($form.data( 'flow-cancel-callback' ), function ( idx, fn ) {
						fn();
					});
				}
			}
		};

		/**
		 * Calls FlowBoardComponent.UI.collapserState to set and render the new Collapser state.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.collapserGroupToggle = function ( event ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) );

			FlowBoardComponent.UI.collapserState( flowBoard, this.href.match( /[a-z]+$/ )[0] );

			event.preventDefault();
		};

		/**
		 * Sets the visibility class based on the user toggle action.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.collapserCollapsibleToggle = function ( event ) {
			var $target = $( event.target ),
				topicId, states,
				$component = $( this ).closest( '.flow-component' );

			// Make sure we didn't click on any interactive elements
			if ( $target.not( '.flow-menu-js-drop' ) && !$target.closest( 'a, button, input, textarea, select, ul, ol' ).length ) {
				$target = $( this ).closest( '.flow-post-main, .flow-topic' ); // @todo genericize this
				if ( $component.is( '.flow-board-collapsed-compact, .flow-board-collapsed-topics' ) ) {
					// Board default is collapsed; topic can be overridden to
					// expanded, or not.

					// We also remove flow-element-collapsed.  That is set on the
					// server for moderated posts, but an explicit user action
					// overrides that.
					if ( $target.is( '.flow-element-expanded' ) ) {
						$target.addClass( 'flow-element-collapsed' ).removeClass( 'flow-element-expanded' );
					} else {
						$target.removeClass( 'flow-element-collapsed' ).addClass( 'flow-element-expanded' );
					}
				} else {
					// .flow-board-collapsed-full; Board default is expanded;
					// topic can be overridden to collapsed, or not.
					if ( $target.is( '.flow-element-collapsed' ) ) {
						$target.removeClass( 'flow-element-collapsed' ).addClass( 'flow-element-expanded' );
					} else {
						$target.addClass( 'flow-element-collapsed' ).removeClass( 'flow-element-expanded' );
					}
				}

				topicId = $target.data('flow-id');

				// Save in sessionStorage
				states = mw.flow.StorageEngine.sessionStorage.getItem( 'collapserStates' ) || {};
				// Opposite of STORAGE_TO_CLASS
				if ( $target.hasClass( 'flow-element-expanded' ) ) {
					states[ topicId ] = '+';
				} else if ( $target.hasClass( 'flow-element-collapsed' ) ) {
					states[ topicId ] = '-';
				} else {
					delete states[ topicId ];
				}
				mw.flow.StorageEngine.sessionStorage.setItem( 'collapserStates', states );

				event.preventDefault();
				this.blur();
			}
		};

		/**
		 * Shows the form for editing a topic title, it's not already showing
		 *
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.editTopicTitle = function( event ) {
			var $link = $( this ),
				$topic = $link.closest( '.flow-topic' ),
				$topicTitleBar = $topic
					.children( '.flow-topic-titlebar' ),
				$title, flowBoard, $form, cancelCallback, linkParams;

			$form = $topicTitleBar.find( 'form' );

			if ( $form.length === 0 ) {
				$title = $topicTitleBar.find( '.flow-topic-title' );

				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $link );

				cancelCallback = function() {
					$form.remove();
					$title.show();
				};

				linkParams = flowBoard.API.getQueryMap( $link.attr( 'href' ) );

				$title.hide();

				$form = $( flowBoard.TemplateEngine.processTemplateGetFragment(
					'flow_edit_topic_title',
					{
						'actions' : {
							'edit' : {
								'url' : $link.attr( 'href' )
							}
						},
						'content': {
							'content' : $title.data( 'title' )
						},
						'revisionId' : linkParams.etrevId
					}
				) ).children();


				flowBoardComponentAddCancelCallback( $form, cancelCallback );
				$form
					.data( 'flow-initial-state', 'hidden' )
					.insertAfter( $title );
			}

			$form.find( '.mw-ui-input' ).focus();

			event.preventDefault();
		};

		/**
		 * Utility to get error message for API result.
		 *
		 * @param string code
		 * @param Object result
		 * @returns string
		 */
		function apiErrorMessage( code, result ) {
			if ( result.error && result.error.info ) {
				return result.error.info;
			} else {
				if ( code === 'http' ) {
					// XXX: some network errors have English info in result.exception and result.textStatus.
					return mw.msg( 'flow-error-http' );
				} else {
					return  mw.msg( 'flow-error-external', code );
				}
			}
		}

		/**
		 * Triggers an API request based on URL and form data, and triggers the callbacks based on flow-api-handler.
		 * @example <a data-flow-interactive-handler="apiRequest" data-flow-api-handler="loadMore" data-flow-api-target="< .flow-component div" href="...">...</a>
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.apiRequest = function ( event ) {
			var $deferred,
				_this = this,
				$this = $( this ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $this ),
				dataParams = $this.data(),
				handlerName = dataParams.flowApiHandler,
				$target,
				preHandlerReturn,
				preHandlerReturns = [],
				info = {
					$target: null,
					status: null
				},
				args = Array.prototype.slice.call( arguments, 0 );

			event.preventDefault();

			// Find the target node
			if ( dataParams.flowApiTarget ) {
				// This fn supports finding parents
				$target = $this.findWithParent( dataParams.flowApiTarget );
			}
			if ( !$target || !$target.length ) {
				// Assign a target node if none
				$target = $this;
			}
			info.$target = $target;
			args.splice( 1, 0, info ); // insert info into args for prehandler

			// Make sure an API call is not already in progress for this target
			if ( $target.closest( '.flow-api-inprogress' ).length ) {
				flowBoard.debug( 'apiRequest already in progress', arguments );
				return;
			}

			// Mark the target node as "in progress" to disallow any further API calls until it finishes
			$target.addClass( 'flow-api-inprogress' );
			$this.addClass( 'flow-api-inprogress' );

			// Let generic pre-handler take care of edit conflicts
			// @todo use oojs or mf events system?
			$.each( FlowBoardComponent.UI.events.globalApiPreHandlers, function( key ) {
				preHandlerReturns.push( FlowBoardComponent.UI.events.globalApiPreHandlers[key].apply( _this, arguments ) );
			} );

			// Use the pre-callback to find out if we should process this
			if ( FlowBoardComponent.UI.events.apiPreHandlers[ handlerName ] ) {
				// apiPreHandlers can return FALSE to prevent processing,
				// nothing at all to proceed,
				// or OBJECT to add param overrides to the API
				// or FUNCTION to modify API params
				preHandlerReturn = FlowBoardComponent.UI.events.apiPreHandlers[ handlerName ].apply( _this, args );
				preHandlerReturns.push( preHandlerReturn );

				if ( preHandlerReturn === false || preHandlerReturn._abort === true ) {
					// Callback returned false
					flowBoard.debug( 'apiPreHandler returned false', handlerName, args );

					// Abort any old request in flight; this is normally done automatically by requestFromNode
					flowBoard.API.abortOldRequestFromNode( this, null, null, preHandlerReturns );

					// @todo support for multiple indicators on same target
					$target.removeClass( 'flow-api-inprogress' );
					$this.removeClass( 'flow-api-inprogress' );

					return;
				}
			}

			// Make the request
			$deferred = flowBoard.API.requestFromNode( this, preHandlerReturns );
			if ( !$deferred ) {
				mw.flow.debug( '[FlowAPI] [interactiveHandlers] apiRequest element is not anchor or form element' );
				$deferred = $.Deferred();
				$deferred.rejectWith( { error: { info: 'Not an anchor or form' } } );
			}

			// Remove the load indicator
			$deferred.always( function () {
				// @todo support for multiple indicators on same target
				$target.removeClass( 'flow-api-inprogress' );
				$this.removeClass( 'flow-api-inprogress' );
			} );

			// Remove existing errors from previous attempts
			FlowBoardComponent.UI.removeError( $this );

			// If this has a special api handler, bind it to the callback.
			if ( FlowBoardComponent.UI.events.apiHandlers[ handlerName ] ) {
				$deferred
					.done( function () {
						var args = Array.prototype.slice.call( arguments, 0 );
						info.status = 'done';
						args.unshift( info );
						FlowBoardComponent.UI.events.apiHandlers[ handlerName ].apply( _this, args );
					} )
					.fail( function ( code, result ) {
						var args = Array.prototype.slice.call( arguments, 0 ),
							$form = $this.closest( 'form' ),
							errorMsg;
						info.status = 'fail';
						args.unshift( info );

						/*
						 * In the event of edit conflicts, store the previous
						 * revision id so we can re-submit an edit against the
						 * current id later.
						 */
						if ( result.error && result.error.prev_revision ) {
							$form.data( 'flow-prev-revision', result.error.prev_revision.revision_id );
						}

						/*
						 * Generic error handling: displays error message in the
						 * nearest error container.
						 *
						 * Errors returned by MW/Flow should always be in the
						 * same format. If the request failed without a specific
						 * error message, just fall back to some default error.
						 */
						errorMsg = apiErrorMessage( code, result );
						FlowBoardComponent.UI.showError( info.$target, errorMsg );

						FlowBoardComponent.UI.events.apiHandlers[ handlerName ].apply( _this, args );
					} );
			}
		};

		/**
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.activateReplyPost = function ( event ) {
			event.preventDefault();

			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				$post = $( this ).closest( '.flow-post' ),
				$targetPost = $( this ).closest( '.flow-post:not([data-flow-post-max-depth])' ),
				postId = $targetPost.data( 'flow-id' ),
				topicTitle = $post.closest( '.flow-topic' ).find( '.flow-topic-title' ).text(),
				replyToContent = $post.find( '.flow-post-content' ).text() || topicTitle,
				author = $.trim( $post.find( '.flow-author:first .mw-userlink' ).text() ),
				initialContent, $form;

			// Check if reply form has already been opened
			if ( $post.data( 'flow-replying' ) ) {
				return;
			}
			$post.data( 'flow-replying', true );

			// if we have a real username, turn it into "[[User]]" (otherwise, just "127.0.0.1")
			if ( !mw.util.isIPv4Address( author , true ) && !mw.util.isIPv6Address( author , true ) ) {
				initialContent = '[[' + mw.Title.newFromText( author, 2 ).getPrefixedText() + '|' + author + ']]: ';
			} else {
				initialContent = author + ': ';
			}

			$form = $( flowBoard.TemplateEngine.processTemplateGetFragment(
				'flow_reply_form',
				// arguments can be empty: we just want an empty reply form
				{
					actions: {
						reply: {
							url: $( this ).attr( 'href' ),
							title: mw.msg( 'flow-reply-link', author )
						}
					},
					postId: postId,
					author: {
						name: author
					},
					// text for flow-reply-topic-title-placeholder placeholder
					content: {
						content: replyToContent,
						format: 'plaintext'
					},
					submitted: {
						postId: postId,
						// prefill content
						content: initialContent
					}
				}
			) ).children();

			// Set the cancel callback on this form so that it gets rid of the form.
			// We have to make sure the data attribute is added to the form; the
			// addBack is failsafe for when form is actually the root node in $form
			// already (there may or may not be parent containers)
			flowBoardComponentAddCancelCallback( $form.find( 'form' ).addBack( 'form' ), function () {
				$post.removeData( 'flow-replying' );
				$form.remove();
			} );

			// Add reply form below the post being replied to (WRT max depth)
			$targetPost.children( '.flow-replies' ).append( $form );
			$form.conditionalScrollIntoView();
		};

		/**
		 *
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.moderationDialog = function ( event ) {
			var html, $container, $form,
				$this = $( this ),
				board = FlowBoardComponent.prototype.getInstanceByElement( $this ),
				// hide, delete, suppress
				role = $this.data( 'role' ),
				type = $this.closest( '.flow-post-main' ).length ? 'post' : 'topic',
				template = $this.data( 'template' ),
				params = {
					editToken: mw.user.tokens.get( 'editToken' ), // might be unnecessary
					submitted: {
						moderationState: role
					},
					actions: {}
				},
				titleText = mw.msg( 'flow-moderation-title-' + role + '-' + type );

			event.preventDefault();

			params.actions[role] = { url: $this.attr( 'href' ), title: $this.attr( 'title' ) };
			html = mw.flow.TemplateEngine.processTemplate( template, params );

			$container = $( '<div>' ).html( html );
			$form = $container.find( 'form' ).data( 'flow-dialog-owner', $this );
			flowBoardComponentAddCancelCallback( $form, function () {
				$container.parent().remove();
			} );

			// @todo Migrate to a simpler non-jquery.ui dialog box
			// this one doesn't work on mobile, among other problems.
			mw.loader.using( 'jquery.ui.dialog' , function() {
				$container.dialog( {
					'title': titleText,
					'modal': true
				} )
				// the $.fn.dialog function attaches the dialog to .body, but we
				// need to move it inside the main container so user interactions
				// go to the correct handlers.
				.parent()
					.detach()
					.appendTo( board.$container );
			} );
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI events
		////////////////////

		/**
		 * On click of a, button, or input, we check to see if this is a link that has a special handler,
		 * defined through a data-flow-interactive-handler="name" attribute.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onClickInteractive = function ( event ) {
			// Only trigger with enter key
			if ( event.type === 'keypress' && ( event.charCode !== 13 || event.metaKey || event.shiftKey || event.ctrlKey || event.altKey )) {
				return;
			}

			var interactiveHandler = $( this ).data( 'flow-interactive-handler' ),
				apiHandler = $( this ).data( 'flow-api-handler' );

			// If this has a special click handler, run it.
			if ( FlowBoardComponent.UI.events.interactiveHandlers[ interactiveHandler ] ) {
				FlowBoardComponent.UI.events.interactiveHandlers[ interactiveHandler ].apply( this, arguments );
			} else if ( FlowBoardComponent.UI.events.apiHandlers[ apiHandler ] ) {
				FlowBoardComponent.UI.events.interactiveHandlers.apiRequest.apply( this, arguments );
			}
		};

		/**
		 * If input is focused, expand it if compressed (into textarea).
		 * Otherwise, trigger the form to unhide.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onFocusField = function ( event ) {
			var $this = $( this );

			// Expand this input
			FlowBoardComponent.UI.Forms.expandInput( $this, event.target );

			// Show the form (and swap it for textarea if needed)
			FlowBoardComponent.UI.Forms.showForm( $this.closest( 'form' ) );
		};

		/**
		 * Allows you to open a flow-menu from a secondary click handler elsewhere.
		 * Uses data-flow-menu-target="< foo .flow-menu"
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.menuToggle = function ( event ) {
			var $this = $( this ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $this ),
				target = $this.data( 'flowMenuTarget' ),
				$target = $.findWithParent( $this, target );

			event.preventDefault();

			if ( !$target || !$target.length ) {
				flowBoard.debug( 'Could not find openFlowMenu target', arguments );
				return;
			}

			$target.find( '.flow-menu-js-drop' ).trigger( 'click' );
		};

		/**
		 * On click, focus, and blur of hover menu events, decides whether or not to hide or show the expanded menu
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onToggleHoverMenu = function ( event ) {
			var $this = $( event.target ),
				$menu = $this.closest( '.flow-menu' );

			if ( event.type === 'click' ) {
				// If the caret was clicked, toggle focus
				if ( $this.closest( '.flow-menu-js-drop' ).length ) {
					$menu.toggleClass( 'focus' );

					// This trick lets us wait for a blur event from A instead on body, to later hide the menu on outside click
					if ( $menu.hasClass( 'focus' ) ) {
						$menu.find( '.flow-menu-js-drop' ).find( 'a' ).focus();
					}
				}
			} else if ( event.type === 'focusin' ) {
				// If we are focused on a menu item (eg. tabbed in), open the whole menu
				$menu.addClass( 'focus' );
			} else if ( event.type === 'focusout' && !$menu.find( 'a' ).filter( ':focus' ).length ) {
				// If we lost focus, make sure no other element in this menu has focus, and then hide the menu
				$menu.removeClass( 'focus' );
			}
		};

		/**
		 * Dispatches to other window.scroll events.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onWindowScroll = function ( event ) {
			// The topic navigation sidebar
			FlowBoardComponent.UI.adjustTopicNavigationSidebar();

			// Infinite scroll handler
			FlowBoardComponent.UI.infiniteScrollCheck();
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI form methods
		////////////////////
		FlowBoardComponent.UI.Forms = {};

		/**
		 * If this input has a textarea stored on it, swap the elements in DOM.
		 * @param {jQuery} $input
		 * @param {Element} [target]
		 */
		FlowBoardComponent.UI.Forms.expandInput = function ( $input, target ) {
			var textarea = $.data( $input[0], 'flow-compressed' ),
				focused = $input.is( ':focus' );

			if ( textarea ) {
				// Swap the nodes
				$input.replaceWith( textarea );

				// Store this data again; jQuery has a habit of losing it after replaceWith
				$.data( textarea, 'flow-expanded', $input[0] );
				$.data( $input[0], 'flow-compressed', textarea );

				// target is a bug fix because the inputs are not being focused on click
				// @todo find out why this is happening ^
				if ( focused || $input[0] === target ) {
					// Swap focus!
					$( textarea ).focus()
						.closest( 'form' ).conditionalScrollIntoView();
				}
			}
		};

		/**
		 * If this textarea has an input stored on it, swap the elements in DOM.
		 * @param {jQuery} $textarea
		 */
		FlowBoardComponent.UI.Forms.compressTextarea = function ( $textarea ) {
			var input = $.data( $textarea[0], 'flow-expanded' );

			if ( input ) {
				// Swap the nodes
				$textarea.replaceWith( input );

				// Store this data again; jQuery has a habit of losing it after replaceWith
				$.data( $textarea[0], 'flow-expanded', input );
				$.data( input, 'flow-compressed', $textarea[0] );
			}
		};

		/**
		 * Compress and hide a flow form and/or its actions, depending on data-flow-initial-state.
		 * @param {jQuery} $form
		 */
		FlowBoardComponent.UI.Forms.hideForm = function ( $form ) {
			var initialState = $form.data( 'flow-initial-state' );

			// Store state
			$form.data( 'flow-state', 'hidden' );

			// If any preview is visible cancel it
			// Must be done before compressing text areas because
			// the preview may have manipulated them.
			if ( $form.parent().find( '.flow-preview-warning' ).length ) {
				flowBoardComponentResetPreview(
					$form.find( 'button[data-role="cancel"]' )
				);
			}

			$form.find( 'textarea' ).each( function () {
				var $editor = $( this );

				// Kill editor instances
				if ( mw.flow.editor.exists( $editor ) ) {
					mw.flow.editor.destroy( $editor );
				}

				// Compress all textareas to inputs if needed
				FlowBoardComponent.UI.Forms.compressTextarea( $editor );
			} );

			if ( initialState === 'collapsed' ) {
				// Hide its actions
				// @todo Use TemplateEngine to find and hide actions?
				$form.find( '.flow-form-collapsible' ).hide();
				$form.data( 'flow-form-collapse-state', 'collapsed' );
			} else if ( initialState === 'hidden' ) {
				// Hide the form itself
				$form.hide();
			}
		};

		/**
		 * Expand and make visible a flow form and/or its actions, depending on data-flow-initial-state.
		 * @param {jQuery} $form
		 */
		FlowBoardComponent.UI.Forms.showForm = function ( $form ) {
			var initialState = $form.data( 'flow-initial-state' );

			if ( initialState === 'collapsed' ) {
				// Show its actions
				if ( $form.data( 'flow-form-collapse-state' ) === 'collapsed' ) {
					$form.removeData( 'flow-form-collapse-state' );
					$form.find( '.flow-form-collapsible' ).show();
				}
			} else if ( initialState === 'hidden' ) {
				// Show the form itself
				$form.show();
			}

			// Expand all inputs to textareas if needed
			$form.find( 'input' ).each( function () {
				FlowBoardComponent.UI.Forms.expandInput( $( this ) );
			} );

			// Initialize editors, turning them from textareas into editor objects
			FlowBoardComponent.UI.initializeEditors( $form );

			// Store state
			$form.data( 'flow-state', 'visible' );
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI methods
		////////////////////

		/**
		 * @param {FlowBoardComponent|jQuery} $node or entire FlowBoard
		 * @param string msg The error that occurred. Currently hardcoded.
		 */
		FlowBoardComponent.UI.showError = function ( $node, msg ) {
			var html = mw.flow.TemplateEngine.processTemplate( 'flow_errors', { errors: [ { message: msg } ] } );

			if ( !$node.jquery ) {
				$node = $node.$container;
			}

			flowFindUpward( $node, '.flow-content-preview' ).hide();
			flowFindUpward( $node, '.flow-error-container' ).filter( ':first' ).replaceWith( html );

			mw.log.warn( msg );
		};

		/**
		 * @param {FlowBoardComponent|jQuery} $node or entire FlowBoard
		 */
		FlowBoardComponent.UI.removeError = function ( $node ) {
			flowFindUpward( $node, '.flow-error-container' ).filter( ':first' ).empty();
		};

		/**
		 * Given node & a selector, this will return the result closest to $node
		 * by first looking inside $node, then travelling up the DOM tree to
		 * locate the first result in a common ancestor.
		 *
		 * @param jQuery $node
		 * @param string selector
		 * @returns jQuery
		 */
		function flowFindUpward( $node, selector ) {
			// first check if result can already be found inside $node
			var $result = $node.find( selector );

			// then keep looking up the tree until a result is found
			while ( $result.length === 0 && $node.length !== 0 ) {
				$node = $node.parent();
				$result = $node.children( selector );
			}

			return $result;
		}

		/**
		 *
		 * @param {FlowBoardComponent|jQuery} $container or entire FlowBoard
		 * @todo Move to FlowComponent somehow, perhaps use name="flow-load-handler" for performance in older browsers
		 * @todo use EventEmitter to handle registration on new elements
		 */
		FlowBoardComponent.UI.makeContentInteractive = function ( $container ) {
			if ( !$container.jquery ) {
				$container = $container.$container;
			}

			// Find all load-handlers and trigger them
			$container.find( '.flow-load-interactive' ).add( $container.filter( '.flow-load-interactive' ) ).each( function () {
				var $this = $( this ),
					handlerName = $this.data( 'flow-load-handler' );

				// If this has a special load handler, run it.
				if ( FlowBoardComponent.UI.events.loadHandlers[ handlerName ] ) {
					FlowBoardComponent.UI.events.loadHandlers[ handlerName ].apply( this, [ $this ] );
				}
			} );

			// Find all the forms
			// @todo move this into a flow-load-handler
			$container.find( 'form' ).add( $container.filter( 'form' ) ).each( function () {
				var $this = $( this );

				// Trigger for flow-actions-disabler
				$this.find( 'input, textarea' ).trigger( 'keyup' );

				// Find this form's inputs
				$this.find( 'textarea' ).filter( '[data-flow-expandable]').each( function () {
					// @todo Should this be done via TemplateEngine? Maybe don't modify elements within a template?
					var $this = $( this ),
					// If any of these textareas are expandable, compress them at start via pseudo-cloning into inputs.
						attributes = $this.prop( 'attributes' ),
						$input = $( '<input/>' );
					$.each( attributes, function () {
						$input.attr( this.name, this.value );
					} );

					// Store the old textarea as data on the input
					$.data( $input[0], 'flow-compressed', $this[0] );
					// Store the new input as data on the old textarea
					$.data( $this[0], 'flow-expanded', $input[0] );

					// Drop the new input in place
					FlowBoardComponent.UI.Forms.compressTextarea( $this );
				} );

				FlowBoardComponent.UI.Forms.hideForm( $this );
			} );
		};

		/**
		 * Binds event handlers to individual boards
		 * @param {FlowBoardComponent} flowBoard
		 * @todo Move to FlowComponent
		 */
		FlowBoardComponent.UI.bindBoardHandlers = function ( flowBoard ) {
			var $container = flowBoard.$container,
				$board = flowBoard.$board;

			// Load the collapser state from localStorage
			FlowBoardComponent.UI.collapserState( flowBoard );

			// Container handlers
			$container
				.off( '.FlowBoardComponent' )
				.on(
					'click.FlowBoardComponent keypress.FlowBoardComponent',
					'a, input, button, .flow-click-interactive',
					FlowBoardComponent.UI.events.onClickInteractive
				)
				.on( // @todo REMOVE. This is just to stop from following empty links in demo.
					'click.FlowBoardComponent',
					"a[href='#']",
					function () { event.preventDefault(); }
				)
				.on(
					'click.FlowBoardComponent focusin.FlowBoardComponent focusout.FlowBoardComponent',
					'.flow-menu',
					FlowBoardComponent.UI.events.onToggleHoverMenu
				);

			// Board handlers
			$board
				.off( '.FlowBoardComponent' )
				.on(
					'focus.FlowBoardComponent',
					'input.mw-ui-input, textarea',
					FlowBoardComponent.UI.events.onFocusField
				);
		};

		/**
		 * Binds the window and body event handlers for Flow. More efficient than binding for every board.
		 */
		FlowBoardComponent.UI.bindGlobalHandlers = function () {
			if ( _isGlobalBound ) {
				// Don't do these again.
				return;
			}

			_isGlobalBound = true;

			// Handle scroll to update the topic navigation sidebar
			$( window )
				.on(
					'scroll.flow',
					$.throttle( 50, FlowBoardComponent.UI.events.onWindowScroll )
				)
				.trigger( 'scroll.flow' );
		};

		/**
		 * Initialize all editors, turning them from textareas into editor objects.
		 *
		 * @param jQuery $container
		 */
		FlowBoardComponent.UI.initializeEditors = function ( $container ) {
			var $editors = $container.find( 'textarea' );

			$editors.each( function() {
				var $editor = $( this );

				// All editors already have their content in wikitext-format
				// (mostly because we need to prefill them server-side so that
				// JS-less users can interact)
				mw.flow.editor.load( $editor, $editor.val(), 'wikitext' );

				// Kill editor instance when the form it's in is cancelled
				flowBoardComponentAddCancelCallback( $editor.closest( 'form' ), function() {
					if ( mw.flow.editor.exists( $editor ) ) {
						mw.flow.editor.destroy( $editor );
					}
				} );
			} );
		};

		/**
		 * Triggered by window.scroll. It iterates over every FlowBoard's topic navigator list, affixes it to the
		 * viewport, and will apply the necessary adjustments to show the active topic and its read progress.
		 * @todo This should probably be done in TemplateEngine.
		 */
		FlowBoardComponent.UI.adjustTopicNavigationSidebar = function () {
			var $temp        = $( '<div/>', { 'class': 'flow-topic-navigator', 'style': 'display: none;' } ).appendTo( 'body' ),
				unreadColor  = $temp.css( 'background-color' ),
				readColor    = $temp.addClass( 'flow-topic-navigator-read' ).css( 'background-color' ),
				scrollTop    = $( window ).scrollTop(),
				windowHeight = $( window ).height();

			$temp.remove();

			$.each( FlowBoardComponent.prototype.getInstances(), function () {
				var $topicNavigation = this.$topicNavigation,
					$this, $topic, offsetTop, outerHeight, percent;

				// Only proceed with this wacky stuff if the navigation bar is currently in use
				if ( !$topicNavigation || !$topicNavigation.is( ':visible' ) ) {
					return;
				}

				// Affix the sidebar to the top of the window after scrolling past it
				offsetTop = $topicNavigation.data( 'affix-top' );
				if ( offsetTop && scrollTop < offsetTop ) {
					// Scrolled back up, unfix it
					$topicNavigation
						.removeClass( 'flow-topic-navigation-fixed' )
						.removeData( 'affix-top' );
				} else if ( !offsetTop ) {
					// Not affixed yet
					offsetTop = $topicNavigation.offset().top;
					if ( scrollTop > offsetTop ) {
						// Needs to be affixed to window viewport
						$topicNavigation
							.data( 'affix-top', offsetTop )
							.css( { 'right': $( window ).width() - $topicNavigation.offset().left - $topicNavigation.outerWidth() } )
							.addClass( 'flow-topic-navigation-fixed' );
					}
				}

				// Iterate over every topic link
				$topicNavigation.find( '.flow-topic-navigator' ).each( function () {
					$topic = $( this.href.substr(this.href.indexOf('#')) );
					if ( !$topic.length ) {
						return;
					}

					$this = $( this );

					offsetTop = $topic.offset().top;
					outerHeight = $topic.outerHeight();
					percent = ( scrollTop - offsetTop + windowHeight ) / outerHeight * 100;

					if ( percent >= 100 ) {
						// Entire topic is scrolled beyond.
						if ( !$this.hasClass( 'flow-topic-navigator-read' ) ) {
							// Give it the read class
							$this
								.removeClass( 'flow-topic-navigator-active' )
								.addClass( 'flow-topic-navigator-read' )
								.css( { background: '' } );
						}
					} else if ( percent <= 0.1 ) {
						// Topic is not at all in viewport.
						$this
							.removeClass( 'flow-topic-navigator-active' )
							.removeClass( 'flow-topic-navigator-read' )
							.css( { background: '' } );
					} else {
						// Part of topic is in view. Do partial background progress bar.

						if (percent < 0.01) {
							percent = 0;
						}
						$this
							.removeClass( 'flow-topic-navigator-read' )
							.addClass( 'flow-topic-navigator-active' )
							.css( { background: '-moz-linear-gradient(left,  ' + readColor + ' ' + percent + '%, ' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // FF 3.6+
							.css( { background: '-webkit-gradient(linear, left top, right top, color-stop(' + percent + '%,' + readColor + '), color-stop(' + ( percent + 1 ) + '%,' + unreadColor + '))' } ) // Chrome 4+
							.css( { background: '-webkit-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // Chrome 10+
							.css( { background: '-o-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // Opera 11+
							.css( { background: '-ms-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // IE10+
							.css( { background: 'linear-gradient(to right,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ); // CSS3
					}
				} );
			} );
		};

		/**
		 * Checks whether the user is near the end of the viewport
		 */
		function isEndNear( $end ) {
			var $window = $( window ),
				threshold = $window.height(),
				scrollBottom = $window.scrollTop() + $window.height();
			return scrollBottom + threshold > $end.offset().top;
		}

		/**
		 * Called on window.scroll. Checks to see if a FlowBoard needs to have more content loaded.
		 */
		FlowBoardComponent.UI.infiniteScrollCheck = function () {
			$.each( FlowBoardComponent.prototype.getInstances(), function () {
				var flowBoard = this,
					$loadMoreNodes = flowBoard.$loadMoreNodes || $();

				// Check each loadMore button
				$loadMoreNodes.each( function () {
					if ( isEndNear( $( this ) ) ) {
						$( this ).trigger( 'click' );
					}
				} );
			} );
		};

		/**
		 * Sets the Collapser state to newState, and will load this state on next page refresh.
		 * @param {FlowBoardComponent} flowBoard
		 * @param {String} [newState]
		 */
		FlowBoardComponent.UI.collapserState = function ( flowBoard, newState ) {
			var $heading,
				$container = flowBoard.$container;

			if ( !newState ) {
				// Get last
				newState = mw.flow.StorageEngine.localStorage.getItem( 'collapserState' ) || 'full'; // @todo genericize this
			} else {
				// Save
				mw.flow.StorageEngine.localStorage.setItem( 'collapserState', newState ); // @todo genericize this
				flowBoard.$board.find( '.flow-element-expanded, .flow-element-collapsed' )
					// If moderated topics are currently collapsed, leave them that way
					.not( '.flow-element-moderated.flow-element-collapsed' )
					.not( '.flow-topic-moderated' )
					.removeClass( 'flow-element-expanded flow-element-collapsed' );

				// Remove individual topic states
				mw.flow.StorageEngine.sessionStorage.removeItem( 'collapserStates' );
			}

			// @todo genericize this
			$container
				.removeClass( 'flow-board-collapsed-full flow-board-collapsed-topics flow-board-collapsed-compact' )
				.addClass( 'flow-board-collapsed-' + newState );

			$heading = $container.find( '.flow-topic-title' );
			// In compact mode also truncate the text.
			if ( newState === 'compact' ) {
				$heading.addClass( 'flow-ui-text-truncated' );
			} else {
				$heading.removeClass( 'flow-ui-text-truncated' );
			}
		};

		/**
		 * If a topic is collapsed, expand it.
		 * @param  {Element|jQuery} topic The (single) topic element to show
		 */
		FlowBoardComponent.UI.expandTopicIfNecessary = function( topic ) {
			var $component,
				isFullView,
				isInverted,
				$topic = $( topic );

			$component = $topic.closest( '.flow-component' );
			isFullView = $component.hasClass( 'flow-board-collapsed-full' );
			isInverted = $topic.hasClass( 'flow-element-collapsed-invert' );

			// Either full view and inverted (invisible)
			// or compacted view and not inverted (invisible)
			if ( isFullView === isInverted ) {
				$topic.toggleClass( 'flow-element-collapsed-invert' );
			}
		};

		/**
		 * Shows a tooltip telling the user that they have subscribed
		 * to this topic|board
		 * @param  {jQuery} $tooltipTarget Element to attach tooltip to.
		 * @param  {string} type           'topic' or 'board'
		 * @param  {string} dir            Direction to point the pointer. 'left' or 'up'
		 */
		FlowBoardComponent.UI.showSubscribedTooltip = function( $tooltipTarget, type, dir ) {
			dir = dir || 'left';

			mw.tooltip.show(
				$tooltipTarget,
				// tooltipTarget will not always be part of a FlowBoardComponent
				$( mw.flow.TemplateEngine.processTemplateGetFragment(
						'flow_tooltip_subscribed',
						{
							unsubscribe: false,
							type: type,
							direction: dir
						}
					)
				).children(),
				{
					tooltipPointing: dir
				}
			);

			// Hide after 5s
			setTimeout( function () {
				mw.tooltip.hide( $tooltipTarget );
			}, 5000 );
		};


		/**
		 * Adds a flow-cancel-callback to a given form, to be triggered on click of the "cancel" button.
		 * @param {jQuery} $form
		 * @param {Function} callback
		 */
		function flowBoardComponentAddCancelCallback( $form, callback ) {
			var fns = $form.data( 'flow-cancel-callback' ) || [];
			fns.push( callback );
			$form.data( 'flow-cancel-callback', fns );
		}

		/**
		 * Removes the preview and unhides the form fields.
		 * @param {jQuery} $cancelButton
		 * @return {bool} true if success
		 */
		function flowBoardComponentResetPreview( $cancelButton ) {
			var $form = $cancelButton.closest( 'form' ),
				$button = $form.find( '[name=preview]' ),
				oldData = $button.data( 'flow-return-to-edit' );

			if ( oldData ) {
				// We're in preview mode. Revert it back.
				$button.text( oldData.text );

				// Show the inputs again
				$form.find( '.flow-preview-target-hidden' ).removeClass( 'flow-preview-target-hidden' ).focus();

				// Remove the preview
				oldData.$nodes.remove();

				// Remove this reset info
				$button.removeData( 'flow-return-to-edit' );

				return true;
			}
			return false;
		}

		/**
		 * Refreshes the titlebar of a topic given an API response.
		 * @param  {jQuery} $targetElement An element in the topic.
		 * @param  {Object} apiResult      Plain object containing the API response to build from.
		 */
		function flowBoardComponentRefreshTopic( $targetElement, apiResult ) {
			var $topic = $targetElement.closest( '.flow-topic' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $targetElement ),
				$newTopic = $( flowBoard.TemplateEngine.processTemplateGetFragment(
					'flow_topiclist_loop',
					apiResult
				) ).children();

			$topic.replaceWith( $newTopic );

			FlowBoardComponent.UI.makeContentInteractive( $newTopic );
		}
	}() );
}( jQuery, mediaWiki ) );
