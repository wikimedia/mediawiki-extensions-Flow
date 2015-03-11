<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Exception\FailCommitException;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\PostSummaryViewQuery;
use Flow\Formatter\PostSummaryQuery;
use Flow\Formatter\RevisionViewFormatter;
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
	protected $supportedPostActions = array( 'edit-topic-summary' );

	/**
	 * @var string[]
	 */
	protected $supportedGetActions = array( 'view-topic-summary', 'compare-postsummary-revisions', 'edit-topic-summary' );

	/**
	 * @var string[]
	 */
	protected $requiresWikitext = array( 'edit-topic-summary' );

	protected $templates = array(
		'view-topic-summary' => 'single_view',
		'compare-postsummary-revisions' => 'diff_view',
		'edit-topic-summary' => 'edit',
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
			if ( !$this->permissions->isAllowed( null, 'create-topic-summary' ) ) {
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

		if ( $this->wasSubmitted() ) {
			$output += array(
				'submitted' => $this->submitted,
				'errors' => $this->errors,
			);
		} else {
			$output += array(
				'submitted' => array(),
				'errors' => array(),
			);
		}

		switch ( $this->action ) {
			case 'view-topic-summary':
				// @Todo - duplicated logic in other single view block
				if ( isset( $options['revId'] ) && $options['revId'] ) {
					/** @var PostSummaryViewQuery $query */
					$query = Container::get( 'query.postsummary.view' );
					$row = $query->getSingleViewResult( $options['revId'] );
					/** @var RevisionViewFormatter $formatter */
					$formatter = Container::get( 'formatter.revisionview' );
					$output['revision'] = $formatter->formatApi( $row, $this->context );
				} else {
					if ( isset( $options['contentFormat'] ) && $options['contentFormat'] === 'wikitext' ) {
						$this->requiresWikitext[] = 'view-topic-summary';
					}
					$output += $this->renderNewestTopicSummary();
				}
				break;
			case 'edit-topic-summary':
				$output += $this->renderNewestTopicSummary();
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
				$output['revision'] = Container::get( 'formatter.revision.diff.view' )->formatApi( $new, $old, $this->context );
				break;
		}

		return $output;
	}

	protected function renderNewestTopicSummary() {
		$output = array();
		$formatter = Container::get( 'formatter.revision' );

		if ( in_array( $this->action, $this->requiresWikitext ) ) {
			$formatter->setContentFormat( 'wikitext' );
		}
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
			$out->setHtmlTitle( $out->msg( 'flow-topic-html-title', array(
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
