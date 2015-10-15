/*!
 * Runs Flow code, using methods in FlowUI.
 */

( function ( $ ) {
	// Pretend we got some data and run with it
	/*
	 * Now do stuff
	 * @todo not like this
	 */
	$( document ).ready( function () {
		var dataBlob, navWidget, flowBoard, dmBoard, newTopicWidget, sidebarExpandWidget, descriptionWidget,
			siderailCollapsed = mw.user.options.get( 'flow-side-rail-state' ) === 'collapsed',
			pageTitle = mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			$component = $( '.flow-component' ),
			$board = $( '.flow-board' ),
			preloadTopic, preloadContent, preloadFormat,
			finishLoading = function () {
				$component.addClass( 'flow-component-ready' );
				$( '.flow-ui-load-overlay' ).addClass( 'oo-ui-element-hidden' );
			};

		// HACK: If there is no component, we are not on a flow
		// board at all, and there's no need to load anything.
		// This is especially true for tests, though we should
		// fix this by telling ResourceLoader to not load
		// flow-initialize at all on tests.
		if ( $component.length === 0 ) {
			finishLoading();
			return;
		}

		mw.flow.initComponent( $component );

		// HACK: Similarly to the above hack, if there is no board
		// we shouldn't proceed. This is true mainly to history pages
		// that have the component but not the board DOM element.
		if ( $board.length === 0 ) {
			// Editing summary in a separate window. That has no
			// flow-board, but we should still replace it
			startEditTopicSummary(
				false,
				$component.data( 'flow-id' )
			);

			// Mark as finished loading
			finishLoading();
			return;
		}

		// Set up window overlay
		$( 'body' ).append( mw.flow.ui.windowOverlay.$element );
		mw.flow.ui.windowOverlay.$element.append( mw.flow.ui.windowManager.$element );

		flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $board );

		if (
			$component.hasClass( 'topic-page' ) &&
			$( 'body' ).hasClass( 'action-view' )
		) {
			$board
				.toggleClass( 'flow-board-expanded', siderailCollapsed );
			// We are in single-topic view. Initialize the sidebar expand widget
			sidebarExpandWidget = new mw.flow.ui.SidebarExpandWidget( {
				collapsed: siderailCollapsed,
				expandedButtonTitle: mw.msg( 'flow-topic-collapse-siderail' ),
				collapsedButtonTitle: mw.msg( 'flow-topic-expand-siderail' )
			} );
			sidebarExpandWidget.$element.insertAfter( $board );
			sidebarExpandWidget.on( 'toggle', function ( collapsed ) {
				$board.toggleClass( 'flow-board-expanded', collapsed );
			} );
		}

		// Load data model
		mw.flow.system = new mw.flow.dm.System( {
			pageTitle: pageTitle,
			tocPostsLimit: 50,
			renderedTopics: $( '.flow-topic' ).length,
			boardId: $component.data( 'flow-id' ),
			defaultSort: $board.data( 'flow-sortby' )
		} );

		dmBoard = mw.flow.system.getBoard();
		dmBoard.on( 'add', function ( newItems ) {
			var i, len, item, itemId;

			for ( i = 0, len = newItems.length; i < len; i++ ) {
				item = newItems[ i ];
				itemId = item.getId();

				if ( $.inArray( itemId, flowBoard.orderedTopicIds ) === -1 ) {
					flowBoard.orderedTopicIds.push( itemId );
				}

				flowBoard.topicTitlesById[ itemId ] = item.getContent();
				flowBoard.updateTimestampsByTopicId[ itemId ] = item.getLastUpdate();
			}
			flowBoard.sortTopicIds( flowBoard );
		} );

		// E.g. on topic re-order, before re-population.
		dmBoard.on( 'clear', function () {
			flowBoard.orderedTopicIds = [];
			flowBoard.topicTitlesById = {};
		} );
		// We shouldn't have to worry about 'remove', since by the time we have filtering,
		// orderedTopicIds should be gone.

		// Initialize the old system to accept the default
		// 'newest' order for the topic order widget
		// Get the current default sort
		flowBoard.topicIdSort = mw.flow.system.getBoard().getSortOrder();

		/* UI Widgets */
		navWidget = new mw.flow.ui.NavigationWidget( mw.flow.system, {
			defaultSort: flowBoard.topicIdSort
		} );
		$( '.flow-board-navigation' ).append( navWidget.$element );

		// HACK: These event handlers should be in the prospective widgets
		// they will move once we have Board UI and Topic UI widgets
		mw.flow.system.on( 'resetBoardStart', function () {
			$component.addClass( 'flow-api-inprogress' );
			// Before we reinitialize the board we have to detach
			// the navigation widget. This should not be necessary when
			// the board and topics are OOUI widgets
			navWidget.$element.detach();
		} );

		mw.flow.system.on( 'resetBoardEnd', function ( data ) {
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
				$board.empty().append( $rendered[ 1 ] );

				// Since we've replaced the entire board, we need to reinitialize
				// it. This also takes away the original navWidget, so we need to
				// make sure it's reinitialized too
				flowBoard.reinitializeContainer( $rendered );
				$( '.flow-board-navigation' ).append( navWidget.$element );

				$component.removeClass( 'flow-api-inprogress' );
			}, 50 );

		} );

		// HACK: On load more, populate the board dm
		flowBoard.on( 'loadmore', function ( topiclist ) {
			// Add to the DM board
			mw.flow.system.populateBoardTopicsFromJson( topiclist );

			replaceReplyForms( $board );
			deactivateReplyLinks( $board );
		} );

		// HACK: Update the DM when topic is refreshed
		flowBoard.on( 'refreshTopic', function ( workflowId, topicData ) {
			var revisionId, revision,
				topic = dmBoard.getItemById( workflowId ),
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

			replaceReplyForms( topicData.$topic );
			deactivateReplyLinks( topicData.$topic );
		} );

		// Load a topic from the ToC that isn't rendered on
		// the page yet. This will be gone once board, topic
		// and post are widgetized.
		navWidget.on( 'loadTopic', function ( topicId ) {
			flowBoard.jumpToTopic( topicId );
		} );
		navWidget.on( 'reorderTopics', function ( newOrder ) {
			flowBoard.topicIdSort = newOrder;
		} );

		// Replace reply inputs with the editor widget
		function replaceReplyForms( $element ) {
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

					// HACK get the old system to rerender the topic
					return flowBoard.flowBoardComponentRefreshTopic(
						$topic,
						workflow
					);
				} );
				replyWidget.$element.data( 'self', replyWidget );

				// Replace the reply form with the new editor widget
				$( this ).replaceWith( replyWidget.$element );
			} );
		}
		replaceReplyForms( $board );

		// No-JS view with JavaScript on (replace old editor)
		function replaceNoJSEditor( $element ) {
			var editPostWidget,
				$post = $element.parent(),
				$topic = $post.closest( '.flow-topic' );

			function saveOrCancelHandler( workflow ) {
				editPostWidget.destroy();
				editPostWidget.$element.remove();

				// HACK get the old system to rerender the topic
				return flowBoard.flowBoardComponentRefreshTopic(
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
		}
		// Editing in a separate window
		replaceNoJSEditor( $( '.flow-edit-post-form' ) );

		// Undo actions
		if ( $( 'form[data-module="topic"]' ).length ) {
			replaceEditorInUndoEditPost( $( 'form[data-module="topic"]' ) );
		} else if ( $( 'form[data-module="header"]' ).length ) {
			replaceEditorInUndoHeaderPost( $( 'form[data-module="header"]' ) );
		}

		function replaceEditorInUndoEditPost( $form ) {
			var editPostWidget, postId,
				pageName = mw.config.get( 'wgPageName' ),
				title = mw.Title.newFromText( pageName ),
				topicId = title.getNameText(),
				goBackToTitle = function () {
					editPostWidget.toggle( false );
					// HACK: redirect to topic view
					window.location.href = title.getUrl();
				};

			if ( !$form.length ) {
				return;
			}
			postId = $form.find( 'input[name="topic_postId"]' ).val();
			editPostWidget = new mw.flow.ui.EditPostWidget( topicId, postId );

			editPostWidget
				.on( 'saveContent', goBackToTitle )
				.on( 'cancel', goBackToTitle );

			$form.replaceWith( editPostWidget.$element );
		}

		function replaceEditorInUndoHeaderPost( $form ) {
			var descWidget, prevRevId,
				pageName = mw.config.get( 'wgPageName' ),
				title = mw.Title.newFromText( pageName ),
				model = mw.flow.system.getBoard();

			if ( !$form.length ) {
				return;
			}

			prevRevId = $form.find( 'input[name="header_prev_revision"]' ).val();
			model.getDescription().setRevisionId( prevRevId );
			descWidget = new mw.flow.ui.BoardDescriptionWidget( model );
			// HACK: Hide the edit button even if it tries to toggle( true ) itself
			// later in the code after saveContent
			descWidget.button.$element.css( 'display', 'none' );
			// HACK: Simulate an 'edit' click event so the description widget
			// loads already in edit mode. This saves us the trouble of creating
			// and already open description widget and duplicating all the api
			// logic that happens when clicking the 'edit' button, seeing as this
			// addition is already a quickfix hack.
			descWidget.button.emit( 'click' );

			descWidget
				.on( 'saveContent', function () {
					// HACK: redirect to topic view
					window.location.href = title.getUrl();
				} )
				.on( 'cancel', function () {
					// HACK: redirect to topic view
					window.location.href = title.getUrl();
				} );

			$form.replaceWith( descWidget.$element );
		}

		// Replace the 'reply' buttons so they all produce replyWidgets rather
		// than the reply forms from the API
		$board
			.on( 'click', '.flow-ui-reply-link-trigger', function () {
				var replyWidget,
					$topic = $( this ).closest( '.flow-topic' ),
					placeholder = mw.msg( 'flow-reply-topic-title-placeholder', $topic.find( '.flow-topic-title' ).text().trim() ),
					replyTo = $( this ).data( 'postId' ),
					// replyTo can refer to a post ID or a topic ID
					// For posts, the ReplyWidget should go in .flow-replies
					// For topics, it's directly inside the topic
					$targetContainer = $( '#flow-post-' + replyTo + ' > .flow-replies, #flow-topic-' + replyTo ),
					$existingWidget = $targetContainer.children( '.flow-ui-replyWidget' );

				// Check that there's not already a reply widget existing in the same place
				if ( $existingWidget.length > 0 ) {
					// Focus the existing reply widget
					$existingWidget.data( 'self' ).activateEditor();
					$existingWidget.data( 'self' ).focus();
					return false;
				}

				replyWidget = new mw.flow.ui.ReplyWidget( $topic.data( 'flowId' ), replyTo, {
					placeholder: placeholder,
					expandable: false
				} );
				// Create a reference so we can call it from the DOM above
				replyWidget.$element.data( 'self', replyWidget );

				// Add reply form below the post being replied to (WRT max depth)
				$targetContainer.append( replyWidget.$element );
				replyWidget.activateEditor();

				replyWidget
					.on( 'saveContent', function ( workflow ) {
						replyWidget.destroy();
						replyWidget.$element.remove();

						// HACK get the old system to rerender the topic
						return flowBoard.flowBoardComponentRefreshTopic(
							$topic,
							workflow
						);
					} )
					.on( 'cancel', function () {
						replyWidget.destroy();
						replyWidget.$element.remove();
					} );

				return false;
			} );

		function deactivateReplyLinks( $element ) {
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
		}
		deactivateReplyLinks( $board );

		// New topic form
		newTopicWidget = new mw.flow.ui.NewTopicWidget( pageTitle.getPrefixedDb() );
		newTopicWidget.on( 'save', function ( newTopicId ) {
			// Display the new topic with the old system
			var $stub = $( '<div class="flow-topic"><div></div></div>' ).prependTo( flowBoard.$container.find( '.flow-topics' ) );
			return flowBoard.flowBoardComponentRefreshTopic( $stub.find( 'div' ), newTopicId );
		} );
		$( 'form.flow-newtopic-form' ).replaceWith( newTopicWidget.$element );

		$board.on( 'click', '.flow-ui-edit-post-link', function ( event ) {
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

		function startEditTopicSummary( isFullBoard, topicId, action ) {
			var editTopicSummaryWidget,
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
						return flowBoard.flowBoardComponentRefreshTopic(
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
		}

		$board.on( 'click', '.flow-ui-summarize-topic-link', function ( event ) {
			var $topic = $( this ).closest( '.flow-topic' ),
				topicId = $topic.data( 'flow-id' );
			startEditTopicSummary( true, topicId );
			event.preventDefault();
		} );

		$board.on( 'click', '.flow-ui-topicmenu-lock', function () {
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
					return flowBoard.flowBoardComponentRefreshTopic(
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
						startEditTopicSummary( true, topicId, action );
					}
				} );

			// Prevent default
			return false;
		} );

		// Fall back to mw.flow.data, which was used until September 2015
		// NOTICE: This block must be after the initialization of the ui widgets so
		// they can populate themselves according to the events.
		dataBlob = mw.config.get( 'wgFlowData' ) || ( mw.flow && mw.flow.data );
		if ( dataBlob && dataBlob.blocks ) {
			// Populate the rendered topics or topic (if we are in a single-topic view)
			mw.flow.system.populateBoardTopicsFromJson( dataBlob.blocks.topiclist || dataBlob.blocks.topic );
			// Populate header
			mw.flow.system.populateBoardDescriptionFromJson( dataBlob.blocks.header || {} );
			// Populate the ToC topics
			if ( dataBlob.toc ) {
				mw.flow.system.populateBoardTopicsFromJson( dataBlob.toc );
			}
		} else {
			mw.flow.system.populateBoardFromApi();
		}

		// Board description widget
		descriptionWidget = new mw.flow.ui.BoardDescriptionWidget( dmBoard, {
			$existing: $( '.flow-ui-boardDescriptionWidget-content' ).contents(),
			$categories: $( '.flow-board-header-category-view-nojs' ).contents(),
			specialPageCategoryLink: dataBlob.specialCategoryLink
		} );
		$( '.flow-ui-boardDescriptionWidget' ).replaceWith( descriptionWidget.$element );

		// The category widget is inside the board description widget.
		// Remove it here
		$( '.flow-board-header-category-view-nojs' ).detach();
		// HACK: Remove the MW page categories
		$( '.catlinks:not(.flow-ui-categoriesWidget)' ).detach();

		preloadTopic = OO.getProp( dataBlob, 'blocks', 'topiclist', 'submitted', 'topic' );
		preloadContent = OO.getProp( dataBlob, 'blocks', 'topiclist', 'submitted', 'content' );
		preloadFormat = OO.getProp( dataBlob, 'blocks', 'topiclist', 'submitted', 'format' );
		if ( preloadTopic || preloadContent ) {
			newTopicWidget.preload( preloadTopic, preloadContent, preloadFormat );
		}

		// Show the board
		finishLoading();
	} );
}( jQuery ) );
