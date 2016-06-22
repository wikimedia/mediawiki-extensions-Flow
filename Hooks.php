<?php

use Flow\Collection\PostCollection;
use Flow\Container;
use Flow\Conversion\Utils;
use Flow\Exception\FlowException;
use Flow\Exception\PermissionException;
use Flow\Exception\InvalidUndeleteException;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Formatter\CheckUserQuery;
use Flow\Import\OptInUpdate;
use Flow\Model\UUID;
use Flow\OccupationController;
use Flow\SpamFilter\AbuseFilter;
use Flow\TalkpageManager;
use Flow\WorkflowLoader;
use Flow\WorkflowLoaderFactory;

class FlowHooks {
	/**
	 * @var OccupationController Initialized during extension initialization
	 */
	protected static $occupationController;

	/**
	 * @var AbuseFilter Initialized during extension initialization
	 */
	protected static $abuseFilter;

	public static function onResourceLoaderRegisterModules( ResourceLoader &$resourceLoader ) {
		global $wgFlowEventLogging, $wgResourceModules;

		// Only if EventLogging in Flow is enabled & EventLogging exists
		if ( $wgFlowEventLogging && class_exists( 'ResourceLoaderSchemaModule' ) ) {
			$resourceLoader->register( 'schema.FlowReplies', array(
				'class' => 'ResourceLoaderSchemaModule',
				'schema' => 'FlowReplies',
				// See https://meta.wikimedia.org/wiki/Schema:FlowReplies, below title
				'revision' => 10561344,
			) );

			// Add as dependency to Flow JS
			$wgResourceModules['ext.flow']['dependencies'][] = 'schema.FlowReplies';
		}

		// Register a dummy supportCheck module in case VE isn't loaded, as we attempt
		// to load this module unconditionally on load.
		if ( !$resourceLoader->isModuleRegistered( 'ext.visualEditor.supportCheck' ) ) {
			$resourceLoader->register( 'ext.visualEditor.supportCheck', array() );
		}

		if ( class_exists( 'GuidedTourHooks' ) ) {
			$resourceLoader->register( 'ext.guidedTour.tour.flowOptIn', array(
				'localBasePath' => __DIR__ . '/modules',
				'remoteExtPath' => 'Flow/modules',
					'scripts' => 'tours/flowOptIn.js',
					'styles' => 'tours/flowOptIn.less',
					'messages' => array(
						"flow-guidedtour-optin-welcome",
						"flow-guidedtour-optin-welcome-description",
						"flow-guidedtour-optin-find-old-conversations",
						"flow-guidedtour-optin-find-old-conversations-description",
						"flow-guidedtour-optin-feedback",
						"flow-guidedtour-optin-feedback-description"
					)
			) );
		}

		return true;
	}

	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$title = $skin->getTitle();

