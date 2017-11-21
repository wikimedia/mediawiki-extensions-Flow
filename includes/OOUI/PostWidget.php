<?php

namespace Flow\OOUI;

/**
 * A widget representing a simple post
 */
class PostWidget extends BaseUiWidget {
	use \OOUI\GroupElement;

	const WIDGET_NAME = 'PostWidget';

	/**
	 * @param array $revisionData Post revision data from the API
	 * @param array $config Configuration options
	 */
	public function __construct( $postID, $revisionData, $allTopicPosts, $allTopicRevisions, array $config = [] ) {
		global $wgLang;

		// Parent constructor
		parent::__construct( $config );

		$this->replies = $this->makeSection( 'replies' );

		// Traits
		$this->initializeGroupElement( array_merge( $config, [ 'group' => $this->replies ] ) );

		// Author info
		$userID = $revisionData['author']['id'];
		$userName = $revisionData['author']['name'];
		$authorName = new \OOUI\LabelWidget( [
			'label' => new \OOUI\HtmlSnippet( \Linker::userLink( $userID, $userName ) ),
			'classes' => [ 'mw-flow-ui-postWidget-user-name' ]
		] );
		$authorInfo = new \OOUI\LabelWidget( [
			'label' => new \OOUI\HtmlSnippet( \Linker::userToolLinks( $userID, $userName ) ),
			'classes' => [ 'mw-flow-ui-postWidget-user-links' ]
		] );
		$authorSection = $this->makeSection( 'user' );
		$authorSection->appendContent( $authorName, $authorInfo );

		// Action menu
		$actionMenu = new SimpleMenuWidget( [
			'menuItems' => [
				[
					'definition' => [
						'action' => 'edit',
						'id' => $postID,
					],
					'label' => 'Edit',
					'icon' => 'edit',
					'url' => ''
				],
				[
					// 'definition' => [
					// 	'action' => 'permalink', // We actually don't want to prevent click on permalink
					// 	'id' => $postID,
					// ],
					'action' => 'permalink',
					'label' => 'Permalink',
					'icon' => 'link',
					'url' => '',
					'addOnClick' => false,
				],
				'separator',
				[
					'definition' => [
						'action' => 'hide',
						'id' => $postID,
					],
					'label' => 'Hide',
					'icon' => '',
					'url' => ''
				],
				[
					'definition' => [
						'action' => 'delete',
						'id' => $postID,
					],
					'label' => 'Delete',
					'icon' => '',
					'url' => ''
				]
			],
			'classes' => [ 'mw-flow-ui-postWidget-actionMenu' ],
			'align' => 'backwards'
		] );

		$postHeader = $this->makeTable( [
			[
				'content' => $authorSection,
				'classes' => [ 'mw-flow-ui-postWidget-head-user' ]
			],
			[
				'content' => $actionMenu,
				'classes' => [ 'mw-flow-ui-postWidget-head-menu' ]
			]
		] );
		$postHeader->addClasses( [ 'mw-flow-ui-postWidget-head' ] );

		// Post content
		$content = $this->makeSection( 'content' );
		$content->appendContent( new \OOUI\HtmlSnippet( $revisionData['content']['content'] ) );

		// Bottom menu
		$formatter = \Flow\Container::get( 'formatter.revisionview' );
		$userObj = \User::newFromId( $revisionData['author']['id'] );
		$bottomMenu = $this->makeTable( [
			[
				'content' => [
					// TODO: This should really be done through a hook where
					// we allow other extensions to add their actions, including
					// the Thank extension. However, this doesn't seem to be the
					// way it's done right now, so we will hard-code "thanks" here
					// too for now
					new FlowButtonWidget( [
						'framed' => false,
						'label' => 'Reply', // TODO: i18n
						'definition' => [
							'widget' => 'post',
							'action' => 'reply',
							'id' => $postID,
						],
						'url' => '',
						'addOnClick' => true,
						'classes' => [ 'mw-flow-ui-postWidget-actions-reply' ],
					] ),
					new FlowButtonWidget( [
						'framed' => false,
						'label' => 'Thank', // TODO: i18n
						'definition' => [
							'widget' => 'post',
							'action' => 'thank',
							'id' => $postID,
						],
						'url' => '',
						'addOnClick' => true,
						'classes' => [ 'mw-flow-ui-postWidget-actions-thank' ],
					] ),
				],
				'classes' => [ 'mw-flow-ui-postWidget-bottomMenu-actions' ]
			], [
				'content' => [
					new \OOUI\LabelWidget( [
						'label' => $formatter->getHumanTimestamp( $revisionData['timestamp'] ),
						'classes' => [ 'mw-flow-ui-postWidget-bottomMenu-timestamp-ago' ]
					] ),
					new \OOUI\LabelWidget( [
						'label' => $wgLang->userTimeAndDate( $timestamp, $userObj ),
						'classes' => [ 'mw-flow-ui-postWidget-bottomMenu-timestamp-full' ]
					] )
				],
				'classes' => [ 'mw-flow-ui-postWidget-bottomMenu-timestamp' ]
			]
		] );

		// Replies
		$replies = [];
		foreach ( $revisionData['replies'] as $replyPostID ) {
			$replyRevisionID = $allTopicPosts[$replyPostID][ 0 ];
			$replyRevisionData = $allTopicRevisions[$replyRevisionID];
			$replies[] = new PostWidget( $replyPostID, $replyRevisionData );
		}
		$this->addItems( $replies );

		// Initialization
		$this
			->addClasses( [ 'mw-flow-ui-postWidget' ] )
			->appendContent(
				$postHeader,
				$content,
				$bottomMenu,
				$this->replies
			);

		// TODO: Add replies

		// if ( isset( $config['items'] ) ) {
		// 	$this->addItems( $config['items'] );
		// }
	}
}
