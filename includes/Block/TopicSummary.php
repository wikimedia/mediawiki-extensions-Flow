<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Exception\FailCommitException;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\PostSummaryViewQuery;
use Flow\Formatter\PostSummaryQuery;
use Flow\Formatter\RevisionViewFormatter;
use Flow\Formatter\RevisionViewQuery;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use IContextSource;
use Message;

class TopicSummaryBlock extends AbstractBlock {
	/**
	 * @var PostSummary|null
	 */
	protected $topicSummary;

	/**
	 * @var FormatterRow
	 */
	protected $formatterRow;

	/**
	 * @var PostSummary|null
	 */
	protected $nextRevision;

	/**
	 * @var PostRevision|null
	 */
	protected $topicTitle;

	/**
	 * @var string[]
	 */
	protected $supportedPostActions = array( 'edit-topic-summary', 'undo-edit-topic-summary' );

	/**
	 * @var string[]
	 */
	protected $supportedGetActions = array( 'view-topic-summary', 'compare-postsummary-revisions', 'edit-topic-summary', 'undo-edit-topic-summary' );

	protected $templates = array(
		'view-topic-summary' => 'single_view',
		'compare-postsummary-revisions' => 'diff_view',
		'edit-topic-summary' => 'edit',
		'undo-edit-topic-summary' => 'undo_edit',
	);

	/**
	 * @param IContextSource $context
	 * @param string $action
	 */
	public function init( IContextSource $context, $action ) {
		parent::init( $context, $action );

		if ( !$this->workflow->isNew() ) {
			/** @var PostSummaryQuery $query */
			$query = Container::get( 'query.postsummary' );
			$this->formatterRow = $query->getResult( $this->workflow->getId() );
			if ( $this->formatterRow ) {
				$this->topicSummary = $this->formatterRow->revision;
			}
		}
	}

	/**
	 * Validate data before commiting change
	 */
	public function validate() {
		switch( $this->action ) {
			case 'undo-edit-topic-summary':
			case 'edit-topic-summary':
				$this->validateTopicSummary();
			break;

			default:
				throw new InvalidActionException( "Unexpected action: {$this->action}", 'invalid-action' );
		}
	}

	/**
	 * Validate topic summary
	 *
	 * @throws InvalidDataException
	 */
	protected function validateTopicSummary() {
		if ( !isset( $this->submitted['summary'] ) || !is_string( $this->submitted['summary'] ) ) {
			$this->addError( 'content', $this->context->msg( 'flow-error-missing-summary' ) );
			return;
		}

		if ( $this->workflow->isNew() ) {
			throw new InvalidDataException( 'Topic summary can only be added to an existing topic', 'missing-topic-title' );
		}

		// Create topic summary
		if ( !$this->topicSummary ) {
			$topicTitle = $this->findTopicTitle();
			$boardWorkflow = $topicTitle->getCollection()->getBoardWorkflow();
			if (
				!$this->permissions->isRevisionAllowed( null, 'create-topic-summary' ) ||
				!$this->permissions->isRootAllowed( $topicTitle, 'create-topic-summary' ) ||
				!$this->permissions->isBoardAllowed( $boardWorkflow, 'create-topic-summary' )
			) {
				$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
				return;
			}
			// new summary should not have a previous revision
			if ( !empty( $this->submitted['prev_revision'] ) ) {
				$this->addError( 'prev_revision', $this->context->msg( 'flow-error-prev-revision-does-not-exist' ) );
				return;
			}

			$this->nextRevision = PostSummary::create(
				$this->workflow->getArticleTitle(),
				$this->findTopicTitle(),
				$this->context->getUser(),
				$this->submitted['summary'],
				// default to wikitext when not specified, for old API requests
				isset( $this->submitted['format'] ) ? $this->submitted['format'] : 'wikitext',
				'create-topic-summary'
			);
		// Edit topic summary
		} else {
			if ( !$this->permissions->isAllowed( $this->topicSummary, 'edit-topic-summary' ) ) {
				$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
				return;
			}
			// Check the previous revision to catch possible edit conflict
			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->addError( 'prev_revision', $this->context->msg( 'flow-error-missing-prev-revision-identifier' ) );
				return;
			} elseif ( $this->topicSummary->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
				$this->addError(
					'prev_revision',
					$this->context->msg( 'flow-error-prev-revision-mismatch' )->params(
						$this->submitted['prev_revision'],
						$this->topicSummary->getRevisionId()->getAlphadecimal(),
						$this->context->getUser()->getName()
					),
					array( 'revision_id' => $this->topicSummary->getRevisionId()->getAlphadecimal() )
				);
				return;
			}

			$this->nextRevision = $this->topicSummary->newNextRevision(
				$this->context->getUser(),
				$this->submitted['summary'],
				// default to wikitext when not specified, for old API requests
				isset( $this->submitted['format'] ) ? $this->submitted['format'] : 'wikitext',
				'edit-topic-summary',
				$this->workflow->getArticleTitle()
			);
		}

