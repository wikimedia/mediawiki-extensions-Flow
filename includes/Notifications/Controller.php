<?php

namespace Flow;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Parsoid\Utils;
use EchoEvent;
use Language;
use Title;
use User;

class NotificationController {
	/**
	 * @var Language
	 */
	protected $language;

	/**
	 * @param Language $language
	 */
	public function __construct( Language $language ) {
		$this->language = $language;
	}

	/**
	 * Set up Echo notification for Flow extension
	 */
	public static function setup() {
		global $wgHooks,
			$wgEchoNotifications, $wgEchoNotificationIcons, $wgEchoNotificationCategories;

		$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'Flow\NotificationController::getDefaultNotifiedUsers';
		$wgHooks['EchoGetBundleRules'][] = 'Flow\NotificationController::onEchoGetBundleRules';

		/**
		 * Load notification definitions from file.
		 * @var $notifications array[]
		 */
		$wgEchoNotifications += require( __DIR__ . "/Notifications.php" );

		$wgEchoNotificationIcons['flow-discussion'] = array(
			'path' => array(
				'ltr' => 'Flow/modules/notification/icon/Talk-ltr.png',
				'rtl' => 'Flow/modules/notification/icon/Talk-rtl.png'
			)
		);

		$wgEchoNotificationCategories['flow-discussion'] = array(
			'priority' => 3,
			'tooltip' => 'echo-pref-tooltip-flow-discussion',
		);
	}

