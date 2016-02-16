<?php

namespace Flow;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Conversion\Utils;
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

	public static function onBeforeCreateEchoEvent( &$notifs, &$categories, &$icons ) {
		$notifs += require __DIR__ . "/Notifications.php";
		$categories['flow-discussion'] = array(
			'priority' => 3,
			'tooltip' => 'echo-pref-tooltip-flow-discussion',
		);
		$icons['flow-new-topic'] = array(
			'path' => 'Flow/modules/notification/icon/flow-new-topic.svg',
		);
		$icons['flow-post-edited'] = array(
			'path' => 'Flow/modules/notification/icon/flow-post-edited.svg',
		);
		$icons['flow-topic-renamed'] = array(
			'path' => 'Flow/modules/notification/icon/flow-topic-renamed.svg',
		);
		$icons['flowusertalk-new-topic'] = array(
			'path' => 'Flow/modules/notification/icon/flowusertalk-new-topic.svg',
		);
	}

	/**
	 * Causes notifications to be fired for a Header-related event.
	 * @param  String $eventName The event that occurred.
	 * @param  array  $data Associative array of parameters.
	 * * revision: The PostRevision created by the action. Always required.
	 * * board-workflow: The Workflow object for the board. Always required.
	 * * timestamp: Original event timestamp, for imports. Optional.
	 * * extra-data: Additional data to pass along to Event extra.
	 * @return array Array of created EchoEvent objects.
	 * @throws FlowException When $data contains unexpected types/values
	 */
	public function notifyHeaderChange( $eventName, $data = array() ) {
		if ( !class_exists( 'EchoEvent' ) ) {
			return array();
		}

		if ( isset( $data['extra-data'] ) ) {
			$extraData = $data['extra-data'];
		} else {
			$extraData = array();
		}

		$revision = $data['revision'];
		if ( !$revision instanceof Header ) {
			throw new FlowException( 'Expected Header but received ' . get_class( $revision ) );
		}
		$boardWorkflow = $data['board-workflow'];
		if ( !$boardWorkflow instanceof Workflow ) {
			throw new FlowException( 'Expected Workflow but received ' . get_class( $boardWorkflow ) );
		}

		$user = $revision->getUser();
		$events = array();

		$extraData['content'] = Utils::htmlToPlaintext( $revision->getContent(), 200, $this->language );
		$extraData['revision-id'] = $revision->getRevisionId();
		$extraData['collection-id'] = $revision->getCollectionId();

		$info = array(
			'type' => $eventName,
			'agent' => $user,
			'title' => $boardWorkflow->getOwnerTitle(),
			'extra' => $extraData,
		);

		// Allow a specific timestamp to be set - useful when importing existing data
		if ( isset( $data['timestamp'] ) ){
			$info['timestamp'] = $data['timestamp'];
		}

		array_unshift( $events, EchoEvent::create( $info ) );

		return $events;
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

		$user = $revision->getUser();
		$mentionedUsers = $this->getMentionedUsers( $revision, $topicWorkflow->getOwnerTitle() );

		$extraData['revision-id'] = $revision->getRevisionId();
		$extraData['post-id'] = $revision->getPostId();
		$extraData['topic-workflow'] = $topicWorkflow->getId();
		$extraData['target-page'] = $topicWorkflow->getArticleTitle()->getArticleID();
		// pass along mentioned users to other notification, so it knows who to ignore
		$extraData['mentioned-users'] = $mentionedUsers;

		switch( $eventName ) {
			case 'flow-post-reply':
				$extraData += array(
					'reply-to' => $revision->getReplyToId(),
					'content' => Utils::htmlToPlaintext( $revision->getContent(), 200, $this->language ),
					'topic-title' => Utils::htmlToPlaintext( $topicRevision->getContent( 'topic-title-html' ), 200, $this->language ),
				);

				// if we're looking at the initial post (submitted along with the topic
				// title), we don't want to send the flow-post-reply notification,
				// because users will already receive flow-new-topic as well
				if ( $this->isFirstPost( $revision, $topicWorkflow ) ) {
					// if users were mentioned here, we'll want to make sure
					// that they weren't also mentioned in the topic title (in
					// which case they would get 2 notifications...)
					if ( $mentionedUsers ) {
						$mentionedInTitle = $this->getMentionedUsers( $topicRevision, $topicWorkflow->getArticleTitle() );
						$mentionedUsers = array_diff_key( $mentionedUsers, $mentionedInTitle );
						$extraData['mentioned-users'] = $mentionedUsers;
					}

					return $this->generateMentionEvent( $revision, $topicRevision, $topicWorkflow, $user, $mentionedUsers );
				}

			break;
			case 'flow-topic-renamed':
				$previousRevision = $revision->getCollection()->getPrevRevision( $revision );
				$extraData += array(
					'old-subject' => Utils::htmlToPlaintext( $previousRevision->getContent( 'topic-title-html' ), 200, $this->language ),
					'new-subject' => Utils::htmlToPlaintext( $revision->getContent( 'topic-title-html' ), 200, $this->language ),
				);
			break;
			case 'flow-post-edited':
				$extraData += array(
					'content' => Utils::htmlToPlaintext( $revision->getContent(), 200, $this->language ),
					'topic-title' => Utils::htmlToPlaintext( $topicRevision->getContent( 'topic-title-html' ), 200, $this->language ),
				);
			break;
		}

		$info = array(
			'type' => $eventName,
			'agent' => $user,
			'title' => $topicWorkflow->getOwnerTitle(),
			'extra' => $extraData,
		);

		// Allow a specific timestamp to be set - useful when importing existing data
		if ( isset( $data['timestamp'] ) ){
			$info['timestamp'] = $data['timestamp'];
		}

		$events = array( EchoEvent::create( $info ) );
		if ( $mentionedUsers ) {
			$events[] = $this->generateMentionEvent( $revision, $topicRevision, $topicWorkflow, $user, $mentionedUsers );
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
		if ( !class_exists( 'EchoEvent' ) ) {
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

		$mentionedUsers = $this->getMentionedUsers( $topicTitle, $topicWorkflow->getOwnerTitle() );

		$events = array();
		$events[] = EchoEvent::create( array(
			'type' => 'flow-new-topic',
			'agent' => $user,
			'title' => $boardWorkflow->getArticleTitle(),
			'extra' => array(
				'board-workflow' => $boardWorkflow->getId(),
				'topic-workflow' => $topicWorkflow->getId(),
				'post-id' => $firstPost ? $firstPost->getRevisionId() : null,
				'topic-title' => Utils::htmlToPlaintext( $topicTitle->getContent( 'topic-title-html' ), 200, $this->language ),
				'content' => $firstPost
					? Utils::htmlToPlaintext( $firstPost->getContent(), 200, $this->language )
					: null,
				// Force a read from master database since this could be a new page
				'target-page' => array(
					$topicWorkflow->getOwnerTitle()->getArticleID( Title::GAID_FOR_UPDATE ),
					$topicWorkflow->getArticleTitle()->getArticleID( Title::GAID_FOR_UPDATE ),
				),
				// pass along mentioned users to other notification, so it knows who to ignore
				// also look at users mentioned in first post: if there are any, this
				// (flow-new-topic) notification shouldn't go through (because they'll
				// already receive the mention notification)
				'mentioned-users' => $mentionedUsers + $this->getMentionedUsers( $firstPost, $topicWorkflow->getArticleTitle() ),
			)
		) );

		if ( $mentionedUsers ) {
			$events[] = $this->generateMentionEvent( $topicTitle, $topicTitle, $topicWorkflow, $user, $mentionedUsers );
		}

		return $events;
	}

	public function notifyFlowEnabledOnTalkpage( User $user ) {
		if ( !class_exists( 'EchoEvent' ) ) {
			// Nothing to do here.
			return array();
		}

		$events = array();
		$events[] = EchoEvent::create( array(
			'type' => 'flow-enabled-on-talkpage',
			'agent' => $user,
			'title' => $user->getTalkPage(),
			'extra' => array(
				'notifyAgent' => true,
			),
		) );

		return $events;
	}

	/**
	 * @param PostRevision $content The (post|topic) revision that contains the content of the mention
	 * @param PostRevision $topic Topic PostRevision object
	 * @param Workflow $workflow Topic Workflow object
	 * @param User $user User who created the new post
	 * @param array $mentionedUsers
	 * @return bool|EchoEvent
	 * @throws Exception\InvalidDataException
	 * @throws \MWException
	 */
	protected function generateMentionEvent( PostRevision $content, PostRevision $topic, Workflow $workflow, User $user, array $mentionedUsers ) {
		if ( count( $mentionedUsers ) === 0 ) {
			return false;
		}

		$title = $workflow->getOwnerTitle();

		return EchoEvent::create( array(
			'type' => 'flow-mention',
			'title' => $title,
			'extra' => array(
				// don't include topic content again if the notification IS in the title
				'content' => $content !== $topic ? Utils::htmlToPlaintext( $content->getContent(), 200, $this->language ) : '',
				'topic-title' => Utils::htmlToPlaintext( $topic->getContent( 'topic-title-html' ), 200, $this->language ),
				'post-id' => $content->getPostId(),
				'mentioned-users' => $mentionedUsers,
				'topic-workflow' => $workflow->getId(),
				'target-page' => $workflow->getArticleTitle()->getArticleID(),
				// lets us differentiate between different revision types
				'revision-type' => $content->getRevisionType(),
			),
			'agent' => $user,
		) );
	}

	/**
	 * Analyses a PostRevision to determine which users are mentioned.
	 *
	 * @param PostRevision $post The Post to analyse.
	 * @param Title $title
	 * @return int[] Array of user ids.
	 */
	protected function getMentionedUsers( PostRevision $post, Title $title ) {
		// At the moment, it is not possible to get a list of mentioned users from HTML
		//  unless that HTML comes from Parsoid. But VisualEditor (what is currently used
		//  to convert wikitext to HTML) does not currently use Parsoid.
		$wikitext = $post->getContentInWikitext();
		$mentions = $this->getMentionedUsersFromWikitext( $wikitext );

		// if this post had a previous revision (= this is an edit), we don't
		// want to pick up on the same mentions as in the previous edit, only
		// new mentions
		$previousRevision = $post->getCollection()->getPrevRevision( $post );
		if ( $previousRevision !== null ) {
			$previousWikitext = $previousRevision->getContentInWikitext();
			$previousMentions = $this->getMentionedUsersFromWikitext( $previousWikitext );
			$mentions = array_diff( $mentions, $previousMentions );
		}

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
	 * @param  Title $title The Title of the page that the comment is made on.
	 * @return int[] Array of user IDs
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
	 * @return User[] Array of User objects
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

			case 'flow-description-edited':
				$headerId = $event->getExtraParam( 'collection-id' );
				if ( $headerId instanceof UUID ) {
					$bundleString = $event->getType() . '-' . $headerId->getAlphadecimal();
				}
			break;
		}
		return true;
	}

	/**
	 * Get the owner of the page if the workflow belongs to a talk page
	 *
	 * @param string $topicId Topic workflow UUID
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

	/**
	 * @param PostRevision $revision
	 * @param Workflow $workflow
	 * @return bool
	 */
	protected function isFirstPost( PostRevision $revision, Workflow $workflow ) {
		$postId = $revision->getPostId();
		$workflowId = $workflow->getId();
		$replyToId = $revision->getReplyToId();

		// if the post is not a direct reply to the topic, it definitely can't be
		// first post
		if ( !$replyToId->equals( $workflowId ) ) {
			return false;
		}

		/*
		 * We don't want to go fetch the entire topic tree, so we'll use a crude
		 * technique to figure out if we're dealing with the first post: check if
		 * they were posted at (almost) the exact same time.
		 * If they're more than 1 second apart, it's very likely a not-first-post
		 * (or a very slow server, upgrade your machine!). False positives on the
		 * other side are also very rare: who on earth can refresh the page, read
		 * the post and write a meaningful reply in just 1 second? :)
		 */
		$diff = $postId->getTimestamp( TS_UNIX ) - $workflowId->getTimestamp( TS_UNIX );
		return $diff <= 1;
	}
}
