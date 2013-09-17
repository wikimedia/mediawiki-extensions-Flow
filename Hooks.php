<?php

use Flow\Model\UUID;

class FlowHooks {
	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgEchoNotifications;

		if ( isset( $wgEchoNotifications ) ) {
			$container = Flow\Container::getContainer();
			$container['controller.notification']->setup();
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
		$updater->addExtensionField( 'flow_revision', 'rev_last_edit_id', "$dir/db_patches/patch-revision_last_editor.sql" );
		$updater->modifyExtensionField( 'flow_subscription', 'subscription_user_id', "$dir/db_patches/patch-subscription_user_id.sql" );

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
