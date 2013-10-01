<?php

namespace Flow;

use EchoEvent;
use Flow\Container;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use MWException;
use User;

class NotificationController {
	public function setup() {
		global $wgEchoNotifications, $wgHooks, $wgEchoNotificationIcons, $wgEchoNotificationCategories;
		$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'Flow\NotificationController::getDefaultNotifiedUsers';
		// @Todo - put this hook in controller as well?
		$wgHooks['EchoGetBundleRules'][] = 'Flow\NotificationController::onEchoGetBundleRules';

		// Load notification definitions from file.
		require( __DIR__ . "/Notifications.php" );
		$wgEchoNotifications += $notifications;

		$wgEchoNotificationIcons['flow-discussion'] = array(
			'path' => 'Flow/modules/discussion/images/Talk.png',
		);

		$wgEchoNotificationCategories['flow-discussion'] = array(
			'priority' => 3,
			'tooltip' => 'echo-pref-tooltip-flow-discussion',
		);
	}

	/**
	 * Causes notifications to be fired for a Flow event.
	 * @param  String $eventName The event that ocurred. Choice of:
	 * * post-reply
	 * * topic-renamed
	 * * post-edited
	 * @param  array  $data Associative array of parameters.
	 * * user: The user who made the change. Always required.
	 * * revision: The PostRevision created by the action. Always required.
	 * * title: The Title on which this Topic sits. Always required.
	 * * topic-workflow: The Workflow object for the topic. Always required.
	 * * reply-to: The UUID of the post that is being replied to. Required for replies.
	 * * topic-title: The Title of the Topic that the post belongs to. Required except for topic renames.
	 * * old-subject: The old subject of a Topic. Required for topic renames.
	 * * new-subject: The new subject of a Topic. Required for topic renames.
	 * @return array Array of created EchoEvent objects.
	 */
	public function notifyPostChange(
		$eventName,
		$data = array()
	) {
		if ( ! class_exists( 'EchoEvent' ) ) {
			// Nothing to do here.
			return;
		}

		$events = array();
		$title = $data['title'];
		$user = $data['user'];

		$extraData = array();

		$revision = $data['revision'];
		$topicTitle = $data['topic-title'];
		$topicWorkflow = $data['topic-workflow'];

		$extraData['revision-id'] = $revision->getRevisionId();
		$extraData['post-id'] = $revision->getPostId();
		$extraData['topic-workflow'] = $topicWorkflow->getId();

		switch( $eventName ) {
			case 'flow-post-reply':
				$replyToPost = $data['reply-to'];
				$extraData += array(
					'reply-to' => $replyToPost->getPostId(),
					'content' => $revision->getContent(),
					'topic-title' => $topicTitle,
				);
			break;
			case 'flow-topic-renamed':
				$extraData += array(
					'old-subject' => $data['old-subject'],
					'new-subject' => $data['old-subject'],
				);
			break;
			case 'flow-post-edited':
				$extraData += array(
					'content' => $revision->getContent(),
					'topic-title' => $topicTitle,
				);
			break;
		}

		$events = array(
			EchoEvent::create( array(
				'type' => $eventName,
				'agent' => $user,
				'title' => $title,
				'extra' => $extraData,
			) ),
		);

		if ( $eventName == 'flow-post-reply' ) {
			$events = array_merge( $events,
				$this->notifyNewPost( array(
					'title' => $title,
					'user' => $user,
					'post' => $revision,
					'topic-title' => $topicTitle,
					'topic-workflow' => $topicWorkflow,
				) )
			);
		}

		return $events;
	}

