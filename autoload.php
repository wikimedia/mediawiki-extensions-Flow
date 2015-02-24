<?php
// This file is generated by scripts/gen-autoload.php, do not adjust manually

global $wgAutoloadClasses;

$wgAutoloadClasses += array(
	'ApiFlow' => __DIR__ . '/includes/api/ApiFlow.php',
	'ApiFlowBase' => __DIR__ . '/includes/api/ApiFlowBase.php',
	'ApiFlowBaseGet' => __DIR__ . '/includes/api/ApiFlowBaseGet.php',
	'ApiFlowBasePost' => __DIR__ . '/includes/api/ApiFlowBasePost.php',
	'ApiFlowEditHeader' => __DIR__ . '/includes/api/ApiFlowEditHeader.php',
	'ApiFlowEditPost' => __DIR__ . '/includes/api/ApiFlowEditPost.php',
	'ApiFlowEditTitle' => __DIR__ . '/includes/api/ApiFlowEditTitle.php',
	'ApiFlowEditTopicSummary' => __DIR__ . '/includes/api/ApiFlowEditTopicSummary.php',
	'ApiFlowLockTopic' => __DIR__ . '/includes/api/ApiFlowLockTopic.php',
	'ApiFlowModeratePost' => __DIR__ . '/includes/api/ApiFlowModeratePost.php',
	'ApiFlowModerateTopic' => __DIR__ . '/includes/api/ApiFlowModerateTopic.php',
	'ApiFlowNewTopic' => __DIR__ . '/includes/api/ApiFlowNewTopic.php',
	'ApiFlowReply' => __DIR__ . '/includes/api/ApiFlowReply.php',
	'ApiFlowViewHeader' => __DIR__ . '/includes/api/ApiFlowViewHeader.php',
	'ApiFlowViewPost' => __DIR__ . '/includes/api/ApiFlowViewPost.php',
	'ApiFlowViewTopic' => __DIR__ . '/includes/api/ApiFlowViewTopic.php',
	'ApiFlowViewTopicList' => __DIR__ . '/includes/api/ApiFlowViewTopicList.php',
	'ApiFlowViewTopicSummary' => __DIR__ . '/includes/api/ApiFlowViewTopicSummary.php',
	'ApiParsoidUtilsFlow' => __DIR__ . '/includes/api/ApiParsoidUtilsFlow.php',
	'ApiQueryPropFlowInfo' => __DIR__ . '/includes/api/ApiQueryPropFlowInfo.php',
	'FlowHooks' => __DIR__ . '/Hooks.php',
	'Flow\\Actions\\CompareHeaderRevisionsAction' => __DIR__ . '/includes/Actions/CompareHeaderRevisionsAction.php',
	'Flow\\Actions\\ComparePostRevisionsAction' => __DIR__ . '/includes/Actions/ComparePostRevisionsAction.php',
	'Flow\\Actions\\ComparePostSummaryRevisionsAction' => __DIR__ . '/includes/Actions/ComparePostSummaryRevisionsAction.php',
	'Flow\\Actions\\CreateTopicSummaryAction' => __DIR__ . '/includes/Actions/CreateTopicSummaryAction.php',
	'Flow\\Actions\\EditAction' => __DIR__ . '/includes/Actions/EditAction.php',
	'Flow\\Actions\\EditHeaderAction' => __DIR__ . '/includes/Actions/EditHeaderAction.php',
	'Flow\\Actions\\EditPostAction' => __DIR__ . '/includes/Actions/EditPostAction.php',
	'Flow\\Actions\\EditTitleAction' => __DIR__ . '/includes/Actions/EditTitleAction.php',
	'Flow\\Actions\\EditTopicSummaryAction' => __DIR__ . '/includes/Actions/EditTopicSummaryAction.php',
	'Flow\\Actions\\FlowAction' => __DIR__ . '/includes/Actions/Action.php',
	'Flow\\Actions\\HistoryAction' => __DIR__ . '/includes/Actions/HistoryAction.php',
	'Flow\\Actions\\LockTopicAction' => __DIR__ . '/includes/Actions/LockTopicAction.php',
	'Flow\\Actions\\ModeratePostAction' => __DIR__ . '/includes/Actions/ModeratePostAction.php',
	'Flow\\Actions\\ModerateTopicAction' => __DIR__ . '/includes/Actions/ModerateTopicAction.php',
	'Flow\\Actions\\NewTopicAction' => __DIR__ . '/includes/Actions/NewTopicAction.php',
	'Flow\\Actions\\PostSingleViewAction' => __DIR__ . '/includes/Actions/PostSingleViewAction.php',
	'Flow\\Actions\\PurgeAction' => __DIR__ . '/includes/Actions/PurgeAction.php',
	'Flow\\Actions\\ReplyAction' => __DIR__ . '/includes/Actions/ReplyAction.php',
	'Flow\\Actions\\RestorePostAction' => __DIR__ . '/includes/Actions/RestorePostAction.php',
	'Flow\\Actions\\RestoreTopicAction' => __DIR__ . '/includes/Actions/RestoreTopicAction.php',
	'Flow\\Actions\\ViewAction' => __DIR__ . '/includes/Actions/ViewAction.php',
	'Flow\\Actions\\ViewHeaderAction' => __DIR__ . '/includes/Actions/ViewHeaderAction.php',
	'Flow\\Actions\\ViewTopicSummaryAction' => __DIR__ . '/includes/Actions/ViewTopicSummaryAction.php',
	'Flow\\BlockFactory' => __DIR__ . '/includes/BlockFactory.php',
	'Flow\\Block\\AbstractBlock' => __DIR__ . '/includes/Block/Block.php',
	'Flow\\Block\\Block' => __DIR__ . '/includes/Block/Block.php',
	'Flow\\Block\\BoardHistoryBlock' => __DIR__ . '/includes/Block/BoardHistory.php',
	'Flow\\Block\\HeaderBlock' => __DIR__ . '/includes/Block/Header.php',
	'Flow\\Block\\TopicBlock' => __DIR__ . '/includes/Block/Topic.php',
	'Flow\\Block\\TopicListBlock' => __DIR__ . '/includes/Block/TopicList.php',
	'Flow\\Block\\TopicSummaryBlock' => __DIR__ . '/includes/Block/TopicSummary.php',
	'Flow\\Collection\\AbstractCollection' => __DIR__ . '/includes/Collection/AbstractCollection.php',
	'Flow\\Collection\\CollectionCache' => __DIR__ . '/includes/Collection/CollectionCache.php',
	'Flow\\Collection\\HeaderCollection' => __DIR__ . '/includes/Collection/HeaderCollection.php',
	'Flow\\Collection\\LocalCacheAbstractCollection' => __DIR__ . '/includes/Collection/LocalCacheAbstractCollection.php',
	'Flow\\Collection\\PostCollection' => __DIR__ . '/includes/Collection/PostCollection.php',
	'Flow\\Collection\\PostSummaryCollection' => __DIR__ . '/includes/Collection/PostSummaryCollection.php',
	'Flow\\Container' => __DIR__ . '/includes/Container.php',
	'Flow\\Content\\BoardContent' => __DIR__ . '/includes/Content/BoardContent.php',
	'Flow\\Content\\BoardContentHandler' => __DIR__ . '/includes/Content/BoardContentHandler.php',
	'Flow\\Content\\Content' => __DIR__ . '/includes/Content/Content.php',
	'Flow\\Data\\BagOStuff\\BufferedBagOStuff' => __DIR__ . '/includes/Data/BagOStuff/BufferedBagOStuff.php',
	'Flow\\Data\\BagOStuff\\LocalBufferedBagOStuff' => __DIR__ . '/includes/Data/BagOStuff/LocalBufferedBagOStuff.php',
	'Flow\\Data\\BufferedCache' => __DIR__ . '/includes/Data/BufferedCache.php',
	'Flow\\Data\\Compactor' => __DIR__ . '/includes/Data/Compactor.php',
	'Flow\\Data\\Compactor\\FeatureCompactor' => __DIR__ . '/includes/Data/Compactor/FeatureCompactor.php',
	'Flow\\Data\\Compactor\\ShallowCompactor' => __DIR__ . '/includes/Data/Compactor/ShallowCompactor.php',
	'Flow\\Data\\Index' => __DIR__ . '/includes/Data/Index.php',
	'Flow\\Data\\Index\\BoardHistoryIndex' => __DIR__ . '/includes/Data/Index/BoardHistoryIndex.php',
	'Flow\\Data\\Index\\FeatureIndex' => __DIR__ . '/includes/Data/Index/FeatureIndex.php',
	'Flow\\Data\\Index\\TopKIndex' => __DIR__ . '/includes/Data/Index/TopKIndex.php',
	'Flow\\Data\\Index\\TopicHistoryIndex' => __DIR__ . '/includes/Data/Index/TopicHistoryIndex.php',
	'Flow\\Data\\Index\\UniqueFeatureIndex' => __DIR__ . '/includes/Data/Index/UniqueFeatureIndex.php',
	'Flow\\Data\\LifecycleHandler' => __DIR__ . '/includes/Data/LifecycleHandler.php',
	'Flow\\Data\\Listener\\AbstractTopicInsertListener' => __DIR__ . '/includes/Data/Listener/WatchTopicListener.php',
	'Flow\\Data\\Listener\\DeferredInsertLifecycleHandler' => __DIR__ . '/includes/Data/Listener/DeferredInsertLifecycleHandler.php',
	'Flow\\Data\\Listener\\EditCountListener' => __DIR__ . '/includes/Data/Listener/EditCountListener.php',
	'Flow\\Data\\Listener\\ImmediateWatchTopicListener' => __DIR__ . '/includes/Data/Listener/WatchTopicListener.php',
	'Flow\\Data\\Listener\\NotificationListener' => __DIR__ . '/includes/Data/Listener/NotificationListener.php',
	'Flow\\Data\\Listener\\OccupationListener' => __DIR__ . '/includes/Data/Listener/OccupationListener.php',
	'Flow\\Data\\Listener\\RecentChangesListener' => __DIR__ . '/includes/Data/Listener/RecentChangesListener.php',
	'Flow\\Data\\Listener\\ReferenceRecorder' => __DIR__ . '/includes/Data/Listener/ReferenceRecorder.php',
	'Flow\\Data\\Listener\\UrlGenerationListener' => __DIR__ . '/includes/Data/Listener/UrlGenerationListener.php',
	'Flow\\Data\\Listener\\UserNameListener' => __DIR__ . '/includes/Data/Listener/UserNameListener.php',
	'Flow\\Data\\Listener\\WorkflowTopicListListener' => __DIR__ . '/includes/Data/Listener/WorkflowTopicListListener.php',
	'Flow\\Data\\ManagerGroup' => __DIR__ . '/includes/Data/ManagerGroup.php',
	'Flow\\Data\\Mapper\\BasicObjectMapper' => __DIR__ . '/includes/Data/Mapper/BasicObjectMapper.php',
	'Flow\\Data\\Mapper\\CachingObjectMapper' => __DIR__ . '/includes/Data/Mapper/CachingObjectMapper.php',
	'Flow\\Data\\ObjectLocator' => __DIR__ . '/includes/Data/ObjectLocator.php',
	'Flow\\Data\\ObjectManager' => __DIR__ . '/includes/Data/ObjectManager.php',
	'Flow\\Data\\ObjectMapper' => __DIR__ . '/includes/Data/ObjectMapper.php',
	'Flow\\Data\\ObjectStorage' => __DIR__ . '/includes/Data/ObjectStorage.php',
	'Flow\\Data\\Pager\\HistoryPager' => __DIR__ . '/includes/Data/Pager/HistoryPager.php',
	'Flow\\Data\\Pager\\Pager' => __DIR__ . '/includes/Data/Pager/Pager.php',
	'Flow\\Data\\Pager\\PagerPage' => __DIR__ . '/includes/Data/Pager/PagerPage.php',
	'Flow\\Data\\Storage\\BasicDbStorage' => __DIR__ . '/includes/Data/Storage/BasicDbStorage.php',
	'Flow\\Data\\Storage\\BoardHistoryStorage' => __DIR__ . '/includes/Data/Storage/BoardHistoryStorage.php',
	'Flow\\Data\\Storage\\DbStorage' => __DIR__ . '/includes/Data/Storage/DbStorage.php',
	'Flow\\Data\\Storage\\HeaderRevisionStorage' => __DIR__ . '/includes/Data/Storage/HeaderRevisionStorage.php',
	'Flow\\Data\\Storage\\PostRevisionStorage' => __DIR__ . '/includes/Data/Storage/PostRevisionStorage.php',
	'Flow\\Data\\Storage\\PostSummaryRevisionStorage' => __DIR__ . '/includes/Data/Storage/PostSummaryRevisionStorage.php',
	'Flow\\Data\\Storage\\RevisionStorage' => __DIR__ . '/includes/Data/Storage/RevisionStorage.php',
	'Flow\\Data\\Storage\\TopicHistoryStorage' => __DIR__ . '/includes/Data/Storage/TopicHistoryStorage.php',
	'Flow\\Data\\Storage\\TopicListLastUpdatedStorage' => __DIR__ . '/includes/Data/Storage/TopicListLastUpdatedStorage.php',
	'Flow\\Data\\Storage\\TopicListStorage' => __DIR__ . '/includes/Data/Storage/TopicListStorage.php',
	'Flow\\Data\\Utils\\Merger' => __DIR__ . '/includes/Data/Utils/Merger.php',
	'Flow\\Data\\Utils\\MultiDimArray' => __DIR__ . '/includes/Data/Utils/MultiDimArray.php',
	'Flow\\Data\\Utils\\RawSql' => __DIR__ . '/includes/Data/Utils/RawSql.php',
	'Flow\\Data\\Utils\\RecentChangeFactory' => __DIR__ . '/includes/Data/Utils/RecentChangeFactory.php',
	'Flow\\Data\\Utils\\ResultDuplicator' => __DIR__ . '/includes/Data/Utils/ResultDuplicator.php',
	'Flow\\Data\\Utils\\SortArrayByKeys' => __DIR__ . '/includes/Data/Utils/SortArrayByKeys.php',
	'Flow\\Data\\Utils\\UserMerger' => __DIR__ . '/includes/Data/Utils/UserMerger.php',
	'Flow\\DbFactory' => __DIR__ . '/includes/DbFactory.php',
	'Flow\\Exception\\CatchableFatalErrorException' => __DIR__ . '/includes/Exception/CatchableFatalErrorException.php',
	'Flow\\Exception\\CrossWikiException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\DataModelException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\DataPersistenceException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\FailCommitException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\FlowException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\InvalidActionException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\InvalidDataException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\InvalidInputException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\InvalidReferenceException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\InvalidTopicUuidException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\NoIndexException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\NoParsoidException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\PermissionException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\UnknownWorkflowIdException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\WikitextException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\Exception\\WrongNumberArgumentsException' => __DIR__ . '/includes/Exception/ExceptionHandling.php',
	'Flow\\FlowActions' => __DIR__ . '/includes/FlowActions.php',
	'Flow\\Formatter\\AbstractFormatter' => __DIR__ . '/includes/Formatter/AbstractFormatter.php',
	'Flow\\Formatter\\AbstractQuery' => __DIR__ . '/includes/Formatter/AbstractQuery.php',
	'Flow\\Formatter\\BaseTopicListFormatter' => __DIR__ . '/includes/Formatter/BaseTopicListFormatter.php',
	'Flow\\Formatter\\BoardHistoryQuery' => __DIR__ . '/includes/Formatter/BoardHistoryQuery.php',
	'Flow\\Formatter\\CategoryViewerFormatter' => __DIR__ . '/includes/Formatter/CategoryViewerFormatter.php',
	'Flow\\Formatter\\CategoryViewerQuery' => __DIR__ . '/includes/Formatter/CategoryViewerQuery.php',
	'Flow\\Formatter\\CheckUserFormatter' => __DIR__ . '/includes/Formatter/CheckUserFormatter.php',
	'Flow\\Formatter\\CheckUserQuery' => __DIR__ . '/includes/Formatter/CheckUserQuery.php',
	'Flow\\Formatter\\CheckUserRow' => __DIR__ . '/includes/Formatter/CheckUserQuery.php',
	'Flow\\Formatter\\Contributions' => __DIR__ . '/includes/Formatter/Contributions.php',
	'Flow\\Formatter\\ContributionsQuery' => __DIR__ . '/includes/Formatter/ContributionsQuery.php',
	'Flow\\Formatter\\ContributionsRow' => __DIR__ . '/includes/Formatter/ContributionsQuery.php',
	'Flow\\Formatter\\FeedItemFormatter' => __DIR__ . '/includes/Formatter/FeedItemFormatter.php',
	'Flow\\Formatter\\FormatterRow' => __DIR__ . '/includes/Formatter/AbstractQuery.php',
	'Flow\\Formatter\\HeaderViewQuery' => __DIR__ . '/includes/Formatter/RevisionViewQuery.php',
	'Flow\\Formatter\\IRCLineUrlFormatter' => __DIR__ . '/includes/Formatter/IRCLineUrlFormatter.php',
	'Flow\\Formatter\\PostHistoryQuery' => __DIR__ . '/includes/Formatter/PostHistoryQuery.php',
	'Flow\\Formatter\\PostSummaryQuery' => __DIR__ . '/includes/Formatter/PostSummaryQuery.php',
	'Flow\\Formatter\\PostSummaryViewQuery' => __DIR__ . '/includes/Formatter/RevisionViewQuery.php',
	'Flow\\Formatter\\PostViewQuery' => __DIR__ . '/includes/Formatter/RevisionViewQuery.php',
	'Flow\\Formatter\\RecentChanges' => __DIR__ . '/includes/Formatter/RecentChanges.php',
	'Flow\\Formatter\\RecentChangesQuery' => __DIR__ . '/includes/Formatter/RecentChangesQuery.php',
	'Flow\\Formatter\\RecentChangesRow' => __DIR__ . '/includes/Formatter/RecentChangesQuery.php',
	'Flow\\Formatter\\RevisionDiffViewFormatter' => __DIR__ . '/includes/Formatter/RevisionDiffViewFormatter.php',
	'Flow\\Formatter\\RevisionFormatter' => __DIR__ . '/includes/Formatter/RevisionFormatter.php',
	'Flow\\Formatter\\RevisionViewFormatter' => __DIR__ . '/includes/Formatter/RevisionViewFormatter.php',
	'Flow\\Formatter\\RevisionViewQuery' => __DIR__ . '/includes/Formatter/RevisionViewQuery.php',
	'Flow\\Formatter\\SinglePostQuery' => __DIR__ . '/includes/Formatter/SinglePostQuery.php',
	'Flow\\Formatter\\TocTopicListFormatter' => __DIR__ . '/includes/Formatter/TocTopicListFormatter.php',
	'Flow\\Formatter\\TopicFormatter' => __DIR__ . '/includes/Formatter/TopicFormatter.php',
	'Flow\\Formatter\\TopicHistoryQuery' => __DIR__ . '/includes/Formatter/TopicHistoryQuery.php',
	'Flow\\Formatter\\TopicListFormatter' => __DIR__ . '/includes/Formatter/TopicListFormatter.php',
	'Flow\\Formatter\\TopicListQuery' => __DIR__ . '/includes/Formatter/TopicListQuery.php',
	'Flow\\Formatter\\TopicRow' => __DIR__ . '/includes/Formatter/TopicRow.php',
	'Flow\\Import\\Converter' => __DIR__ . '/includes/Import/Converter.php',
	'Flow\\Import\\FileImportSourceStore' => __DIR__ . '/includes/Import/ImportSourceStore.php',
	'Flow\\Import\\HistoricalUIDGenerator' => __DIR__ . '/includes/Import/Importer.php',
	'Flow\\Import\\IConversionStrategy' => __DIR__ . '/includes/Import/IConversionStrategy.php',
	'Flow\\Import\\IImportHeader' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IImportObject' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IImportPost' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IImportSource' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IImportSummary' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IImportTopic' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IObjectRevision' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\IRevisionableObject' => __DIR__ . '/includes/Import/ImportSource.php',
	'Flow\\Import\\ImportException' => __DIR__ . '/includes/Import/Exception.php',
	'Flow\\Import\\ImportSourceStore' => __DIR__ . '/includes/Import/ImportSourceStore.php',
	'Flow\\Import\\ImportSourceStoreException' => __DIR__ . '/includes/Import/Exception.php',
	'Flow\\Import\\Importer' => __DIR__ . '/includes/Import/Importer.php',
	'Flow\\Import\\LiquidThreadsApi\\ApiBackend' => __DIR__ . '/includes/Import/LiquidThreadsApi/Source.php',
	'Flow\\Import\\LiquidThreadsApi\\ApiNotFoundException' => __DIR__ . '/includes/Import/LiquidThreadsApi/Exception.php',
	'Flow\\Import\\LiquidThreadsApi\\CachedApiData' => __DIR__ . '/includes/Import/LiquidThreadsApi/CachedData.php',
	'Flow\\Import\\LiquidThreadsApi\\CachedData' => __DIR__ . '/includes/Import/LiquidThreadsApi/CachedData.php',
	'Flow\\Import\\LiquidThreadsApi\\CachedPageData' => __DIR__ . '/includes/Import/LiquidThreadsApi/CachedData.php',
	'Flow\\Import\\LiquidThreadsApi\\CachedThreadData' => __DIR__ . '/includes/Import/LiquidThreadsApi/CachedData.php',
	'Flow\\Import\\LiquidThreadsApi\\ConversionStrategy' => __DIR__ . '/includes/Import/LiquidThreadsApi/ConversionStrategy.php',
	'Flow\\Import\\LiquidThreadsApi\\ImportHeader' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\ImportPost' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\ImportRevision' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\ImportSource' => __DIR__ . '/includes/Import/LiquidThreadsApi/Source.php',
	'Flow\\Import\\LiquidThreadsApi\\ImportSummary' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\ImportTopic' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\LocalApiBackend' => __DIR__ . '/includes/Import/LiquidThreadsApi/Source.php',
	'Flow\\Import\\LiquidThreadsApi\\MovedImportPost' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\MovedImportRevision' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\MovedImportTopic' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\PageRevisionedObject' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\RemoteApiBackend' => __DIR__ . '/includes/Import/LiquidThreadsApi/Source.php',
	'Flow\\Import\\LiquidThreadsApi\\ReplyIterator' => __DIR__ . '/includes/Import/LiquidThreadsApi/Iterators.php',
	'Flow\\Import\\LiquidThreadsApi\\RevisionIterator' => __DIR__ . '/includes/Import/LiquidThreadsApi/Iterators.php',
	'Flow\\Import\\LiquidThreadsApi\\ScriptedImportRevision' => __DIR__ . '/includes/Import/LiquidThreadsApi/Objects.php',
	'Flow\\Import\\LiquidThreadsApi\\TopicIterator' => __DIR__ . '/includes/Import/LiquidThreadsApi/Iterators.php',
	'Flow\\Import\\NullImportSourceStore' => __DIR__ . '/includes/Import/ImportSourceStore.php',
	'Flow\\Import\\PageImportState' => __DIR__ . '/includes/Import/Importer.php',
	'Flow\\Import\\Plain\\ImportHeader' => __DIR__ . '/includes/Import/Plain/ImportHeader.php',
	'Flow\\Import\\Plain\\ObjectRevision' => __DIR__ . '/includes/Import/Plain/ObjectRevision.php',
	'Flow\\Import\\Postprocessor\\LqtRedirector' => __DIR__ . '/includes/Import/Postprocessor/LqtRedirector.php',
	'Flow\\Import\\Postprocessor\\PostprocessingException' => __DIR__ . '/includes/Import/Postprocessor/PostprocessingException.php',
	'Flow\\Import\\Postprocessor\\Postprocessor' => __DIR__ . '/includes/Import/Postprocessor/Postprocessor.php',
	'Flow\\Import\\Postprocessor\\ProcessorGroup' => __DIR__ . '/includes/Import/Postprocessor/ProcessorGroup.php',
	'Flow\\Import\\Postprocessor\\SpecialLogTopic' => __DIR__ . '/includes/Import/Postprocessor/SpecialLogTopic.php',
	'Flow\\Import\\TalkpageImportOperation' => __DIR__ . '/includes/Import/Importer.php',
	'Flow\\Import\\TopicImportState' => __DIR__ . '/includes/Import/Importer.php',
	'Flow\\Import\\Wikitext\\ConversionStrategy' => __DIR__ . '/includes/Import/Wikitext/ConversionStrategy.php',
	'Flow\\Import\\Wikitext\\ImportSource' => __DIR__ . '/includes/Import/Wikitext/ImportSource.php',
	'Flow\\LinksTableUpdater' => __DIR__ . '/includes/LinksTableUpdater.php',
	'Flow\\Log\\ActionFormatter' => __DIR__ . '/includes/Log/ActionFormatter.php',
	'Flow\\Log\\Logger' => __DIR__ . '/includes/Log/Logger.php',
	'Flow\\Log\\LqtImportFormatter' => __DIR__ . '/includes/Log/LqtImportFormatter.php',
	'Flow\\Log\\PostModerationLogger' => __DIR__ . '/includes/Log/PostModerationLogger.php',
	'Flow\\Model\\AbstractRevision' => __DIR__ . '/includes/Model/AbstractRevision.php',
	'Flow\\Model\\AbstractSummary' => __DIR__ . '/includes/Model/AbstractSummary.php',
	'Flow\\Model\\Anchor' => __DIR__ . '/includes/Model/Anchor.php',
	'Flow\\Model\\Header' => __DIR__ . '/includes/Model/Header.php',
	'Flow\\Model\\PostRevision' => __DIR__ . '/includes/Model/PostRevision.php',
	'Flow\\Model\\PostSummary' => __DIR__ . '/includes/Model/PostSummary.php',
	'Flow\\Model\\Reference' => __DIR__ . '/includes/Model/Reference.php',
	'Flow\\Model\\TopicListEntry' => __DIR__ . '/includes/Model/TopicListEntry.php',
	'Flow\\Model\\URLReference' => __DIR__ . '/includes/Model/URLReference.php',
	'Flow\\Model\\UUID' => __DIR__ . '/includes/Model/UUID.php',
	'Flow\\Model\\UserTuple' => __DIR__ . '/includes/Model/UserTuple.php',
	'Flow\\Model\\WikiReference' => __DIR__ . '/includes/Model/WikiReference.php',
	'Flow\\Model\\Workflow' => __DIR__ . '/includes/Model/Workflow.php',
	'Flow\\NewTopicFormatter' => __DIR__ . '/includes/Notifications/Formatter.php',
	'Flow\\NotificationController' => __DIR__ . '/includes/Notifications/Controller.php',
	'Flow\\NotificationFormatter' => __DIR__ . '/includes/Notifications/Formatter.php',
	'Flow\\NotificationsUserLocator' => __DIR__ . '/includes/Notifications/UserLocator.php',
	'Flow\\OccupationController' => __DIR__ . '/includes/TalkpageManager.php',
	'Flow\\Parsoid\\ContentFixer' => __DIR__ . '/includes/Parsoid/ContentFixer.php',
	'Flow\\Parsoid\\Extractor' => __DIR__ . '/includes/Parsoid/Extractor.php',
	'Flow\\Parsoid\\Extractor\\CategoryExtractor' => __DIR__ . '/includes/Parsoid/Extractor/CategoryExtractor.php',
	'Flow\\Parsoid\\Extractor\\ExtLinkExtractor' => __DIR__ . '/includes/Parsoid/Extractor/ExtLinkExtractor.php',
	'Flow\\Parsoid\\Extractor\\ImageExtractor' => __DIR__ . '/includes/Parsoid/Extractor/ImageExtractor.php',
	'Flow\\Parsoid\\Extractor\\PlaceholderExtractor' => __DIR__ . '/includes/Parsoid/Extractor/PlaceholderExtractor.php',
	'Flow\\Parsoid\\Extractor\\TransclusionExtractor' => __DIR__ . '/includes/Parsoid/Extractor/TransclusionExtractor.php',
	'Flow\\Parsoid\\Extractor\\WikiLinkExtractor' => __DIR__ . '/includes/Parsoid/Extractor/WikiLinkExtractor.php',
	'Flow\\Parsoid\\Fixer' => __DIR__ . '/includes/Parsoid/Fixer.php',
	'Flow\\Parsoid\\Fixer\\BadImageRemover' => __DIR__ . '/includes/Parsoid/Fixer/BadImageRemover.php',
	'Flow\\Parsoid\\Fixer\\Redlinker' => __DIR__ . '/includes/Parsoid/Fixer/Redlinker.php',
	'Flow\\Parsoid\\ReferenceExtractor' => __DIR__ . '/includes/Parsoid/ReferenceExtractor.php',
	'Flow\\Parsoid\\ReferenceFactory' => __DIR__ . '/includes/Parsoid/ReferenceFactory.php',
	'Flow\\Parsoid\\Utils' => __DIR__ . '/includes/Parsoid/Utils.php',
	'Flow\\RecoverableErrorHandler' => __DIR__ . '/includes/RecoverableErrorHandler.php',
	'Flow\\ReferenceClarifier' => __DIR__ . '/includes/ReferenceClarifier.php',
	'Flow\\Repository\\MultiGetList' => __DIR__ . '/includes/Repository/MultiGetList.php',
	'Flow\\Repository\\RootPostLoader' => __DIR__ . '/includes/Repository/RootPostLoader.php',
	'Flow\\Repository\\TitleRepository' => __DIR__ . '/includes/Repository/TitleRepository.php',
	'Flow\\Repository\\TreeRepository' => __DIR__ . '/includes/Repository/TreeRepository.php',
	'Flow\\Repository\\UserNameBatch' => __DIR__ . '/includes/Repository/UserNameBatch.php',
	'Flow\\Repository\\UserName\\OneStepUserNameQuery' => __DIR__ . '/includes/Repository/UserName/OneStepUserNameQuery.php',
	'Flow\\Repository\\UserName\\TwoStepUserNameQuery' => __DIR__ . '/includes/Repository/UserName/TwoStepUserNameQuery.php',
	'Flow\\Repository\\UserName\\UserNameQuery' => __DIR__ . '/includes/Repository/UserName/UserNameQuery.php',
	'Flow\\RevisionActionPermissions' => __DIR__ . '/includes/RevisionActionPermissions.php',
	'Flow\\SpamFilter\\AbuseFilter' => __DIR__ . '/includes/SpamFilter/AbuseFilter.php',
	'Flow\\SpamFilter\\ConfirmEdit' => __DIR__ . '/includes/SpamFilter/ConfirmEdit.php',
	'Flow\\SpamFilter\\ContentLengthFilter' => __DIR__ . '/includes/SpamFilter/ContentLengthFilter.php',
	'Flow\\SpamFilter\\Controller' => __DIR__ . '/includes/SpamFilter/Controller.php',
	'Flow\\SpamFilter\\SpamBlacklist' => __DIR__ . '/includes/SpamFilter/SpamBlacklist.php',
	'Flow\\SpamFilter\\SpamFilter' => __DIR__ . '/includes/SpamFilter/SpamFilter.php',
	'Flow\\SpamFilter\\SpamRegex' => __DIR__ . '/includes/SpamFilter/SpamRegex.php',
	'Flow\\SpecialFlow' => __DIR__ . '/includes/SpecialFlow.php',
	'Flow\\SubmissionHandler' => __DIR__ . '/includes/SubmissionHandler.php',
	'Flow\\TalkpageManager' => __DIR__ . '/includes/TalkpageManager.php',
	'Flow\\TemplateHelper' => __DIR__ . '/includes/TemplateHelper.php',
	'Flow\\Templating' => __DIR__ . '/includes/Templating.php',
	'Flow\\Tests\\Api\\ApiFlowEditHeaderTest' => __DIR__ . '/tests/phpunit/api/ApiFlowEditHeaderTest.php',
	'Flow\\Tests\\Api\\ApiFlowEditPostTest' => __DIR__ . '/tests/phpunit/api/ApiFlowEditPostTest.php',
	'Flow\\Tests\\Api\\ApiFlowEditTitleTest' => __DIR__ . '/tests/phpunit/api/ApiFlowEditTitleTest.php',
	'Flow\\Tests\\Api\\ApiFlowEditTopicSummaryTest' => __DIR__ . '/tests/phpunit/api/ApiFlowEditTopicSummary.php',
	'Flow\\Tests\\Api\\ApiFlowLockTopicTest' => __DIR__ . '/tests/phpunit/api/ApiFlowLockTopicTest.php',
	'Flow\\Tests\\Api\\ApiFlowModeratePostTest' => __DIR__ . '/tests/phpunit/api/ApiFlowModeratePostTest.php',
	'Flow\\Tests\\Api\\ApiFlowModerateTopicTest' => __DIR__ . '/tests/phpunit/api/ApiFlowModerateTopicTest.php',
	'Flow\\Tests\\Api\\ApiFlowReplyTest' => __DIR__ . '/tests/phpunit/api/ApiFlowReplyTest.php',
	'Flow\\Tests\\Api\\ApiFlowViewHeaderTest' => __DIR__ . '/tests/phpunit/api/ApiFlowViewHeaderTest.php',
	'Flow\\Tests\\Api\\ApiFlowViewTopicListTest' => __DIR__ . '/tests/phpunit/api/ApiFlowViewTopicListTest.php',
	'Flow\\Tests\\Api\\ApiTestCase' => __DIR__ . '/tests/phpunit/api/ApiTestCase.php',
	'Flow\\Tests\\Api\\ApiWatchTopicTest' => __DIR__ . '/tests/phpunit/api/ApiWatchTopicTest.php',
	'Flow\\Tests\\BlockFactoryTest' => __DIR__ . '/tests/phpunit/BlockFactoryTest.php',
	'Flow\\Tests\\Block\\TopicListTest' => __DIR__ . '/tests/phpunit/Block/TopicListTest.php',
	'Flow\\Tests\\BufferedBagOStuffTest' => __DIR__ . '/tests/phpunit/Data/BagOStuff/BufferedBagOStuffTest.php',
	'Flow\\Tests\\BufferedCacheTest' => __DIR__ . '/tests/phpunit/Data/BufferedCacheTest.php',
	'Flow\\Tests\\Collection\\PostCollectionTest' => __DIR__ . '/tests/phpunit/Collection/PostCollectionTest.php',
	'Flow\\Tests\\Collection\\RevisionCollectionPermissionsTest' => __DIR__ . '/tests/phpunit/Collection/RevisionCollectionPermissionsTest.php',
	'Flow\\Tests\\ContainerTest' => __DIR__ . '/tests/phpunit/ContainerTest.php',
	'Flow\\Tests\\Data\\CachingObjectManagerTest' => __DIR__ . '/tests/phpunit/Data/CachingObjectMapperTest.php',
	'Flow\\Tests\\Data\\FlowNothingTest' => __DIR__ . '/tests/phpunit/Data/NothingTest.php',
	'Flow\\Tests\\Data\\IndexTest' => __DIR__ . '/tests/phpunit/Data/IndexTest.php',
	'Flow\\Tests\\Data\\Index\\FeatureIndexTest' => __DIR__ . '/tests/phpunit/Data/Index/FeatureIndexTest.php',
	'Flow\\Tests\\Data\\Index\\MockFeatureIndex' => __DIR__ . '/tests/phpunit/Data/Index/FeatureIndexTest.php',
	'Flow\\Tests\\Data\\Listener\\RecentChangesListenerTest' => __DIR__ . '/tests/phpunit/Data/Listener/RecentChangesListenerTest.php',
	'Flow\\Tests\\Data\\ManagerGroupTest' => __DIR__ . '/tests/phpunit/Data/ManagerGroupTest.php',
	'Flow\\Tests\\Data\\ObjectLocatorTest' => __DIR__ . '/tests/phpunit/Data/ObjectLocatorTest.php',
	'Flow\\Tests\\Data\\Pager\\PagerTest' => __DIR__ . '/tests/phpunit/Data/Pager/PagerTest.php',
	'Flow\\Tests\\Data\\RevisionStorageTest' => __DIR__ . '/tests/phpunit/Data/RevisionStorageTest.php',
	'Flow\\Tests\\Data\\Storage\\RevisionStorageTest' => __DIR__ . '/tests/phpunit/Data/Storage/RevisionStorageTest.php',
	'Flow\\Tests\\Data\\UserNameBatchTest' => __DIR__ . '/tests/phpunit/Data/UserNameBatchTest.php',
	'Flow\\Tests\\Data\\UserNameListenerTest' => __DIR__ . '/tests/phpunit/Data/UserNameListenerTest.php',
	'Flow\\Tests\\FlowActionsTest' => __DIR__ . '/tests/phpunit/FlowActionsTest.php',
	'Flow\\Tests\\FlowTestCase' => __DIR__ . '/tests/phpunit/FlowTestCase.php',
	'Flow\\Tests\\Formatter\\FormatterTest' => __DIR__ . '/tests/phpunit/Formatter/FormatterTest.php',
	'Flow\\Tests\\Formatter\\RevisionFormatterTest' => __DIR__ . '/tests/phpunit/Formatter/RevisionFormatterTest.php',
	'Flow\\Tests\\Handlebars\\FlowPostMetaActionsTest' => __DIR__ . '/tests/phpunit/Handlebars/FlowPostMetaActionsTest.php',
	'Flow\\Tests\\HookTest' => __DIR__ . '/tests/phpunit/HookTest.php',
	'Flow\\Tests\\Import\\ConverterTest' => __DIR__ . '/tests/phpunit/Import/ConverterTest.php',
	'Flow\\Tests\\Import\\HistoricalUIDGeneratorTest' => __DIR__ . '/tests/phpunit/Import/HistoricalUIDGeneratorTest.php',
	'Flow\\Tests\\Import\\LiquidThreadsApi\\ConversionStrategyTest' => __DIR__ . '/tests/phpunit/Import/LiquidThreadsApi/ConversionStrategyTest.php',
	'Flow\\Tests\\Import\\PageImportStateTest' => __DIR__ . '/tests/phpunit/Import/PageImportStateTest.php',
	'Flow\\Tests\\Import\\TalkpageImportOperationTest' => __DIR__ . '/tests/phpunit/Import/TalkpageImportOperationTest.php',
	'Flow\\Tests\\Import\\Wikitext\\ConversionStrategyTest' => __DIR__ . '/tests/phpunit/Import/Wikitext/ConversionStrategyTest.php',
	'Flow\\Tests\\Import\\Wikitext\\ImportSourceTest' => __DIR__ . '/tests/phpunit/Import/Wikitext/ImportSourceTest.php',
	'Flow\\Tests\\LinksTableTest' => __DIR__ . '/tests/phpunit/LinksTableTest.php',
	'Flow\\Tests\\LocalBufferedBagOStuffTest' => __DIR__ . '/tests/phpunit/Data/BagOStuff/LocalBufferedBagOStuffTest.php',
	'Flow\\Tests\\Mock\\MockImportHeader' => __DIR__ . '/tests/phpunit/Mock/MockImportHeader.php',
	'Flow\\Tests\\Mock\\MockImportPost' => __DIR__ . '/tests/phpunit/Mock/MockImportPost.php',
	'Flow\\Tests\\Mock\\MockImportRevision' => __DIR__ . '/tests/phpunit/Mock/MockImportRevision.php',
	'Flow\\Tests\\Mock\\MockImportSource' => __DIR__ . '/tests/phpunit/Mock/MockImportSource.php',
	'Flow\\Tests\\Mock\\MockImportSummary' => __DIR__ . '/tests/phpunit/Mock/MockImportSummary.php',
	'Flow\\Tests\\Mock\\MockImportTopic' => __DIR__ . '/tests/phpunit/Mock/MockImportTopic.php',
	'Flow\\Tests\\Model\\PostRevisionTest' => __DIR__ . '/tests/phpunit/Model/PostRevisionTest.php',
	'Flow\\Tests\\Model\\UUIDTest' => __DIR__ . '/tests/phpunit/Model/UUIDTest.php',
	'Flow\\Tests\\Model\\UserTupleTest' => __DIR__ . '/tests/phpunit/Model/UserTupleTest.php',
	'Flow\\Tests\\NotifiedUsersTest' => __DIR__ . '/tests/phpunit/Notifications/NotifiedUsersTest.php',
	'Flow\\Tests\\PagerTest' => __DIR__ . '/tests/phpunit/PagerTest.php',
	'Flow\\Tests\\Parsoid\\BadImageRemoverTest' => __DIR__ . '/tests/phpunit/Parsoid/Fixer/BadImageRemoverTest.php',
	'Flow\\Tests\\Parsoid\\Fixer\\MethodReturnsConstraint' => __DIR__ . '/tests/phpunit/Parsoid/Fixer/RedlinkerTest.php',
	'Flow\\Tests\\Parsoid\\Fixer\\RedlinkerTest' => __DIR__ . '/tests/phpunit/Parsoid/Fixer/RedlinkerTest.php',
	'Flow\\Tests\\Parsoid\\ParsoidUtilsTest' => __DIR__ . '/tests/phpunit/Parsoid/UtilsTest.php',
	'Flow\\Tests\\Parsoid\\ReferenceExtractorTestCase' => __DIR__ . '/tests/phpunit/Parsoid/ReferenceExtractorTest.php',
	'Flow\\Tests\\Parsoid\\ReferenceFactoryTest' => __DIR__ . '/tests/phpunit/Parsoid/ReferenceFactoryTest.php',
	'Flow\\Tests\\PermissionsTest' => __DIR__ . '/tests/phpunit/PermissionsTest.php',
	'Flow\\Tests\\PostRevisionTestCase' => __DIR__ . '/tests/phpunit/PostRevisionTestCase.php',
	'Flow\\Tests\\Repository\\TreeRepositoryTest' => __DIR__ . '/tests/phpunit/Repository/TreeRepositoryTest.php',
	'Flow\\Tests\\Repository\\TreeRepositorydbTest' => __DIR__ . '/tests/phpunit/Repository/TreeRepositoryDbTest.php',
	'Flow\\Tests\\SpamFilter\\AbuseFilterTest' => __DIR__ . '/tests/phpunit/SpamFilter/AbuseFilterTest.php',
	'Flow\\Tests\\SpamFilter\\ConfirmEditTest' => __DIR__ . '/tests/phpunit/SpamFilter/ConfirmEditTest.php',
	'Flow\\Tests\\SpamFilter\\ContentLengthFilterTest' => __DIR__ . '/tests/phpunit/SpamFilter/ContentLengthFilterTest.php',
	'Flow\\Tests\\SpamFilter\\SpamBlacklistTest' => __DIR__ . '/tests/phpunit/SpamFilter/SpamBlacklistTest.php',
	'Flow\\Tests\\SpamFilter\\SpamRegexTest' => __DIR__ . '/tests/phpunit/SpamFilter/SpamRegexTest.php',
	'Flow\\Tests\\TemplateHelperTest' => __DIR__ . '/tests/phpunit/TemplateHelperTest.php',
	'Flow\\Tests\\TemplatingTest' => __DIR__ . '/tests/phpunit/TemplatingTest.php',
	'Flow\\Tests\\UrlGeneratorTest' => __DIR__ . '/tests/phpunit/UrlGeneratorTest.php',
	'Flow\\Tests\\WatchedTopicItemTest' => __DIR__ . '/tests/phpunit/WatchedTopicItemsTest.php',
	'Flow\\UrlGenerator' => __DIR__ . '/includes/UrlGenerator.php',
	'Flow\\Utils\\NamespaceIterator' => __DIR__ . '/includes/Utils/NamespaceIterator.php',
	'Flow\\Utils\\PagesWithPropertyIterator' => __DIR__ . '/includes/Utils/PagesWithPropertyIterator.php',
	'Flow\\View' => __DIR__ . '/includes/View.php',
	'Flow\\WatchedTopicItems' => __DIR__ . '/includes/WatchedTopicItems.php',
	'Flow\\WorkflowLoader' => __DIR__ . '/includes/WorkflowLoader.php',
	'Flow\\WorkflowLoaderFactory' => __DIR__ . '/includes/WorkflowLoaderFactory.php',
	'MaintenanceDebugLogger' => __DIR__ . '/maintenance/MaintenanceDebugLogger.php',
	'Pimple' => __DIR__ . '/vendor/Pimple.php',
);
