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
			$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'Flow\NotificationController::getDefaultNotifiedUsers';

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
				'title-message' => 'flow-notification-reply',
				'title-params' => array( 'agent', 'subject', 'flow-title', 'title', 'post-permalink' ),
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

			$wgEchoNotifications['flow-mention'] = array(
				'title-message' => 'flow-notification-mention',
				'title-params' => array( 'agent', 'post-permalink', 'subject', 'flow-title', 'title' ),
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
