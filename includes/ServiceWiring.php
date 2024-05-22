<?php

use Flow\Data\FlowObjectCache;
use Flow\Data\Listener\EditCountListener;
use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\Data\Storage\PostRevisionTopicHistoryStorage;
use Flow\DbFactory;
use Flow\FlowActions;
use Flow\Formatter\CategoryViewerFormatter;
use Flow\Notifications\Controller as NotificationsController;
use Flow\Parsoid\ContentFixer;
use Flow\Parsoid\Fixer\BadImageRemover;
use Flow\Parsoid\Fixer\BaseHrefFixer;
use Flow\Parsoid\Fixer\ExtLinkFixer;
use Flow\Parsoid\Fixer\WikiLinkFixer;
use Flow\Repository\TreeRepository;
use Flow\Repository\UserName\OneStepUserNameQuery;
use Flow\Repository\UserNameBatch;
use Flow\RevisionActionPermissions;
use Flow\TalkpageManager;
use Flow\TemplateHelper;
use Flow\Templating;
use Flow\UrlGenerator;
use Flow\WatchedTopicItems;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;

/**
 * Service wiring for Flow services
 *
 * Currently most services are defined in and retrieved
 * from the flow container, but they should be moved
 * here, see T170330.
 *
 * PHPUnit doesn't understand code coverage for code outside of classes/functions,
 * like service wiring files.
 * @codeCoverageIgnore
 *
 * @author DannyS712
 *
 * @phpcs-require-sorted-array
 */
return [
	'FlowActions' => static function ( MediaWikiServices $services ): FlowActions {
		// Flow configuration
		return new FlowActions(
			require __DIR__ . '/../FlowActions.php'
		);
	},

	'FlowCache' => static function ( MediaWikiServices $services ): FlowObjectCache {
		// New storage implementation
		return new FlowObjectCache(
			$services->getMainWANObjectCache(),
			$services->getService( 'FlowDbFactory' ),
			$services->getMainConfig()->get( 'FlowCacheTime' )
		);
	},

	'FlowCategoryViewerFormatter' => static function (
		MediaWikiServices $services
	): CategoryViewerFormatter {
		return new CategoryViewerFormatter(
			$services->getService( 'FlowPermissions' )
		);
	},

	'FlowDbFactory' => static function ( MediaWikiServices $services ): DbFactory {
		// Always returns the correct database for flow storage
		$config = $services->getMainConfig();
		return new DbFactory(
			$config->get( 'FlowDefaultWikiDb' ),
			$config->get( 'FlowCluster' )
		);
	},

	'FlowDefaultLogger' => static function ( MediaWikiServices $services ): LoggerInterface {
		return LoggerFactory::getInstance( 'Flow' );
	},

	'FlowEditCountListener' => static function ( MediaWikiServices $services ): EditCountListener {
		return new EditCountListener(
			$services->getService( 'FlowActions' )
		);
	},

	'FlowNotificationsController' => static function (
		MediaWikiServices $services
	): NotificationsController {
		return new NotificationsController(
			new ServiceOptions( NotificationsController::CONSTRUCTOR_OPTIONS, $services->getMainConfig() ),
			$services->getContentLanguage(),
			$services->getService( 'FlowTreeRepository' )
		);
	},

	'FlowPermissions' => static function ( MediaWikiServices $services ): RevisionActionPermissions {
		return new RevisionActionPermissions(
			$services->getService( 'FlowActions' ),
			$services->getService( 'FlowUser' )
		);
	},

	'FlowPostRevisionStorage' => static function ( MediaWikiServices $services ): PostRevisionStorage {
		return new PostRevisionStorage(
			$services->getService( 'FlowDbFactory' ),
			$services->getMainConfig()->get( 'FlowExternalStore' ),
			$services->getService( 'FlowTreeRepository' )
		);
	},

	'FlowPostRevisionTopicHistoryStorage' => static function (
		MediaWikiServices $services
	): PostRevisionTopicHistoryStorage {
		return new PostRevisionTopicHistoryStorage(
			$services->getService( 'FlowPostRevisionStorage' ),
			$services->getService( 'FlowTreeRepository' )
		);
	},

	'FlowStorage.WorkflowMapper' => static function (
		MediaWikiServices $services
	): CachingObjectMapper {
		return CachingObjectMapper::model(
			\Flow\Model\Workflow::class,
			[ 'workflow_id' ]
		);
	},

	'FlowTalkpageManager' => static function ( MediaWikiServices $services ): TalkpageManager {
		return new TalkpageManager( $services->getUserGroupManager() );
	},

	'FlowTemplateHandler' => static function ( MediaWikiServices $services ): TemplateHelper {
		return new TemplateHelper(
			__DIR__ . '/../handlebars',
			$services->getMainConfig()->get( 'FlowServerCompileTemplates' )
		);
	},

	'FlowTemplating' => static function ( MediaWikiServices $services ): Templating {
		global $wgArticlePath;

		$wikiLinkFixer = new WikiLinkFixer(
			$services->getLinkBatchFactory()->newLinkBatch()
		);
		$badImageRemover = new BadImageRemover(
			[ $services->getBadFileLookup(), 'isBadFile' ]
		);
		$contextFixer = new ContentFixer(
			$wikiLinkFixer,
			$badImageRemover,
			new BaseHrefFixer( $wgArticlePath ),
			new ExtLinkFixer()
		);

		return new Templating(
			$services->getService( 'FlowUserNameRepository' ),
			$contextFixer,
			$services->getService( 'FlowPermissions' )
		);
	},

	'FlowTreeRepository' => static function ( MediaWikiServices $services ): TreeRepository {
		// Database Access Layer external from main implementation
		return new TreeRepository(
			$services->getService( 'FlowDbFactory' ),
			$services->getService( 'FlowCache' )
		);
	},

	'FlowUrlGenerator' => static function ( MediaWikiServices $services ): UrlGenerator {
		return new UrlGenerator(
			$services->getService( 'FlowStorage.WorkflowMapper' )
		);
	},

	'FlowUser' => static function ( MediaWikiServices $services ): User {
		if ( defined( 'RUN_MAINTENANCE_IF_MAIN' ) ) {
			return new User;
		} else {
			return RequestContext::getMain()->getUser();
		}
	},

	'FlowUserNameRepository' => static function ( MediaWikiServices $services ): UserNameBatch {
		return new UserNameBatch(
			new OneStepUserNameQuery(
				$services->getService( 'FlowDbFactory' ),
				$services->getHideUserUtils()
			)
		);
	},

	'FlowWatchedTopicItems' => static function ( MediaWikiServices $services ): WatchedTopicItems {
		return new Flow\WatchedTopicItems(
			$services->getService( 'FlowUser' ),
			$services->getConnectionProvider()
				->getReplicaDatabase( false, 'watchlist' )
		);
	},

];