	/**
	 * Triggers notifications for a new topic.
	 * @param  array $params Associative array of parameters, all required:
	 * * board-workflow: Workflow object for the Flow board.
	 * * topic-workflow: Workflow object for the new Topic.
	 * * title-post: PostRevision object for the "topic post", containing the
	 *    title.
	 * * first-post: PostRevision object for the first post.
	 * * user: The User who created the topic.
	 * @return array Array of created EchoEvent objects.
	 */
	public function notifyNewTopic( $params ) {
		if ( ! class_exists( 'EchoEvent' ) ) {
			// Nothing to do here.
			return;
		}

		$topicWorkflow = $params['topic-workflow'];
		$topicPost = $params['title-post'];
		$firstPost = $params['first-post'];
		$user = $params['user'];
		$boardWorkflow = $params['board-workflow'];

		$events[] = EchoEvent::create( array(
			'type' => 'flow-new-topic',
			'agent' => $user,
			'title' => $boardWorkflow->getArticleTitle(),
			'extra' => array(
				'board-workflow' => $boardWorkflow->getId(),
				'topic-workflow' => $topicWorkflow->getId(),
				'post-id' => $firstPost->getRevisionId(),
				'topic-title' => $topicPost->getContent(),
				'content' => $firstPost->getContent(),
			)
		) );

		$events = array_merge( $events,
			$this->notifyNewPost( array(
				'title' => $boardWorkflow->getArticleTitle(),
				'user' => $user,
				'post' => $firstPost,
				'topic-title' => $topicPost->getContent('html'),
				'topic-workflow' => $topicWorkflow,
			) )
		);

		return $events;
	}

	/**
	 * Called when a new Post is added, whether it be a new topic or a reply.
	 * Do not call directly, use notifyPostChange for new replies.
	 * @param  array $data Associative array of parameters, all required:
	 * * title: Title for the page on which the new Post sits.
	 * * user: User who created the new Post.
	 * * post: The Post that was created.
	 * * topic-title: The title for the Topic.
	 * @return array Array of created EchoEvent objects.
	 */
	protected function notifyNewPost( $data ) {
		// Handle mentions.
		$newRevision = $data['post'];
		$title = $data['title'];
		$user = $data['user'];
		$topicWorkflow = $data['topic-workflow'];
		$events = array();

		$mentionedUsers = $this->getMentionedUsers( $newRevision, $title );

		if ( count( $mentionedUsers ) ) {
			$events[] = EchoEvent::create( array(
				'type' => 'flow-mention',
				'title' => $title,
				'extra' => array(
					'content' => $newRevision->getContent(),
					'topic-title' => $data['topic-title'],
					'post-id' => $newRevision->getPostId(),
					'mentioned-users' => $mentionedUsers,
					'topic-workflow' => $topicWorkflow->getId(),
				),
				'agent' => $user,
			) );
		}

		return $events;
	}

	/**
	 * Analyses a PostRevision to determine which users are mentioned.
	 * @param  PostRevision $post The Post to analyse.
	 * @return array Array of User objects.
	 */
	protected function getMentionedUsers( $post, $title ) {
		// At the moment, it is not possible to get a list of mentioned users from HTML
		//  unless that HTML comes from Parsoid. But VisualEditor (what is currently used
		//  to convert wikitext to HTML) does not currently use Parsoid.
		$wikitext = $post->getContent( null, 'wikitext' );
		$mentions = $this->getMentionedUsersFromWikitext( $wikitext );
		$notifyUsers = $this->filterMentionedUsers( $mentions, $post, $title );

		return $notifyUsers;
	}

	/**
	 * Process an array of users linked to in a comment into a list of users
	 * who should actually be notified.
	 *
	 * Removes duplicates, anonymous users, self-mentions, and mentions of the
	 * owner of the talk page
	 * @param  array $mentions Array of User objects
	 * @param  PostRevision $post The Post that is being examined.
	 * @param  Title $title The Title of the page that the comment is made on.
	 * @return array Array of user IDs
	 */
	protected function filterMentionedUsers( $mentions, PostRevision $post, $title ) {
		$outputMentions = array();
		global $wgFlowMaxMentionCount;

		foreach( $mentions as $mentionedUser ) {
			// Don't notify anonymous users
			if ( $mentionedUser->isAnon() ) {
				continue;
			}

			// Don't notify the user who made the post
			if ( $mentionedUser->getId() == $post->getUserId() ) {
				continue;
			}

			if ( count( $outputMentions ) > $wgFlowMaxMentionCount ) {
				break;
			}

			$outputMentions[$mentionedUser->getId()] = $mentionedUser->getId();
		}

		return $outputMentions;
	}

