<?php

namespace Flow\Import;

use DeferredUpdates;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Import\Postprocessor\Postprocessor;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use IP;
use MWCryptRand;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use SplQueue;
use Title;
use UIDGenerator;
use User;

/**
 * The import system uses a TalkpageImportOperation class.
 * This class is essentially a factory class that makes the
 * dependency injection less inconvenient for callers.
 */
class TalkpageImportOperation {
	/**
	 * @var IImportSource
	 */
	protected $importSource;

	/**
	 * @param IImportSource $source
	 */
	public function __construct( IImportSource $source ) {
		$this->importSource = $source;
	}

	/**
	 * @param PageImportState $state
	 * @return bool True if import completed successfully
	 */
	public function import( PageImportState $state ) {
		$state->logger->info( 'Importing to ' . $state->boardWorkflow->getArticleTitle()->getPrefixedText() );
		if ( $state->boardWorkflow->isNew() ) {
			$state->put( $state->boardWorkflow, array() );
		}

		$imported = $failed = 0;
		$header = $this->importSource->getHeader();
		if ( $header ) {
			try {
				$state->begin();
				$this->importHeader( $state, $header );
				$state->commit();
				$state->postprocessor->afterHeaderImported( $state, $header );
				$imported++;
			} catch ( ImportSourceStoreException $e ) {
				// errors from the source store are more serious and should
				// not just be logged and swallowed.  This may indicate that
				// we are not properly recording progress.
				$state->rollback();
				throw $e;
			} catch ( \Exception $e ) {
				$state->rollback();
				\MWExceptionHandler::logException( $e );
				$state->logger->error( 'Failed importing header: ' . $header->getObjectKey() );
				$state->logger->error( (string)$e );
				$failed++;
			}
		}

		foreach( $this->importSource->getTopics() as $topic ) {
			try {
				// @todo this may be too large of a chunk for one commit, unsure
				$state->begin();
				$topicState = $this->getTopicState( $state, $topic );
				$this->importTopic( $topicState, $topic );
				$state->commit();
				$state->postprocessor->afterTopicImported( $topicState, $topic );
				$imported++;
			} catch ( ImportSourceStoreException $e ) {
				// errors from the source store are more serious and shuld
				// not juts be logged and swallowed.  This may indicate that
				// we are not properly recording progress.
				$state->rollback();
				throw $e;
			} catch ( \Exception $e ) {
				$state->rollback();
				\MWExceptionHandler::logException( $e );
				$state->logger->error( 'Failed importing topic: ' . $topic->getObjectKey() );
				$state->logger->error( (string)$e );
				$failed++;
			}
		}
		$state->logger->info( "Imported $imported items, failed $failed" );

		return $failed === 0;
	}

	/**
	 * @param PageImportState $pageState
	 * @param IImportHeader   $importHeader
	 */
	public function importHeader( PageImportState $pageState, IImportHeader $importHeader ) {
		$pageState->logger->info( 'Importing header' );
		if ( ! $importHeader->getRevisions()->valid() ) {
			$pageState->logger->info( 'no revisions located for header' );
			// No revisions
			return;
		}

		$existingId = $pageState->getImportedId( $importHeader );
		if ( $existingId && $pageState->getTopRevision( 'Header', $existingId ) ) {
			$pageState->logger->info( 'header previously imported' );
			return;
		}

		$revisions = $this->importObjectWithHistory(
			$importHeader,
			function( IObjectRevision $rev ) use ( $pageState ) {
				return Header::create(
					$pageState->boardWorkflow,
					$pageState->createUser( $rev->getAuthor() ),
					$rev->getText(),
					'create-header'
				);
			},
			'edit-header',
			$pageState,
			$pageState->boardWorkflow->getArticleTitle()
		);

		$pageState->put( $revisions, array(
			'workflow' => $pageState->boardWorkflow,
		) );
		$pageState->recordAssociation(
			reset( $revisions )->getCollectionId(),
			$importHeader
		);

		$pageState->logger->info( 'Imported ' . count( $revisions ) . ' revisions for header' );
	}

