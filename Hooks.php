<?php

use Flow\Container;
use Flow\Model\UUID;

class FlowHooks {
	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgEchoNotifications;

		if ( isset( $wgEchoNotifications ) ) {
			Container::get( 'controller.notification' )->setup();
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
		if ( $updater->getDB()->getType() === 'sqlite' ) {
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sqlite.sql" );
		} else {
			// sqlite doesn't support alter table change, it also considers all types the same so
			// this patch doesn't matter to it.
			$updater->modifyExtensionField( 'flow_subscription', 'subscription_user_id', "$dir/db_patches/patch-subscription_user_id.sql" );
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sql" );
		}

		$updater->addExtensionIndex( 'flow_workflow', 'flow_workflow_lookup', "$dir/db_patches/patch-workflow_lookup_idx.sql" );

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

	public static function onOldChangesListRecentChangesLine( \ChangesList &$changesList, &$s, \RecentChange $rc, &$classes = array() ) {
		$source = $rc->getAttribute( 'rc_source' );
		if ( $source === null ) {
			$rcType = (int) $rc->getAttribute( 'rc_type' );
			if ( $rcType !== RC_FLOW ) {
				return true;
			}
		} elseif ( $source !== RC_SRC_FLOW ) {
			return true;
		}

		$line = Container::get( 'recentchanges.formatter' )->format( $changesList, $rc );

		if ( $line === false ) {
			return false;
		}

		$classes[] = 'flow-recentchanges-line';
		$s = $line;

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
		$container = Container::getContainer();
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
