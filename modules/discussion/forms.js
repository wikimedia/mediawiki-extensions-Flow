( function ( $, mw ) {
//	'use strict'; // @todo: uncomment once all code is actually strict

	mw.flow.discussion = {
		/**
		 * @param {Event} e
		 */
		init: function ( e ) {
			var $container = $( e.target );

			$( 'form.flow-newtopic-form' ).flow( 'setupEmptyDisabler',
				[ '.flow-newtopic-title' ],
				'.flow-newtopic-submit'
			);

			// Overload 'new topic' handler.
			$container.flow( 'setupFormHandler',
				'.flow-newtopic-submit',
				mw.flow.api.newTopic,
				function() {
					var $form = $( this ).closest( '.flow-newtopic-form' ),
						workflowParam = $container.flow( 'getWorkflowParameters' ),
						title = $form.find( '.flow-newtopic-title' ).val(),
						content = mw.flow.editor.getContent( $form.find( '.flow-newtopic-content' ) );

					return [ workflowParam, title, content ];
				},
				function ( workflowParam, title, content ) {
					return !!title;
				},
				function ( promise ) {
					promise
						.done( function ( output ) {
							$( output.rendered )
								.hide()
								.prependTo( $( '.flow-topics' ) )
								.trigger( 'flow_init' )
								.slideDown();
						} );
				}
			);

			// init post interaction
			$container.find( '.flow-post' ).addBack( '.flow-post' ).each( function () {
				/*
				 * Initialisation (registerInitFunction) is re-done after every
				 * change (e.g. adding a new topic), by .trigger( 'flow_init' )
				 *
				 * We're now creating JS objects by looping all DOM title nodes.
				 * We don't want to create multiple objects when re-initializing
				 * so let's mark initialised state in a data-attribute.
				 */
				if ( !$( this ).data( 'flow-initialised-post' ) ) {
					var postId = $( this ).parent().data( 'post-id' );
					new mw.flow.discussion.post( postId );

					$( this ).data( 'flow-initialised-post', true );
				}
			} );

			// init title interaction
			$container.find( '.flow-topic-container' ).addBack( '.flow-topic-container' ).each( function () {
				/*
				 * Initialisation (registerInitFunction) is re-done after every
				 * change (e.g. adding a new topic), by .trigger( 'flow_init' )
				 *
				 * We're now creating JS objects by looping all DOM title nodes.
				 * We don't want to create multiple objects when re-initializing
				 * so let's mark initialised state in a data-attribute.
				 */
				if ( !$( this ).data( 'flow-initialised-topic' ) ) {
					var topicId = $( this ).data( 'topic-id' );
					new mw.flow.discussion.topic( topicId );

					$( this ).data( 'flow-initialised-topic', true );
				}
			} );
		}
	};

	$( document ).flow( 'registerInitFunction', mw.flow.discussion.init );
} ( jQuery, mediaWiki ) );