	/**
	 * Examines an HTML string from Parsoid and finds users who were mentioned.
	 * @todo Implement
	 * @param  string $html
	 * @return array Array of User objects.
	 */
	protected function getMentionedUsersFromHtml( $html ) {
		throw new \MWException( "Currently, it is not possible to extract a list of mentioned users from HTML" );
	}

	/**
	 * Examines a wikitext string and finds users that were mentioned
	 * @param  string $wikitext
	 * @return array Array of User objects
	 */
	protected function getMentionedUsersFromWikitext( $wikitext ) {
		global $wgParser;

		$title = \SpecialPage::getTitleFor( 'Flow' );

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $wikitext, $title, $options );

		$links = $output->getLinks();

		if ( ! isset( $links[NS_USER] ) || ! is_array( $links[NS_USER] ) ) {
			// Nothing
			return array();
		}

		$users = array();
		foreach ( $links[NS_USER] as $dbk => $page_id ) {
			$users[] = User::newFromName( $dbk );
		}

		return $users;
	}

	/**
	 * Handler for EchoGetBundleRule hook, which defines the bundle rules for each notification
	 * @param $event EchoEvent
	 * @param $bundleString string Determines how the notification should be bundled
	 */
	public static function onEchoGetBundleRules( $event, &$bundleString ) {
		switch ( $event->getType() ) {
			case 'flow-post-reply':
				$extra = $event->getExtra();

				if ( isset( $extra['reply-to'] ) ) {
					$postId = $extra['reply-to'];
				} elseif ( isset( $extra['post-id'] ) ) {
					$postId = $extra['post-id'];
				} else {
					$postId = null;
				}

				if ( $postId ) {
					$container = Container::getContainer();
					$treeRepository = $container['repository.tree'];
					$rootId = $treeRepository->findRoot( $postId );
					$bundleString = 'flow-post-reply-' . $rootId->getHex();
				}
			break;
		}
		return true;
	}

	/**
	 * Handler for EchoGetDefaultNotifiedUsers hook
	 *  Returns a list of User objects in the second param
	 *
	 * @param $event EchoEvent being triggered
	 * @param &$users Array of User objects.
	 * @return true
	 */
	public static function getDefaultNotifiedUsers( EchoEvent $event, &$users ) {
		$container = Container::getContainer();
		$storage = $container['storage'];
		$extra = $event->getExtra();
		switch ( $event->getType() ) {
		case 'flow-mention':
			$mentionedUsers = $extra['mentioned-users'];

			foreach( $mentionedUsers as $uid ) {
				$users[$uid] = User::newFromId( $uid );
			}
			break;
		case 'flow-new-topic':
			$title = $event->getTitle();
			if ( $title->getNamespace() == NS_USER_TALK ) {
				$users[] = User::newFromName( $title->getText() );
			}
			break;
		case 'flow-topic-renamed':
			$users += self::getUserForPost( array( $extra['topic-workflow'] ) );
			break;
		case 'flow-post-reply':
		case 'flow-post-edited':
		case 'flow-post-moderated':
			if ( isset( $extra['reply-to'] ) ) {
				$postId = $extra['reply-to'];
			} else {
				$postId = $extra['post-id'];
			}

			$ids = array( $postId );
			$container = Container::getContainer();
			$treeRepository = $container['repository.tree'];
			$rootId = $treeRepository->findRoot( $postId );
			if ( $rootId->getBinary() != $postId->getBinary() ) {
				$ids[] = $rootId;
			}
			$users += self::getUserForPost( $ids );
			break;
		default:
			// Do nothing
		}
		return true;
	}

	protected static function getUserForPost( array $posts ) {
		$users = array();
		$container = Container::getContainer();
		foreach ( $posts as $postId ) {
			$post = $container['storage']->find(
				'PostRevision',
				array(
					'tree_rev_descendant_id' => UUID::create( $postId )
				),
				array(
					'sort' => 'rev_id',
					'order' => 'DESC',
					'limit' => 1
				)
			);

			$post = reset( $post );

			if ( $post ) {
				$user = User::newFromName( $post->getCreatorName() );

				if ( $user && !$user->isAnon() ) {
					$users[$user->getId()] = $user;
				}
			}
		}
		return $users;
        }
}