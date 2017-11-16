<?php

namespace Flow\OOUI;

/**
 * A widget representing a simple post
 */
class PostWidget extends \OOUI\Widget {
	use \OOUI\GroupElement;

	/**
	 * @param array $revisionData Post revision data from the API
	 * @param array $config Configuration options
	 */
	public function __construct( $revisionData, $allTopicPosts, $allTopicRevisions, array $config = [] ) {
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
					'label' => 'Edit',
					'icon' => 'edit',
					'url' => ''
				],
				[
					'label' => 'Permalink',
					'icon' => 'link',
					'url' => ''
				],
				'separator',
				[
					'label' => 'Hide',
					'icon' => '',
					'url' => ''
				],
				[
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
					new \OOUI\ButtonWidget( [
						'framed' => false,
						'label' => 'Reply', // TODO: i18n
						'url' => '',
					] ),
					new \OOUI\ButtonWidget( [
						'framed' => false,
						'label' => 'Thank', // TODO: i18n
						'url' => '',
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
			$replies[] = new PostWidget( $replyRevisionData );
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

	protected function makeTable( $cells ) {
		$table = new \OOUI\Tag( 'div' );
		$table->addClasses( [ 'mw-flow-ui-table' ] );

		$row = new \OOUI\Tag( 'div' );
		$row->addClasses( [ 'mw-flow-ui-row' ] );

		foreach ( $cells as $cellData ) {
			$cell = new \OOUI\Tag( 'div' );
			if ( isset( $cellData['classes'] ) ) {
				$cell->addClasses( $cellData['classes'] );
				$cell->addClasses( [ 'mw-flow-ui-cell' ] );
			}
			if ( isset( $cellData['content'] ) ) {
				$cell->appendContent( $cellData['content'] );
			}

			$row->appendContent( $cell );
		}

		$table->appendContent( $row );

		return $table;
	}

	protected function makeSection( $name, $class = '' ) {
		$tag = new \OOUI\Tag( 'div' );

		$class = $class ? $class : 'mw-flow-ui-postWidget-' . $name;
		$tag->addClasses( [ $class ] );
		return $tag;
	}
}