		if ( !$this->checkSpamFilters( $this->topicSummary, $this->nextRevision ) ) {
			return;
		}
	}

	/**
	 * Find the topic title for the summary
	 *
	 * @throws InvalidDataException
	 * @return PostRevision
	 */
	public function findTopicTitle() {
		if ( $this->topicTitle ) {
			return $this->topicTitle;
		}
		$found = $this->storage->find(
			'PostRevision',
			array( 'rev_type_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);
		if ( !$found ) {
			throw new InvalidDataException( 'Every workflow must have an associated topic title', 'missing-topic-title' );
		}
		return $this->topicTitle = reset( $found );
	}

	/**
	 * Save topic summary
	 *
	 * @throws FailCommitException
	 */
	protected function saveTopicSummary() {
		if ( !$this->nextRevision ) {
			throw new FailCommitException( 'Attempt to save summary on null revision', 'fail-commit' );
		}

		$this->storage->put( $this->nextRevision, array(
			'workflow' => $this->workflow,
		) );
		// Reload the $this->formatterRow for renderApi() after save
		$this->formatterRow = new FormatterRow();
		$this->formatterRow->revision = $this->nextRevision;
		$this->formatterRow->previousRevision = $this->topicSummary;
		$this->formatterRow->currentRevision = $this->nextRevision;
		$this->formatterRow->workflow = $this->workflow;
		$this->topicSummary = $this->nextRevision;

		return array(
			'summary-revision-id' => $this->nextRevision->getRevisionId(),
		);
	}

	/**
	 * Save change for any valid committed action
	 *
	 * @throws InvalidActionException
	 */
	public function commit() {
		switch( $this->action ) {
			case 'undo-edit-topic-summary':
			case 'edit-topic-summary':
				return $this->saveTopicSummary();
			break;

			default:
				throw new InvalidActionException( "Unexpected action: {$this->action}", 'invalid-action' );
		}
	}

	/**
	 * Render the data for API request
	 *
	 * @param array $options
	 * @return array
	 * @throws InvalidInputException
	 */
	public function renderApi( array $options ) {
		$output = array( 'type' => $this->getName() );

		switch ( $this->action ) {
			case 'view-topic-summary':
				// @Todo - duplicated logic in other single view block
				if ( isset( $options['revId'] ) && $options['revId'] ) {
					/** @var PostSummaryViewQuery $query */
					$query = Container::get( 'query.postsummary.view' );
					$row = $query->getSingleViewResult( $options['revId'] );
					if ( !$this->permissions->isAllowed( $row->revision, 'view-topic-summary' ) ) {
						$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
						break;
					}

					/** @var RevisionViewFormatter $formatter */
					$formatter = Container::get( 'formatter.revisionview' );
					$output['revision'] = $formatter->formatApi( $row, $this->context );
				} else {
					$format = isset( $options['format'] ) ? $options['format'] : 'fixed-html';
					$output += $this->renderNewestTopicSummary( $format );
				}
				break;
			case 'edit-topic-summary':
				// default to wikitext for no-JS
				$format = isset( $options['format'] ) ? $options['format'] : 'wikitext';
				$output += $this->renderNewestTopicSummary( $format );
				break;
			case 'undo-edit-topic-summary':
				$output = $this->renderUndoApi( $options ) + $output;
				break;
			case 'compare-postsummary-revisions':
				// @Todo - duplicated logic in other diff view block
				if ( !isset( $options['newRevision'] ) ) {
					throw new InvalidInputException( 'A revision must be provided for comparison', 'revision-comparison' );
				}
				$oldRevision = null;
				if ( isset( $options['oldRevision'] ) ) {
					$oldRevision = $options['newRevision'];
				}
				list( $new, $old ) = Container::get( 'query.postsummary.view' )->getDiffViewResult( UUID::create( $options['newRevision'] ), UUID::create( $oldRevision ) );
				if (
					!$this->permissions->isAllowed( $new->revision, 'view-topic-summary' ) ||
					!$this->permissions->isAllowed( $old->revision, 'view-topic-summary' )
				) {
					$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
					break;
				}
				$output['revision'] = Container::get( 'formatter.revision.diff.view' )->formatApi( $new, $old, $this->context );
				break;
		}

		if ( $this->wasSubmitted() ) {
			$output += array(
				'submitted' => $this->submitted,
				'errors' => $this->errors,
			);
		} else {
			$output += array(
				'submitted' => array(),
				'errors' => $this->errors,
			);
		}

		return $output;
	}

	/**
	 * @param string $format Content format (wikitext|html)
	 * @return array
	 */
	protected function renderNewestTopicSummary( $format ) {
		$topicTitle = $this->findTopicTitle();
		$boardWorkflow = $topicTitle->getCollection()->getBoardWorkflow();
		if (
			// topicSummary can be null PostSummary object or null (doesn't exist yet)
			!$this->permissions->isRevisionAllowed( $this->topicSummary, 'view-topic-summary' ) ||
			!$this->permissions->isRootAllowed( $topicTitle, 'view-topic-summary' ) ||
			!$this->permissions->isBoardAllowed( $boardWorkflow, 'view-topic-summary' )
		) {
			$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
			return array();
		}

		$output = array();
		$formatter = Container::get( 'formatter.revision' );
		$formatter->setContentFormat( $format );

		if ( $this->formatterRow ) {
			$output['revision'] = $formatter->formatApi(
				$this->formatterRow,
				$this->context
			);
		} else {
			$urlGenerator = Container::get( 'url_generator' );
			$title = $this->workflow->getArticleTitle();
			$workflowId = $this->workflow->getId();
			$output['revision'] = array(
				'actions' => array(
					'summarize' => $urlGenerator->editTopicSummaryAction(
						$title,
						$workflowId
					)
				),
				'links' => array(
					'topic' => $urlGenerator->topicLink(
						$title,
						$workflowId
					)
				)
			);
		}
		return $output;
	}

	protected function renderUndoApi( array $options ) {
		if ( $this->workflow->isNew() ) {
			throw new FlowException( 'No topic exists to undo' );
		}

		if ( !isset( $options['startId'], $options['endId'] ) ) {
			throw new InvalidInputException( 'Both startId and endId must be provided' );
		}

		/** @var RevisionViewQuery */
		$query = Container::get( 'query.postsummary.view' );
		$rows = $query->getUndoDiffResult( $options['startId'], $options['endId'] );
		if ( !$rows ) {
			throw new InvalidInputException( 'Could not load revision to undo' );
		}

		$serializer = Container::get( 'formatter.undoedit' );
		return $serializer->formatApi( $rows[0], $rows[1], $rows[2], $this->context );
	}

	public function getName() {
		return 'topicsummary';
	}

	/**
	 * @param \OutputPage $out
	 */
	public function setPageTitle( \OutputPage $out ) {
		$topic = $this->findTopicTitle();
		$title = $this->workflow->getOwnerTitle();
		$out->setPageTitle( $out->msg( 'flow-topic-first-heading', $title->getPrefixedText() ) );
		if ( $this->permissions->isAllowed( $topic, 'view' ) ) {
			if ( $this->action === 'undo-edit-topic-summary' ) {
				$key = 'flow-undo-edit-topic-summary';
			} else {
				$key = 'flow-topic-html-title';
			}
			$out->setHtmlTitle( $out->msg( $key, array(
				// This must be a rawParam to not expand {{foo}} in the title, it must
				// not be htmlspecialchar'd because OutputPage::setHtmlTitle handles that.
				Message::rawParam( $topic->getContent( 'wikitext' ) ),
				$title->getPrefixedText()
			) ) );
		} else {
			$out->setHtmlTitle( $title->getPrefixedText() );
		}

		$out->setSubtitle( '&lt; ' . \Linker::link( $title ) );
	}
}
