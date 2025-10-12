<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace Flow;

use Article;
use ChangesList;
use EnhancedChangesList;
use Exception;
use Flow\Collection\PostCollection;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;
use Flow\Formatter\CheckUserQuery;
use Flow\Hooks\HookRunner;
use Flow\Import\OptInController;
use Flow\Model\UUID;
use Flow\SpamFilter\AbuseFilter;
use LogEntry;
use MediaWiki\Api\Hook\ApiFeedContributions__feedItemHook;
use MediaWiki\CheckUser\CheckUser\Pagers\AbstractCheckUserPager;
use MediaWiki\Config\Config;
use MediaWiki\Content\Content;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Extension\BetaFeatures\BetaFeatures;
use MediaWiki\Extension\GuidedTour\GuidedTourLauncher;
use MediaWiki\Feed\FeedItem;
use MediaWiki\Hook\AbortEmailNotificationHook;
use MediaWiki\Hook\CategoryViewer__doCategoryQueryHook;
use MediaWiki\Hook\CategoryViewer__generateLinkHook;
use MediaWiki\Hook\ChangesListInitRowsHook;
use MediaWiki\Hook\ChangesListInsertArticleLinkHook;
use MediaWiki\Hook\ContribsPager__reallyDoQueryHook;
use MediaWiki\Hook\ContributionsLineEndingHook;
use MediaWiki\Hook\DeletedContribsPager__reallyDoQueryHook;
use MediaWiki\Hook\DeletedContributionsLineEndingHook;
use MediaWiki\Hook\EnhancedChangesList__getLogTextHook;
use MediaWiki\Hook\EnhancedChangesListModifyBlockLineDataHook;
use MediaWiki\Hook\EnhancedChangesListModifyLineDataHook;
use MediaWiki\Hook\ImportHandleToplevelXMLTagHook;
use MediaWiki\Hook\InfoActionHook;
use MediaWiki\Hook\IRCLineURLHook;
use MediaWiki\Hook\MovePageCheckPermissionsHook;
use MediaWiki\Hook\MovePageIsValidMoveHook;
use MediaWiki\Hook\OldChangesListRecentChangesLineHook;
use MediaWiki\Hook\PageMoveCompletingHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Hook\SpecialWatchlistGetNonRevisionTypesHook;
use MediaWiki\Hook\TitleMoveStartingHook;
use MediaWiki\Hook\TitleSquidURLsHook;
use MediaWiki\Hook\UnwatchArticleHook;
use MediaWiki\Hook\WatchArticleHook;
use MediaWiki\Hook\WatchlistEditorBeforeFormRenderHook;
use MediaWiki\Hook\WatchlistEditorBuildRemoveLineHook;
use MediaWiki\Hook\WhatLinksHerePropsHook;
use MediaWiki\Html\FormOptions;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Hook\Article__MissingArticleConditionsHook;
use MediaWiki\Page\Hook\ArticleConfirmDeleteHook;
use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use MediaWiki\Page\Hook\ArticleDeleteHook;
use MediaWiki\Page\Hook\ArticleUndeleteHook;
use MediaWiki\Page\Hook\RevisionUndeletedHook;
use MediaWiki\Page\Hook\ShowMissingArticleHook;
use MediaWiki\Pager\ContribsPager;
use MediaWiki\Pager\DeletedContribsPager;
use MediaWiki\Pager\IndexPager;
use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderGetConfigVarsHook;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderRegisterModulesHook;
use MediaWiki\ResourceLoader\ResourceLoader;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Search\Hook\SearchableNamespacesHook;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageQueryHook;
use MediaWiki\Status\Status;
use MediaWiki\Storage\Hook\ArticleEditUpdateNewTalkHook;
use MediaWiki\Title\Title;
use MediaWiki\User\Hook\UserGetReservedNamesHook;
use MediaWiki\User\Options\Hook\SaveUserOptionsHook;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWiki\WikiMap\WikiMap;
use MessageLocalizer;
use MWException;
use MWExceptionHandler;
use OldChangesList;
use RecentChange;
use Skin;
use SkinTemplate;
use stdClass;
use WikiImporter;
use Wikimedia\Rdbms\SelectQueryBuilder;
use WikiPage;
use XMLReader;

