<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Exception\FailCommitException;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Templating;
use Flow\RevisionActionPermissions;

class TopicSummaryBlock extends AbstractBlock {

	/**
	 * @var PostSummary|null
	 */
	protected $topicSummary;

	/**
	 * Allows or denies actions to be performed
	 *
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

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
	protected $supportedPostActions = array( 'edit-topic-summary', 'close-open-topic' );

	/**
	 * @var string[]
	 */
	protected $supportedGetActions = array( 'topic-summary-view', 'edit-topic-summary' );

	/**
	 * @param string
	 * @param User
	 */
	public function init( $action, $user ) {
		parent::init( $action, $user );
		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );

		if ( !$this->workflow->isNew() ) {
			$found = $this->storage->find(
				'PostSummary',
				array( 'rev_type_id' => $this->workflow->getId() ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( $found ) {
				$this->topicSummary = reset( $found );
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

			case 'close-open-topic':
				if ( !$this->isCloseOpenTopic() ) {
					$this->addError( 'moderate', wfMessage( 'flow-error-invalid-moderation-state' ) );
					return;
				}
				$this->validateTopicSummary();
			break;

			default:
				throw new InvalidActionException( "Unexpected action: {$this->action}", 'invalid-action' );
		}
	}

	/**
	 * Check if this is closing/restoring a topic
	 */
	protected function isCloseOpenTopic() {
		$state = isset( $this->submitted['moderationState'] ) ? $this->submitted['moderationState'] : '';
		if ( $state == AbstractRevision::MODERATED_CLOSED ) {
			return true;
		}
		$root = $this->findTopicTitle();
		if (
			$root->getModerationState() == AbstractRevision::MODERATED_CLOSED &&
			$state == 'restore' )
		{
			return true;
		}
		return false;
	}

	/**
	 * Validate topic summary
	 *
	 * @throws InvalidDataException
	 */
	protected function validateTopicSummary() {
		if ( !isset( $this->submitted['summary'] ) || !is_string( $this->submitted['summary'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-summary' ) );
			return;
		}

		if ( $this->workflow->isNew() ) {
			throw new InvalidDataException( 'Topic summary can only be added to an existing topic', 'missing-topic-title' );
		}

		// Create topic summary
		if ( !$this->topicSummary ) {
			if ( !$this->permissions->isAllowed( null, 'create-topic-summary' ) ) {
				$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
				return;
			}
			// new summary should not have a previous revision
			if ( !empty( $this->submitted['prev_revision'] ) ) {
				$this->addError( 'prev_revision', wfMessage( 'flow-error-prev-revision-does-not-exist' ) );
				return;
			}

			$this->nextRevision = PostSummary::create( $this->findTopicTitle(), $this->user, $this->submitted['summary'], 'create-topic-summary' );
		// Edit topic summary
		} else {
			if ( !$this->permissions->isAllowed( $this->topicSummary, 'edit-topic-summary' ) ) {
				$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
				return;
			}
			// Check the previous revision to catch possible edit conflict
			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->addError( 'prev_revision', wfMessage( 'flow-error-missing-prev-revision-identifier' ) );
				return;
			} elseif ( $this->topicSummary->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
				$this->addError(
					'prev_revision',
					wfMessage( 'flow-error-prev-revision-mismatch' )->params(
						$this->submitted['prev_revision'],
						$this->topicSummary->getRevisionId()->getAlphadecimal()
					),
					array( 'revision_id' => $this->topicSummary->getRevisionId()->getAlphadecimal() )
				);
				return;
			}

			$this->nextRevision = $this->topicSummary->newNextRevision( $this->user, $this->submitted['summary'], 'edit-topic-summary' );
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
	protected function findTopicTitle() {
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

		$this->storage->put( $this->nextRevision );
		$newRevision = $this->nextRevision;
		return array(
			'new-revision-id' => $this->nextRevision->getRevisionId(),
			'render-function' => function( Templating $templating ) use ( $newRevision ) {
				return $templating->getContent( $newRevision, 'html' );
			}
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

			case 'close-open-topic':
				return $this->saveTopicSummary();
			break;

			default:
				throw new InvalidActionException( "Unexpected action: {$this->action}", 'invalid-action' );
		}
	}

	/**
	 * Render for an action
	 * @param Templating
	 * @param array
	 * @throws InvalidActionException
	 */
	public function render( Templating $templating, array $options, $return = false ) {
		$templating->getOutput()->addModuleStyles( array( 'ext.flow.discussion' ) );
		$templating->getOutput()->addModules( array( 'ext.flow.discussion' ) );
		switch( $this->action ) {
			case 'topic-summary-view':
				// @Todo - This will be implemented in diff view patch, having a placeholder in
				// here will allow us to query data from api via renderAPI()
			break;

			case 'edit-topic-summary':
				return $templating->render( 'flow:edit-topic-summary.html.php', array(
					'block' => $this,
					'workflow' => $this->getWorkflow(),
					'topicSummary' => $this->topicSummary,
					'user' => $this->user,
				), $return );
			break;

			default:
				throw new InvalidActionException( "Unexpected action: {$this->action}", 'invalid-action' );
		}
	}

	/**
	 * Render the data for API request
	 *
	 * @param Templating
	 * @param array
	 * @return array
	 */
	public function renderAPI( Templating $templating, array $options ) {
		$output = array( 'type' => 'topicsummary' );

		if ( $this->topicSummary !== null ) {
			if ( isset( $options['contentFormat'] ) ) {
				$contentFormat = $options['contentFormat'];
			} else {
				$contentFormat = $this->topicSummary->getContentFormat();
			}

			$output['format'] = $contentFormat;
			$output['*'] = $templating->getContent( $this->topicSummary, $contentFormat );
			$output['topicsummary-id'] = $this->topicSummary->getRevisionId()->getAlphadecimal();
		} else {
			$output['*'] = '';
			$output['topicsummary-id'] = '';
		}

		return array(
			'_element' => 'topicsummary',
			0 => $output,
		);
	}

	public function getName() {
		return 'topicsummary';
	}
}
