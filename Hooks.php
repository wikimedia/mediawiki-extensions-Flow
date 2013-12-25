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
		$updater->modifyExtensionField( 'flow_revision', 'rev_change_type', "$dir/db_patches/patch-censor_to_suppress.sql" );

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
		$files[] = glob( __DIR__ . '/tests/*Test.php' );
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

			try {
				$workflowId = $request->getVal( 'workflow' );
				$action = $request->getVal( 'action', 'view' );

				$loader = $container['factory.loader.workflow']
					->createWorkflowLoader( $title, UUID::create( $workflowId ) );

				if ( !$loader->getWorkflow()->isNew() ) {
					// Workflow currently exists, make sure a revision also exists
					$occupationController->ensureFlowRevision( $article );
				}

				$view->show( $loader, $action );
			} catch ( Flow\Exception\FlowException $e ) {
				$handling = new Flow\Exception\FlowExceptionHandling( $container['templating'], RequestContext::getMain() );
				$handling->handle( $e );
			}

			return false;
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
					$conds[] = "log_action != " . wfGetDB( DB_SLAVE )->addQuotes( "flow-$action" );
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
	 * confusion with a real user when we print e.g. "Suppressed" instead of a
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
		$vars['wgFlowMaxTopicLength'] = Flow\Model\PostRevision::MAX_TOPIC_LENGTH;

		return true;
	}

	/**
	 * Intercept contribution entries and format those belonging to Flow
	 *
	 * @param ContribsPager $page Contributions object
	 * @param string $ret The HTML line
	 * @param stdClass $row The data for this line
	 * @param array $classes the classes to add to the surrounding <li>
	 * @return bool
	 */
	public static function onContributionsLineEnding( $pager, &$ret, $row, &$classes ) {
		if ( !isset( $row->flow_contribution ) || $row->flow_contribution !== 'flow' ) {
			return true;
		}

		$line = Container::get( 'contribitions.formatter' )->format( $pager, $row );

		if ( $line === false ) {
			return false;
		}

		$classes[] = 'mw-flow-contribution';
		$ret = $line;

		return true;
	}

	/**
	 * Adds Flow contributions to the Contributions special page
	 *
	 * @param $data array an array of results of all contribs queries, to be merged to form all contributions data
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return bool
	 */
	public static function onContributionsQuery( &$data, $pager, $offset, $limit, $descending ) {
		global $wgFlowOccupyNamespaces, $wgFlowOccupyPages;

		// Not searching within Flow namespace = ignore
		// (but only if no individual pages are occupied)
		if ( $pager->namespace != '' &&
			!in_array( $pager->namespace, $wgFlowOccupyNamespaces ) &&
			!count( $wgFlowOccupyPages )
		) {
			return true;
		}

		// Flow has nothing to do with the tag filter, so ignore tag searches
		if ( $pager->tagFilter != false ) {
			return true;
		}

		$results = Container::get( 'contribitions.query' )->getResults( $pager, $offset, $limit, $descending );

		if ( $results === false ) {
			return false;
		}

		$data[] = $results;

		return true;
	}
}