		// Register guided tour if needed
		if (
			// Check that the cookie for Flow opt-in tour exists
			$out->getRequest()->getCookie( 'Flow_optIn_guidedTour' ) &&
			// Check that the user is on their own talk page
			$out->getUser()->getTalkPage()->equals( $title ) &&
			// Check that we are on a flow board
			$title->getContentModel() === CONTENT_MODEL_FLOW_BOARD &&
			// Check that guided tour exists
			class_exists( 'GuidedTourHooks' )
		) {
			// Activate tour
			GuidedTourLauncher::launchTourByCookie( 'flowOptIn', 'newTopic' );

			// Destroy Flow cookie
			$out->getRequest()->response()->setcookie( 'Flow_optIn_guidedTour', '', time() - 3600);
		}
	}

	/**
	 * Initialized during extension initialization rather than
	 * in container so that non-flow pages don't load the container.
	 *
	 * @return OccupationController
	 */
	public static function getOccupationController() {
		if ( self::$occupationController === null ) {
			self::$occupationController = new TalkpageManager();
		}
		return self::$occupationController;
	}

	/**
	 * Initialized during extension initialization rather than
	 * in container so that non-flow pages don't load the container.
	 *
	 * @return AbuseFilter
	 */
	public static function getAbuseFilter() {
		if ( self::$abuseFilter === null ) {
			global $wgFlowAbuseFilterGroup,
				$wgFlowAbuseFilterEmergencyDisableThreshold,
				$wgFlowAbuseFilterEmergencyDisableCount,
				$wgFlowAbuseFilterEmergencyDisableAge;

			self::$abuseFilter = new AbuseFilter( $wgFlowAbuseFilterGroup );
			self::$abuseFilter->setup( array(
				'threshold' => $wgFlowAbuseFilterEmergencyDisableThreshold,
				'count' => $wgFlowAbuseFilterEmergencyDisableCount,
				'age' => $wgFlowAbuseFilterEmergencyDisableAge,
			) );
		}
		return self::$abuseFilter;
	}

	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgFlowContentFormat;

		// needed to determine if a page is occupied by flow
		self::getOccupationController();

		// necessary to provide flow options in abuse filter on-wiki pages
		global $wgFlowAbuseFilterGroup;
		if ( $wgFlowAbuseFilterGroup ) {
			self::getAbuseFilter();
		}

		if ( $wgFlowContentFormat === 'html' && !Utils::isParsoidConfigured() ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Warning: $wgFlowContentFormat was set to \'html\', but you do not have Parsoid enabled.  Changing $wgFlowContentFormat to \'wikitext\'' );
			$wgFlowContentFormat = 'wikitext';
		}

		// development dependencies to simplify testing
		if ( defined( 'MW_PHPUNIT_TEST' ) && file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			require_once __DIR__ . '/vendor/autoload.php';
		}

		global $wgGrantPermissions;

		// This is semantically equivalent to editing a talk page and
		// blanking an offending post or topic.
		$wgGrantPermissions['editpage']['flow-hide'] = true;

		// We might want to make a separate grant for this, so it can be
		// given out without giving out core 'protect'.
		$wgGrantPermissions['protect']['flow-lock'] = true;

		$wgGrantPermissions['delete']['flow-delete'] = true;
		$wgGrantPermissions['delete']['flow-suppress'] = true;
		$wgGrantPermissions['editpage']['flow-edit-post'] = true;

		// Creating a board somewhere it normally can't be created is sort
		// of like creating a page that can't normally be edited.  But
		// maybe make a grant.
		$wgGrantPermissions['editprotected']['flow-create-board'] = true;
	}

	/**
	 * Reset anything that happened in self::initFlowExtension for
	 * unit tests
	 */
	public static function resetFlowExtension() {
		self::$abuseFilter = null;
		self::$occupationController = null;
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
			$updater->modifyExtensionField( 'flow_workflow', 'workflow_id', "$dir/db_patches/patch-88bit_uuids.sqlite.sql" );
			$updater->addExtensionField( 'flow_workflow', 'workflow_type', "$dir/db_patches/patch-add_workflow_type.sqlite" );
			$updater->modifyExtensionField( 'flow_workflow', 'workflow_user_id', "$dir/db_patches/patch-default_null_workflow_user.sqlite.sql" );
		} else {
			// sqlite doesn't support alter table change, it also considers all types the same so
			// this patch doesn't matter to it.
			$updater->modifyExtensionField( 'flow_subscription', 'subscription_user_id', "$dir/db_patches/patch-subscription_user_id.sql" );
			// renames columns, alternate patch is above for sqlite
			$updater->modifyExtensionField( 'flow_summary_revision', 'summary_workflow_id', "$dir/db_patches/patch-summary2header.sql" );
			// rename rev_change_type -> rev_comment, alternate patch is above for sqlite
			$updater->modifyExtensionField( 'flow_revision', 'rev_comment', "$dir/db_patches/patch-rev_change_type.sql" );
			// convert 128 bit uuid's into 88bit
			$updater->modifyExtensionField( 'flow_workflow', 'workflow_id', "$dir/db_patches/patch-88bit_uuids.sql" );
			$updater->addExtensionField( 'flow_workflow', 'workflow_type', "$dir/db_patches/patch-add_workflow_type.sql" );
			$updater->modifyExtensionField( 'flow_workflow', 'workflow_user_id', "$dir/db_patches/patch-default_null_workflow_user.sql" );

			// Doesn't need SQLite support, since SQLite doesn't care about text widths.
			$updater->modifyExtensionField( 'flow_workflow', 'workflow_wiki', "$dir/db_patches/patch-increase_width_wiki_fields.sql" );
		}

		$updater->addExtensionIndex( 'flow_workflow', 'flow_workflow_lookup', "$dir/db_patches/patch-workflow_lookup_idx.sql" );
		$updater->addExtensionIndex( 'flow_topic_list', 'flow_topic_list_topic_id', "$dir/db_patches/patch-topic_list_topic_id_idx.sql" );
		$updater->modifyExtensionField( 'flow_revision', 'rev_change_type', "$dir/db_patches/patch-rev_change_type_update.sql" );
		$updater->modifyExtensionField( 'recentchanges', 'rc_source', "$dir/db_patches/patch-rc_source.sql" );
		$updater->modifyExtensionField( 'flow_revision', 'rev_change_type', "$dir/db_patches/patch-censor_to_suppress.sql" );
		$updater->addExtensionField( 'flow_revision', 'rev_user_ip', "$dir/db_patches/patch-remove_usernames.sql" );
		$updater->addExtensionField( 'flow_revision', 'rev_user_wiki', "$dir/db_patches/patch-add-wiki.sql" );
		$updater->addExtensionIndex( 'flow_tree_revision', 'flow_tree_descendant_rev_id', "$dir/db_patches/patch-flow_tree_idx_fix.sql" );
		$updater->dropExtensionField( 'flow_tree_revision', 'tree_orig_create_time', "$dir/db_patches/patch-tree_orig_create_time.sql" );
		$updater->addExtensionIndex( 'flow_revision', 'flow_revision_user', "$dir/db_patches/patch-revision_user_idx.sql" );
		$updater->modifyExtensionField( 'flow_revision', 'rev_user_ip', "$dir/db_patches/patch-revision_user_ip.sql" );
		$updater->addExtensionField( 'flow_revision', 'rev_type_id', "$dir/db_patches/patch-rev_type_id.sql" );
		$updater->addExtensionTable( 'flow_ext_ref', "$dir/db_patches/patch-add-linkstables.sql" );
		$updater->dropExtensionTable( 'flow_definition', "$dir/db_patches/patch-drop_definition.sql" );
		$updater->dropExtensionField( 'flow_workflow', 'workflow_user_ip', "$dir/db_patches/patch-drop_workflow_user.sql" );
		$updater->addExtensionField( 'flow_revision', 'rev_content_length', "$dir/db_patches/patch-add-revision-content-length.sql" );
		$updater->dropExtensionIndex( 'flow_ext_ref', 'flow_ext_ref_pk', "$dir/db_patches/patch-remove_unique_ref_indices.sql" );
		$updater->addExtensionIndex( 'flow_workflow', 'flow_workflow_update_timestamp', "$dir/db_patches/patch-flow_workflow_update_timestamp_idx.sql" );
		$updater->addExtensionField( 'flow_wiki_ref', 'ref_src_wiki', "$dir/db_patches/patch-reference_wiki.sql" );
		$updater->addExtensionField( 'flow_wiki_ref', 'ref_id', "$dir/db_patches/patch-ref_id-phase1.sql" );
		$updater->modifyExtensionField( 'flow_ext_ref', 'ref_target', "$dir/db_patches/patch-ref_target_not_null.sql" );

		require_once __DIR__.'/maintenance/FlowUpdateRecentChanges.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateRecentChanges' );

		require_once __DIR__.'/maintenance/FlowSetUserIp.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowSetUserIp' );

		/*
		 * Remove old *_user_text columns once the maintenance script that
		 * moves the necessary data has been run.
		 * This duplicates what is being done in FlowSetUserIp already, but that
		 * was not always the case, so that script may have already run without
		 * having executed this.
		 */
		if ( $updater->updateRowExists( 'FlowSetUserIp' ) ) {
			$updater->dropExtensionField( 'flow_revision', 'rev_user_text', "$dir/db_patches/patch-remove_usernames_2.sql" );
		}

		require_once __DIR__.'/maintenance/FlowUpdateUserWiki.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateUserWiki' );

		require_once __DIR__.'/maintenance/FlowUpdateRevisionTypeId.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateRevisionTypeId' );

		require_once __DIR__.'/maintenance/FlowPopulateLinksTables.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowPopulateLinksTables' );

		require_once __DIR__.'/maintenance/FlowFixLog.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowFixLog' );

		require_once __DIR__.'/maintenance/FlowUpdateWorkflowPageId.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateWorkflowPageId' );

		require_once __DIR__.'/maintenance/FlowCreateTemplates.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowCreateTemplates' );

		require_once __DIR__.'/maintenance/FlowFixLinks.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowFixLinks' );

		require_once __DIR__.'/maintenance/FlowUpdateBetaFeaturePreference.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowUpdateBetaFeaturePreference' );

		require_once __DIR__.'/maintenance/FlowPopulateRefId.php';
		$updater->addPostDatabaseUpdateMaintenance( 'FlowPopulateRefId' );

		/*
		 * Add primary key, but only after we've made sure the newly added
		 * column has been populated (otherwise they'd all be null values)
		 */
		if ( $updater->updateRowExists( 'FlowPopulateRefId' ) ) {
			if ( $updater->getDB()->getType() === 'sqlite' ) {
				$updater->addExtensionIndex( 'flow_wiki_ref', 'PRIMARY', "$dir/db_patches/patch-ref_id-phase2.sqlite.sql" );
			} else {
				$updater->addExtensionIndex( 'flow_wiki_ref', 'PRIMARY', "$dir/db_patches/patch-ref_id-phase2.sql" );
			}
		}

		return true;
	}

	/**
	 * Hook: UnitTestsList
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 *
	 * @param &$files Array of unit test files
	 * @return bool true in all cases
	 */
	static function getUnitTests( &$files ) {
		$it = new RecursiveDirectoryIterator( __DIR__ . '/tests/phpunit' );
		$it = new RecursiveIteratorIterator( $it );
		foreach ( $it as $path => $file ) {
			if ( substr( $path, -8 ) === 'Test.php' ) {
				$files[] = $path;
			}
		}
		return true;
	}

	/**
	 * Loads RecentChanges list metadata into a temporary cache for later use.
	 *
	 * @param ChangesList $changesList
	 * @param array       $rows
	 */
	public static function onChangesListInitRows( ChangesList $changesList, $rows ) {
		if ( !( $changesList instanceof OldChangesList || $changesList instanceof EnhancedChangesList ) ) {
			return;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\Formatter\ChangesListQuery $query */
			$query = Container::get( 'query.changeslist' );
			$query->loadMetadataBatch(
				$rows,
				$changesList->isWatchlist()
			);
		} catch ( Exception $e ) {
			MWExceptionHandler::logException( $e );
		}
		restore_error_handler();
	}

	/**
	 * Updates the given Flow topic line in an enhanced changes list (grouped RecentChanges).
	 *
	 * @param ChangesList    $changesList
	 * @param string         $articlelink
	 * @param string         $s
	 * @param RecentChange   $rc
	 * @param bool           $unpatrolled
	 * @param bool           $isWatchlist
	 * @return bool
	 */
	public static function onChangesListInsertArticleLink(
		ChangesList &$changesList,
		&$articlelink,
		&$s,
		&$rc,
		$unpatrolled,
		$isWatchlist
	) {
		if ( !( $changesList instanceof EnhancedChangesList ) ) {
			// This method is only to update EnhancedChangesList.
			// onOldChangesListRecentChangesLine allows updating OldChangesList,
			// and supports adding wrapper classes.
			return true;
		}
		$classes = null; // avoid pass-by-reference error
		return self::processRecentChangesLine( $changesList, $articlelink, $rc, $classes, true );
	}

	/**
	 * Updates a Flow line in the old changes list (standard RecentChanges).
	 *
	 * @param ChangesList  $changesList
	 * @param string       $s
	 * @param RecentChange $rc
	 * @param array        $classes
	 * @return bool
	 */
	public static function onOldChangesListRecentChangesLine(
		ChangesList &$changesList,
		&$s,
		RecentChange $rc,
		&$classes = array()
	) {
		return self::processRecentChangesLine( $changesList, $s, $rc, $classes );
	}

	/**
	 * Does the actual work for onOldChangesListRecentChangesLine and
	 * onChangesListInsertArticleLink hooks. Either updates an entire
	 * line with meta info (old changes), or simply updates the link to
	 * the topic (enhanced).
	 *
	 * @param ChangesList    $changesList
	 * @param string         $s
	 * @param RecentChange   $rc
	 * @param array|null     $classes
	 * @param bool           $topicOnly
	 * @return bool
	 */
	protected static function processRecentChangesLine(
		ChangesList &$changesList,
		&$s,
		RecentChange $rc,
		&$classes = null,
		$topicOnly = false
	) {
		$source = $rc->getAttribute( 'rc_source' );
		if ( $source === null ) {
			$rcType = (int) $rc->getAttribute( 'rc_type' );
			if ( $rcType !== RC_FLOW ) {
				return true;
			}
		} elseif ( $source !== Flow\Data\Listener\RecentChangesListener::SRC_FLOW ) {
			return true;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\Formatter\ChangesListQuery $query */
			$query = Container::get( 'query.changeslist' );

			$row = $query->getResult( $changesList, $rc, $changesList->isWatchlist() );
			if ( $row === false ) {
				restore_error_handler();
				return false;
			}

			/** @var Flow\Formatter\ChangesListFormatter $formatter */
			$formatter = Container::get( 'formatter.changeslist' );
			$line = $formatter->format( $row, $changesList, $topicOnly );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Exception formatting rc ' . $rc->getAttribute( 'rc_id' ) . ' ' . $e );
			MWExceptionHandler::logException( $e );
			restore_error_handler();
			return false;
		}
		restore_error_handler();

		if ( $line === false ) {
			return false;
		}

		if ( is_array( $classes ) ) {
			// Add the flow class to <li>
			$classes[] = 'flow-recentchanges-line';
		}
		// Update the line markup
		$s = $line;

		return true;
	}

	/**
	 * Alter the enhanced RC links: (n changes | history)
	 * The default diff links are incorrect!
	 *
	 * @param EnhancedChangesList $changesList
	 * @param array $links
	 * @param RecentChange[] $block
	 * @return bool
	 */
	public static function onGetLogText( $changesList, &$links, $block ) {
		$rc = $block[0];

		// quit if non-flow
		if ( !FlowHooks::isFlow( $rc ) ) {
			return true;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\Formatter\ChangesListQuery $query */
			$query = Container::get( 'query.changeslist' );

			$row = $query->getResult( $changesList, $rc, $changesList->isWatchlist() );
			if ( $row === false ) {
				restore_error_handler();
				return false;
			}

			/** @var Flow\Formatter\ChangesListFormatter $formatter */
			$formatter = Container::get( 'formatter.changeslist' );
			$logTextLinks = $formatter->getLogTextLinks( $row, $changesList, $block, $links );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Exception formatting rc logtext ' . $rc->getAttribute( 'rc_id' ) . ' ' . $e );
			MWExceptionHandler::logException( $e );
			restore_error_handler();
			return false;
		}
		restore_error_handler();

		if ($logTextLinks === false) {
			return false;
		}

		$links = $logTextLinks;
		return true;
	}

	/**
	 * @param EnhancedChangesList $changesList
	 * @param array $data
	 * @param RecentChange[] $block
	 * @param RecentChange $rc
	 * @return bool
	 */
	public static function onEnhancedChangesListModifyLineData( $changesList, &$data, $block, $rc ) {
		return static::onEnhancedChangesListModifyBlockLineData( $changesList, $data, $rc );
	}

	/**
	 * @param EnhancedChangesList $changesList
	 * @param array $data
	 * @param RecentChange $rc
	 * @return bool
	 */
	public static function onEnhancedChangesListModifyBlockLineData( $changesList, &$data, $rc ) {
		// quit if non-flow
		if ( !FlowHooks::isFlow( $rc ) ) {
			return true;
		}

		$query = Container::get( 'query.changeslist' );
		$row = $query->getResult( $changesList, $rc, $changesList->isWatchlist() );
		if ( $row === false ) {
			return false;
		}

		/** @var Flow\Formatter\ChangesListFormatter $formatter */
		$formatter = Container::get( 'formatter.changeslist' );
		try {
			$data['timestampLink'] = $formatter->getTimestampLink( $row, $changesList );
			$data['recentChangesFlags'] = $formatter->getFlags( $row, $changesList );
		} catch ( PermissionException $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the given recent change entry is from Flow
	 * @param RecentChange $rc
	 * @return bool
	 */
	private static function isFlow( $rc ) {
		$source = $rc->getAttribute( 'rc_source' );
		if ( $source === null ) {
			$rcType = (int) $rc->getAttribute( 'rc_type' );
			return $rcType === RC_FLOW;
		} else {
			return $source === RecentChangesListener::SRC_FLOW;
		}
	}

	public static function onSpecialCheckUserGetLinksFromRow( CheckUser $checkUser, $row, &$links ) {
		if ( !$row->cuc_type == RC_FLOW ) {
			return true;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		$replacement = null;
		try {
			/** @var CheckUserQuery $query */
			$query = Container::get( 'query.checkuser' );
			// @todo: create hook to allow batch-loading this data, instead of doing piecemeal like this
			$query->loadMetadataBatch( array( $row ) );
			$row = $query->getResult( $checkUser, $row );
			if ( $row !== false ) {
				/** @var Flow\Formatter\CheckUserFormatter $formatter */
				$formatter = Container::get( 'formatter.checkuser' );
				$replacement = $formatter->format( $row, $checkUser->getContext() );
			}
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Exception formatting cu ' . json_encode( $row ) . ' ' . $e );
			MWExceptionHandler::logException( $e );
		}
		restore_error_handler();

		if ( $replacement === null ) {
			// some sort of failure, but this is a RC_FLOW so blank out hist/diff links
			// which aren't correct
			unset( $links['history'] );
			unset( $links['diff'] );
		} else {
			$links = $replacement;
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
		global $wgFlowCoreActionWhitelist,
			$wgMFPageActions;

		$title = $template->getTitle();

		// if Flow is enabled on this talk page, overrule talk page red link
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			// Turn off page actions in MobileFrontend.
			// FIXME: Find more elegant standard way of doing this.
			$wgMFPageActions = array();

			// watch star & delete links are inside the topic itself
			if ( $title->getNamespace() === NS_TOPIC ) {
				unset( $links['actions']['watch'] );
				unset( $links['actions']['unwatch'] );
				unset( $links['actions']['delete'] );
			}

			// hide all views unless whitelisted
			foreach ( $links['views'] as $action => $data ) {
				if ( !in_array( $action, $wgFlowCoreActionWhitelist ) ) {
					unset( $links['views'][$action] );
				}
			}

			// hide all actions unless whitelisted
			foreach ( $links['actions'] as $action => $data ) {
				if ( !in_array( $action, $wgFlowCoreActionWhitelist ) ) {
					unset( $links['actions'][$action] );
				}
			}

			if ( isset( $links['namespaces']['topic_talk'] ) ) {
				// hide discussion page in Topic namespace(which is already discussion)
				unset( $links['namespaces']['topic_talk'] );
				// hide protection (topic protection is done via moderation)
				unset( $links['actions']['protect'] );
				// topic pages are also not movable
				unset( $links['actions']['move'] );
			}
		}

		return true;
	}

	/**
	 * Interact with the mobile skin's default modules on Flow enabled pages
	 *
	 * @param Skin $skin
	 * @param array $modules
	 * @return bool
	 */
	public static function onSkinMinervaDefaultModules( Skin $skin, array &$modules ) {
		// Disable toggling on occupied talk pages in mobile
		$title = $skin->getTitle();
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			$modules['toggling'] = array();
		}
		// Turn off default mobile talk overlay for these pages
		if ( $title->canTalk() ) {
			$talkPage = $title->getTalkPage();
			if ( $talkPage->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
				// TODO: Insert lightweight JavaScript that opens flow via ajax
				$modules['talk'] = array();
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
		/** @var Flow\FlowActions $actions */
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
	 *
	 * @param array &$types Type array to modify
	 * @return boolean true
	 */
	public static function onSpecialWatchlistGetNonRevisionTypes( &$types ) {
		$types[] = RC_FLOW;
		return true;
	}

	/**
	 * Make sure no user can register a flow-*-usertext username, to avoid
	 * confusion with a real user when we print e.g. "Suppressed" instead of a
	 * username. Additionally reserve the username used to add a revision on
	 * taking over a page.
	 *
	 * @param array $names
	 * @return bool
	 */
	public static function onUserGetReservedNames( &$names ) {
		$permissions = Flow\Model\AbstractRevision::$perms;
		foreach ( $permissions as $permission ) {
			$names[] = "msg:flow-$permission-usertext";
		}
		$names[] = 'msg:flow-system-usertext';

		// Reserve the bot account we use during content model changes & LQT conversion
		$names[] = FLOW_TALK_PAGE_MANAGER_USER;

		return true;
	}

	// Static variables that do not vary by request; delivered through startup module
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgFlowEditorList, $wgFlowAjaxTimeout;

		$vars['wgFlowEditorList'] = $wgFlowEditorList;
		$vars['wgFlowMaxTopicLength'] = Flow\Model\PostRevision::MAX_TOPIC_LENGTH;
		$vars['wgFlowMentionTemplate'] = wfMessage( 'flow-ve-mention-template-title' )->inContentLanguage()->plain();
		$vars['wgFlowAjaxTimeout'] = $wgFlowAjaxTimeout;

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
	public static function onDeletedContributionsLineEnding( $pager, &$ret, $row, &$classes ) {
		global $wgHooks;
		static $javascriptIncluded = false;

		if ( !$row instanceof Flow\Formatter\FormatterRow ) {
			return true;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\Formatter\ContributionsFormatter $formatter */
			$formatter = Container::get( 'formatter.contributions' );
			$line = $formatter->format( $row, $pager );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed formatting contribution ' . json_encode( $row ) . ': ' . $e->getMessage() );
			MWExceptionHandler::logException( $e );
			$line = false;
		}
		restore_error_handler();

		if ( $line === false ) {
			return false;
		}

		$classes[] = 'mw-flow-contribution';
		$ret = $line;

		// If we output one or more lines of contributions entries we also need to include
		// the javascript that hooks into moderation actions.
		// @todo not a huge fan of this static variable, what else though?
		if ( !$javascriptIncluded ) {
			$javascriptIncluded = true;
			$wgHooks['SpecialPageAfterExecute'][] = function( $specialPage, $subPage ) {
				$specialPage->getOutput()->addModules( array( 'ext.flow.contributions' ) );
				$specialPage->getOutput()->addModuleStyles( array( 'ext.flow.contributions.styles' ) );
			};
		}

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
		return static::onDeletedContributionsLineEnding( $pager, $ret, $row, $classes );
	}

	/**
	 * Convert flow contributions entries into FeedItem instances
	 * for ApiFeedContributions
	 *
	 * @param object $row Single row of data from ContribsPager
	 * @param IContextSource $ctx The context to creat the feed item within
	 * @param FeedItem &$feedItem Return value holder for created feed item.
	 * @return bool
	 */
	public static function onContributionsFeedItem( $row, IContextSource $ctx, FeedItem &$feedItem = null ) {
		if ( !$row instanceof Flow\Formatter\FormatterRow ) {
			return true;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\Formatter\FeedItemFormatter $formatter */
			$formatter = Container::get( 'formatter.contributions.feeditem' );
			$result = $formatter->format( $row, $ctx );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed formatting contribution ' . json_encode( $row ) . ': ' . $e->getMessage() );
			MWExceptionHandler::logException( $e );
			return false;
		}
		restore_error_handler();

		if ( $result instanceof FeedItem ) {
			$feedItem = $result;
			return true;
		} else {
			// If we failed to render a flow row, cancel it. This could be
			// either permissions or bugs.
			return false;
		}
	}

	/**
	 * Adds Flow contributions to the DeletedContributions special page
	 *
	 * @param $data array an array of results of all contribs queries, to be
	 *  merged to form all contributions data
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return bool
	 */
	public static function onDeletedContributionsQuery( &$data, $pager, $offset, $limit, $descending ) {
		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\Formatter\ContributionsQuery $query */
			$query = Container::get( 'query.contributions' );
			$results = $query->getResults( $pager, $offset, $limit, $descending );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed contributions query' );
			MWExceptionHandler::logException( $e );
			$results = false;
		}
		restore_error_handler();

		if ( $results === false ) {
			return false;
		}

		$data[] = $results;

		return true;
	}

	/**
	 * Adds Flow contributions to the Contributions special page
	 *
	 * @param $data array an array of results of all contribs queries, to be
	 *  merged to form all contributions data
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return bool
	 */
	public static function onContributionsQuery( &$data, $pager, $offset, $limit, $descending ) {
		// Flow has nothing to do with the tag filter, so ignore tag searches
		if ( $pager->tagFilter != false ) {
			return true;
		}

		return static::onDeletedContributionsQuery( $data, $pager, $offset, $limit, $descending );
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
		// fetch all lazy-load methods
		$methods = self::$abuseFilter->lazyLoadMethods();

		// method isn't known here
		if ( !isset( $methods[$method] ) ) {
			return true;
		}

		// fetch variable result from lazy-load method
		$result = $methods[$method]( $vars, $parameters );
		return false;
	}

	/**
	 * Abort notifications regarding occupied pages coming from the RecentChange class.
	 * Flow has its own notifications through Echo.
	 *
	 * Also don't notify for actions made by the talk page manager.
	 *
	 * @param User $editor
	 * @param Title $title
	 * @return bool false to abort email notification
	 */
	public static function onAbortEmailNotification( $editor, $title ) {
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			// Since we are aborting the notification we need to manually update the watchlist
			EmailNotification::updateWatchlistTimestamp( $editor, $title, wfTimestampNow() );

			return false;
		}

		if ( !$editor instanceof User ) {
			return true;
		}

		if ( self::isTalkpageManagerUser( $editor ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Suppress all Echo notifications generated by the Talk page manager user
	 *
	 * @param EchoEvent $event
	 * @return bool
	 */
	public static function onBeforeEchoEventInsert( EchoEvent $event ) {
		$agent = $event->getAgent();

		if ( $agent === null ) {
			return true;
		}

		if ( self::isTalkpageManagerUser( $agent ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Suppress the 'You have new messages!' indication when a change to a
	 * user talk page is done by the talk page manager user.
	 *
	 * @param WikiPage $page
	 * @param User $recipient
	 * @return bool
	 */
	public static function onArticleEditUpdateNewTalk( WikiPage $page, User $recipient ) {
		$user = User::newFromId( $page->getUser( Revision::RAW ) );

		if ( self::isTalkpageManagerUser( $user ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	private static function isTalkpageManagerUser( User $user ) {
		return $user->getName() === FLOW_TALK_PAGE_MANAGER_USER;
	}

	/**
	 * Don't send email notifications that are imported from LiquidThreads.  It will
	 * still be in their web notifications (if enabled), but they will never be
	 * notified via email (regardless of batching settings) for this particular
	 * notification.
	 *
	 */
	public static function onEchoAbortEmailNotification( User $user, EchoEvent $event ) {
		$extra = $event->getExtra();
		if ( isset( $extra['lqtThreadId'] ) && $extra['lqtThreadId'] !== null ) {
			return false;
		}

		return true;
	}


	public static function onInfoAction( IContextSource $ctx, &$pageinfo ) {
		if ( $ctx->getTitle()->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		// All of the info in this section is wrong for Flow pages,
		// so we'll just remove it.
		unset( $pageinfo['header-edits'] );

		// These keys are wrong on Flow pages, so we'll remove them
		static $badMessageKeys = array( 'pageinfo-length' );

		foreach ( $pageinfo['header-basic'] as $num => $val ) {
			if ( $val[0] instanceof Message && in_array( $val[0]->getKey(), $badMessageKeys ) ) {
				unset($pageinfo['header-basic'][$num]);
			}
		}
		return true;
	}

	/**
	 * @param RecentChange $rc
	 * @param array &$rcRow
	 * @return bool
	 */
	public static function onCheckUserInsertForRecentChange( RecentChange $rc, array &$rcRow ) {
		if ( $rc->getAttribute( 'rc_source' ) !== Flow\Data\Listener\RecentChangesListener::SRC_FLOW ) {
			return true;
		}

		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$change = $params['flow-workflow-change'];

		// don't forget to increase the version number when data format changes
		$comment = CheckUserQuery::VERSION_PREFIX;
		$comment .= ',' . $change['action'];
		$comment .= ',' . $change['workflow'];
		$comment .= ',' . $change['revision'];
		if ( isset( $change['post'] ) ) {
			$comment .= ',' . $change['post'];
		}

		$rcRow['cuc_comment'] = $comment;

		return true;
	}

	public static function onIRCLineURL( &$url, &$query, RecentChange $rc ) {
		if ( $rc->getAttribute( 'rc_source' ) !== Flow\Data\Listener\RecentChangesListener::SRC_FLOW ) {
			return true;
		}

		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		$result = null;
		try {
			/** @var Flow\Formatter\IRCLineUrlFormatter $formatter */
			$formatter = Container::get( 'formatter.irclineurl' );
			$result = $formatter->format( $rc );
		} catch ( Exception $e ) {
			$result = null;
			wfDebugLog( 'Flow', __METHOD__ . ': Failed formatting rc ' . $rc->getAttribute( 'rc_id' ) . ': ' . $e->getMessage() );
			MWExceptionHandler::logException( $e );
		}
		restore_error_handler();

		if ( $result !== null ) {
			$url = $result;
			$query = '';
		}

		return true;
	}

	public static function onWhatLinksHereProps( $row, Title $title, Title $target, &$props ) {
		set_error_handler( new Flow\RecoverableErrorHandler, -1 );
		try {
			/** @var Flow\ReferenceClarifier $clarifier */
			$clarifier = Flow\Container::get( 'reference.clarifier' );
			$newProps = $clarifier->getWhatLinksHereProps( $row, $title, $target );

			$props = array_merge( $props, $newProps );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', sprintf(
				'%s: Failed formatting WhatLinksHere for %s to %s',
				__METHOD__,
				$title->getFullText(),
				$target->getFullText()
			) );
			MWExceptionHandler::logException( $e );
		}
		restore_error_handler();

		return true;
	}

	/**
	 * Add topiclist sortby to preferences.
	 *
	 * @param $user User object
	 * @param &$preferences array Preferences object
	 * @return bool
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['flow-topiclist-sortby'] = array(
			'type' => 'api',
		);

		$preferences['flow-editor'] = array(
			'type' => 'api'
		);

		$preferences['flow-side-rail-state'] = array(
			'type' => 'api'
		);

		return true;
	}

	/**
	 * ResourceLoaderTestModules hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array $testModules
	 * @param ResourceLoader $resourceLoader
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( array &$testModules,
		ResourceLoader &$resourceLoader
	) {
		global $wgResourceModules;

		// find test files for every RL module
		foreach ( $wgResourceModules as $key => $module ) {
			if ( preg_match( '/ext.flow(?:\.|$)/', $key ) && isset( $module['scripts'] ) ) {
				$testFiles = array();

				$scripts = (array) $module['scripts'];
				foreach ( $scripts as $script ) {
					$testFile = 'tests/qunit/' . dirname( $script ) . '/test_' . basename( $script );
					// if a test file exists for a given JS file, add it
					if ( file_exists( __DIR__ . '/' . $testFile ) ) {
						$testFiles[] = $testFile;
					}
				}
				// if test files exist for given module, create a corresponding test module
				if ( count( $testFiles ) > 0 ) {
					$module = array(
						'remoteExtPath' => 'Flow',
						'dependencies' => array( $key ),
						'localBasePath' => __DIR__,
						'scripts' => $testFiles,
					);
					$testModules['qunit']["$key.tests"] = $module;
				}
			}
		}

		return true;
	}

	/**
	 * Don't (un)watch a non-existing flow topic
	 *
	 * @param User $user
	 * @param WikiPage $page
	 * $param Status $status
	 */
	public static function onWatchArticle( &$user, WikiPage &$page, &$status ) {
		$title = $page->getTitle();
		if ( $title->getNamespace() == NS_TOPIC ) {
			// @todo - use !$title->exists()?
			/** @var Flow\Data\ManagerGroup $storage */
			$storage = Container::get( 'storage' );
			$found = $storage->find(
				'PostRevision',
				array( 'rev_type_id' => strtolower( $title->getDBkey() ) ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( !$found ) {
				return false;
			}
			$post = reset( $found );
			if ( !$post->isTopicTitle() ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Adds the topic namespace.
	 */
	public static function onCanonicalNamespaces( &$list ) {
		$list[NS_TOPIC] = 'Topic';
		return true;
	}

	/**
	 * Checks whether this is a valid move technically.  MovePageIsValidMove should not
	 * be affected by the specific user, or user permissions.
	 *
	 * Those are handled in onMovePageCheckPermissions, called later.
	 *
	 * @param Title $oldTitle Old title
	 * @param Title $newTitle New title
	 * @param Status $status Status to update with any technical issues
	 *
	 * @return true to continue, false to abort the hook
	 */
	public static function onMovePageIsValidMove( Title $oldTitle, Title $newTitle, Status $status ) {
		// We only care about moving Flow boards, and *not* moving Flow topics
		// (but both are CONTENT_MODEL_FLOW_BOARD)
		if ( $oldTitle->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		// Pages within the Topic namespace are not movable
		// This is also checked by NamespaceIsMovable.
		if ( $oldTitle->getNamespace() === NS_TOPIC ) {
			$status->fatal( 'flow-error-move-topic' );
			return false;
		}

		$occupationController = self::getOccupationController();
		$flowStatus = $occupationController->checkIfCreationIsPossible( $newTitle, /*mustNotExist*/ true );
		$status->merge( $flowStatus );

		return true;
	}

	/**
	 * Checks whether user has permission to move the board.
	 *
	 * Technical restrictions are handled in onMovePageIsValidMove, called earlier.
	 *
	 * @param Title $oldTitle Old title
	 * @param Title $newTitle New title
	 * @param User $user User doing the move
	 * @param string $reason Reason for the move
	 * @param Status $status Status updated with any permissions issue
	 *
	 * @return true to continue, false to abort the hook
	 */
	public static function onMovePageCheckPermissions( Title $oldTitle, Title $newTitle, User $user, $reason, Status $status ) {
		// Only affect moves if the source has Flow content model
		if ( $oldTitle->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		$occupationController = self::getOccupationController();

		$permissionStatus = $occupationController->checkIfUserHasPermission(
			$newTitle,
			$user
		);
		$status->merge( $permissionStatus );

		return true;
	}

	/**
	 * @param Title $title
	 * @param string[] $urls
	 * @return bool
	 */
	public static function onTitleSquidURLs( Title $title, array &$urls ) {
		if ( $title->getNamespace() !== NS_TOPIC ) {
			return true;
		}
		try {
			$uuid = WorkflowLoaderFactory::uuidFromTitle( $title );
		} catch ( Flow\Exception\InvalidInputException $e ) {
			MWExceptionHandler::logException( $e );
			wfDebugLog( 'Flow', __METHOD__ . ': Invalid title ' . $title->getPrefixedText() );
			return true;
		}
		/** @var Flow\Data\ManagerGroup $storage */
		$storage = Container::get( 'storage' );
		$workflow = $storage->get( 'Workflow', $uuid );
		if ( !$workflow instanceof Flow\Model\Workflow ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Title for non-existent Workflow ' . $title->getPrefixedText() );
			return true;
		}
		$urls = array_merge(
			$urls,
			$workflow->getOwnerTitle()->getSquidURLs()
		);

		return true;
	}

	/**
	 * @param array $tools Extra links
	 * @param Title $title
	 * @param bool $redirect Whether the page is a redirect
	 * @param Skin $skin
	 * @param string $link
	 * @return bool
	 */
	public static function onWatchlistEditorBuildRemoveLine( &$tools, $title, $redirect, $skin, &$link = '' ) {
		if ( $title->getNamespace() !== NS_TOPIC ) {
			// Leave all non Flow topics alone!
			return true;
		}

		/*
		 * Link to talk page is no applicable for Flow topics
		 * Note that key 'talk' doesn't exist prior to
		 * https://gerrit.wikimedia.org/r/#/c/156522/, so on old MW's, the link
		 * to talk page will still be present.
		 */
		unset( $tools['talk'] );

		if ( !$link ) {
			/*
			 * https://gerrit.wikimedia.org/r/#/c/156118/ adds argument $link.
			 * Prior to that patch, it was impossible to change the link, so
			 * let's quit early if it doesn't exist.
			 */
			return true;
		}

		try {
			// Find the title text of this specific topic
			$uuid = WorkflowLoaderFactory::uuidFromTitle( $title );
			$collection = PostCollection::newFromId( $uuid );
			$revision = $collection->getLastRevision();
		} catch ( Exception $e ) {
			wfWarn( __METHOD__ . ': Failed to locate revision for: ' . $title->getDBKey() );
			return true;
		}

		$content = $revision->getContent( 'topic-title-plaintext' );
		$link = Linker::link( $title, htmlspecialchars( $content ) );

		return true;
	}

	/**
	 * @param array $watchlistInfo Watchlisted pages
	 * @return bool
	 */
	public static function onWatchlistEditorBeforeFormRender( &$watchlistInfo ) {
		if ( !isset( $watchlistInfo[NS_TOPIC] ) ) {
			// No topics watchlisted
			return true;
		}

		$ids = array_keys( $watchlistInfo[NS_TOPIC] );

		// build array of queries to be executed all at once
		$queries = array();
		foreach( $ids as $id ) {
			try {
				$uuid = WorkflowLoaderFactory::uuidFromTitlePair( NS_TOPIC, $id );
				$queries[] = array( 'rev_type_id' => $uuid );
			} catch ( Exception $e ) {
				// invalid id
				unset( $watchlistInfo[NS_TOPIC][$id] );
			}
		}

		/** @var Flow\Data\ManagerGroup $storage */
		$storage = Container::get( 'storage' );

		/*
		 * Now, finally find all requested topics - this will be stored in
		 * local cache so subsequent calls (in onWatchlistEditorBuildRemoveLine)
		 * will just find these in memory, instead of doing a bunch of network
		 * requests.
		 */
		$storage->findMulti(
			'PostRevision',
			$queries,
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		return true;
	}

	/**
	 * For integration with the UserMerge extension. Provides the database and
	 * sets of table/column pairs to update user id's within.
	 *
	 * @param array $updateFields
	 * @return bool
	 */
	public static function onUserMergeAccountFields( &$updateFields ) {
		/** @var Flow\Data\Utils\UserMerger $merger */
		$merger = Container::get( 'user_merger' );
		foreach ( $merger->getAccountFields() as $row ) {
			$updateFields[] = $row;
		}

		return true;
	}

	/**
	 * Finalize the merge by purging any cached value that contained $oldUser
	 */
	public static function onMergeAccountFromTo( User &$oldUser, User &$newUser ) {
		/** @var Flow\Data\Utils\UserMerger $merger */
		$merger = Container::get( 'user_merger' );
		$merger->finalizeMerge( $oldUser->getId(), $newUser->getId() );

		return true;
	}

	/**
	 * Gives precedence to Flow over LQT.
	 */
	public static function onIsLiquidThreadsPage( Title $title, &$isLqtPage ) {
		if ( $isLqtPage && $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			$isLqtPage = false;
		}

		return true;
	}

	/**
	 * @param int $namespace
	 * @param bool $movable
	 * @return bool
	 */
	public static function onNamespaceIsMovable( $namespace, &$movable ) {
		if ( $namespace === NS_TOPIC ) {
			$movable = false;
		}

		return true;
	}

	public static function onCategoryViewerDoCategoryQuery( $type, $res ) {
		if ( $type !== 'page' ) {
			return true;
		}

		/** @var Flow\Formatter\CategoryViewerQuery */
		$query = Container::get( 'query.categoryviewer' );
		$query->loadMetadataBatch( $res );

		return true;
	}

	public static function onCategoryViewerGenerateLink( $type, Title $title, $html, &$link ) {
		if ( $type !== 'page' || $title->getNamespace() !== NS_TOPIC ) {
			return true;
		}
		$uuid = UUID::create( strtolower( $title->getDBkey() ) );
		if ( !$uuid ) {
			return true;
		}
		/** @var Flow\Formatter\CategoryViewerQuery */
		$query = Container::get( 'query.categoryviewer' );
		$row = $query->getResult( $uuid );
		/** @var Flow\Formatter\CategoryViewerFormatter */
		$formatter = Container::get( 'formatter.categoryviewer' );
		$result = $formatter->format( $row );
		if ( $result ) {
			$link = $result;
		}

		return true;
	}

	/**
	 * Gets error HTML for attempted NS_TOPIC deletion using core interface
	 *
	 * @param Title $title Topic title they are attempting to delete
	 * @return string Error html
	 */
	protected static function getTopicDeletionError( Title $title ) {
		$error = wfMessage( 'flow-error-core-topic-deletion', $title->getFullURL() )->parse();
		$wrappedError = Html::rawElement( 'span', array(
			'class' => 'plainlinks',
		), $error );
		return $wrappedError;
	}

	// This should block them from wasting their time filling the form, but it won't
	// without a core change.  However, it does show the message.
	/**
	 * Shows an error message when the user visits the deletion form if the page is in
	 * the Topic namespace.
	 *
	 * @param WikiPage $article Page the user requested to delete
	 * @param OutputPage $out Output page
	 * @param string &$reason Pre-filled reason given for deletion (note, this could
	 *   be used to customize this for boards and/or topics later)
	 * @return bool False if it is a Topic; otherwise, true
	 */
	public static function onArticleConfirmDelete( $article, $output, &$reason ) {
		$title = $article->getTitle();
		if ( $title->inNamespace( NS_TOPIC ) ) {
			$output->addHTML( FlowHooks::getTopicDeletionError( $title ) );
			return false;
		}

		return true;
	}

	/**
	 * Blocks topics from being deleted using the core deletion process, since it
	 * doesn't work.
	 *
	 * @param WikiPage &$article Page the user requested to delete
	 * @param User &$user User who requested to delete the article
	 * @param string &$reason Reason given for deletion
	 * @param string &$error Error explaining why we are not allowing the deletion
	 * @return bool False if it is a Topic (to block it); otherwise, true
	 */
	public static function onArticleDelete( WikiPage &$article, User &$user, &$reason, &$error ) {
		$title = $article->getTitle();
		if ( $title->inNamespace( NS_TOPIC ) ) {
			$error = FlowHooks::getTopicDeletionError( $title );
			return false;
		}

		return true;
	}

	/**
	 * Evicts topics from Squid/Varnish when the board is deleted.
	 * We do permission checks for this scenario, but since the topic isn't deleted
	 * at the core level, we need to evict it from Varnish ourselves.
	 *
	 * @param WikiPage &$article Deleted article
	 * @param User &$user User that deleted article
	 * @param string $reason Reason given
	 * @param int $articleId Article ID of deleted article
	 * @param Content $content Content that was deleted, or null on error
	 * @param LogEntry $logEntry Log entry for deletion
	 */
	public static function onArticleDeleteComplete( WikiPage &$article, User &$user, $reason, $articleId, Content $content = null, LogEntry $logEntry ) {
		$title = $article->getTitle();

		// Topics use the same content model, but can't be deleted at the core
		// level currently.
		if ( $content !== null &&
			$title->getNamespace() !== NS_TOPIC &&
			$title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {

			$storage = Container::get( 'storage' );

			DeferredUpdates::addUpdate( new MWCallableUpdate( function () use ( $storage, $articleId ) {
				$workflows = $storage->find( 'Workflow', array(
					'workflow_wiki' => wfWikiId(),
					'workflow_page_id' => $articleId,
				) );

				if ( !$workflows ) {
					return false;
				}

				// If I41ebd2f34347a3f218f7d0bfc8962d286b943c16 is merged to core (see T116095),
				// we can use SquidUpdate::newFromTitles instead of building the URL list ourselves.
				$squidUrls = array();

				foreach ( $workflows as $workflow ) {
					if ( $workflow->getType() === 'topic' ) {
						$topicTitle = $workflow->getArticleTitle();
						$squidUrls = array_merge( $squidUrls, $topicTitle->getSquidURLs() );
					}
				}

				$squidUpdate = new SquidUpdate( $squidUrls );
				$squidUpdate->doUpdate();
			} ) );
		}

		return true;
	}

	/**
	 * @param Title $title Title corresponding to the article restored
	 * @param bool $created Whether or not the restoration caused the page to be created (i.e. it didn't exist before).
	 * @param string $comment The comment associated with the undeletion.
	 * @param int $oldPageId ID of page previously deleted (from archive table)
	 * @throws InvalidUndeleteException
	 * @return bool
	 */
	public static function onArticleUndelete( Title $title, $created, $comment, $oldPageId ) {
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			if ( $title->getArticleID() !== $oldPageId ) {
				throw new InvalidUndeleteException( 'Failed to move ' . $oldPageId . ' over ' . $title->getArticleID() );
			}

			// complete hack to make sure that when the page is saved to new
			// location and rendered it doesn't throw an error about the wrong title
			Container::get( 'factory.loader.workflow' )->pageMoveInProgress();
			// open a database transaction and prepare everything for the move & commit
			$boardMover = Container::get( 'board_mover' );
			$boardMover->prepareMove( $oldPageId, $title );
			$boardMover->commit();
		}

		return true;
	}

	/**
	 * Occurs at the beginning of the MovePage process (just after the startAtomic).
	 *
	 * Perhaps ContentModel should be extended to be notified about moves explicitly.
	 */
	public static function onTitleMoveStarting( Title $oldTitle, Title $newTitle, User $user ) {
		if ( $oldTitle->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			// $newTitle doesn't yet exist, but after the move it'll still have
			// the same ID $oldTitle used to have
			// Since we don't want to wait until after the page has been moved
			// to start preparing relevant Flow moves, I'll make it reflect the
			// correct ID already
			$bogusTitle = clone $newTitle;
			$bogusTitle->resetArticleID( $oldTitle->getArticleID() );

			// This is only safe because we have called
			// checkIfCreationIsPossible and (usually) checkIfUserHasPermission.
			Container::get( 'occupation_controller' )->forceAllowCreation( $bogusTitle );
			// complete hack to make sure that when the page is saved to new
			// location and rendered it doesn't throw an error about the wrong title
			Container::get( 'factory.loader.workflow' )->pageMoveInProgress();
			// open a database transaction and prepare everything for the move, but
			// don't commit yet. That is done below in self::onTitleMoveCompleting
			Container::get( 'board_mover' )->prepareMove( $oldTitle->getArticleID(), $bogusTitle );
		}

		return true;
	}

	public static function onTitleMoveCompleting( Title $oldTitle, Title $newTitle, User $user, $pageid, $redirid, $reason, Revision $revision ) {
		if ( $newTitle->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			Container::get( 'board_mover' )->commit();
		}

		return true;
	}

	public static function onShowMissingArticle( Article $article ) {
		if ( $article->getPage()->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		if ( $article->getTitle()->getNamespace() === NS_TOPIC ) {
			// @todo pretty message about invalid workflow
			throw new FlowException( 'Non-existent topic' );
		}

		$emptyContent = ContentHandler::getForModelID( CONTENT_MODEL_FLOW_BOARD )->makeEmptyContent();
		$parserOutput = $emptyContent->getParserOutput( $article->getTitle() );
		$article->getContext()->getOutput()->addParserOutput( $parserOutput );

		return false;
	}

	/**
	 * Excludes NS_TOPIC from the list of searchable namespaces
	 *
	 * @param array $namespaces Associative array mapping namespace index
	 *  to name
	 * @return bool
	 */
	public static function onSearchableNamespaces( &$namespaces ) {
		unset( $namespaces[NS_TOPIC] );
		return true;
	}

	/**
	 * @return bool
	 */
	private static function isBetaFeatureAvailable() {
		global $wgBetaFeaturesWhitelist, $wgFlowEnableOptInBetaFeature;
		return $wgFlowEnableOptInBetaFeature &&
			( !is_array( $wgBetaFeaturesWhitelist ) || in_array( BETA_FEATURE_FLOW_USER_TALK_PAGE, $wgBetaFeaturesWhitelist ) );
	}

	/**
	 * @param User $user
	 * @param array $prefs
	 * @return bool
	 */
	public static function onGetBetaFeaturePreferences( $user, &$prefs ) {
		global $wgExtensionAssetsPath;

		if ( !self::isBetaFeatureAvailable() ) {
			return true;
		}

		$prefs[BETA_FEATURE_FLOW_USER_TALK_PAGE] = array(
			// The first two are message keys
			'label-message' => 'flow-talk-page-beta-feature-message',
			'desc-message' => 'flow-talk-page-beta-feature-description',
			'screenshot' => array(
				'ltr' => "$wgExtensionAssetsPath/Flow/images/betafeature-flow-ltr.svg",
				'rtl' => "$wgExtensionAssetsPath/Flow/images/betafeature-flow-rtl.svg",
			),
			'info-link' => 'https://www.mediawiki.org/wiki/Flow',
			'discussion-link' => 'https://www.mediawiki.org/wiki/Talk:Flow',
			'exempt-from-auto-enrollment' => true,
		);

		return true;
	}

	/**
	 * @param User $user
	 * @param array $options
	 * @return bool
	 */
	public static function onUserSaveOptions( $user, &$options ) {
		if ( !self::isBetaFeatureAvailable() ) {
			return true;
		}

		if ( !array_key_exists( BETA_FEATURE_FLOW_USER_TALK_PAGE, $options ) ) {
			return true;
		}

		$userClone = User::newFromId( $user->getId() );
		$before = BetaFeatures::isFeatureEnabled( $userClone, BETA_FEATURE_FLOW_USER_TALK_PAGE );
		$after = $options[BETA_FEATURE_FLOW_USER_TALK_PAGE];
		$action = null;

		if ( !$before && $after ) {
			$action = OptInUpdate::$ENABLE;
			// Check if the user had a flow board
			$c = new Flow\Import\OptInController();
			if ( !$c->hasFlowBoardArchive( $user ) ) {
				// Enable the guided tour by setting the cookie
				RequestContext::getMain()->getRequest()->response()->setcookie( 'Flow_optIn_guidedTour', '1' );
			}
		} elseif ( $before && !$after ) {
			$action = OptInUpdate::$DISABLE;
		}

		if ( $action ) {
			DeferredUpdates::addUpdate( new OptInUpdate( $action, $user->getTalkPage(), $user ) );
		}

		return true;
	}

	/**
	 * @param WikiImporter $importer
	 * @return bool
	 */
	public static function onImportHandleToplevelXMLTag( WikiImporter $importer ) {
		// only init Flow's importer once, then re-use it
		static $flowImporter = null;
		if ( $flowImporter === null ) {
			// importer can be dry-run (= parse, but don't store), but we can only
			// derive that from mPageOutCallback. I'll set a new value (which will
			// return the existing value) to see if it's in dry-run mode (= null)
			$callback = $importer->setPageOutCallback( null );
			// restore previous mPageOutCallback value
			$importer->setPageOutCallback( $callback );

			$flowImporter = new \Flow\Dump\Importer( $importer );
			if ( $callback !== null ) {
				// not in dry-run mode
				$flowImporter->setStorage( Container::get( 'storage' ) );
			}
		}

		$reader = $importer->getReader();
		$tag = $reader->localName;
		$type = $reader->nodeType;

		if ( $tag == 'board' ) {
			if ( $type === XMLReader::ELEMENT ) {
				$flowImporter->handleBoard();
			}
			return false;
		} elseif ( $tag == 'description' ) {
			if ( $type === XMLReader::ELEMENT ) {
				$flowImporter->handleHeader();
			}
			return false;
		} elseif ( $tag == 'topic' ) {
			if ( $type === XMLReader::ELEMENT ) {
				$flowImporter->handleTopic();
			}
			return false;
		} elseif ( $tag == 'post' ) {
			if ( $type === XMLReader::ELEMENT ) {
				$flowImporter->handlePost();
			}
			return false;
		} elseif ( $tag == 'summary' ) {
			if ( $type === XMLReader::ELEMENT ) {
				$flowImporter->handleSummary();
			}
			return false;
		} elseif ( $tag == 'children' ) {
			return false;
		}

		return true;
	}

	public static function onNukeGetNewPages( $username, $pattern, $namespace, $limit, &$pages ) {
		if ( $namespace && $namespace !== NS_TOPIC ) {
			// not interested in any Topics
			return true;
		}

		// Remove any pre-existing Topic pages.
		// They are coming from the recentchanges table.
		// Most likely the filters were not applied correctly.
		$pages = array_filter( $pages, function( $entry ) {
			/** @var Title $title */
			$title = $entry[0];
			return $title->getNamespace() !== NS_TOPIC;
		} );

		if ( $pattern ) {
			// pattern is not supported
			return true;
		}

		if ( !RequestContext::getMain()->getUser()->isAllowed( 'flow-delete' ) ) {
			// there's no point adding topics since the current user won't be allowed to delete them
			return true;
		}

		// how many are we allowed to retrieve now
		$newLimit = $limit - count( $pages );

		// we can't add anything
		if ( $newLimit < 1 ) {
			return true;
		}

		$dbFactory = Container::get( 'db.factory' );
		/** @var Database $dbr */
		$dbr = $dbFactory->getDB( DB_SLAVE );

		// if a username is specified, search only for that user
		$userWhere = array();
		if ( $username ) {
			$user = User::newFromName( $username );
			if ( $user ) {
				$userWhere = array( 'tree_orig_user_id' => $user->getId() );
			} else {
				$userWhere = array( 'tree_orig_user_ip' => $username );
			}
		}

		// limit results to the range of RC
		global $wgRCMaxAge;
		$rcTimeLimit = UUID::getComparisonUUID( strtotime("-$wgRCMaxAge seconds") );

		// get latest revision id for each topic
		$result = $dbr->select(
			array(
				'r' => 'flow_revision',
				'flow_tree_revision',
				'flow_workflow',
			),
			array(
				'revId' => 'MAX(r.rev_id)',
				'userIp' => "tree_orig_user_ip",
				'userId' => "tree_orig_user_id",
			),
			array_merge( array(
				'tree_parent_id' => null,
				'r.rev_type' => 'post',
				'workflow_wiki' => wfWikiId(),
				'workflow_id > ' . $dbr->addQuotes( $rcTimeLimit->getBinary() )
			), $userWhere ),
			__METHOD__,
			array(
				'GROUP BY' => 'r.rev_type_id'
			),
			array(
				'flow_tree_revision' => array( 'INNER JOIN', 'r.rev_type_id=tree_rev_descendant_id' ),
				'flow_workflow' => array( 'INNER JOIN', 'r.rev_type_id=workflow_id' ),
			)
		);

		if ( $result->numRows() < 1 ) {
			return true;
		}

		$revIds = array();
		foreach( $result as $r ) {
			$revIds[$r->revId] = array( 'userIp' => $r->userIp, 'userId' => $r->userId, 'name' => false );
		}

		// get non-moderated revisions
		$result = $dbr->select(
			'flow_revision',
			array(
				'topicId' => 'rev_type_id',
				'revId' => 'rev_id'
			),
			array(
				'rev_mod_state' => '',
				'rev_id' => array_keys( $revIds )
			),
			__METHOD__,
			array(
				'LIMIT' => $newLimit,
				'ORDER BY' => 'rev_type_id DESC'
			)
		);

		// all topics previously found appear to be moderated
		if ( $result->numRows() < 1 ) {
			return true;
		}

		// keep only the relevant topics in [topicId => userInfo] format
		$limitedRevIds = array();
		foreach ( $result as $r ) {
			$limitedRevIds[$r->topicId] = $revIds[$r->revId];
		}

		// fill usernames if no $username filter was specified
		if ( !$username ) {
			$userIds = array_map(
				function ( $userInfo ) { return $userInfo['userId']; },
				array_values( $limitedRevIds )
			);
			$userIds = array_filter( $userIds );

			$userMap = array();
			if ( $userIds ) {
				$wikiDbr = $dbFactory->getWikiDB( DB_SLAVE );
				$result = $wikiDbr->select(
					'user',
					array( 'user_id', 'user_name' ),
					array( 'user_id' => array_values( $userIds ) )
				);
				foreach( $result as $r ) {
					$userMap[$r->user_id] = $r->user_name;
				}
			}

			// set name in userInfo structure
			foreach( $limitedRevIds as $topicId => &$userInfo ) {
				if ( $userInfo['userIp'] ) {
					$userInfo['name'] = $userInfo['userIp'];
				} elseif ( $userInfo['userId'] ) {
					$userInfo['name'] = $userMap[$userInfo['userId']];
				} else {
					$userInfo['name'] = false;
					$topicIdAlpha = UUID::create( $topicId )->getAlphadecimal();
					wfLogWarning( __METHOD__ . ": Cannot find user information for topic {$topicIdAlpha}" );
				}
			}
		}

		// add results to the list of pages to nuke
		foreach( $limitedRevIds as $topicId => $userInfo ) {
			$pages[] = array(
				Title::makeTitle( NS_TOPIC, UUID::create( $topicId )->getAlphadecimal() ),
				$userInfo['name']
			);
		}

		return true;
	}

	public static function onNukeDeletePage( Title $title, $reason, &$deletionResult ) {
		if ( $title->getNamespace() !== NS_TOPIC ) {
			// we don't handle it
			return true;
		}

		$action = 'moderate-topic';
		$params = array(
			'topic' => array(
				'moderationState' => 'delete',
				'reason' => $reason,
				'page' => $title->getPrefixedText()
			),
		);

		/** @var WorkflowLoaderFactory $factory */
		$factory = Container::get( 'factory.loader.workflow' );

		$workflowId = WorkflowLoaderFactory::uuidFromTitle( $title );
		/** @var WorkflowLoader $loader */
		$loader = $factory->createWorkflowLoader( $title, $workflowId );

		$blocks = $loader->getBlocks();

		$blocksToCommit = $loader->handleSubmit(
			RequestContext::getMain(),
			$action,
			$params
		);

		$result = true;
		$errors = array();
		foreach ( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$result = false;
				$errorKeys = $block->getErrors();
				foreach ( $errorKeys as $errorKey ) {
					$errors[] = $block->getErrorMessage( $errorKey );
				}
			}
		}

		if ( $result ) {
			$loader->commit( $blocksToCommit );
			$deletionResult = true;
		} else {
			$deletionResult = false;
			$msg = "Failed to delete {$title->getPrefixedText()}. Errors: " . implode( '. ', $errors );
			wfLogWarning( $msg );
		}

		// we've handled the deletion, abort the hook
		return false;
	}
}
