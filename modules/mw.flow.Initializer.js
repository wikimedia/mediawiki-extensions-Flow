( function ( $ ) {
	mw.flow.Initializer = function ( config ) {
		config = config || {};

		// Mixin constructor
		OO.EventEmitter.call( this );

		this.$component = null;
		this.$board = null;
		this.siderailCollapsed = mw.user.options.get( 'flow-side-rail-state' ) === 'collapsed';

		this.system = null;
		this.board = null;
		this.navWidget = null;
	};

	/* Inheritance */

	OO.initClass( mw.flow.Initializer );
	OO.mixinClass( mw.flow.Initializer, OO.EventEmitter );

	/**
	 * Sets the DOM element that is the flow component
	 *
	 * @throws {Error} If there is no component
	 * @param {jQuery} $component The DOM element that is the component
	 */
	mw.flow.Initializer.prototype.setComponentDom = function ( $component ) {
		if ( !$component || !$component.length ) {
			throw new Error( 'No component found.' );
		}
		this.$component = $component;
	};

	/**
	 * Sets the DOM element that is the flow board
	 *
	 * @throws {Error} If there is no board
	 * @param {jQuery} $board The DOM element that is the board
	 */
	mw.flow.Initializer.prototype.setBoardDom = function ( $board ) {
		if ( !$board || !$board.length ) {
			throw new Error( 'Not a board page.' );
		}
		this.$board = $board;
	};

	mw.flow.Initializer.prototype.setBoardObject = function ( board ) {
		var self = this;

		this.flowBoard = board;

		this.flowBoard.connect( this, {
			loadmore: function ( topiclist ) {
				// Add to dm board
				if ( self.system ) {
					self.system.populateBoardTopicsFromJson( topiclist );
				}

				// Replace reply forms
				self.replaceReplyForms( self.$board );
				// Deactivate reply links
				self.deactivateReplyLinks( self.$board );
			},
			// HACK: Update the DM when topic is refreshed
			refreshTopic: function ( workflowId, topicData ) {
				var revisionId, revision,
					topic = self.board.getItemById( workflowId ),
					data = topicData.flow[ 'view-topic' ].result.topic;

				if ( !topic ) {
					// New topic
					mw.flow.system.populateBoardTopicsFromJson( data, 0 );
				} else {
					// Topic already exists. Repopulate
					revisionId = data.posts[ workflowId ];
					revision = data.revisions[ revisionId ];
					topic.populate( revision );
				}

				// Replace reply forms
				self.replaceReplyForms( topicData.$topic );
				// Deactivate reply links
				self.deactivateReplyLinks( topicData.$topic );
			}
		} );
	};

	/**
	 * Set up the window overlay
	 */
	mw.flow.Initializer.prototype.setupWindowOverlay = function () {
		// Set up window overlay
		$( 'body' ).append( mw.flow.ui.windowOverlay.$element );
		mw.flow.ui.windowOverlay.$element.append( mw.flow.ui.windowManager.$element );
	};

	/**
	 * Set up the sidebar widget if needed
	 */
	mw.flow.Initializer.prototype.setupSidebarWidget = function () {
		var sidebarExpandWidget;

		if (
			this.$component.hasClass( 'topic-page' ) &&
			$( 'body' ).hasClass( 'action-view' )
		) {
			this.$board.toggleClass( 'flow-board-expanded', this.siderailCollapsed );

			// We are in single-topic view. Initialize the sidebar expand widget
			sidebarExpandWidget = new mw.flow.ui.SidebarExpandWidget( {
				collapsed: this.siderailCollapsed,
				expandedButtonTitle: mw.msg( 'flow-topic-collapse-siderail' ),
				collapsedButtonTitle: mw.msg( 'flow-topic-expand-siderail' )
			} );
			sidebarExpandWidget.$element.insertAfter( this.$board );

			// Events
			sidebarExpandWidget.on( 'toggle', function ( collapsed ) {
				this.$board.toggleClass( 'flow-board-expanded', collapsed );
			} );
		}
	};

	mw.flow.Initializer.prototype.initializeWidgets = function () {
		// Set up window overlay
		this.setupWindowOverlay();

		// Set up sidebar widget if it needs to be there
		this.setupSidebarWidget();

		// Set up navigation widget
		this.setupNavigationWidget( $( '.flow-board-navigation' ) );

		// Set up new topic widget
		this.setupNewTopicWidget( $( 'form.flow-newtopic-form' ) );

		// Set up description widget
		this.setupDesciptionWidget( $( '.flow-ui-boardDescriptionWidget' ) );

		// Replace reply forms on the board
		this.replaceReplyForms( this.$board );

		/* Take over actions */
		this.setupEditPostAction();
		this.setupEditTopicSummaryAction();
	};

	/**
	 * Initialize the 'old' Flow ui component
	 */
	mw.flow.Initializer.prototype.initOldComponent = function () {
		if ( this.$component ) {
			mw.flow.initComponent( this.$component );
		}
	};

	mw.flow.Initializer.prototype.initDataModel = function ( config ) {
		var self = this;

		this.system = new mw.flow.dm.System( config );
		this.board = this.system.getBoard();
		// Initialize the old system to accept the default
		// 'newest' order for the topic order widget
		// Get the current default sort
		this.flowBoard.topicIdSort = mw.flow.system.getBoard().getSortOrder();

		// Events
		this.board.connect( this, {
			add: function ( newItems ) {
				var i, len, item, itemId;

				for ( i = 0, len = newItems.length; i < len; i++ ) {
					item = newItems[ i ];
					itemId = item.getId();

					if ( $.inArray( itemId, self.flowBoard.orderedTopicIds ) === -1 ) {
						self.flowBoard.orderedTopicIds.push( itemId );
					}

					self.flowBoard.topicTitlesById[ itemId ] = item.getContent();
					self.flowBoard.updateTimestampsByTopicId[ itemId ] = item.getLastUpdate();
				}
				self.flowBoard.sortTopicIds( self.flowBoard );
			},
			// E.g. on topic re-order, before re-population.
			clear: function () {
				self.flowBoard.orderedTopicIds = [];
				self.flowBoard.topicTitlesById = {};
			}
			// We shouldn't have to worry about 'remove', since by the time we have filtering,
			// orderedTopicIds should be gone.
		} );
	};

	mw.flow.Initializer.prototype.getDataModelSystem = function () {
		return this.system;
	};

	mw.flow.Initializer.prototype.populateDataModel = function ( dataBlob ) {
		var preloadTopic = OO.getProp( dataBlob, 'blocks', 'topiclist', 'submitted', 'topic' ),
			preloadContent = OO.getProp( dataBlob, 'blocks', 'topiclist', 'submitted', 'content' ),
			preloadFormat = OO.getProp( dataBlob, 'blocks', 'topiclist', 'submitted', 'format' );

		if ( dataBlob && dataBlob.blocks ) {
			// Populate the rendered topics or topic (if we are in a single-topic view)
			this.system.populateBoardTopicsFromJson( dataBlob.blocks.topiclist || dataBlob.blocks.topic );
			// Populate header
			this.system.populateBoardDescriptionFromJson( dataBlob.blocks.header || {} );
			// Populate the ToC topics
			if ( dataBlob.toc ) {
				this.system.populateBoardTopicsFromJson( dataBlob.toc );
			}
		} else {
			this.system.populateBoardFromApi();
		}
		if ( preloadTopic || preloadContent ) {
			this.newTopicWidget.preload( preloadTopic, preloadContent, preloadFormat );
		}
	};

	mw.flow.Initializer.prototype.setupNavigationWidget = function ( $navDom ) {
		var self = this;

		if ( !$navDom.length ) {
			return;
		}

		this.navWidget = new mw.flow.ui.NavigationWidget( this.system, {
			defaultSort: this.flowBoard.topicIdSort
		} );
		$navDom.append( this.navWidget.$element );

		// Events
		// Load a topic from the ToC that isn't rendered on
		// the page yet. This will be gone once board, topic
		// and post are widgetized.
		this.navWidget.connect( this, {
			loadTopic: function ( topicId ) {
				self.flowBoard.jumpToTopic( topicId );
			},
			reorderTopics: function ( newOrder ) {
				self.flowBoard.topicIdSort = newOrder;
			}
		} );

		// Connect to system events

		// HACK: These event handlers should be in the prospective widgets
		// they will move once we have Board UI and Topic UI widgets
		this.system.connect( this, {
			resetBoardStart: function () {
				self.$component.addClass( 'flow-api-inprogress' );
				// Before we reinitialize the board we have to detach
				// the navigation widget. This should not be necessary when
				// the board and topics are OOUI widgets
				self.navWidget.$element.detach();
			},
			resetBoardEnd: function ( data ) {
				var $rendered;

				// HACK: Trim the data for only the rendered topics
				// We can't have the API request use a limit because when we
				// rebuild the board (when we reorder) we want the full 50
				// topics for the ToC widget. When the topic- and board-widget
				// are operational, we should render properly without using this
				// hack.
				data.topiclist.roots = data.topiclist.roots.splice( 0, mw.flow.system.getRenderedTopics() );
				$rendered = $(
					mw.flow.TemplateEngine.processTemplateGetFragment(
						'flow_block_loop',
						{ blocks: data }
					)
				).children();
				// Run this on a short timeout so that the other board handler in FlowBoardComponentLoadMoreFeatureMixin can run
				// TODO: Using a timeout doesn't seem like the right way to do this.
				setTimeout( function () {
					// Reinitialize the whole board with these nodes
					// flowBoard.reinitializeContainer( $rendered );
					self.$board.empty().append( $rendered[ 1 ] );

					// Since we've replaced the entire board, we need to reinitialize
					// it. This also takes away the original navWidget, so we need to
					// make sure it's reinitialized too
					self.flowBoard.reinitializeContainer( $rendered );
					$( '.flow-board-navigation' ).append( self.navWidget.$element );

					self.$component.removeClass( 'flow-api-inprogress' );
				}, 50 );
			}
		} );
	};

	mw.flow.Initializer.prototype.setupNewTopicWidget = function ( $form ) {
		var self = this;

		this.newTopicWidget = new mw.flow.ui.NewTopicWidget( this.pageTitle.getPrefixedDb() );

		// Events
		this.newTopicWidget.connect( this, {
			save: function ( newTopicId ) {
				// Display the new topic with the old system
				var $stub = $( '<div class="flow-topic"><div></div></div>' ).prependTo( self.flowBoard.$container.find( '.flow-topics' ) );
				return this.flowBoard.flowBoardComponentRefreshTopic( $stub.find( 'div' ), newTopicId );
			}
		} );

		$form.replaceWith( this.newTopicWidget.$element );
	};

	mw.flow.Initializer.prototype.setupDesciptionWidget = function ( $element ) {
		var descriptionWidget;

		if ( !$element.length ) {
			return;
		}

		descriptionWidget = new mw.flow.ui.BoardDescriptionWidget( this.board, {
			$existing: $( '.flow-ui-boardDescriptionWidget-content' ).contents(),
			$categories: $( '.flow-board-header-category-view-nojs' ).contents()
		} );

		// The category widget is inside the board description widget.
		// Remove it from the nojs version here
		$( '.flow-board-header-category-view-nojs' ).detach();
		// HACK: Remove the MW page categories
		$( '.catlinks:not(.flow-ui-categoriesWidget)' ).detach();

		$element.replaceWith( descriptionWidget.$element );
	};

	mw.flow.Initializer.prototype.replaceReplyForms = function ( $element ) {
		var self = this;

		if ( !this.$board || !this.$board.length ) {
			return;
		}

		$element.find( '.flow-post.flow-reply-form' ).each( function () {
			var $topic = $( this ).parent(),
				placeholder = mw.msg( 'flow-reply-topic-title-placeholder', $topic.find( '.flow-topic-title' ).text().trim() ),
				replyTo = $( this ).find( 'input[name="topic_replyTo"]' ).val(),
				replyWidget = new mw.flow.ui.ReplyWidget( $topic.data( 'flowId' ), replyTo, {
					placeholder: placeholder
				} );

			replyWidget.on( 'saveContent', function ( workflow ) {
				replyWidget.destroy();
				replyWidget.$element.remove();

				// HACK: get the old system to rerender the topic
				return self.flowBoard.flowBoardComponentRefreshTopic(
					$topic,
					workflow
				);
			} );
			replyWidget.$element.data( 'self', replyWidget );

			// Replace the reply form with the new editor widget
			$( this ).replaceWith( replyWidget.$element );
		} );
	};

	mw.flow.Initializer.prototype.setupEditPostAction = function () {
		this.$board.on( 'click', '.flow-ui-edit-post-link', function ( event ) {
			var editPostWidget,
				$topic = $( this ).closest( '.flow-topic' ),
				topicId = $topic.data( 'flow-id' ),
				$post = $( this ).closest( '.flow-post' ),
				$postMain = $post.children( '.flow-post-main' ),
				postId = $post.data( 'flow-id' ),
				$board = $( '.flow-board' ),
				flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $board );

			editPostWidget = new mw.flow.ui.EditPostWidget( topicId, postId );
			editPostWidget
				.on( 'saveContent', function ( workflow ) {
					editPostWidget.destroy();
					editPostWidget.$element.remove();

					// HACK get the old system to rerender the topic
					return flowBoard.flowBoardComponentRefreshTopic(
						$topic,
						workflow
					);
				} )
				.on( 'cancel', function () {
					editPostWidget.destroy();
					editPostWidget.$element.replaceWith( $postMain );
				} );

			$postMain.replaceWith( editPostWidget.$element );

			event.preventDefault();
		} );
	};

	mw.flow.Initializer.prototype.setupEditTopicSummaryAction = function () {
		var self = this;

		this.$board
			// Summarize action
			.on( 'click', '.flow-ui-summarize-topic-link', function ( event ) {
				var $topic = $( this ).closest( '.flow-topic' ),
					topicId = $topic.data( 'flow-id' );
				this.startEditTopicSummary( true, topicId );
				event.preventDefault();
			} )
			// Lock action
			.on( 'click', '.flow-ui-topicmenu-lock', function () {
				var promise,
					action = $( this ).data( 'role' ),
					$topic = $( this ).closest( '.flow-topic' ),
					topicId = $topic.data( 'flow-id' ),
					api = new mw.flow.dm.APIHandler();

				if ( action === 'lock' ) {
					promise = api.resolveTopic( topicId );
				} else {
					promise = api.reopenTopic( topicId );
				}

				promise
					.then( function ( workflow ) {
						return self.flowBoard.flowBoardComponentRefreshTopic(
							$topic,
							workflow
						);
					} )
					.then( function ( data ) {
						var revisionId = data.topic.posts[ topicId ],
							revision = data.topic.revisions[ revisionId ],
							summaryContent = OO.getProp( revision, 'summary', 'revision', 'content', 'content' ),
							skipSummarize = action === 'unlock' && !summaryContent;

						if ( !skipSummarize ) {
							self.startEditTopicSummary( true, topicId, action );
						}
					} );

				// Prevent default
				return false;
			} );
	};

	mw.flow.Initializer.prototype.startEditTopicSummary = function ( isFullBoard, topicId, action  ) {
		var editTopicSummaryWidget,
			self = this,
			$topic = $( '#flow-topic-' + topicId ),
			$summaryContainer = $topic.find( '.flow-topic-summary-container' ),
			$topicSummary = $summaryContainer.find( '.flow-topic-summary' ),
			options = {},
			pageName = mw.config.get( 'wgPageName' ),
			title = mw.Title.newFromText( pageName );

		if ( !$summaryContainer.length ) {
			return;
		}

		if ( action === 'lock' || action === 'unlock' ) {
			options = {
				cancelMsgKey: 'flow-skip-summary'
			};
		}

		editTopicSummaryWidget = new mw.flow.ui.EditTopicSummaryWidget( topicId, options );
		editTopicSummaryWidget
			.on( 'saveContent', function ( workflow ) {
				editTopicSummaryWidget.destroy();
				editTopicSummaryWidget.$element.remove();

				if ( isFullBoard ) {
					// HACK get the old system to rerender the topic
					return self.flowBoard.flowBoardComponentRefreshTopic(
						$topic,
						workflow
					);
				} else {
					// HACK: redirect to topic view
					window.location.href = title.getUrl();
				}
			} )
			.on( 'cancel', function () {
				editTopicSummaryWidget.destroy();
				editTopicSummaryWidget.$element.remove();
				if ( isFullBoard ) {
					$summaryContainer.append( $topicSummary );
				} else {
					// HACK: redirect to topic view
					window.location.href = title.getUrl();
				}
			} );

		$topicSummary.remove();
		$summaryContainer.append( editTopicSummaryWidget.$element );
	};

	mw.flow.Initializer.prototype.replaceNoJSEditor = function ( $element ) {
		var editPostWidget,
			$post = $element.parent(),
			$topic = $post.closest( '.flow-topic' );

		function saveOrCancelHandler( workflow ) {
			editPostWidget.destroy();
			editPostWidget.$element.remove();

			// HACK get the old system to rerender the topic
			return this.flowBoard.flowBoardComponentRefreshTopic(
				$topic,
				workflow
			);
		}

		if ( !$element.length ) {
			return;
		}

		editPostWidget = new mw.flow.ui.EditPostWidget( $topic.data( 'flowId' ), $post.data( 'flowId' ) );

		editPostWidget
			.on( 'saveContent', saveOrCancelHandler )
			// HACK: In this case, we are in an edge case where the topic already
			// loaded with the editor open. We can't trust the content of the editor
			// for displaying the post in case of a 'cancel' event and we don't have
			// the actual content stored in the DOM anywhere else.
			// We must reload the topic -- just like we do on save -- for a cancel
			// event too.
			.on( 'cancel', saveOrCancelHandler.bind( null, $topic.data( 'flowId' ) ) );

		$element.replaceWith( editPostWidget.$element );
	};

	mw.flow.Initializer.prototype.deactivateReplyLinks = function ( $element ) {
		// Cancel the interactive handler so "old" system doesn't get triggered for internal replies
		$element.find( 'a.flow-reply-link' ).each( function () {
			// Store the needed details so we can get rid of the URL in JS mode
			var href = $( this ).attr( 'href' ),
				uri = new mw.Uri( href ),
				postId = uri.query.topic_postId;

			$( this )
				.data( 'postId', postId )
				.attr( 'data-flow-interactive-handler', '' )
				.attr( 'href', '' )
				.addClass( 'flow-ui-reply-link-trigger' );
		} );
	};

	mw.flow.Initializer.prototype.createEditorWidget = function ( $domToReplace, content, saveMsgKey ) {
		var $wrapper,
			anonWarning = new mw.flow.ui.AnonWarningWidget(),
			error = new OO.ui.LabelWidget( {
				classes: [ 'flow-ui-boardDescriptionWidget-error flow-errors errorbox' ]
			} ),
			editor = new mw.flow.ui.EditorWidget( {
				saveMsgKey: saveMsgKey
			} );

		error.toggle( false );
		editor.toggle( true );
		anonWarning.toggle( mw.user.isAnon() );

		// HACK: We still need a reference to the error widget, for
		// the api responses in the intialized widgets that use this
		// function, so make a forced connection
		editor.error = error;

		// Prepare the editor
		editor.pushPending();
		editor.activate();

		editor.setContent( content, 'wikitext' )
			.then( function () {
				editor.popPending();
			} );

		editor
			.on( 'saveContent', function ( content, contentFormat ) {
				var $captchaField, captcha;

				editor.pushPending();

				$captchaField = error.$label.find( '[name="wpCaptchaWord"]' );
				if ( $captchaField.length > 0 ) {
					captcha = {
						id: this.error.$label.find( '[name="wpCaptchaId"]' ).val(),
						answer: $captchaField.val()
					};
				}
				error.setLabel( '' );
				error.toggle( false );

				// HACK: This is a cheat so that we can have a single function
				// that creates the editor, but multiple uses, especially for the
				// APIhandler in different cases
				editor.emit( 'afterSaveContent', content, contentFormat, captcha );
			} )
			.on( 'cancel', function () {
				editor.pushPending();
				editor.emit( 'afterCancel' );
				// returnToBoard();
			} );

		$wrapper = $( '<div>' )
			.append(
				error.$element,
				anonWarning.$element,
				editor.$element
			);
		$domToReplace.replaceWith( $wrapper );

		return editor;
	};

	mw.flow.Initializer.prototype.replaceEditorInUndoEditPost = function ( $form ) {
		var apiHandler, content, postId, editor, prevRevId,
			pageName = mw.config.get( 'wgPageName' ),
			title = mw.Title.newFromText( pageName ),
			topicId = title.getNameText(),
			returnToTitle = function () {
				// HACK: redirect to topic view
				window.location.href = title.getUrl();
			};

		if ( !$form.length ) {
			return;
		}

		postId = $form.find( 'input[name="topic_postId"]' ).val();
		prevRevId = $form.find( 'input[name="topic_prev_revision"]' ).val();
		content = $form.find( 'textarea' ).val();

		apiHandler = new mw.flow.dm.APIHandler(
			'Topic:' + topicId,
			{
				currentRevision: prevRevId
			}
		);

		// Create the editor
		editor = this.createEditorWidget(
			$form,
			content,
			mw.user.isAnon() ? 'flow-post-action-edit-post-submit-anonymously' : 'flow-post-action-edit-post-submit'
		);

		// Events
		editor
			.on( 'afterSaveContent', function ( content, contentFormat, captcha ) {
				apiHandler.savePost( topicId, postId, content, contentFormat, captcha )
					.then(
						// Success
						returnToTitle,
						// Failure
						function ( errorCode, errorObj ) {
							if ( /spamfilter$/.test( errorCode ) && errorObj.error.spamfilter === 'flow-spam-confirmedit-form' ) {
								editor.error.setLabel(
									// CAPTCHA form
									new OO.ui.HtmlSnippet( errorObj.error.info )
								);
							} else {
								editor.error.setLabel( errorObj.error && errorObj.error.info || errorObj.exception );
							}

							editor.error.toggle( true );
							editor.popPending();
						}
					);
			} )
			.on( 'afterCancel', returnToTitle );
	};

	mw.flow.Initializer.prototype.replaceEditorInUndoHeaderPost = function ( $form ) {
		var prevRevId, editor, content,
			error, apiHandler,
			pageName = mw.config.get( 'wgPageName' ),
			title = mw.Title.newFromText( pageName ),
			returnToBoard = function () {
				window.location.href = title.getUrl();
			};

		if ( !$form.length ) {
			return;
		}

		prevRevId = $form.find( 'input[name="header_prev_revision"]' ).val();
		content = $form.find( 'textarea[name="header_content"]' ).val();

		apiHandler = new mw.flow.dm.APIHandler(
			title.getPrefixedDb(),
			{
				currentRevision: prevRevId
			}
		);

		// Create the editor
		editor = this.createEditorWidget(
			$form,
			content,
			mw.user.isAnon() ? 'flow-edit-header-submit-anonymously' : 'flow-edit-header-submit'
		);

		// Events
		editor
			.on( 'afterSaveContent', function ( content, contentFormat, captcha ) {
				apiHandler.saveDescription( content, contentFormat, captcha )
					.then(
						// Success
						returnToBoard,
						// Failure
						function ( errorCode, errorObj ) {
							if ( /spamfilter$/.test( errorCode ) && errorObj.error.spamfilter === 'flow-spam-confirmedit-form' ) {
								error.setLabel(
									// CAPTCHA form
									new OO.ui.HtmlSnippet( errorObj.error.info )
								);
							} else {
								error.setLabel( errorObj.error && errorObj.error.info || errorObj.exception );
							}
							editor.popPending();
						}
					);
			} )
			.on( 'afterCancel', function () {
				returnToBoard();
			} );
	};

	/**
	 * Finish the loading process
	 */
	mw.flow.Initializer.prototype.finishLoading = function () {
		this.$component.addClass( 'flow-component-ready' );
		$( '.flow-ui-load-overlay' ).addClass( 'oo-ui-element-hidden' );
	};
}( jQuery ) );
