<?php

use Flow\Model\UUID;

class FlowHooks {
	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgEchoNotifications, $wgHooks, $wgEchoNotificationIcons, $wgEchoNotificationCategories;

		if ( isset( $wgEchoNotifications ) ) {
			$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'FlowHooks::getDefaultNotifiedUsers';
			$wgHooks['EchoGetBundleRules'][] = 'FlowHooks::onEchoGetBundleRules';

			$notificationTemplate = array(
				'category' => 'flow-discussion',
				'group' => 'other',
				'formatter-class' => 'FlowCommentFormatter',
				'icon' => 'flow-discussion',
			);

			$wgEchoNotifications['flow-new-topic'] = array(
				'title-message' => 'flow-notification-newtopic',
				'title-params' => array( 'agent', 'flow-title', 'title', 'subject', 'topic-permalink' ),
				'payload' => array( 'comment-text' ),
			) + $notificationTemplate;

			$wgEchoNotifications['flow-post-reply'] = array(
				'primary-link' => array( 'message' => 'flow-notification-link-text-view-post', 'destination' => 'flow-post' ),
				'secondary-link' => array( 'message' => 'flow-notification-link-text-view-board', 'destination' => 'flow-board' ),
				'title-message' => 'flow-notification-reply',
				'title-params' => array( 'agent', 'subject', 'flow-title', 'title', 'post-permalink' ),
				'bundle' => array( 'web' => true, 'email' => false ),
				'bundle-message' => 'flow-notification-reply-bundle',
				'bundle-params' => array( 'agent', 'subject', 'title', 'post-permalink', 'agent-other-display', 'agent-other-count' ),
				'email-subject-message' => 'flow-notification-reply-email-subject',
				'email-subject-params' => array( 'agent' ),
				'email-body-batch-message' => 'flow-notification-reply-email-batch-body',
				'email-body-batch-params' => array( 'agent', 'subject', 'title' ),
				'email-body-batch-bundle-message' => 'flow-notification-reply-email-batch-bundle-body',
				'email-body-batch-bundle-params' => array( 'agent', 'subject', 'title', 'agent-other-display', 'agent-other-count' ),
				'payload' => array( 'comment-text' ),
			) + $notificationTemplate;

			$wgEchoNotifications['flow-post-edited'] = array(
				'title-message' => 'flow-notification-edit',
				'title-params' => array( 'agent', 'subject', 'flow-title', 'title', 'post-permalink' ),
			) + $notificationTemplate;

			// Saving for a rainy day
			// $wgEchoNotifications['flow-post-moderated'] = array(
			// 	'title-message' => 'flow-notification-moderated',
			// 	'title-params' => array( 'agent', 'subject', 'flow-title', 'title', 'post-permalink' ),
			// ) + $notificationTemplate;

			$wgEchoNotifications['flow-topic-renamed'] = array(
				'title-message' => 'flow-notification-rename',
				'title-params' => array( 'agent', 'topic-permalink', 'old-subject', 'new-subject', 'flow-title', 'title' ),
			) + $notificationTemplate;

			$wgEchoNotificationIcons['flow-discussion'] = array(
				'path' => 'Flow/modules/discussion/images/Talk.png',
			);

			$wgEchoNotificationCategories['flow-discussion'] = array(
				'priority' => 3,
				'tooltip' => 'echo-pref-tooltip-flow-discussion',
			);
		}
	}

	/**
	 * Hook: LoadExtensionSchemaUpdates
	 *
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
	 * After completing setup, adds Special namespace to VE's supported
	 * namespaces, so we can (ab)use it's API to convert wikitext<->html.
	 *
	 * Hook: SetupAfterCache
	 *
	 * @return bool
	 */
	public static function onSetupAfterCache() {
		global $wgVisualEditorNamespaces;
		if ( $wgVisualEditorNamespaces && !in_array( -1, $wgVisualEditorNamespaces ) ) {
			$wgVisualEditorNamespaces[] = -1;
		}

		return true;
	}

	/**
	 * Hook: UnitTestsList
	 *
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
			} elseif ( !isset( $postId ) || !$postId ) {
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
	 * Handler for EchoGetBundleRule hook, which defines the bundle rule for each notification
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
					$bundleString = 'flow-post-reply-' . $postId->getHex();
				}
			break;
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

	/**
	 * Overrides MediaWiki::performAction
	 * @param  OutputPage $output
	 * @param  Article $article
	 * @param  Title $title
	 * @param  User $user
	 * @param  Request $request
	 * @param  MediaWiki $wiki
	 * @return boolean True to continue processing as normal, False to abort.
	 */
	public static function onPerformAction( $output, $article, $title, $user, $request, $wiki ) {
		$container = Flow\Container::getContainer();
		$occupationController = $container['occupation_controller'];

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$view = new Flow\View(
				$container['templating'],
				$container['url_generator'],
				RequestContext::getMain()
			);

			$workflowId = $request->getVal( 'workflow' );
			$action = $request->getVal( 'action', 'view' );

			$loader = $container['factory.loader.workflow']
				->createWorkflowLoader( $title, UUID::create( $workflowId ) );

			$view->show( $loader, $action );
			return false;
		}

		return true;
	}
}
