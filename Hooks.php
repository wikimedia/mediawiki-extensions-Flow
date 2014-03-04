<?php

use Flow\Container;
use Flow\Exception\FlowException;
use Flow\Model\UUID;

class FlowHooks {
	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgEchoNotifications,
			$wgFlowAbuseFilterGroup,
			$wgFlowAbuseFilterEmergencyDisableThreshold,
			$wgFlowAbuseFilterEmergencyDisableCount,
			$wgFlowAbuseFilterEmergencyDisableAge;

		if ( isset( $wgEchoNotifications ) ) {
			Container::get( 'controller.notification' )->setup();
		}

		if ( $wgFlowAbuseFilterGroup ) {
			Container::get( 'controller.abusefilter' )->setup(
				array(
					'threshold' => $wgFlowAbuseFilterEmergencyDisableThreshold,
					'count' => $wgFlowAbuseFilterEmergencyDisableCount,
					'age' => $wgFlowAbuseFilterEmergencyDisableAge,
				)
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
		$updater->addExtensionField( 'flow_revision', 'rev_last_edit_id', "$dir/db_patches/patch-revision_last_editor.sql" );
		$updater->addExtensionField( 'flow_revision', 'rev_mod_reason', "$dir/db_patches/patch-moderation_reason.sql" );
		if ( $updater->getDB()->getType() === 'sqlite' ) {
			$updater->modifyExtensionField( 'flow_summary_revision', 'summary_workflow_id', "$dir/db_patches/patch-summary2header.sqlite.sql" );
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sqlite.sql" );
			// sqlite ignores field types, this just substr's uuid's to 88 bits
			$updater->modifyExtensionField( 'flow_definition', 'definition_id', "$dir/db_patches/patch-88bit_uuids.sqlite.sql" );
		} else {
			// sqlite doesn't support alter table change, it also considers all types the same so
			// this patch doesn't matter to it.
			$updater->modifyExtensionField( 'flow_subscription', 'subscription_user_id', "$dir/db_patches/patch-subscription_user_id.sql" );
			// renames columns, alternate patch is above for sqlite
			$updater->modifyExtensionField( 'flow_summary_revision', 'summary_workflow_id', "$dir/db_patches/patch-summary2header.sql" );
			// rename rev_change_type -> rev_comment, alternate patch is above for sqlite
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sql" );
			// convert 128 bit uuid's into 88bit
			$updater->modifyExtensionField( 'flow_definition', 'definition_id', "$dir/db_patches/patch-88bit_uuids.sql" );
		}

		$updater->addExtensionIndex( 'flow_workflow', 'flow_workflow_lookup', "$dir/db_patches/patch-workflow_lookup_idx.sql" );
		$updater->addExtensionIndex( 'flow_topic_list', 'flow_topic_list_topic_id', "$dir/db_patches/patch-topic_list_topic_id_idx.sql" );
		$updater->modifyExtensionField( 'flow_revision', 'rev_change_type', "$dir/db_patches/patch-rev_change_type_update.sql" );
		$updater->modifyExtensionField( 'recentchanges', 'rc_source', "$dir/db_patches/patch-rc_source.sql" );
		$updater->modifyExtensionField( 'flow_revision', 'rev_change_type', "$dir/db_patches/patch-censor_to_suppress.sql" );
		$updater->addExtensionField( 'flow_workflow', 'workflow_user_ip', "$dir/db_patches/patch-remove_usernames.sql" );
		$updater->addExtensionField( 'flow_workflow', 'workflow_user_wiki', "$dir/db_patches/patch-add-wiki.sql" );
		$updater->addExtensionField( 'flow_revision', 'rev_type_id', "$dir/db_patches/patch-rev_type_id.sql" );
		
		require_once __DIR__.'/maintenance/FlowInsertDefaultDefinitions.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowInsertDefaultDefinitions' );

		require_once __DIR__.'/maintenance/FlowUpdateRecentChanges.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateRecentChanges' );

