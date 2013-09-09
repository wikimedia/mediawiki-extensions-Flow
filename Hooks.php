<?php

use Flow\Model\UUID;

class FlowHooks {
	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgEchoNotifications, $wgHooks, $wgEchoNotificationIcons;

		if ( isset( $wgEchoNotifications ) ) {
			$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'FlowHooks::getDefaultNotifiedUsers';

			$notificationTemplate = array(
				'category' => 'flow-discussion',
				'group' => 'other',
				'formatter-class' => 'FlowCommentFormatter',
				'icon' => 'flow-discussion',
			);

			$wgEchoNotifications['flow-new-topic'] = array(
				'title-message' => 'flow-notification-newtopic',
				'title-params' => array( 'user', 'flow-title', 'title', 'subject', 'topic-permalink' ),
				'payload' => array( 'comment-text' ),
			) + $notificationTemplate;

			$wgEchoNotifications['flow-post-reply'] = array(
				'title-message' => 'flow-notification-reply',
				'title-params' => array( 'user', 'subject', 'flow-title', 'title', 'post-permalink' ),
				'payload' => array( 'comment-text' ),
			) + $notificationTemplate;

			$wgEchoNotifications['flow-post-edited'] = array(
				'title-message' => 'flow-notification-edit',
				'title-params' => array( 'user', 'subject', 'flow-title', 'title', 'post-permalink' ),
			) + $notificationTemplate;

			// Saving for a rainy day
			// $wgEchoNotifications['flow-post-moderated'] = array(
			// 	'title-message' => 'flow-notification-moderated',
			// 	'title-params' => array( 'user', 'subject', 'flow-title', 'title', 'post-permalink' ),
			// ) + $notificationTemplate;

			$wgEchoNotifications['flow-topic-renamed'] = array(
				'title-message' => 'flow-notification-rename',
				'title-params' => array( 'user', 'topic-permalink', 'old-subject', 'new-subject', 'flow-title', 'title' ),
			) + $notificationTemplate;

			$wgEchoNotificationIcons['flow-discussion'] = array(
				'path' => 'Flow/modules/discussion/images/Talk.png',
			);

			$wgEchoNotificationCategories['flow-discussion'] = array(
				// 'echo-pref'
			);
		}
	}

	/**
	 * @param $updater DatabaseUpdater object
	 * @return bool true in all cases
	 */
	public static function getSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__;
		$baseSQLFile = "$dir/flow.sql";
		$updater->addExtensionTable( 'flow_revision', $baseSQLFile );

		require_once __DIR__.'/maintenance/FlowInsertDefaultDefinitions.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowInsertDefaultDefinitions' );

		return true;
	}

	/**
	 * Handler for UnitTestsList hook.
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 * @param &$files Array of unit test files
	 * @return bool true in all cases
	 */
	static function getUnitTests( &$files ) {
		$dir = dirname( __FILE__ ) . '/tests';
		//$files[] = "$dir/DiscussionParserTest.php";
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
		$container = Flow\Container::getContainer();
		$storage = $container['storage'];
		$extra = $event->getExtra();
		switch ( $event->getType() ) {
		case 'flow-new-topic':
			$title = $event->getTitle();
			if ( $title->getNamespace() == NS_USER_TALK ) {
				$users[] = User::newFromName( $title->getText() );
			}
			break;
		case 'flow-topic-renamed':
			$postId = $extra['topic-workflow'];
		case 'flow-post-reply':
		case 'flow-post-edited':
		case 'flow-post-moderated':
			if ( isset( $extra['reply-to'] ) ) {
				$postId = $extra['reply-to'];
			} elseif ( ! $postId ) {
				$postId = $extra['post-id'];
			}

			$post = $storage->find(
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
			break;
		default:
			// Do nothing
		}
		return true;
	}

	/**
	 * Add token type "flow", to generate edit tokens for Flow via
	 * api.php?action=tokens&type=flow
	 *
	 * @param array $tokenFunctions Array of callables for token types
	 * @return bool
	 */
	public static function onApiTokensGetTokenTypes( &$tokenFunctions ) {
		$flowToken = function() {
			global $wgUser, $wgFlowTokenSalt;
			return $wgUser->getEditToken( $wgFlowTokenSalt );
		};

		$tokenFunctions['flow'] = $flowToken;

		return true;
	}
}