	/**
	 * @param TopicImportState $pageState
	 * @param IImportTopic    $importTopic
	 */
	public function importTopic( TopicImportState $topicState, IImportTopic $importTopic ) {
		$summary = $importTopic->getTopicSummary();
		if ( $summary ) {
			$this->importSummary( $topicState, $summary );
		}

		foreach ( $importTopic->getReplies() as $post ) {
			$this->importPost( $topicState, $post, $topicState->topicTitle );
		}

		$topicState->commitLastModified();
		$topicId = $topicState->topicWorkflow->getId();
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic    $importTopic
	 * @return TopicImportState
	 */
	protected function getTopicState( PageImportState $state, IImportTopic $importTopic ) {
		// Check if it's already been imported
		$topicState = $this->getExistingTopicState( $state, $importTopic );
		if ( $topicState ) {
			$state->logger->info( 'Continuing import to ' . $topicState->topicWorkflow->getArticleTitle()->getPrefixedText() );
			return $topicState;
		} else {
			return $this->createTopicState( $state, $importTopic );
		}
	}

	protected function getFirstRevision( IRevisionableObject $obj ) {
		$iterator = $obj->getRevisions();
		$iterator->rewind();
		return $iterator->current();
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic    $importTopic
	 * @return TopicImportState
	 */
	protected function createTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$state->logger->info( 'Importing new topic' );
		$topicWorkflow = Workflow::create(
			'topic',
			$state->boardWorkflow->getArticleTitle()
		);
		$state->setWorkflowTimestamp(
			$topicWorkflow,
			$this->getFirstRevision( $importTopic )->getTimestamp()
		);

		$topicListEntry = TopicListEntry::create(
			$state->boardWorkflow,
			$topicWorkflow
		);

		$titleRevisions = $this->importObjectWithHistory(
			$importTopic,
			function( IObjectRevision $rev ) use ( $state, $topicWorkflow ) {
				return PostRevision::create(
					$topicWorkflow,
					$state->createUser( $rev->getAuthor() ),
					$rev->getText()
				);
			},
			'edit-title',
			$state,
			$topicWorkflow->getArticleTitle()
		);

		$topicState = new TopicImportState( $state, $topicWorkflow, end( $titleRevisions ) );
		$topicMetadata = $topicState->getMetadata();

		// TLE must be first, otherwise you get an error importing the Topic Title
		// Flow/includes/Data/Index/BoardHistoryIndex.php:
		// No topic list contains topic XXX, called for revision YYY
		$state->put( $topicListEntry, $topicMetadata );
		// Topic title must be second, because inserting topicWorkflow requires
		// the topic title to already be in place
		$state->put( $titleRevisions, $topicMetadata );
		$state->put( $topicWorkflow, $topicMetadata );

		$state->recordAssociation( $topicWorkflow->getId(), $importTopic );

		$state->logger->info( 'Finished importing topic title with ' . count( $titleRevisions ) . ' revisions' );
		return $topicState;
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic    $importTopic
	 * @return TopicImportState|null
	 */
	protected function getExistingTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$topicId = $state->getImportedId( $importTopic );
		if ( $topicId ) {
			$topicWorkflow = $state->get( 'Workflow', $topicId );
			$topicTitle = $state->getTopRevision( 'PostRevision', $topicId );
			if ( $topicWorkflow instanceof Workflow && $topicTitle instanceof PostRevision ) {
				return new TopicImportState( $state, $topicWorkflow, $topicTitle );
			}
		}

		return null;
	}

	/**
	 * @param TopicImportState $state
	 * @param IImportSummary   $importSummary
	 */
	public function importSummary( TopicImportState $state, IImportSummary $importSummary ) {
		$state->parent->logger->info( "Importing summary" );
		$existingId = $state->parent->getImportedId( $importSummary );
		if ( $existingId ) {
			$summary = $state->parent->getTopRevision( 'PostSummary', $existingId );
			if ( $summary ) {
				$state->recordModificationTime( $summary->getRevisionId() );
				$state->parent->logger->info( "Summary previously imported" );
				return;
			}
		}

		$revisions = $this->importObjectWithHistory(
			$importSummary,
			function( IObjectRevision $rev ) use ( $state ) {
				return PostSummary::create(
					$state->topicWorkflow->getArticleTitle(),
					$state->topicTitle,
					$state->parent->createUser( $rev->getAuthor() ),
					$rev->getText(),
					'create-topic-summary'
				);
			},
			'edit-topic-summary',
			$state->parent,
			$state->topicWorkflow->getArticleTitle()
		);

		$metadata = array(
			'workflow' => $state->topicWorkflow,
		);
		$state->parent->put( $revisions, $metadata );
		$state->parent->recordAssociation(
			reset( $revisions )->getCollectionId(), // Summary ID
			$importSummary
		);

		$state->recordModificationTime( end( $revisions )->getRevisionId() );
		$state->parent->logger->info( "Finished importing summary with " . count( $revisions ) . " revisions" );
	}

	/**
	 * @param TopicImportState $state
	 * @param IImportPost      $post
	 * @param PostRevision     $replyTo
	 * @param string           $logPrefix
	 */
	public function importPost(
		TopicImportState $state,
		IImportPost $post,
		PostRevision $replyTo,
		$logPrefix = ' '
	) {
		$state->parent->logger->info( $logPrefix . "Importing post" );
		$postId = $state->parent->getImportedId( $post );
		$topRevision = false;
		if ( $postId ) {
			$topRevision = $state->parent->getTopRevision( 'PostRevision', $postId );
		}

		if ( $topRevision ) {
			$state->parent->logger->info( $logPrefix . "Post previously imported" );
		} else {
			$replyRevisions = $this->importObjectWithHistory(
				$post,
				function( IObjectRevision $rev ) use ( $replyTo, $state ) {
					return $replyTo->reply(
						$state->topicWorkflow,
						$state->parent->createUser( $rev->getAuthor() ),
						$rev->getText()
					);
				},
				'edit-post',
				$state->parent,
				$state->topicWorkflow->getArticleTitle()
			);

			$topRevision = end( $replyRevisions );

			$metadata = array(
				'workflow' => $state->topicWorkflow,
				'board-workflow' => $state->parent->boardWorkflow,
				'topic-title' => $state->topicTitle,
				'reply-to' => $replyTo,
			);

			$state->parent->put( $replyRevisions, $metadata );
			$state->parent->recordAssociation(
				$topRevision->getPostId(),
				$post
			);
			$state->parent->logger->info( $logPrefix . "Finished importing post with " . count( $replyRevisions ) . " revisions" );
			$state->parent->postprocessor->afterPostImported( $state, $post, $topRevision->getPostId() );
		}

		$state->recordModificationTime( $topRevision->getRevisionId() );

		foreach ( $post->getReplies() as $subReply ) {
			$this->importPost( $state, $subReply, $topRevision, $logPrefix . ' ' );
		}
	}

	/**
	 * Imports an object with all its revisions
	 *
	 * @param IRevisionableObject $object              Object to import.
	 * @param callable            $importFirstRevision Function which, given the appropriate import revision, creates the Flow revision.
	 * @param string              $editChangeType      The Flow change type (from FlowActions.php) for each new operation.
	 * @param PageImportState     $state               State of the import operation.
	 * @param Title               $title               Title content is rendered against
	 * @return AbstractRevision[] Objects to insert into the database.
	 * @throws ImportException
	 */
	public function importObjectWithHistory(
		IRevisionableObject $object,
		$importFirstRevision,
		$editChangeType,
		PageImportState $state,
		Title $title
	) {
		$insertObjects = array();
		$revisions = $object->getRevisions();
		$revisions->rewind();

		if ( ! $revisions->valid() ) {
			throw new ImportException( "Attempted to import empty history" );
		}

		$importRevision = $revisions->current();
		/** @var AbstractRevision $lastRevision */
		$insertObjects[] = $lastRevision = $importFirstRevision( $importRevision );
		$lastTimestamp = $importRevision->getTimestamp();

		$state->setRevisionTimestamp( $lastRevision, $lastTimestamp );
		$state->recordAssociation( $lastRevision->getRevisionId(), $importRevision );
		$state->recordAssociation( $lastRevision->getCollectionId(), $importRevision );

		$revisions->next();
		while( $revisions->valid() ) {
			$importRevision = $revisions->current();
			$insertObjects[] = $lastRevision =
				$lastRevision->newNextRevision(
					$state->createUser( $importRevision->getAuthor() ),
					$importRevision->getText(),
					$editChangeType,
					$title
				);

			if ( $importRevision->getTimestamp() < $lastTimestamp ) {
				throw new ImportException( "Revision listing is not sorted from oldest to newest" );
			}

			$lastTimestamp = $importRevision->getTimestamp();
			$state->setRevisionTimestamp( $lastRevision, $lastTimestamp );
			$state->recordAssociation( $lastRevision->getRevisionId(), $importRevision );
			$revisions->next();
		}

		return $insertObjects;
	}
}