	/**
	 * Causes notifications to be fired for a Flow event.
	 * @param  String $eventName The event that occurred. Choice of:
	 * * flow-post-reply
	 * * flow-topic-renamed
	 * * flow-post-edited
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
	 * @throws FlowException When $data contains unexpected types/values
	 */
	public function notifyPostChange( $eventName, $data = array() ) {
		if ( !class_exists( 'EchoEvent' ) ) {
			return array();
		}

		if ( isset( $data['extra-data'] ) ) {
			$extraData = $data['extra-data'];
		} else {
			$extraData = array();
		}

		$revision = $data['revision'];
		if ( !$revision instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $revision ) );
		}
		$topicRevision = $data['topic-title'];
		if ( !$topicRevision instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $topicRevision ) );
		}
		$topicWorkflow = $data['topic-workflow'];
		if ( !$topicWorkflow instanceof Workflow ) {
			throw new FlowException( 'Expected Workflow but received ' . get_class( $topicWorkflow ) );
		}

		$title = $data['title'];
		$user = $revision->getUser();

		$extraData['revision-id'] = $revision->getRevisionId();
		$extraData['post-id'] = $revision->getPostId();
		$extraData['topic-workflow'] = $topicWorkflow->getId();
		$extraData['target-page'] = $topicWorkflow->getArticleTitle()->getArticleID();

		$newPost = null;
		switch( $eventName ) {
			case 'flow-post-reply':
				$replyTo = $data['reply-to'];
				if ( !$replyTo instanceof PostRevision ) {
					throw new FlowException( 'Expected PostRevision but received ' . get_class( $replyTo ) );
				}
				$replyToPostId = $replyTo->getPostId();
				$extraData += array(
					'reply-to' => $replyToPostId,
					'content' => Utils::htmlToPlaintext( $revision->getContent(), 200, $this->language ),
					'topic-title' => $this->language->truncate( trim( $topicRevision->getContent( 'wikitext' ) ), 200 ),
				);
				$newPost = array(
					'title' => $title,
					'user' => $user,
					'post' => $revision,
					'reply-to' => $replyToPostId,
					'topic-title' => $topicRevision,
					'topic-workflow' => $topicWorkflow,
				);

			break;
			case 'flow-topic-renamed':
				$extraData += array(
					'old-subject' => $this->language->truncate( trim( $topicRevision->getContent( 'wikitext' ) ), 200 ),
					'new-subject' => $this->language->truncate( trim( $revision->getContent( 'wikitext' ) ), 200 ),
				);
			break;
			case 'flow-post-edited':
				$extraData += array(
					'content' => Utils::htmlToPlaintext( $revision->getContent(), 200, $this->language ),
					'topic-title' => $this->language->truncate( trim( $topicRevision->getContent( 'wikitext' ) ), 200 ),
				);
			break;
		}

		$info = array(
			'type' => $eventName,
			'agent' => $user,
			'title' => $title,
			'extra' => $extraData,
		);

		// Allow a specific timestamp to be set - useful when importing existing data
		if ( isset( $data['timestamp'] ) ){
			$info['timestamp'] = $data['timestamp'];
		}

		$events = array( EchoEvent::create( $info ) );

		if ( $newPost ) {
			$events = array_merge( $events, $this->notifyNewPost( $newPost ) );
		}

		return $events;
	}

	/**
	 * Triggers notifications for a new topic.
	 * @param  array $params Associative array of parameters, all required:
	 * * board-workflow: Workflow object for the Flow board.
	 * * topic-workflow: Workflow object for the new Topic.
	 * * topic-title: PostRevision object for the "topic post", containing the
	 *    title.
	 * * first-post: PostRevision object for the first post, or null when no first post.
	 * * user: The User who created the topic.
	 * @return array Array of created EchoEvent objects.
	 * @throws FlowException When $params contains unexpected types/values
	 */
	public function notifyNewTopic( $params ) {
		if ( ! class_exists( 'EchoEvent' ) ) {
			// Nothing to do here.
			return array();
		}

		$topicWorkflow = $params['topic-workflow'];
		if ( !$topicWorkflow instanceof Workflow ) {
			throw new FlowException( 'Expected Workflow but received ' . get_class( $topicWorkflow ) );
		}
		$topicTitle = $params['topic-title'];
		if ( !$topicTitle instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $topicTitle ) );
		}
		$firstPost = $params['first-post'];
		if ( $firstPost !== null && !$firstPost instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $firstPost ) );
		}
		$user = $topicTitle->getUser();
		$boardWorkflow = $params['board-workflow'];
		if ( !$boardWorkflow instanceof Workflow ) {
			throw new FlowException( 'Expected Workflow but received ' . get_class( $boardWorkflow ) );
		}

		$events = array();
		$events[] = EchoEvent::create( array(
			'type' => 'flow-new-topic',
			'agent' => $user,
			'title' => $boardWorkflow->getArticleTitle(),
			'extra' => array(
				'board-workflow' => $boardWorkflow->getId(),
				'topic-workflow' => $topicWorkflow->getId(),
				'post-id' => $firstPost ? $firstPost->getRevisionId() : null,
				'topic-title' => Utils::htmlToPlaintext( $topicTitle->getContent(), 200, $this->language ),
				'content' => $firstPost
					? Utils::htmlToPlaintext( $firstPost->getContent(), 200, $this->language )
					: null,
				// Force a read from master database since this could be a new page
				'target-page' => array(
					$topicWorkflow->getOwnerTitle()->getArticleID( Title::GAID_FOR_UPDATE ),
					$topicWorkflow->getArticleTitle()->getArticleID( Title::GAID_FOR_UPDATE ),
				),
			)
		) );

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
	 * @throws FlowException When $data contains unexpected types/values
	 */
	protected function notifyNewPost( $data ) {
		// Handle mentions.
		$newRevision = $data['post'];
		if ( $newRevision !== null && !$newRevision instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $newRevision ) );
		}
		$topicRevision = $data['topic-title'];
		if ( !$topicRevision instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $topicRevision ) );
		}
		$title = $data['title'];
		if ( !$title instanceof \Title ) {
			throw new FlowException( 'Expected Title but received ' . get_class( $title ) );
		}
		$user = $data['user'];
		$topicWorkflow = $data['topic-workflow'];
		if ( !$topicWorkflow instanceof Workflow ) {
			throw new FlowException( 'Expected Workflow but received ' . get_class( $topicWorkflow ) );
		}
		$events = array();

		$mentionedUsers = $newRevision ? $this->getMentionedUsers( $newRevision, $title ) : array();

		if ( !$topicRevision instanceof PostRevision ) {
			throw new FlowException( 'Expected PostRevision but received: ' . get_class( $topicRevision ) );
		}

		if ( count( $mentionedUsers ) ) {
			$events[] = EchoEvent::create( array(
				'type' => 'flow-mention',
				'title' => $title,
				'extra' => array(
					'content' => $newRevision
						? Utils::htmlToPlaintext( $newRevision->getContent(), 200, $this->language )
						: null,
					'topic-title' => $this->language->truncate( trim( $topicRevision->getContent( 'wikitext' ) ), 200 ),
					'post-id' => $newRevision ? $newRevision->getPostId() : null,
					'mentioned-users' => $mentionedUsers,
					'topic-workflow' => $topicWorkflow->getId(),
					'target-page' => $topicWorkflow->getArticleTitle()->getArticleID(),
					'reply-to' => isset( $data['reply-to'] ) ? $data['reply-to'] : null
				),
				'agent' => $user,
			) );
		}

		return $events;
	}

	/**
	 * Analyses a PostRevision to determine which users are mentioned.
	 *
	 * @param PostRevision $post The Post to analyse.
	 * @param \Title $title
	 * @return User[] Array of User objects.
	 */
	protected function getMentionedUsers( $post, $title ) {
		// At the moment, it is not possible to get a list of mentioned users from HTML
		//  unless that HTML comes from Parsoid. But VisualEditor (what is currently used
		//  to convert wikitext to HTML) does not currently use Parsoid.
		$wikitext = $post->getContent( 'wikitext' );
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
	 * @param  User[] $mentions Array of User objects
	 * @param  PostRevision $post The Post that is being examined.
	 * @param  \Title $title The Title of the page that the comment is made on.
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
	 * Examines a wikitext string and finds users that were mentioned
	 * @param  string $wikitext
	 * @return array Array of User objects
	 */
	protected function getMentionedUsersFromWikitext( $wikitext ) {
		global $wgParser;

		$title = Title::newMainPage(); // Bogus title used for parser

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
			$user = User::newFromName( $dbk );
			if ( !$user || $user->isAnon() ) {
				continue;
			}

			$users[$user->getId()] = $user;
			// If more than 20 users are being notified this is probably a spam/attack vector.
			// Don't send any mention notifications
			if ( count( $users ) > 20 ) {
				return array();
			}
		}

		return $users;
	}

	/**
	 * Handler for EchoGetBundleRule hook, which defines the bundle rules for each notification
	 *
	 * @param $event EchoEvent
	 * @param $bundleString string Determines how the notification should be bundled
	 * @return boolean True for success
	 */
	public static function onEchoGetBundleRules( $event, &$bundleString ) {
		switch ( $event->getType() ) {
			case 'flow-new-topic':
				$board = $event->getExtraParam( 'board-workflow' );
				if ( $board instanceof UUID ) {
					$bundleString = $event->getType() . '-' . $board->getAlphadecimal();
				}
			break;

			case 'flow-post-reply':
			case 'flow-post-edited':
				$topic = $event->getExtraParam( 'topic-workflow' );
				if ( $topic instanceof UUID ) {
					$bundleString = $event->getType() . '-' . $topic->getAlphadecimal();
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
	 * @return bool
	 */
	public static function getDefaultNotifiedUsers( EchoEvent $event, &$users ) {
		$extra = $event->getExtra();
		switch ( $event->getType() ) {
		case 'flow-mention':
			$mentionedUsers = $extra['mentioned-users'];

			// Ignore mention if the user gets another notification
			// already from the same flow event
			$ids = array();
			$topic = $extra['topic-workflow'];
			if ( $topic instanceof UUID ) {
				$ids[$topic->getAlphadecimal()] = $topic;
			}
			if ( isset( $extra['reply-to'] ) ) {
				if ( $extra['reply-to'] instanceof UUID ) {
					$ids[$extra['reply-to']->getAlphadecimal()] = $extra['reply-to'];
				} else {
					wfDebugLog( 'Flow', __METHOD__ . ': Expected UUID but received ' . get_class( $extra['reply-to'] ) );
				}
			}
			$notifiedUsers = self::getCreatorsFromPostIDs( $ids );

			foreach( $mentionedUsers as $uid ) {
				if ( !isset( $notifiedUsers[$uid] ) ) {
					$users[$uid] = User::newFromId( $uid );
				}
			}
			break;
		case 'flow-topic-renamed':
			$users += self::getCreatorsFromPostIDs( array( $extra['topic-workflow'] ) );
			break;
		case 'flow-post-edited':
		case 'flow-post-moderated':
			if ( isset( $extra['reply-to'] ) ) {
				$postId = $extra['reply-to'];
			} else {
				$postId = $extra['post-id'];
			}
			if ( !$postId instanceof UUID ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Non-UUID value provided' );
				break;
			}

			$users += self::getCreatorsFromPostIDs( array( $postId ) );
			break;
		default:
			// Do nothing
		}
		return true;
	}

	/**
	 * Retrieves the post creators from a set of posts.
	 * @param  array  $posts Array of UUIDs or hex representations
	 * @return array Associative array, of user ID => User object.
	 */
	protected static function getCreatorsFromPostIDs( array $posts ) {
		$users = array();
		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );

		$user = new User;
		$actionPermissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );

		foreach ( $posts as $postId ) {
			$post = $storage->find(
				'PostRevision',
				array(
					'rev_type_id' => UUID::create( $postId )
				),
				array(
					'sort' => 'rev_id',
					'order' => 'DESC',
					'limit' => 1
				)
			);

			$post = reset( $post );

			if ( $post && $actionPermissions->isAllowed( $post, 'view' ) ) {
				$userid = $post->getCreatorId();
				if ( $userid ) {
					$users[$userid] = User::newFromId( $userid );
				}
			}
		}

		return $users;
	}

	/**
	 * Get the owner of the page if the workflow belongs to a talk page
	 *
	 * @param string|UUID topic workflow id
	 * @param array
	 * @return array Map from userid to User object
	 */
	protected static function getTalkPageOwner( $topicId ) {
		$talkUser = array();
		// Owner of talk page should always get a reply notification
		/** @var Workflow|null $workflow */
		$workflow = Container::get( 'storage' )
				->getStorage( 'Workflow' )
				->get( UUID::create( $topicId ) );
		if ( $workflow ) {
			$title = $workflow->getOwnerTitle();
			if ( $title->isTalkPage() ) {
				$user = User::newFromName( $title->getDBkey() );
				if ( $user && $user->getId() ) {
					$talkUser[$user->getId()] = $user;
				}
			}
		}
		return $talkUser;
	}
}
