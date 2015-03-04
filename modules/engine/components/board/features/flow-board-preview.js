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
	function FlowBoardComponentPreviewMixin( $container ) {
		// Bind event callbacks
		this.bindNodeHandlers( FlowBoardComponentPreviewMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentPreviewMixin );

	/** Event handlers are stored here, but are registered in the constructor */
	FlowBoardComponentPreviewMixin.UI = {
		events: {
			apiPreHandlers: {},
			apiHandlers: {}
		}
	};

	//
	// pre-api callback handlers, to do things before the API call
	//

	/**
	 * First, resets the previous preview (if any).
	 * Then, using the form fields, finds the content element to be sent to Parsoid by looking
	 * for one ending in "content", or, failing that, with data-role=content.
	 * @param  {Event} event The event being handled
	 * @return {Function} Callback to modify the API request
	 * @todo genericize into FlowComponent
	 */
	FlowBoardComponentPreviewMixin.UI.events.apiPreHandlers.preview = function ( event ) {
		var callback,
			$this = $( this ),
			$target = $this.findWithParent( $this.data( 'flow-api-target' ) ),
			previewTitleGenerator = $target.data( 'flow-preview-title-generator' ),
			previewTitle = $target.data( 'flow-preview-title' ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $this ),
			schemaName = $this.data( 'flow-eventlog-schema' ),
			funnelId = $this.data( 'flow-eventlog-funnel-id' ),
			logAction = $this.data( 'flow-return-to-edit' ) ? 'keep-editing' : 'preview',
			generators = {
				newTopic: function() {
					// Convert current timestamp to base-2
					var namespace = mw.config.get( 'wgFormattedNamespaces' )[2600],
						timestamp = mw.flow.baseConvert( Date.now(), 10, 2 );
					// Pad base-2 out to 88 bits (@todo why 84?)
					timestamp += [ 84 - timestamp.length ].join( '0' );
					// convert base-2 to base-36
					return namespace + ':' + mw.flow.baseConvert( timestamp, 2, 36 );
				},
				wgPageName: function() {
					return mw.config.get( 'wgPageName' );
				}
			};

		if ( !previewTitleGenerator || !generators.hasOwnProperty( previewTitleGenerator ) ) {
			previewTitleGenerator = 'wgPageName';
		}

		flowBoard.logEvent( schemaName, { action: logAction, funnelId: funnelId } );

		callback = function ( queryMap ) {
			var content = null;

			// XXX: Find the content parameter
			$.each( queryMap, function( key, value ) {
				var piece = key.slice( -7 );
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
				'content': content
			};

			if ( previewTitle ) {
				queryMap.title = previewTitle;
			} else {
				queryMap.title = generators[previewTitleGenerator]();
			}

			return queryMap;
		};

		// Reset the preview state if already in it
		if ( flowBoard.resetPreview( $this ) ) {
			// Special way of cancelling a request, other than returning false outright
			callback._abort = true;
		}

		return callback;
	};

	/**
	 * Triggers a preview of the given content.
	 * @param {Object} info (status:done|fail, $target: jQuery)
	 * @param {Object} data
	 * @param {jqXHR} jqxhr
	 * @returns {$.Promise}
	 */
	FlowBoardComponentPreviewMixin.UI.events.apiHandlers.preview = function( info, data, jqxhr ) {
		var revision, creator,
			$previewContainer,
			templateParams,
			$button = $( this ),
			$form = $button.closest( 'form' ),
			$cancelButton = $form.find('.mw-ui-button[data-role="cancel"]'),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $form ),
			$titleField = $form.find( 'input' ).filter( '[data-role=title]' ),
			$target = info.$target,
			username = $target.data( 'flow-username' ) || mw.user.getName(),
			id = Math.random(),
			previewTemplate = $target.data( 'flow-preview-template' ),
			contentNode = $target.data( 'flow-preview-node' ) || 'content';

		if ( info.status !== 'done' ) {
			// Error will be displayed by default, nothing else to wrap up
			return $.Deferred().reject().promise();
		}

		creator = {
			links: {
				userpage: {
					url: mw.util.getUrl( 'User:' + username ),
					// FIXME: Assume, as we don't know at this point...
					exists: true
				},
				talk: {
					url: mw.util.getUrl( 'User talk:' + username ),
					// FIXME: Assume, as we don't know at this point...
					exists: true
				},
				contribs: {
					url: mw.util.getUrl( 'Special:Contributions/' + username ),
					exists: true,
					title: username
				}
			},
			name: username || flowBoard.constructor.static.TemplateEngine.l10n( 'flow-anonymous' )
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
		$previewContainer = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_preview_warning'
		) ).children();

		// @todo Perhaps this should be done in each template, and not here?
		$previewContainer.addClass( 'flow-preview' );

		// Render this template with the preview data
		$previewContainer = $previewContainer.add(
			$( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
				previewTemplate,
				templateParams
			) ).children()
		);

		// Hide any input fields and anon warning
		$form.find( 'input, textarea, .flow-anon-warning' )
			.addClass( 'flow-preview-target-hidden' );

		// Insert the new preview before the form
		$target
			.parent( 'form' )
			.before( $previewContainer );

		// Hide cancel button on preview screen
		$cancelButton.hide();

		// Assign the reset-preview information for later use
		$button
			.data( 'flow-return-to-edit', {
				text: $button.text(),
				$nodes: $previewContainer
			} )
			.text( flowBoard.constructor.static.TemplateEngine.l10n( 'flow-preview-return-edit-post' ) )
			.one( 'click', function() {
				$cancelButton.show();
			} );

		return $.Deferred().resolve().promise();
	};

	// Mixin to FlowBoardComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentPreviewMixin );
}( jQuery, mediaWiki ) );
