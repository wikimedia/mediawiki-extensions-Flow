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
		$updater->addExtensionField( 'flow_revision', 'rev_mod_reason', "$dir/db_patches/patch-moderation_reason.sql" );
		if ( $updater->getDB()->getType() === 'sqlite' ) {
			$updater->modifyExtensionField( 'flow_summary_revision', 'summary_workflow_id', "$dir/db_patches/patch-summary2header.sqlite.sql" );
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sqlite.sql" );
		} else {
			// sqlite doesn't support alter table change, it also considers all types the same so
			// this patch doesn't matter to it.
			$updater->modifyExtensionField( 'flow_subscription', 'subscription_user_id', "$dir/db_patches/patch-subscription_user_id.sql" );
			// renames columns, alternate patch is above for sqlite
			$updater->modifyExtensionField( 'flow_summary_revision', 'summary_workflow_id', "$dir/db_patches/patch-summary2header.sql" );
			// rename rev_change_type -> rev_comment, alternate patch is above for sqlite
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sql" );
		}

		$updater->addExtensionIndex( 'flow_workflow', 'flow_workflow_lookup', "$dir/db_patches/patch-workflow_lookup_idx.sql" );
		$updater->addExtensionIndex( 'flow_topic_list', 'flow_topic_list_topic_id', "$dir/db_patches/patch-topic_list_topic_id_idx.sql" );
		$updater->modifyExtensionField( 'flow_revision', 'rev_change_type', "$dir/db_patches/patch-rev_change_type_update.sql" );
		$updater->modifyExtensionField( 'recentchanges', 'rc_source', "$dir/db_patches/patch-rc_source.sql" );

		require_once __DIR__.'/maintenance/FlowInsertDefaultDefinitions.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowInsertDefaultDefinitions' );

		require_once __DIR__.'/maintenance/FlowUpdateRecentChanges.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateRecentChanges' );

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
		} elseif ( $source !== Flow\Data\RecentChanges::SRC_FLOW ) {
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

	/**
	 * Regular article-based checks are useless for Flow-enabled pages.
	 * Whis will make sure we won't have redlinks for Flow boards.
	 *
	 * @param Title $title
	 * @param bool $isKnown
	 * @return bool
	 */
	public static function onTitleIsAlwaysKnown( Title $title, &$isKnown) {
		$container = Container::getContainer();
		$occupationController = $container['occupation_controller'];

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$loader = $container['factory.loader.workflow']
				->createWorkflowLoader( $title );
			$workflow = $loader->getWorkflow();

			$isKnown = !$workflow->isNew();
		}

		return true;
	}

	/**
	 * Regular talk page "Create source" and "Add topic" links are quite useless
	 * in the context of Flow boards. Let's get rid of them.
	 *
	 * @param SkinTemplate $template
	 * @param array $links
	 * @return bool
	 */
	public static function onSkinTemplateNavigation( SkinTemplate &$template, &$links ) {
		$title = $template->getTitle();

		$container = Container::getContainer();
		$occupationController = $container['occupation_controller'];

		// if Flow is enabled on this talk page, overrule talk page red link
		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$skname = $template->skinname;

			global $wgRequest;
			$selected = $wgRequest->getVal( 'action' ) == 'board-history';
			$links['views'] = array( array(
				'class' => $selected ? 'selected' : '',
				'text' => wfMessageFallback( "$skname-view-history", "history_short" )->text(),
				'href' => $title->getLocalURL( 'action=board-history' ),
			) );

			unset(
				$links['actions']['protect'],
				$links['actions']['unprotect'],
				$links['actions']['delete'],
				$links['actions']['move'],
				$links['actions']['undelete']
			);
		}

		return true;
	}

	/**
	 * When a (talk) page does not exist, one of the checks being performed is
	 * to see if the page had once existed but was removed. In doing so, the
	 * deletion & move log is checked.
	 *
	 * In theory, a Flow board could overtake a non-existing talk page. If that
	 * board is later removed, this will be run to see if a message can be
	 * displayed to inform the user if the page has been deleted/moved.
	 *
	 * Since, in Flow, we also write (topic, post, ...) deletion to the deletion
	 * log, we don't want those to appear, since they're not actually actions
	 * related to that talk page (rather: they were actions on the board)
	 *
	 * @param array &$conds Array of conditions
	 * @param array &$logTypes Array of log types
	 * @return bool
	 */
	public static function onMissingArticleConditions( array &$conds, array $logTypes ) {
		global $wgLogActionsHandlers;
		$actions = Container::get( 'flow_actions' );

		foreach ( $actions->getActions() as $action ) {
			foreach ( $logTypes as $logType ) {
				// Check if Flow actions are defined for the requested log types
				// and make sure they're ignored.
				if ( isset( $wgLogActionsHandlers["$logType/flow-$action"] ) ) {
					$conds[] = "log_action != 'flow-$action'";
				}
			}
		}

		return true;
	}

	/**
	 * Adds Flow entries to watchlists
	 * @param  array &$types Type array to modify
	 * @return boolean       true
	 */
	public static function onSpecialWatchlistGetNonRevisionTypes( &$types ) {
		$types[] = RC_FLOW;
		return true;
	}

	/**
	 * Make sure no user can register a flow-*-usertext username, to avoid
	 * confusion with a real user when we print e.g. "Censored" instead of a
	 * username.
	 *
	 * @param array $names
	 * @return bool
	 */
	public static function onUserGetReservedNames( &$names ) {
		$permissions = array_keys( Flow\Model\AbstractRevision::$perms );
		foreach ( $permissions as $permission ) {
			$names[] = "msg:flow-$permission-usertext";
		}

		return true;
	}

	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgFlowEditorList;

		$vars['wgFlowEditorList'] = $wgFlowEditorList;
		return true;
	}
}
