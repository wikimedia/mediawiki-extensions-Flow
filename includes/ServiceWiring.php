<?php

use Flow\Data\FlowObjectCache;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\Data\Storage\PostRevisionTopicHistoryStorage;
use Flow\DbFactory;
use Flow\FlowActions;
use Flow\Notifications\Controller as NotificationsController;
use Flow\Repository\TreeRepository;
use Flow\RevisionActionPermissions;
use Flow\TemplateHelper;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
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

	'FlowNotificationsController' => static function (
		MediaWikiServices $services
	): NotificationsController {
		return new NotificationsController(
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

	'FlowTemplateHandler' => static function ( MediaWikiServices $services ): TemplateHelper {
		return new TemplateHelper(
			__DIR__ . '/../handlebars',
			$services->getMainConfig()->get( 'FlowServerCompileTemplates' )
		);
	},

	'FlowTreeRepository' => static function ( MediaWikiServices $services ): TreeRepository {
		// Database Access Layer external from main implementation
		return new TreeRepository(
			$services->getService( 'FlowDbFactory' ),
			$services->getService( 'FlowCache' )
		);
	},

	'FlowUser' => static function ( MediaWikiServices $services ): User {
		if ( defined( 'RUN_MAINTENANCE_IF_MAIN' ) ) {
			return new User;
		} else {
			return RequestContext::getMain()->getUser();
		}
	},
];
