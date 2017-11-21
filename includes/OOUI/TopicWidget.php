<?php

namespace Flow\OOUI;

/**
 * A widget representing a topic
 */
class TopicWidget extends BaseUiWidget {
	use \OOUI\GroupElement;

	const WIDGET_NAME = 'TopicWidget';
	/**
	 * @param array $topicAPIData Topic data from the API
	 * @param array $config Configuration options
	 */
	public function __construct( $APIData, array $config = [] ) {
		// Parent constructor
		parent::__construct( $config );

		$this->posts = $this->makeSection( 'posts' );

		// Traits
		$this->initializeGroupElement( array_merge( $config, [ 'group' => $this->posts ] ) );

		// TODO: We should have a simple output so both PHP and JS can read
		// a sensible construct of data ratehr than go back and forth looking
		// for the references to posts and their first-order revisions.
		// For rendering there's no reason to have the current API structure
		// we can create a better representation in a submodule of the API
		$topicPostID = $APIData['blocks']['topic']['roots'][ 0 ];
		$topicRevisionID = $APIData['blocks']['topic']['posts'][ $topicPostID ][ 0 ];
		$topicRevisionData = $APIData['blocks']['topic']['revisions'][ $topicRevisionID ];

		$title = new \OOUI\Tag( 'h2' );
		$title->addClasses( [ 'mw-flow-ui-topicWidget-header-title-content' ] );
		$title->appendContent( new \OOUI\HtmlSnippet( $topicRevisionData['content']['content'] ) );

		// Meta
		$formatter = \Flow\Container::get( 'formatter.revisionview' );
		$meta = $this->makeSection( 'meta' );
		$meta->appendContent( [
			new \OOUI\LabelWidget( [
				'label' => wfMessage( 'flow-topic-comments' )->params( (int)$topicRevisionData['size']['new'] )->parse(),
			] ),
			new \OOUI\LabelWidget( [
				// 'label' => $formatter->getHumanTimestamp( $topicRevisionData['last_updated'] ),
			] ),
		] );

		// Build header
		$header = $this->makeTable( [
			[
				'content' => [ $title, $meta ],
				'classes' => [ 'mw-flow-ui-topicWidget-header-title' ]
			],
			[
				'content' => [
					new \OOUI\ButtonWidget( [
						'definition' => [
							'action' => 'watch',
							'id' => $topicPostID,
						],
						'framed' => false,
						'title' => $topicRevisionData['isWatched'] ? $topicRevisionData['links']['unwatch-topic']['title'] : $topicRevisionData['links']['watch-topic']['title'],
						'icon' => $topicRevisionData['isWatched'] ? 'unStar' : 'star',
						'href' => $topicRevisionData['isWatched'] ? $topicRevisionData['links']['unwatch-topic']['url'] : $topicRevisionData['links']['watch-topic']['url'],
						'classes' => [ 'mw-flow-ui-topicWidget-header-actions-star' ],
					] ),
					new SimpleMenuWidget( [
						'menuItems' => [
							[
								'definition' => [
									'action' => 'edit',
									'id' => $topicRevisionID,
								],
								'label' => $topicRevisionData['actions']['edit']['text'],
								'icon' => 'edit',
								'href' => $topicRevisionData['actions']['edit']['url']
							],
							[
								'label' => 'Permalink', // TODO: i18n
								'icon' => 'link',
								'href' => $topicRevisionData['links']['topic']['url'],
								'addOnClick' => false, // Do not prevent click on permalink
							],
							'separator',
							[
								'definition' => [
									'action' => 'hide',
									'id' => $topicRevisionID,
								],
								'label' => $topicRevisionData['actions']['hide']['text'],
								'href' => $topicRevisionData['actions']['hide']['url']
							],
							[
								'definition' => [
									'action' => 'delete',
									'id' => $topicRevisionID,
								],
								'label' => $topicRevisionData['actions']['delete']['text'],
								'href' => $topicRevisionData['actions']['delete']['url']
							]
						],
						'classes' => [ 'mw-flow-ui-topicWidget-header-actions-menu' ],
						'align' => 'backwards'
					] )
				],
				'classes' => [ 'mw-flow-ui-topicWidget-header-actions' ]
			]
		] );
		$header->addClasses( [ 'mw-flow-ui-topicWidget-header' ] );

		// Posts
		$topicReplies = $topicRevisionData['replies'];
		$postWidgets = [];
		foreach ( $topicReplies as $postID ) {
			$revisionID = $APIData['blocks']['topic']['posts'][ $postID ][ 0 ];
			$revisionData = $APIData['blocks']['topic']['revisions'][ $revisionID ];

			$postWidgets[] = new PostWidget(
				$postID,
				$revisionData,
				// TODO: The structure here is really messy, which is why
				// we have to send the post widgets the full structure
				// rather than a smaller one... we should clean this up
				// in the API responses
				$APIData['blocks']['topic']['posts'],
				$APIData['blocks']['topic']['revisions']
			);
		}
		$this->addItems( $postWidgets );

		// Initialization
		$this
			->addClasses( [ 'mw-flow-ui-topicWidget' ] )
			->appendContent(
				$header,
				$content,
				$bottomMenu,
				$this->posts
			);
	}
}