		require_once __DIR__.'/maintenance/FlowSetUserIp.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowSetUserIp' );

		require_once __DIR__.'/maintenance/FlowUpdateUserWiki.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateUserWiki' );

		require_once __DIR__.'/maintenance/FlowUpdateRevisionTypeId.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateRevisionTypeId' );

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
		$files = array_merge( $files, glob( __DIR__ . '/tests/*Test.php' ) );
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

		try {
			$formatter = Container::get( 'recentchanges.formatter' );
			$watchlist = $formatter->isWatchlist( $classes );
			$line = $formatter->format( $changesList, $rc, $watchlist );
		} catch ( FlowException $e ) {
			wfWarn( __METHOD__ . ': Exception formatting rc ' . $rc->getAttribute( 'rc_id' ) . ' ' . $e );
			\MWExceptionHandler::logException( $e );
			return false;
		}
		if ( $line === false ) {
			return false;
		}

		$classes[] = 'flow-recentchanges-line';
		$s = $line;

		return true;
	}

	public static function onSpecialCheckUserGetLinksFromRow( CheckUser $checkUser, $row, &$links ) {
		if ( $row->cuc_type == RC_FLOW ) {
			$replacement = Container::get( 'checkuser.formatter' )->format( $checkUser, $row );
			if ( $replacement === null ) {
				// some sort of failure, but this is a RC_FLOW so blank out hist/diff links
				// which aren't correct
				unset( $links['history'] );
				unset( $links['diff'] );
			} else {
				$links = $replacement;
			}
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
	 * @param  WebRequest $request
	 * @param  MediaWiki $wiki
	 * @throws Flow\Exception\FlowException
	 * @return boolean True to continue processing as normal, False to abort.
	 */
	public static function onPerformAction( $output, $article, $title, $user, $request, $wiki ) {
		global $wgFlowCoreActionWhitelist;
		$container = Container::getContainer();
		$occupationController = $container['occupation_controller'];
		$action = $wiki->getAction();

		if ( $occupationController->isTalkpageOccupied( $title ) && !in_array( $action, $wgFlowCoreActionWhitelist ) ) {

			$view = new Flow\View(
				$container['templating'],
				$container['url_generator'],
				$output
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
			} catch( Flow\Exception\FlowException $e ) {
				$e->setOutput( $output );
				throw $e;
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
		global $wgFlowCoreActionWhitelist;

		$title = $template->getTitle();

		$container = Container::getContainer();
		$occupationController = $container['occupation_controller'];

		// if Flow is enabled on this talk page, overrule talk page red link
		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$skname = $template->getSkinName();

			$selected = $template->getRequest()->getVal( 'action' ) == 'board-history';
			$links['views'] = array( array(
				'class' => $selected ? 'selected' : '',
				'text' => wfMessageFallback( "$skname-view-history", "history_short" )->text(),
				'href' => $title->getLocalURL( 'action=board-history' ),
			) );

			// hide all ?action= links unless whitelisted
			foreach ( $links['actions'] as $action => $data ) {
				if ( !in_array( $action, $wgFlowCoreActionWhitelist ) ) {
					unset( $links['actions'][$action] );
				}
			}
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
		$permissions = Flow\Model\AbstractRevision::$perms;
		foreach ( $permissions as $permission ) {
			$names[] = "msg:flow-$permission-usertext";
		}

		$names[] = "msg:flow-system-usertext";

		return true;
	}

	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgFlowEditorList, $wgFlowDefaultLimit;

		$vars['wgFlowEditorList'] = $wgFlowEditorList;
		$vars['wgFlowMaxTopicLength'] = Flow\Model\PostRevision::MAX_TOPIC_LENGTH;
		$vars['wgFlowPageSize'] = array(
			'expanded' => $wgFlowDefaultLimit,
			'collapsed-full' => $wgFlowDefaultLimit * 2,
			'collapsed-oneline' => $wgFlowDefaultLimit * 3,
		);

		return true;
	}

	/**
	 * Intercept contribution entries and format those belonging to Flow
	 *
	 * @param ContribsPager $pager Contributions object
	 * @param string &$ret The HTML line
	 * @param stdClass $row The data for this line
	 * @param array &$classes the classes to add to the surrounding <li>
	 * @return bool
	 */
	public static function onContributionsLineEnding( $pager, &$ret, $row, &$classes ) {
		if ( !isset( $row->flow_contribution ) || $row->flow_contribution !== 'flow' ) {
			return true;
		}

		$line = Container::get( 'contributions.formatter' )->format( $pager, $row );

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

		$results = Container::get( 'contributions.query' )->getResults( $pager, $offset, $limit, $descending );

		if ( $results === false ) {
			return false;
		}

		$data[] = $results;

		return true;
	}

	/**
	 * Adds lazy-load methods for AbstractRevision objects.
	 *
	 * @param string $method: Method to generate the variable
	 * @param AbuseFilterVariableHolder $vars
	 * @param array $parameters Parameters with data to compute the value
	 * @param mixed &$result Result of the computation
	 * @return bool
	 */
	public static function onAbuseFilterComputeVariable( $method, AbuseFilterVariableHolder $vars, $parameters, &$result ) {
		$spamfilter = Container::get( 'controller.abusefilter' );

		// fetch all lazy-load methods
		$methods = $spamfilter->lazyLoadMethods();

		// method isn't known here
		if ( !isset( $methods[$method] ) ) {
			return true;
		}

		// fetch variable result from lazy-load method
		$result = $methods[$method]( $vars, $parameters );
		return false;
	}

	/**
	 * Abort notifications coming from RecentChange class, Flow has its
	 * own notifications through Echo.
	 *
	 * @param User $editor
	 * @param Title $title
	 * @return bool false to abort email notification
	 */
	public static function onAbortEmailNotification( $editor, $title ) {
		if ( Container::get( 'occupation_controller' )->isTalkpageOccupied( $title ) ) {
			return false;
		}

		return true;
	}

	public static function onInfoAction( IContextSource $ctx, &$pageinfo ) {
		if ( !Container::get( 'occupation_controller' )->isTalkpageOccupied( $ctx->getTitle() ) ) {
			return true;
		}

		// All of the info in this section is wrong for Flow pages,
		// so we'll just remove it.
		unset( $pageinfo['header-edits'] );

		// These keys are wrong on Flow pages, so we'll remove them
		static $badMessageKeys = array( 'pageinfo-length', 'pageinfo-content-model' );

		foreach ( $pageinfo['header-basic'] as $num => $val ) {
			if ( $val[0] instanceof Message && in_array( $val[0]->getKey(), $badMessageKeys ) ) {
				unset($pageinfo['header-basic'][$num]);
			}
		}
		return true;
	}

	/**
	 * Make the terms of use for editing messages available in JavaScript
	 *
	 * @param array &$vars
	 * @param OutputPage $out
	 * @return bool
	 */
	public static function onMakeGlobalVariablesScript( array &$vars, OutputPage $out ) {
		$vars['wgFlowTermsOfUseEdit'] = Flow\TermsOfUse::getEditTerms();
		return true;
	}
}
