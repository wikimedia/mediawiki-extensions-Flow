<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Exception\FailCommitException;
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
	 * Supported Post actions
	 */
	protected $supportedPostActions = array( 'edit-topic-summary', 'moderate-topic' );

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

	public function validate() {
		switch( $this->action ) {
			case 'edit-topic-summary':
				$this->validateTopicSummary();
			break;
			case 'moderate-topic':
				if ( $this->isCloseOpenTopic() ) {
					$this->validateTopicSummary();
				}
			break;
		}
	}

	/**
	 * Check if this is closing/reopening a topic
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

			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->addError( 'prev_revision', wfMessage( 'flow-error-missing-prev-revision-identifier' ) );
				return;
			} elseif ( $this->topicSummary->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
				$this->addError(
					'prev_revision',
					wfMessage( 'flow-error-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $this->topicSummary->getRevisionId()->getAlphadecimal() ),
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

	public function commit() {
		switch( $this->action ) {
			case 'edit-topic-summary':
				return $this->saveTopicSummary();
			break;
			case 'moderate-topic':
				if ( $this->isCloseOpenTopic() ) {
					return $this->saveTopicSummary();
				}
			break;
		}
	}

	/**
	 * @Todo - Add edit-topic-summary get action for no js support in UI patch
	 */
	public function render( Templating $templating, array $options ) {

	}

	public function renderAPI( Templating $templating, array $options ) {
		$output = array( 'type' => 'topicsummary' );

		if ( isset( $options['contentFormat'] ) ) {
			$contentFormat = $options['contentFormat'];
		} else {
			$contentFormat = 'wikitext';
		}

		$output['format'] = $contentFormat;
		if ( $this->topicSummary !== null ) {
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
