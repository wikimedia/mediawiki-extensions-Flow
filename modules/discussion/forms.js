( function ( $, mw ) {
	'use strict';

	mw.flow.discussion = {
		/**
		 * @param {Event} e
		 */
		init: function ( e ) {
			var $container = $( e.target ),
				$newTopic = $container.find( '.flow-new-topic-container' ).addBack( '.flow-new-topic-container' ),
				$topics = $container.find( '.flow-topic-container' ).addBack( '.flow-topic-container' ),
				$posts = $container.find( '.flow-post' ).addBack( '.flow-post' );

			// init new title interaction
			if ( $newTopic.length && !$newTopic.data( 'flow-initialised-new-topic' ) ) {
				new mw.flow.action.topic.new();

				$newTopic.data( 'flow-initialised-new-topic', true );
			}

			// init topic interaction
			$topics.each( function () {
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

			// init post interaction
			$posts.each( function () {
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

			// live bind tipsy edit title link to real edit title link
			// @Todo: is this just another ugly hack?
			if ( !$( document ).data( 'flow-tipsy-edit-title' ) ) {
				$( document )
					.data( 'flow-tipsy-edit-title', true )
					.on( 'click', '.tipsy .flow-edit-topic-link', function ( e ) {
						e.preventDefault();
						var $topic = $( '#flow-topic-' + $( this ).data( 'post-id' ) );

						$topic.find( '.flow-edit-topic-link' ).click();
						// close the tipsy
						$topic.find( '.flow-tipsy-open' ).each( function() {
							$( this ).removeClass( 'flow-tipsy-open' ).tipsy( 'hide' );
						} );
					} );
			}
		}
	};

	$( document ).flow( 'registerInitFunction', mw.flow.discussion.init );
} ( jQuery, mediaWiki ) );