class Hooks implements
	ResourceLoaderRegisterModulesHook,
	BeforePageDisplayHook,
	GetPreferencesHook,
	OldChangesListRecentChangesLineHook,
	ChangesListInsertArticleLinkHook,
	ChangesListInitRowsHook,
	EnhancedChangesList__getLogTextHook,
	EnhancedChangesListModifyLineDataHook,
	EnhancedChangesListModifyBlockLineDataHook,
	ChangesListSpecialPageQueryHook,
	SkinTemplateNavigation__UniversalHook,
	Article__MissingArticleConditionsHook,
	SpecialWatchlistGetNonRevisionTypesHook,
	UserGetReservedNamesHook,
	ResourceLoaderGetConfigVarsHook,
	ContribsPager__reallyDoQueryHook,
	DeletedContribsPager__reallyDoQueryHook,
	ContributionsLineEndingHook,
	DeletedContributionsLineEndingHook,
	ApiFeedContributions__feedItemHook,
	AbortEmailNotificationHook,
	ArticleEditUpdateNewTalkHook,
	InfoActionHook,
	IRCLineURLHook,
	WhatLinksHerePropsHook,
	ShowMissingArticleHook,
	WatchArticleHook,
	UnwatchArticleHook,
	MovePageCheckPermissionsHook,
	MovePageIsValidMoveHook,
	TitleMoveStartingHook,
	PageMoveCompletingHook,
	TitleSquidURLsHook,
	WatchlistEditorBuildRemoveLineHook,
	WatchlistEditorBeforeFormRenderHook,
	CategoryViewer__doCategoryQueryHook,
	CategoryViewer__generateLinkHook,
	ArticleConfirmDeleteHook,
	ArticleDeleteHook,
	ArticleDeleteCompleteHook,
	RevisionUndeletedHook,
	ArticleUndeleteHook,
	SearchableNamespacesHook,
	ImportHandleToplevelXMLTagHook,
	SaveUserOptionsHook,
	GetUserPermissionsErrorsHook
{
	/**
	 * @var AbuseFilter|null Initialized during extension initialization
	 */
	protected static $abuseFilter;

	public static function registerExtension() {
		require_once dirname( __DIR__ ) . '/defines.php';
	}

	public function onResourceLoaderRegisterModules( ResourceLoader $resourceLoader ): void {
		// Register a dummy supportCheck module in case VE isn't loaded, as we attempt
		// to load this module unconditionally on load.
		if ( !$resourceLoader->isModuleRegistered( 'ext.visualEditor.supportCheck' ) ) {
			$resourceLoader->register( 'ext.visualEditor.supportCheck', [] );
		}

		if ( ExtensionRegistry::getInstance()->isLoaded( 'GuidedTour' ) ) {
			$resourceLoader->register( 'ext.guidedTour.tour.flowOptIn', [
				'localBasePath' => dirname( __DIR__ ) . '/modules',
				'remoteExtPath' => 'Flow/modules',
				'scripts' => 'tours/flowOptIn.js',
				'styles' => 'tours/flowOptIn.less',
				'messages' => [
					"flow-guidedtour-optin-welcome",
					"flow-guidedtour-optin-welcome-description",
					"flow-guidedtour-optin-find-old-conversations",
					"flow-guidedtour-optin-find-old-conversations-description",
					"flow-guidedtour-optin-feedback",
					"flow-guidedtour-optin-feedback-description"
				],
				'dependencies' => 'ext.guidedTour',
			] );
		}
	}

	public function onBeforePageDisplay( $out, $skin ): void {
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
			ExtensionRegistry::getInstance()->isLoaded( 'GuidedTour' )
		) {
			// Activate tour
			GuidedTourLauncher::launchTourByCookie( 'flowOptIn', 'newTopic' );

			// Destroy Flow cookie
			$out->getRequest()->response()->setCookie( 'Flow_optIn_guidedTour', '', time() - 3600 );
		}
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
			self::$abuseFilter->setup( [
				'threshold' => $wgFlowAbuseFilterEmergencyDisableThreshold,
				'count' => $wgFlowAbuseFilterEmergencyDisableCount,
				'age' => $wgFlowAbuseFilterEmergencyDisableAge,
			] );
		}
		return self::$abuseFilter;
	}

	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
		global $wgFlowAbuseFilterGroup;

		// necessary to provide flow options in abuse filter on-wiki pages
		if ( $wgFlowAbuseFilterGroup ) {
			self::getAbuseFilter();
		}
	}

	/**
	 * Reset anything that happened in self::initFlowExtension for
	 * unit tests
	 */
	public static function resetFlowExtension() {
		self::$abuseFilter = null;
	}

	/**
	 * Loads RecentChanges list metadata into a temporary cache for later use.
	 *
	 * @param ChangesList $changesList
	 * @param array $rows
	 */
	public function onChangesListInitRows( $changesList, $rows ) {
		if ( !( $changesList instanceof OldChangesList || $changesList instanceof EnhancedChangesList ) ) {
			return;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var Formatter\ChangesListQuery $query */
			$query = Container::get( 'query.changeslist' );
			$query->loadMetadataBatch(
				$rows,
				$changesList->isWatchlist()
			);
		} catch ( Exception $e ) {
			MWExceptionHandler::logException( $e );
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Updates the given Flow topic line in an enhanced changes list (grouped RecentChanges).
	 *
	 * @param ChangesList $changesList
	 * @param string &$articlelink
	 * @param string &$s
	 * @param RecentChange $rc
	 * @param bool $unpatrolled
	 * @param bool $isWatchlist
	 * @return bool
	 */
	public function onChangesListInsertArticleLink(
		$changesList,
		&$articlelink,
		&$s,
		$rc,
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
	 * @param ChangesList $changesList
	 * @param string &$s
	 * @param RecentChange $rc
	 * @param array &$classes
	 * @param array &$attribs
	 * @return bool
	 */
	public function onOldChangesListRecentChangesLine(
		$changesList,
		&$s,
		$rc,
		&$classes,
		&$attribs
	) {
		return self::processRecentChangesLine( $changesList, $s, $rc, $classes );
	}

	/**
	 * Does the actual work for onOldChangesListRecentChangesLine and
	 * onChangesListInsertArticleLink hooks. Either updates an entire
	 * line with meta info (old changes), or simply updates the link to
	 * the topic (enhanced).
	 *
	 * @param ChangesList $changesList
	 * @param string &$s
	 * @param RecentChange $rc
	 * @param array|null &$classes
	 * @param bool $topicOnly
	 * @return bool
	 */
	protected static function processRecentChangesLine(
		ChangesList $changesList,
		&$s,
		RecentChange $rc,
		&$classes = null,
		$topicOnly = false
	) {
		$source = $rc->getAttribute( 'rc_source' );
		if ( $source === null ) {
			$rcType = (int)$rc->getAttribute( 'rc_type' );
			if ( $rcType !== RC_FLOW ) {
				return true;
			}
		} elseif ( $source !== RecentChangesListener::SRC_FLOW ) {
			return true;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var Formatter\ChangesListQuery $query */
			$query = Container::get( 'query.changeslist' );

			$row = $query->getResult( $changesList, $rc, $changesList->isWatchlist() );
			if ( $row === false ) {
				restore_error_handler();
				return false;
			}

			/** @var Formatter\ChangesListFormatter $formatter */
			$formatter = Container::get( 'formatter.changeslist' );
			$line = $formatter->format( $row, $changesList, $topicOnly );
		} catch ( PermissionException $pe ) {
			// It is expected that some rows won't be formatted because the current user
			// doesn't have permission to see some of the data they contain.
			return false;
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Exception formatting rc ' .
				$rc->getAttribute( 'rc_id' ) . ' ' . $e );
			MWExceptionHandler::logException( $e );
			return false;
		} finally {
			restore_error_handler();
		}

		if ( $line === false ) {
			return false;
		}

		if ( is_array( $classes ) ) {
			// Add the flow class to <li>
			$classes[] = 'flow-recentchanges-line';
			$classes[] = 'mw-changeslist-src-mw-edit';
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
	 * @param array &$links
	 * @param RecentChange[] $block
	 * @return bool
	 */
	public function onEnhancedChangesList__getLogText( $changesList, &$links, $block ) {
		$rc = $block[0];

		// quit if non-flow
		// FIXME: It could be that $rc is a non-Flow change (e.g. Wikidata), but $block still
		// contains Flow changes. In that case we should probably process those?
		if ( !self::isFlow( $rc ) ) {
			return true;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var Formatter\ChangesListQuery $query */
			$query = Container::get( 'query.changeslist' );

			$row = $query->getResult( $changesList, $rc, $changesList->isWatchlist() );
			if ( $row === false ) {
				restore_error_handler();
				return false;
			}

			/** @var Formatter\ChangesListFormatter $formatter */
			$formatter = Container::get( 'formatter.changeslist' );
			$logTextLinks = $formatter->getLogTextLinks( $row, $changesList, $block, $links );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Exception formatting rc logtext ' .
				$rc->getAttribute( 'rc_id' ) . ' ' . $e );
			MWExceptionHandler::logException( $e );
			return false;
		} finally {
			restore_error_handler();
		}

		if ( $logTextLinks === false ) {
			return false;
		}

		$links = $logTextLinks;
		return true;
	}

	/**
	 * @param EnhancedChangesList $changesList
	 * @param array &$data
	 * @param RecentChange[] $block
	 * @param RecentChange $rc
	 * @param string[] &$classes
	 * @param string[] &$attribs
	 * @return bool
	 */
	public function onEnhancedChangesListModifyLineData( $changesList, &$data, $block, $rc, &$classes, &$attribs ) {
		return static::modifyChangesListLine( $changesList, $data, $rc, $classes );
	}

	/**
	 * @param EnhancedChangesList $changesList
	 * @param array &$data
	 * @param RecentChange $rc
	 * @return bool
	 */
	public function onEnhancedChangesListModifyBlockLineData( $changesList, &$data, $rc ) {
		$classes = [];
		return static::modifyChangesListLine( $changesList, $data, $rc, $classes );
	}

	/**
	 * @param ChangesList $changesList
	 * @param array &$data
	 * @param RecentChange $rc
	 * @param string[] &$classes
	 * @return bool
	 */
	private static function modifyChangesListLine( $changesList, &$data, $rc, &$classes ) {
		// quit if non-flow
		if ( !self::isFlow( $rc ) ) {
			return true;
		}

		$query = Container::get( 'query.changeslist' );
		$row = $query->getResult( $changesList, $rc, $changesList->isWatchlist() );
		if ( $row === false ) {
			return false;
		}

		/** @var Formatter\ChangesListFormatter $formatter */
		$formatter = Container::get( 'formatter.changeslist' );
		try {
			$data['timestampLink'] = $formatter->getTimestampLink( $row, $changesList );
			$data['recentChangesFlags'] = array_merge(
				$data['recentChangesFlags'],
				$formatter->getFlags( $row, $changesList )
			);
			$classes[] = 'mw-changeslist-src-mw-edit';
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
			$rcType = (int)$rc->getAttribute( 'rc_type' );
			return $rcType === RC_FLOW;
		} else {
			return $source === RecentChangesListener::SRC_FLOW;
		}
	}

	public static function onSpecialCheckUserGetLinksFromRow( AbstractCheckUserPager $pager, $row, &$links ) {
		// TODO: Replace accesses to $row properties with the prefix "cuc_" to
		// remove the need for this aliasing.
		if ( isset( $row->type ) ) {
			$row->cuc_type = $row->type;
		}
		if ( isset( $row->comment_text ) ) {
			$row->cuc_comment_text = $row->comment_text;
			$row->cuc_comment_data = $row->comment_data;
		}

		if ( $row->cuc_type != RC_FLOW ) {
			return;
		}

		$replacement = self::getReplacementRowItems( $pager->getContext(), $row );

		if ( $replacement === null ) {
			// some sort of failure, but this is a RC_FLOW so blank out hist/diff links
			// which aren't correct
			$links['history'] = '';
			$links['diff'] = '';
		} else {
			$links = $replacement;
		}
	}

	public static function onCheckUserFormatRow( IContextSource $context, $row, &$rowItems ) {
		// TODO: Replace accesses to $row properties with the prefix "cuc_" to
		// remove the need for this aliasing.
		if ( isset( $row->type ) ) {
			$row->cuc_type = $row->type;
		}
		if ( isset( $row->comment_text ) ) {
			$row->cuc_comment_text = $row->comment_text;
			$row->cuc_comment_data = $row->comment_data;
		}

		if ( $row->cuc_type != RC_FLOW ) {
			return;
		}

		$replacement = self::getReplacementRowItems( $context, $row );

		// These links are incorrect for Flow
		$rowItems['links']['diffLink'] = '';
		$rowItems['links']['historyLink'] = '';

		if ( $replacement !== null ) {
			array_unshift( $rowItems['links'], $replacement['links'] );
			$rowItems['info']['titleLink'] = $replacement['title'];
		}
	}

	/**
	 * @param IContextSource $context
	 * @param stdClass $row
	 * @return array|null
	 */
	private static function getReplacementRowItems( IContextSource $context, $row ): ?array {
		set_error_handler( new RecoverableErrorHandler, -1 );
		$replacement = null;
		try {
			/** @var CheckUserQuery $query */
			$query = Container::get( 'query.checkuser' );
			// @todo: create hook to allow batch-loading this data, instead of doing piecemeal like this
			$query->loadMetadataBatch( [ $row ] );
			$row = $query->getResult( $row );
			if ( $row !== false ) {
				/** @var Formatter\CheckUserFormatter $formatter */
				$formatter = Container::get( 'formatter.checkuser' );
				$replacement = $formatter->format( $row, $context );
			}
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Exception formatting cu ' . json_encode( $row ) . ' ' . $e );
			MWExceptionHandler::logException( $e );
		} finally {
			restore_error_handler();
		}

		return $replacement;
	}

	/**
	 * Regular talk page "Create source" and "Add topic" links are quite useless
	 * in the context of Flow boards. Let's get rid of them.
	 *
	 * @param SkinTemplate $template
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $template, &$links ): void {
		global $wgFlowCoreActionWhitelist,
			$wgMFPageActions;

		$title = $template->getTitle();

		// if Flow is enabled on this talk page, overrule talk page red link
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			// Turn off page actions in MobileFrontend.
			// FIXME: Find more elegant standard way of doing this.
			$wgMFPageActions = [];

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
				unset( $links['associated-pages']['topic_talk'] );
				// hide protection (topic protection is done via moderation)
				unset( $links['actions']['protect'] );
				// topic pages are also not movable
				unset( $links['actions']['move'] );
			}
		}
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
	 * @param array $logTypes Array of log types
	 */
	public function onArticle__MissingArticleConditions( &$conds, $logTypes ) {
		global $wgLogActionsHandlers;
		/** @var FlowActions $actions */
		$actions = Container::get( 'flow_actions' );

		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		foreach ( $actions->getActions() as $action ) {
			foreach ( $logTypes as $logType ) {
				// Check if Flow actions are defined for the requested log types
				// and make sure they're ignored.
				if ( isset( $wgLogActionsHandlers["$logType/flow-$action"] ) ) {
					$conds[] = $dbr->expr( 'log_action', '!=', "flow-$action" );
				}
			}
		}
	}

	/**
	 * Adds Flow entries to watchlists
	 *
	 * @param array &$types Type array to modify
	 */
	public function onSpecialWatchlistGetNonRevisionTypes( &$types ) {
		$types[] = RC_FLOW;
	}

	/**
	 * Make sure no user can register a flow-*-usertext username, to avoid
	 * confusion with a real user when we print e.g. "Suppressed" instead of a
	 * username. Additionally reserve the username used to add a revision on
	 * taking over a page.
	 *
	 * @param array &$names
	 */
	public function onUserGetReservedNames( &$names ) {
		$permissions = Model\AbstractRevision::$perms;
		foreach ( $permissions as $permission ) {
			$names[] = "msg:flow-$permission-usertext";
		}

		// Reserve the bot account we use during content model changes & LQT conversion
		$names[] = FLOW_TALK_PAGE_MANAGER_USER;
	}

	/**
	 * Static variables that do not vary by request; delivered through startup module
	 * @param array &$vars
	 * @param string $skin
	 * @param Config $config
	 */
	public function onResourceLoaderGetConfigVars( array &$vars, $skin, Config $config ): void {
		global $wgFlowAjaxTimeout;

		$vars['wgFlowMaxTopicLength'] = Model\PostRevision::MAX_TOPIC_LENGTH;
		$vars['wgFlowMentionTemplate'] = wfMessage( 'flow-ve-mention-template-title' )->inContentLanguage()->plain();
		$vars['wgFlowAjaxTimeout'] = $wgFlowAjaxTimeout;
	}

	/**
	 * Intercept contribution entries and format those belonging to Flow
	 *
	 * @param IContextSource $pager
	 * @param string &$ret The HTML line
	 * @param stdClass $row The data for this line
	 * @param array &$classes the classes to add to the surrounding <li>
	 * @param array &$attribs
	 * @return bool
	 */
	public function onDeletedContributionsLineEnding( $pager, &$ret, $row, &$classes, &$attribs ) {
		if ( !$row instanceof Formatter\FormatterRow ) {
			return true;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var Formatter\ContributionsFormatter $formatter */
			$formatter = Container::get( 'formatter.contributions' );
			$line = $formatter->format( $row, $pager );
		} catch ( PermissionException $e ) {
			$line = false;
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed formatting contribution ' .
				json_encode( $row ) . ': ' . $e->getMessage() );
			MWExceptionHandler::logException( $e );
			$line = false;
		} finally {
			restore_error_handler();
		}

		if ( $line === false ) {
			return false;
		}

		$classes[] = 'mw-flow-contribution';
		$ret = $line;

		// If we output one or more lines of contributions entries we also need to include
		// the javascript that hooks into moderation actions.
		$pager->getOutput()->addModules( [ 'ext.flow.contributions' ] );
		$pager->getOutput()->addModuleStyles( [ 'ext.flow.contributions.styles' ] );

		return true;
	}

	/**
	 * Intercept contribution entries and format those belonging to Flow
	 *
	 * @param IContextSource $pager
	 * @param string &$ret The HTML line
	 * @param stdClass $row The data for this line
	 * @param array &$classes the classes to add to the surrounding <li>
	 * @param array &$attribs
	 * @return bool
	 */
	public function onContributionsLineEnding( $pager, &$ret, $row, &$classes, &$attribs ) {
		return static::onDeletedContributionsLineEnding( $pager, $ret, $row, $classes, $attribs );
	}

	/**
	 * Convert flow contributions entries into FeedItem instances
	 * for ApiFeedContributions
	 *
	 * @param object $row Single row of data from ContribsPager
	 * @param IContextSource $ctx The context to creat the feed item within
	 * @param FeedItem|null &$feedItem Return value holder for created feed item.
	 * @return bool
	 */
	public function onApiFeedContributions__feedItem( $row, $ctx, &$feedItem ) {
		if ( !$row instanceof Formatter\FormatterRow ) {
			return true;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var Formatter\FeedItemFormatter $formatter */
			$formatter = Container::get( 'formatter.contributions.feeditem' );
			$result = $formatter->format( $row, $ctx );
		} catch ( PermissionException $e ) {
			return false;
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed formatting contribution ' .
				json_encode( $row ) . ': ' . $e->getMessage() );
			MWExceptionHandler::logException( $e );
			return false;
		} finally {
			restore_error_handler();
		}

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
	 * Gets Flow contributions for contributions-related special pages
	 *
	 * @see onDeletedContributionsQuery
	 * @see onContributionsQuery
	 *
	 * @param array &$data
	 * @param IndexPager $pager
	 * @param string $offset
	 * @param int $limit
	 * @param bool $descending
	 * @param array $rangeOffsets Query range, in the format of [ startOffset, endOffset ]
	 * @return bool
	 */
	private static function getContributionsQuery( &$data, $pager, $offset, $limit, $descending, $rangeOffsets = [] ) {
		if (
			!( $pager instanceof ContribsPager ) &&
			!( $pager instanceof DeletedContribsPager )
		) {
			return false;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var Formatter\ContributionsQuery $query */
			$query = Container::get( 'query.contributions' );
			$results = $query->getResults( $pager, $offset, $limit, $descending, $rangeOffsets );
		} catch ( Exception $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed contributions query' );
			MWExceptionHandler::logException( $e );
			$results = false;
		} finally {
			restore_error_handler();
		}

		if ( $results === false ) {
			return false;
		}

		$data[] = $results;

		return true;
	}

	/**
	 * Adds Flow contributions to the DeletedContributions special page
	 *
	 * @inheritDoc
	 */
	public function onDeletedContribsPager__reallyDoQuery( &$data, $pager, $offset, $limit, $descending ) {
		return self::getContributionsQuery( $data, $pager, $offset, $limit, $descending, [ $pager->getEndOffset() ] );
	}

	/**
	 * Adds Flow contributions to the Contributions special page
	 *
	 * @inheritDoc
	 */
	public function onContribsPager__reallyDoQuery( &$data, $pager, $offset, $limit, $descending ) {
		// Flow has nothing to do with the tag filter, so ignore tag searches
		if ( $pager->getTagFilter() != false ) {
			return true;
		}

		return static::getContributionsQuery( $data, $pager, $offset, $limit, $descending, $pager->getRangeOffsets() );
	}

	/**
	 * Define and add descriptions for board-related variables
	 * @param array &$realValues
	 */
	public static function onAbuseFilterBuilder( &$realValues ) {
		$realValues['vars'] += [
			'board_id' => 'board-id',
			'board_namespace' => 'board-namespace',
			'board_title' => 'board-title',
			'board_prefixedtitle' => 'board-prefixedtitle',
		];
	}

	/**
	 * Add our deprecated variables
	 * @param array &$deprecatedVars
	 */
	public static function onAbuseFilterDeprecatedVariables( &$deprecatedVars ) {
		$deprecatedVars += [
			'board_articleid' => 'board_id',
			'board_text' => 'board_title',
			'board_prefixedtext' => 'board_prefixedtitle',
		];
	}

	/**
	 * Adds lazy-load methods for AbstractRevision objects.
	 *
	 * @param string $method Method to generate the variable
	 * @param VariableHolder $vars
	 * @param array $parameters Parameters with data to compute the value
	 * @param mixed &$result Result of the computation
	 * @return bool
	 */
	public static function onAbuseFilterComputeVariable(
		$method,
		VariableHolder $vars,
		$parameters,
		&$result
	) {
		// fetch all lazy-load methods
		$methods = self::getAbuseFilter()->lazyLoadMethods();

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
	 * @param RecentChange $rc
	 * @return bool false to abort email notification
	 */
	public function onAbortEmailNotification( $editor, $title, $rc ) {
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			// Since we are aborting the notification we need to manually update the watchlist
			$config = RequestContext::getMain()->getConfig();
			if ( $config->get( 'EnotifWatchlist' ) || $config->get( 'ShowUpdatedMarker' ) ) {
				MediaWikiServices::getInstance()->getWatchedItemStore()->updateNotificationTimestamp(
					$editor,
					$title,
					wfTimestampNow()
				);
			}
			return false;
		}

		if ( !$editor instanceof UserIdentity ) {
			return true;
		}

		if ( self::isTalkpageManagerUser( $editor ) ) {
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
	public function onArticleEditUpdateNewTalk( $page, $recipient ) {
		$user = User::newFromId( $page->getUser( RevisionRecord::RAW ) );

		if ( self::isTalkpageManagerUser( $user ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param UserIdentity $user
	 * @return bool
	 */
	public static function isTalkpageManagerUser( UserIdentity $user ) {
		return $user->getName() === FLOW_TALK_PAGE_MANAGER_USER;
	}

	public function onInfoAction( $ctx, &$pageinfo ) {
		if ( $ctx->getTitle()->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return;
		}

		// All of the info in this section is wrong for Flow pages,
		// so we'll just remove it.
		unset( $pageinfo['header-edits'] );

		// These keys are wrong on Flow pages, so we'll remove them
		static $badMessageKeys = [ 'pageinfo-length' ];

		foreach ( $pageinfo['header-basic'] as $num => $val ) {
			if ( $val[0] instanceof Message && in_array( $val[0]->getKey(), $badMessageKeys ) ) {
				unset( $pageinfo['header-basic'][$num] );
			}
		}
	}

	/**
	 * Provide detail about the Flow edit for checkusers using Special:CheckUser / Special:Investigate
	 *
	 * @param string &$ip
	 * @param string|false &$xff
	 * @param array &$row The row to be inserted (before defaults are applied)
	 * @param UserIdentity $user
	 * @param ?RecentChange $rc If triggered by a RecentChange, then this is the associated
	 *   RecentChange object. Null if not triggered by a RecentChange.
	 */
	public static function onCheckUserInsertChangesRow(
		string &$ip,
		&$xff,
		array &$row,
		UserIdentity $user,
		?RecentChange $rc
	) {
		if ( $rc === null || $rc->getAttribute( 'rc_source' ) !== RecentChangesListener::SRC_FLOW ) {
			return;
		}

		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$change = $params['flow-workflow-change'];

		// don't forget to increase the version number when data format changes
		$comment = CheckUserQuery::VERSION_PREFIX;
		$comment .= ',' . $change['action'];
		$comment .= ',' . $change['workflow'];
		$comment .= ',' . $change['revision'];

		$row['cuc_comment'] = $comment;
	}

	public function onIRCLineURL( &$url, &$query, $rc ) {
		if ( $rc->getAttribute( 'rc_source' ) !== RecentChangesListener::SRC_FLOW ) {
			return;
		}

		set_error_handler( new RecoverableErrorHandler, -1 );
		$result = null;
		try {
			/** @var Formatter\IRCLineUrlFormatter $formatter */
			$formatter = Container::get( 'formatter.irclineurl' );
			$result = $formatter->format( $rc );
		} catch ( Exception $e ) {
			$result = null;
			wfDebugLog( 'Flow', __METHOD__ . ': Failed formatting rc ' .
				$rc->getAttribute( 'rc_id' ) . ': ' . $e->getMessage() );
			MWExceptionHandler::logException( $e );
		} finally {
			restore_error_handler();
		}

		if ( $result !== null ) {
			$url = $result;
			$query = '';
		}
	}

	public function onWhatLinksHereProps( $row, $title, $target, &$props ) {
		set_error_handler( new RecoverableErrorHandler, -1 );
		try {
			/** @var ReferenceClarifier $clarifier */
			$clarifier = Container::get( 'reference.clarifier' );
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
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Add topiclist sortby to preferences.
	 *
	 * @param User $user
	 * @param array &$preferences
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['flow-topiclist-sortby'] = [
			'type' => 'api',
		];

		$preferences['flow-editor'] = [
			'type' => 'api'
		];

		$preferences['flow-side-rail-state'] = [
			'type' => 'api'
		];

		if ( ExtensionRegistry::getInstance()->isLoaded( 'VisualEditor' ) ) {
			$preferences['flow-visualeditor'] = [
				'type' => 'toggle',
				'label-message' => 'flow-preference-visualeditor',
				'section' => 'editing/editor',
			];
		}
	}

	/**
	 * @param User $user
	 * @param WikiPage $page
	 * @param Status &$status
	 * @return bool
	 */
	public static function handleWatchArticle( $user, WikiPage $page, &$status ) {
		$title = $page->getTitle();
		if ( $title->getNamespace() == NS_TOPIC ) {
			// @todo - use !$title->exists()?
			/** @var Data\ManagerGroup $storage */
			$storage = Container::get( 'storage' );
			$found = $storage->find(
				'PostRevision',
				[ 'rev_type_id' => strtolower( $title->getDBkey() ) ],
				[ 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 ]
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
	 * Don't watch a non-existing flow topic
	 *
	 * @param User $user
	 * @param WikiPage $page
	 * @param Status &$status
	 * @param string|null $expiry
	 * @return bool
	 */
	public function onWatchArticle( $user, $page, &$status, $expiry ) {
		return self::handleWatchArticle( $user, $page, $status );
	}

	/**
	 * Don't unwatch a non-existing flow topic
	 *
	 * @param User $user
	 * @param WikiPage $page
	 * @param Status &$status
	 * @return bool
	 */
	public function onUnwatchArticle( $user, $page, &$status ) {
		return self::handleWatchArticle( $user, $page, $status );
	}

	/**
	 * Checks whether this is a valid move technically.  MovePageIsValidMove should not
	 * be affected by the specific user, or user permissions.
	 *
	 * Those are handled in onMovePageCheckPermissions, called later.
	 *
	 * @param Title $oldTitle
	 * @param Title $newTitle
	 * @param Status $status Status to update with any technical issues
	 *
	 * @return bool true to continue, false to abort the hook
	 */
	public function onMovePageIsValidMove( $oldTitle, $newTitle, $status ) {
		// We only care about moving Flow boards, and *not* moving Flow topics
		// (but both are CONTENT_MODEL_FLOW_BOARD)
		if ( $oldTitle->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		// Pages within the Topic namespace are not movable
		// This is also enforced by the namespace configuration in extension.json.
		if ( $oldTitle->getNamespace() === NS_TOPIC ) {
			$status->fatal( 'flow-error-move-topic' );
			return false;
		}

		/** @var OccupationController $occupationController */
		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$flowStatus = $occupationController->checkIfCreationIsPossible( $newTitle, /*mustNotExist*/ true );
		$status->merge( $flowStatus );

		return true;
	}

	/**
	 * Checks whether user has permission to move the board.
	 *
	 * Technical restrictions are handled in onMovePageIsValidMove, called earlier.
	 *
	 * @param Title $oldTitle
	 * @param Title $newTitle
	 * @param User $user User doing the move
	 * @param string $reason Reason for the move
	 * @param Status $status Status updated with any permissions issue
	 */
	public function onMovePageCheckPermissions(
		$oldTitle,
		$newTitle,
		$user,
		$reason,
		$status
	) {
		// Only affect moves if the source has Flow content model
		if ( $oldTitle->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return;
		}

		/** @var OccupationController $occupationController */
		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$permissionStatus = $occupationController->checkIfUserHasPermission(
			$newTitle,
			$user
		);
		$status->merge( $permissionStatus );
	}

	/**
	 * @param Title $title
	 * @param string[] &$urls
	 */
	public function onTitleSquidURLs( $title, &$urls ) {
		if ( $title->getNamespace() !== NS_TOPIC ) {
			return;
		}
		try {
			$uuid = WorkflowLoaderFactory::uuidFromTitle( $title );
		} catch ( InvalidInputException $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Invalid title ' . $title->getPrefixedText() );
			return;
		}
		/** @var Data\ManagerGroup $storage */
		$storage = Container::get( 'storage' );
		$workflow = $storage->get( 'Workflow', $uuid );
		if ( !$workflow instanceof Model\Workflow ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Title for non-existent Workflow ' .
				$title->getPrefixedText() );
			return;
		}

		$htmlCache = MediaWikiServices::getInstance()->getHtmlCacheUpdater();
		$urls = array_merge(
			$urls,
			$htmlCache->getUrls( $workflow->getOwnerTitle() )
		);
	}

	/**
	 * @param array &$tools Extra links
	 * @param Title $title
	 * @param bool $redirect Whether the page is a redirect
	 * @param Skin $skin
	 * @param string &$link
	 */
	public function onWatchlistEditorBuildRemoveLine(
		&$tools,
		$title,
		$redirect,
		$skin,
		&$link
	) {
		if ( $title->getNamespace() !== NS_TOPIC ) {
			// Leave all non Flow topics alone!
			return;
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
			return;
		}

		try {
			// Find the title text of this specific topic
			$uuid = WorkflowLoaderFactory::uuidFromTitle( $title );
			$collection = PostCollection::newFromId( $uuid );
			$revision = $collection->getLastRevision();
		} catch ( Exception $e ) {
			wfWarn( __METHOD__ . ': Failed to locate revision for: ' . $title->getDBkey() );
			return;
		}

		$content = $revision->getContent( 'topic-title-plaintext' );
		$link = MediaWikiServices::getInstance()->getLinkRenderer()->makeLink( $title, $content );
	}

	/**
	 * @param array &$watchlistInfo Watchlisted pages
	 */
	public function onWatchlistEditorBeforeFormRender( &$watchlistInfo ) {
		if ( !isset( $watchlistInfo[NS_TOPIC] ) ) {
			// No topics watchlisted
			return;
		}

		$ids = array_keys( $watchlistInfo[NS_TOPIC] );

		// build array of queries to be executed all at once
		$queries = [];
		foreach ( $ids as $id ) {
			try {
				$uuid = WorkflowLoaderFactory::uuidFromTitlePair( NS_TOPIC, $id );
				$queries[] = [ 'rev_type_id' => $uuid ];
			} catch ( Exception $e ) {
				// invalid id
				unset( $watchlistInfo[NS_TOPIC][$id] );
			}
		}

		/** @var Data\ManagerGroup $storage */
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
			[ 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 ]
		);
	}

	/**
	 * For integration with the UserMerge extension. Provides the database and
	 * sets of table/column pairs to update user id's within.
	 *
	 * @param array &$updateFields
	 */
	public static function onUserMergeAccountFields( &$updateFields ) {
		/** @var Data\Utils\UserMerger $merger */
		$merger = Container::get( 'user_merger' );
		foreach ( $merger->getAccountFields() as $row ) {
			$updateFields[] = $row;
		}
	}

	/**
	 * Finalize the merge by purging any cached value that contained $oldUser
	 * @param User &$oldUser
	 * @param User &$newUser
	 */
	public static function onMergeAccountFromTo( User &$oldUser, User &$newUser ) {
		/** @var Data\Utils\UserMerger $merger */
		$merger = Container::get( 'user_merger' );
		$merger->finalizeMerge( $oldUser->getId(), $newUser->getId() );
	}

	/**
	 * Gives precedence to Flow over LQT.
	 * @param Title $title
	 * @param bool &$isLqtPage
	 */
	public static function onIsLiquidThreadsPage( Title $title, &$isLqtPage ) {
		if ( $isLqtPage && $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			$isLqtPage = false;
		}
	}

	public function onCategoryViewer__doCategoryQuery( $type, $res ) {
		if ( $type !== 'page' ) {
			return;
		}

		/** @var Formatter\CategoryViewerQuery $query */
		$query = Container::get( 'query.categoryviewer' );
		$query->loadMetadataBatch( $res );
	}

	public function onCategoryViewer__generateLink( $type, $title, $html, &$link ) {
		if ( $type !== 'page' || $title->getNamespace() !== NS_TOPIC ) {
			return;
		}
		$uuid = UUID::create( strtolower( $title->getDBkey() ) );
		if ( !$uuid ) {
			return;
		}
		/** @var Formatter\CategoryViewerQuery $query */
		$query = Container::get( 'query.categoryviewer' );
		$row = $query->getResult( $uuid );
		/** @var Formatter\CategoryViewerFormatter $formatter */
		$formatter = Container::get( 'formatter.categoryviewer' );
		$result = $formatter->format( $row );
		if ( $result ) {
			$link = $result;
		}
	}

	/**
	 * Gets error HTML for attempted NS_TOPIC deletion using core interface
	 *
	 * @param Title $title Topic title they are attempting to delete
	 * @return string Error html
	 */
	protected static function getTopicDeletionError( Title $title ) {
		$error = wfMessage( 'flow-error-core-topic-deletion', $title->getFullURL() )->parse();
		$wrappedError = Html::rawElement( 'span', [
			'class' => 'plainlinks',
		], $error );
		return $wrappedError;
	}

	// This should block them from wasting their time filling the form, but it won't
	// without a core change.  However, it does show the message.

	/**
	 * Shows an error message when the user visits the deletion form if the page is in
	 * the Topic namespace.
	 *
	 * @param Article $article Page the user requested to delete
	 * @param OutputPage $output Output page
	 * @param string &$reason Pre-filled reason given for deletion (note, this could
	 *   be used to customize this for boards and/or topics later)
	 * @return bool False if it is a Topic; otherwise, true
	 */
	public function onArticleConfirmDelete( $article, $output, &$reason ) {
		$title = $article->getTitle();
		if ( $title->inNamespace( NS_TOPIC ) ) {
			$output->addHTML( self::getTopicDeletionError( $title ) );
			return false;
		}

		return true;
	}

	/**
	 * Blocks topics from being deleted using the core deletion process, since it
	 * doesn't work.
	 *
	 * @param WikiPage $article Page the user requested to delete
	 * @param User $user User who requested to delete the article
	 * @param string &$reason Reason given for deletion
	 * @param string &$error Error explaining why we are not allowing the deletion
	 * @param Status &$status
	 * @param bool $suppress
	 * @return bool False if it is a Topic (to block it); otherwise, true
	 */
	public function onArticleDelete( WikiPage $article, User $user, &$reason, &$error, Status &$status, $suppress ) {
		$title = $article->getTitle();
		if ( $title->inNamespace( NS_TOPIC ) ) {
			$error = self::getTopicDeletionError( $title );
			return false;
		}

		return true;
	}

	/**
	 * Evicts topics from Squid/Varnish when the board is deleted.
	 * We do permission checks for this scenario, but since the topic isn't deleted
	 * at the core level, we need to evict it from Varnish ourselves.
	 *
	 * @param WikiPage $article Deleted article
	 * @param User $user User that deleted article
	 * @param string $reason Reason given
	 * @param int $articleId Article ID of deleted article
	 * @param Content|null $content Content that was deleted, or null on error
	 * @param LogEntry $logEntry Log entry for deletion
	 * @param int $archivedRevisionCount
	 */
	public function onArticleDeleteComplete(
		$article,
		$user,
		$reason,
		$articleId,
		$content,
		$logEntry,
		$archivedRevisionCount
	) {
		$title = $article->getTitle();

		// Topics use the same content model, but can't be deleted at the core
		// level currently.
		if ( $content !== null &&
			$title->getNamespace() !== NS_TOPIC &&
			$title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			$storage = Container::get( 'storage' );

			DeferredUpdates::addCallableUpdate( static function () use ( $storage, $articleId ) {
				/** @var Model\Workflow[] $workflows */
				$workflows = $storage->find( 'Workflow', [
					'workflow_wiki' => WikiMap::getCurrentWikiId(),
					'workflow_page_id' => $articleId,
				] );
				if ( !$workflows ) {
					return;
				}

				$topicTitles = [];
				foreach ( $workflows as $workflow ) {
					if ( $workflow->getType() === 'topic' ) {
						$topicTitles[] = $workflow->getArticleTitle();
					}
				}

				$hcu = MediaWikiServices::getInstance()->getHtmlCacheUpdater();
				$hcu->purgeTitleUrls( $topicTitles, $hcu::PURGE_INTENT_TXROUND_REFLECTED );
			} );
		}
	}

	/**
	 * @param RevisionRecord $revisionRecord Revision just undeleted
	 * @param ?int $oldPageId Old page ID stored with that revision when it was in the archive table
	 */
	public function onRevisionUndeleted( $revisionRecord, $oldPageId ) {
		$contentModel = $revisionRecord->getSlot(
			SlotRecord::MAIN, RevisionRecord::RAW
		)->getModel();
		if ( $contentModel === CONTENT_MODEL_FLOW_BOARD ) {
			// complete hack to make sure that when the page is saved to new
			// location and rendered it doesn't throw an error about the wrong title
			Container::get( 'factory.loader.workflow' )->pageMoveInProgress();

			$title = Title::newFromLinkTarget( $revisionRecord->getPageAsLinkTarget() );

			// Reassociate the Flow board associated with this undeleted revision.
			$boardMover = Container::get( 'board_mover' );
			$boardMover->move( intval( $oldPageId ), $title );
		}
	}

	/**
	 * @param Title $title Title corresponding to the article restored
	 * @param bool $create Whether or not the restoration caused the page to be created (i.e. it didn't exist before).
	 * @param string $comment The comment associated with the undeletion.
	 * @param int $oldPageId ID of page previously deleted (from archive table)
	 * @param array $restoredPages
	 */
	public function onArticleUndelete( $title, $create, $comment, $oldPageId, $restoredPages ) {
		// Avoid CI errors when other ArticleUndelete implementations are present, see: T356704
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			return;
		}
		$boardMover = Container::get( 'board_mover' );
		$boardMover->commit();
	}

	/**
	 * Occurs at the beginning of the MovePage process (just after the startAtomic).
	 *
	 * Perhaps ContentModel should be extended to be notified about moves explicitly.
	 * @param Title $oldTitle
	 * @param Title $newTitle
	 * @param User $user
	 */
	public function onTitleMoveStarting( $oldTitle, $newTitle, $user ) {
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
			// don't commit yet. That is done below in self::onPageMoveCompleting
			$boardMover = Container::get( 'board_mover' );
			$boardMover->move( $oldTitle->getArticleID(), $bogusTitle );
		}
	}

	public function onPageMoveCompleting(
		$oldTitle,
		$newTitle,
		$user,
		$pageid,
		$redirid,
		$reason,
		$revisionRecord
	) {
		$newTitle = Title::newFromLinkTarget( $newTitle );
		if ( $newTitle->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			Container::get( 'board_mover' )->commit();
		}
	}

	public function onShowMissingArticle( $article ) {
		if ( $article->getPage()->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		if ( $article->getTitle()->getNamespace() === NS_TOPIC ) {
			// @todo pretty message about invalid workflow
			throw new FlowException( 'Non-existent topic' );
		}

		$services = MediaWikiServices::getInstance();
		$emptyContent = $services->getContentHandlerFactory()
			->getContentHandler( CONTENT_MODEL_FLOW_BOARD )->makeEmptyContent();
		$contentRenderer = $services->getContentRenderer();
		$parserOutput = $contentRenderer->getParserOutput( $emptyContent, $article->getTitle() );
		$article->getContext()->getOutput()->addParserOutput( $parserOutput );

		return false;
	}

	/**
	 * Excludes NS_TOPIC from the list of searchable namespaces
	 *
	 * @param array &$namespaces Associative array mapping namespace index
	 *  to name
	 */
	public function onSearchableNamespaces( &$namespaces ) {
		unset( $namespaces[NS_TOPIC] );
	}

	/**
	 * @return bool
	 */
	private static function isBetaFeatureAvailable() {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			return false;
		}

		$config = RequestContext::getMain()->getConfig();
		$betaFeaturesAllowList = $config->get( 'BetaFeaturesAllowList' );

		return $config->get( 'FlowEnableOptInBetaFeature' )
			&& (
				!is_array( $betaFeaturesAllowList )
				|| in_array( BETA_FEATURE_FLOW_USER_TALK_PAGE, $betaFeaturesAllowList )
			);
	}

	/**
	 * @param User $user
	 * @param array &$prefs
	 */
	public function onGetBetaFeaturePreferences( $user, &$prefs ) {
		global $wgExtensionAssetsPath;

		if ( !self::isBetaFeatureAvailable() ) {
			return;
		}
		// Do not allow users to opt-in for Flow as preliminary sunset step
		if ( !BetaFeatures::isFeatureEnabled( $user, BETA_FEATURE_FLOW_USER_TALK_PAGE ) ) {
			return;
		}

		$prefs[BETA_FEATURE_FLOW_USER_TALK_PAGE] = [
			// The first two are message keys
			'label-message' => 'flow-talk-page-beta-feature-message',
			'desc-message' => 'flow-talk-page-beta-feature-description',
			'screenshot' => [
				'ltr' => "$wgExtensionAssetsPath/Flow/images/betafeature-flow-ltr.svg",
				'rtl' => "$wgExtensionAssetsPath/Flow/images/betafeature-flow-rtl.svg",
			],
			'info-link' => 'https://www.mediawiki.org/wiki/Flow',
			'discussion-link' => 'https://www.mediawiki.org/wiki/Talk:Flow',
			'exempt-from-auto-enrollment' => true,
		];
	}

	/**
	 * @param UserIdentity $user
	 * @param array &$modifiedOptions
	 * @param array $originalOptions
	 */
	public function onSaveUserOptions( UserIdentity $user, array &$modifiedOptions, array $originalOptions ) {
		if ( !self::isBetaFeatureAvailable() ) {
			return;
		}

		// Short circuit, it's fine because this beta feature is exempted from auto-enroll.
		if ( !array_key_exists( BETA_FEATURE_FLOW_USER_TALK_PAGE, $modifiedOptions ) ) {
			return;
		}

		$before = BetaFeatures::isFeatureEnabled( $user, BETA_FEATURE_FLOW_USER_TALK_PAGE, $originalOptions );
		$after = BetaFeatures::isFeatureEnabled( $user, BETA_FEATURE_FLOW_USER_TALK_PAGE );
		$action = null;

		$optInController = Container::get( 'controller.opt_in' );
		$user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity( $user );
		if ( !$before && $after ) {
			$action = OptInController::ENABLE;
			// Check if the user had a flow board
			if ( !$optInController->hasFlowBoardArchive( $user ) ) {
				// Enable the guided tour by setting the cookie
				RequestContext::getMain()->getRequest()->response()->setCookie( 'Flow_optIn_guidedTour', '1' );
			}
		} elseif ( $before && !$after ) {
			$action = OptInController::DISABLE;
		}

		if ( $action ) {
			$optInController->initiateChange( $action, $user->getTalkPage(), $user );
		}
	}

	/**
	 * @param WikiImporter $importer
	 * @return bool
	 */
	public function onImportHandleToplevelXMLTag( $importer ) {
		// only init Flow's importer once, then re-use it
		static $flowImporter = null;
		if ( $flowImporter === null ) {
			// importer can be dry-run (= parse, but don't store), but we can only
			// derive that from mPageOutCallback. I'll set a new value (which will
			// return the existing value) to see if it's in dry-run mode (= null)
			$callback = $importer->setPageOutCallback( null );
			// restore previous mPageOutCallback value
			$importer->setPageOutCallback( $callback );

			$flowImporter = new Dump\Importer( $importer );
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
		// The most likely case is that the filters were not applied correctly.
		$pages = array_filter( $pages, static function ( $entry ) {
			/** @var Title $title */
			$title = $entry[0];
			return $title->getNamespace() !== NS_TOPIC;
		} );

		if ( $pattern ) {
			// pattern is not supported
			return true;
		}

		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( RequestContext::getMain()->getUser(), 'flow-delete' )
		) {
			// there's no point adding topics since the current user won't be allowed to delete them
			return true;
		}

		// how many revisions are we allowed to retrieve now
		$newLimit = $limit - count( $pages );

		// we can't add anything
		if ( $newLimit < 1 ) {
			return true;
		}

		/** @var DbFactory $dbFactory */
		$dbFactory = Container::get( 'db.factory' );
		$dbr = $dbFactory->getDB( DB_REPLICA );

		// if a username is specified, search only for that user
		$userWhere = [];
		if ( $username ) {
			$user = User::newFromName( $username );
			if ( $user && $user->isRegistered() ) {
				$userWhere = [ 'tree_orig_user_id' => $user->getId() ];
			} else {
				$userWhere = [ 'tree_orig_user_ip' => $username ];
			}
		}

		// limit results to the range of RC
		global $wgRCMaxAge;
		$rcTimeLimit = UUID::getComparisonUUID( strtotime( "-$wgRCMaxAge seconds" ) );

		// get the latest revision id for each topic
		$result = $dbr->newSelectQueryBuilder()
			->select( [
				'revId' => 'MAX(r.rev_id)',
				'userIp' => "tree_orig_user_ip",
				'userId' => "tree_orig_user_id",
			] )
			->from( 'flow_revision', 'r' )
			->join( 'flow_tree_revision', null, 'r.rev_type_id=tree_rev_descendant_id' )
			->join( 'flow_workflow', null, 'r.rev_type_id=workflow_id' )
			->where( [
				'tree_parent_id' => null,
				'r.rev_type' => 'post',
				'workflow_wiki' => WikiMap::getCurrentWikiId(),
				$dbr->expr( 'workflow_id', '>', $rcTimeLimit->getBinary() )
			] )
			->andWhere( $userWhere )
			->groupBy( [ 'r.rev_type_id', 'tree_orig_user_ip' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		if ( $result->numRows() < 1 ) {
			return true;
		}

		$revIds = [];
		foreach ( $result as $r ) {
			$revIds[$r->revId] = [ 'userIp' => $r->userIp, 'userId' => $r->userId, 'name' => false ];
		}

		// get non-moderated revisions (but include hidden ones for T180607)
		$result = $dbr->newSelectQueryBuilder()
			->select( [
				'topicId' => 'rev_type_id',
				'revId' => 'rev_id'
			] )
			->from( 'flow_revision' )
			->where( [
				'rev_mod_state' => [ '', 'hide' ],
				'rev_id' => array_keys( $revIds )
			] )
			->limit( $newLimit )
			->orderBy( 'rev_type_id', SelectQueryBuilder::SORT_DESC )
			->caller( __METHOD__ )
			->fetchResultSet();

		// all topics previously found appear to be moderated
		if ( $result->numRows() < 1 ) {
			return true;
		}

		// keep only the relevant topics in [topicId => userInfo] format
		$limitedRevIds = [];
		foreach ( $result as $r ) {
			$limitedRevIds[$r->topicId] = $revIds[$r->revId];
		}

		// fill usernames if no $username filter was specified
		if ( !$username ) {
			$userIds = array_column( array_values( $limitedRevIds ), 'userId' );
			$userIds = array_filter( $userIds );

			$userMap = [];
			if ( $userIds ) {
				$wikiDbr = $dbFactory->getWikiDB( DB_REPLICA );
				$result = $wikiDbr->newSelectQueryBuilder()
					->select( [ 'user_id', 'user_name' ] )
					->from( 'user' )
					->where( [ 'user_id' => array_values( $userIds ) ] )
					->caller( __METHOD__ )
					->fetchResultSet();
				foreach ( $result as $r ) {
					$userMap[$r->user_id] = $r->user_name;
				}
			}

			// set name in userInfo structure
			foreach ( $limitedRevIds as $topicId => &$userInfo ) {
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
		unset( $userInfo );

		// add results to the list of pages to nuke
		foreach ( $limitedRevIds as $topicId => $userInfo ) {
			$pages[] = [
				Title::makeTitle( NS_TOPIC, UUID::create( $topicId )->getAlphadecimal() ),
				$userInfo['name']
			];
		}

		return true;
	}

	public static function onNukeDeletePage( Title $title, $reason, &$deletionResult ) {
		if ( $title->getNamespace() !== NS_TOPIC ) {
			// we don't handle it
			return true;
		}

		$action = 'moderate-topic';
		$params = [
			'topic' => [
				'moderationState' => 'delete',
				'reason' => $reason,
				'page' => $title->getPrefixedText()
			],
		];

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
		$errors = [];
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

	/**
	 * Filter out all Flow changes when hidepageedits=1
	 *
	 * @param string $name
	 * @param array &$tables
	 * @param array &$fields
	 * @param array &$conds
	 * @param array &$query_options
	 * @param array &$join_conds
	 * @param FormOptions $opts
	 */
	public function onChangesListSpecialPageQuery(
		$name, &$tables, &$fields, &$conds,
		&$query_options, &$join_conds, $opts
	) {
		try {
			$hidePageEdits = $opts->getValue( 'hidepageedits' );
		} catch ( MWException $e ) {
			// If not set, assume they should be hidden.
			$hidePageEdits = true;
		}
		if ( $hidePageEdits ) {
			$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
			$conds[] = $dbr->expr( 'rc_type', '!=', RC_FLOW );
		}
	}

	/**
	 * @inheritDoc
	 * @return false|void
	 */
	public function onGetUserPermissionsErrors( $title, $user, $action, &$result ) {
		global $wgFlowReadOnly;

		$tempUserConfig = MediaWikiServices::getInstance()->getTempUserConfig();
		// Flow has no support for temp accounts. If temp accounts are
		// known on the wiki, don't let anonymous users edit, and
		// don't let temporary users edit either.
		if (
			$tempUserConfig->isKnown() && !$user->isNamed() &&
			$title->getContentModel() === CONTENT_MODEL_FLOW_BOARD &&
			$action !== 'read'
		) {
			$result = 'flow-error-protected-readonly';
			return false;
		}

		if ( !$wgFlowReadOnly ) {
			return;
		}

		// Deny all actions related to Flow pages, and deny all flow-create-board actions,
		// but allow read and delete/undelete
		$allowedActions = [ 'read', 'delete', 'undelete' ];
		if (
			$action === 'flow-create-board' ||
			(
				$title->getContentModel() === CONTENT_MODEL_FLOW_BOARD &&
				!in_array( $action, $allowedActions )
			)
		) {
			$result = 'flow-error-protected-readonly';
			return false;
		}
	}

	/**
	 * Return information about terms-of-use messages.
	 *
	 * @param MessageLocalizer $context
	 * @param Config $config
	 * @return array Map from internal name to array of parameters for MessageLocalizer::msg()
	 * @phan-return non-empty-array[]
	 */
	private static function getTermsOfUseMessages(
		MessageLocalizer $context, Config $config
	): array {
		$messages = [
			'new-topic' => [ 'flow-terms-of-use-new-topic' ],
			'reply' => [ 'flow-terms-of-use-reply' ],
			'edit' => [ 'flow-terms-of-use-edit' ],
			'summarize' => [ 'flow-terms-of-use-summarize' ],
			'lock-topic' => [ 'flow-terms-of-use-lock-topic' ],
			'unlock-topic' => [ 'flow-terms-of-use-unlock-topic' ],
		];

		$hookRunner = new HookRunner( MediaWikiServices::getInstance()->getHookContainer() );
		$hookRunner->onFlowTermsOfUseMessages( $messages, $context, $config );

		return $messages;
	}

	/**
	 * Return parsed terms-of-use messages, for use in a ResourceLoader module.
	 *
	 * @param MessageLocalizer $context
	 * @param Config $config
	 * @return array
	 */
	public static function getTermsOfUseMessagesParsed(
		MessageLocalizer $context, Config $config
	): array {
		$messages = self::getTermsOfUseMessages( $context, $config );
		foreach ( $messages as &$msg ) {
			$msg = $context->msg( ...$msg )->parse();
		}
		return $messages;
	}

	/**
	 * Return information about terms-of-use messages, for use in a ResourceLoader module as
	 * 'versionCallback'. This is to avoid calling the parser from version invalidation code.
	 *
	 * @param MessageLocalizer $context
	 * @param Config $config
	 * @return array
	 */
	public static function getTermsOfUseMessagesVersion(
		MessageLocalizer $context, Config $config
	): array {
		$messages = self::getTermsOfUseMessages( $context, $config );
		foreach ( $messages as &$msg ) {
			$message = $context->msg( ...$msg );
			$msg = [
				// Include the text of the message, in case the canonical translation changes
				$message->plain(),
				// Include the page touched time, in case the on-wiki override is invalidated
				Title::makeTitle( NS_MEDIAWIKI, ucfirst( $message->getKey() ) )->getTouched(),
			];
		}
		return $messages;
	}
}
