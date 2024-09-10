<?php

use Flow\Data\FlowObjectCache;
use Flow\Data\Index\TopKIndex;
use Flow\Data\Index\UniqueFeatureIndex;
use Flow\Data\Listener\EditCountListener;
use Flow\Data\Mapper\BasicObjectMapper;
use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Data\ObjectManager;
use Flow\Data\Storage\BasicDbStorage;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\Data\Storage\PostRevisionTopicHistoryStorage;
use Flow\Data\Storage\TopicListStorage;
use Flow\DbFactory;
use Flow\FlowActions;
use Flow\Formatter\CategoryViewerFormatter;
use Flow\Formatter\ChangesListFormatter;
use Flow\Formatter\CheckUserFormatter;
use Flow\Formatter\ContributionsFormatter;
use Flow\Formatter\FeedItemFormatter;
use Flow\Formatter\IRCLineUrlFormatter;
use Flow\Formatter\RevisionDiffViewFormatter;
use Flow\Formatter\RevisionFormatterFactory;
use Flow\Formatter\RevisionUndoViewFormatter;
use Flow\Formatter\RevisionViewFormatter;
use Flow\Formatter\TocTopicListFormatter;
use Flow\Formatter\TopicFormatter;
use Flow\Formatter\TopicListFormatter;
use Flow\Import\ArchiveNameHelper;
use Flow\Import\OptInController;
use Flow\Notifications\Controller as NotificationsController;
use Flow\OccupationController;
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
use MediaWiki\Context\RequestContext;
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

	'FlowChangesListFormatter' => static function (
		MediaWikiServices $services
	): ChangesListFormatter {
		return new ChangesListFormatter(
			$services->getService( 'FlowPermissions' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
		);
	},

	'FlowCheckUserFormatter' => static function (
		MediaWikiServices $services
	): CheckUserFormatter {
		return new CheckUserFormatter(
			$services->getService( 'FlowPermissions' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
		);
	},

	'FlowContributionsFormatter' => static function (
		MediaWikiServices $services
	): ContributionsFormatter {
		return new ContributionsFormatter(
			$services->getService( 'FlowPermissions' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
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

	'FlowDeferredQueue' => static function ( MediaWikiServices $services ): SplQueue {
		// Queue of callbacks to run by DeferredUpdates, but only
		// on successful commit
		return new SplQueue;
	},

	'FlowEditCountListener' => static function ( MediaWikiServices $services ): EditCountListener {
		return new EditCountListener(
			$services->getService( 'FlowActions' )
		);
	},

	'FlowFeedItemFormatter' => static function (
		MediaWikiServices $services
	): FeedItemFormatter {
		return new FeedItemFormatter(
			$services->getService( 'FlowPermissions' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
		);
	},

	'FlowIRCLineUrlFormatter' => static function (
		MediaWikiServices $services
	): IRCLineUrlFormatter {
		return new IRCLineUrlFormatter(
			$services->getService( 'FlowPermissions' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
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

	'FlowOptInController' => static function (
		MediaWikiServices $services
	): OptInController {
		/** @var OccupationController $occupationController */
		$occupationController = $services->getService( 'FlowTalkpageManager' );
		$archiveNameHelper = new ArchiveNameHelper();
		return new OptInController(
			$occupationController,
			$services->getService( 'FlowNotificationsController' ),
			$archiveNameHelper,
			$services->getService( 'FlowDefaultLogger' ),
			$occupationController->getTalkpageManager()
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

	'FlowRevisionDiffViewFormatter' => static function (
		MediaWikiServices $services
	): RevisionDiffViewFormatter {
		return new RevisionDiffViewFormatter(
			$services->getService( 'FlowRevisionViewFormatter' ),
			$services->getService( 'FlowUrlGenerator' )
		);
	},

	'FlowRevisionFormatterFactory' => static function (
		MediaWikiServices $services
	): RevisionFormatterFactory {
		global $wgFlowMaxThreadingDepth;

		return new RevisionFormatterFactory(
			$services->getService( 'FlowPermissions' ),
			$services->getService( 'FlowTemplating' ),
			$services->getService( 'FlowUrlGenerator' ),
			$services->getService( 'FlowUserNameRepository' ),
			$wgFlowMaxThreadingDepth
		);
	},

	'FlowRevisionUndoViewFormatter' => static function (
		MediaWikiServices $services
	): RevisionUndoViewFormatter {
		return new RevisionUndoViewFormatter(
			$services->getService( 'FlowRevisionViewFormatter' )
		);
	},

	'FlowRevisionViewFormatter' => static function (
		MediaWikiServices $services
	): RevisionViewFormatter {
		return new RevisionViewFormatter(
			$services->getService( 'FlowUrlGenerator' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
		);
	},

	'FlowStorage.TopicList' => static function ( MediaWikiServices $services ): ObjectManager {
		// Lookup from topic_id to its owning board id
		$topicListPrimaryIndex = new UniqueFeatureIndex(
			$services->getService( 'FlowCache' ),
			$services->getService( 'FlowStorage.TopicList.Backend' ),
			$services->getService( 'FlowStorage.TopicList.Mapper' ),
			'flow_topic_list:topic',
			[ 'topic_id' ]
		);
		// Lookup from board to contained topics
		// In reverse order by topic_id
		$topicListReverseLookupIndex = new TopKIndex(
			$services->getService( 'FlowCache' ),
			$services->getService( 'FlowStorage.TopicList.Backend' ),
			$services->getService( 'FlowStorage.TopicList.Mapper' ),
			'flow_topic_list:list',
			[ 'topic_list_id' ],
			[ 'sort' => 'topic_id' ]
		);
		$indexes = [
			$topicListPrimaryIndex,
			$topicListReverseLookupIndex,
			$services->getService( 'FlowStorage.TopicList.LastUpdatedIndex' )
		];
		return new ObjectManager(
			$services->getService( 'FlowStorage.TopicList.Mapper' ),
			$services->getService( 'FlowStorage.TopicList.Backend' ),
			$services->getService( 'FlowDbFactory' ),
			$indexes
		);
	},

	'FlowStorage.TopicList.Backend' => static function ( MediaWikiServices $services ): TopicListStorage {
		return new TopicListStorage(
			// factory and table
			$services->getService( 'FlowDbFactory' ),
			'flow_topic_list',
			[ 'topic_list_id', 'topic_id' ]
		);
	},

	'FlowStorage.TopicList.LastUpdatedIndex' => static function ( MediaWikiServices $services ): TopKIndex {
		// In reverse order by topic last_updated
		return new TopKIndex(
			$services->getService( 'FlowCache' ),
			$services->getService( 'FlowStorage.TopicList.Backend' ),
			$services->getService( 'FlowStorage.TopicList.Mapper' ),
			'flow_topic_list_last_updated:list',
			[ 'topic_list_id' ],
			[
				'sort' => 'workflow_last_update_timestamp',
				'order' => 'desc'
			]
		);
	},

	'FlowStorage.TopicList.Mapper' => static function ( MediaWikiServices $services ): BasicObjectMapper {
		// Must be BasicObjectMapper, due to variance in when
		// we have workflow_last_update_timestamp
		return BasicObjectMapper::model(
			\Flow\Model\TopicListEntry::class
		);
	},

	'FlowStorage.UrlReference' => static function ( MediaWikiServices $services ): ObjectManager {
		$urlReferenceMapper = BasicObjectMapper::model(
			\Flow\Model\URLReference::class
		);
		$urlReferenceBackend = new BasicDbStorage(
			// factory and table
			$services->getService( 'FlowDbFactory' ),
			'flow_ext_ref',
			[
				'ref_src_wiki',
				'ref_src_namespace',
				'ref_src_title',
				'ref_src_object_id',
				'ref_type',
				'ref_target',
			]
		);
		$urlReferenceSourceLookupIndex = new TopKIndex(
			$services->getService( 'FlowCache' ),
			$urlReferenceBackend,
			$urlReferenceMapper,
			'flow_ref:url:by-source:v3',
			[
				'ref_src_wiki',
				'ref_src_namespace',
				'ref_src_title',
			],
			[
				'order' => 'ASC',
				'sort' => 'ref_src_object_id',
			]
		);
		$urlReferenceRevisionLookupIndex = new TopKIndex(
			$services->getService( 'FlowCache' ),
			$urlReferenceBackend,
			$urlReferenceMapper,
			'flow_ref:url:by-revision:v3',
			[
				'ref_src_wiki',
				'ref_src_object_type',
				'ref_src_object_id',
			],
			[
				'order' => 'ASC',
				'sort' => [ 'ref_target' ],
			]
		);
		$indexes = [
			$urlReferenceSourceLookupIndex,
			$urlReferenceRevisionLookupIndex,
		];
		return new ObjectManager(
			$urlReferenceMapper,
			$urlReferenceBackend,
			$services->getService( 'FlowDbFactory' ),
			$indexes,
			[]
		);
	},

	'FlowStorage.WikiReference' => static function ( MediaWikiServices $services ): ObjectManager {
		$wikiReferenceMapper = BasicObjectMapper::model(
			\Flow\Model\WikiReference::class
		);
		$wikiReferenceBackend = new BasicDbStorage(
			$services->getService( 'FlowDbFactory' ),
			'flow_wiki_ref',
			[
				'ref_src_wiki',
				'ref_src_namespace',
				'ref_src_title',
				'ref_src_object_id',
				'ref_type',
				'ref_target_namespace',
				'ref_target_title'
			]
		);
		$wikiReferenceSourceLookupIndex = new TopKIndex(
			$services->getService( 'FlowCache' ),
			$wikiReferenceBackend,
			$wikiReferenceMapper,
			'flow_ref:wiki:by-source:v3',
			[
				'ref_src_wiki',
				'ref_src_namespace',
				'ref_src_title',
			],
			[
				'order' => 'ASC',
				'sort' => 'ref_src_object_id',
			]
		);
		$wikiReferenceRevisionLookupIndex = new TopKIndex(
			$services->getService( 'FlowCache' ),
			$wikiReferenceBackend,
			$wikiReferenceMapper,
			'flow_ref:wiki:by-revision:v3',
			[
				'ref_src_wiki',
				'ref_src_object_type',
				'ref_src_object_id',
			],
			[
				'order' => 'ASC',
				'sort' => [ 'ref_target_namespace', 'ref_target_title' ],
			]
		);
		$indexes = [
			$wikiReferenceSourceLookupIndex,
			$wikiReferenceRevisionLookupIndex,
		];
		return new ObjectManager(
			$wikiReferenceMapper,
			$wikiReferenceBackend,
			$services->getService( 'FlowDbFactory' ),
			$indexes,
			[]
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

	'FlowTalkpageManager' => static function ( MediaWikiServices $services ): OccupationController {
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
			new BaseHrefFixer( $wgArticlePath, $services->getUrlUtils() ),
			new ExtLinkFixer( $services->getUrlUtils() )
		);

		return new Templating(
			$services->getService( 'FlowUserNameRepository' ),
			$contextFixer,
			$services->getService( 'FlowPermissions' )
		);
	},

	'FlowTocTopicListFormatter' => static function (
		MediaWikiServices $services
	): TocTopicListFormatter {
		return new TocTopicListFormatter(
			$services->getService( 'FlowTemplating' )
		);
	},

	'FlowTopicFormatter' => static function (
		MediaWikiServices $services
	): TopicFormatter {
		return new TopicFormatter(
			$services->getService( 'FlowUrlGenerator' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
		);
	},

	'FlowTopicListFormatter' => static function (
		MediaWikiServices $services
	): TopicListFormatter {
		return new TopicListFormatter(
			$services->getService( 'FlowUrlGenerator' ),
			$services->getService( 'FlowRevisionFormatterFactory' )->create()
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
