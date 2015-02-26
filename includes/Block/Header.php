<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidInputException;
use Flow\Formatter\HeaderViewQuery;
use Flow\Formatter\RevisionDiffViewFormatter;
use Flow\Formatter\RevisionViewFormatter;
use Flow\Formatter\FormatterRow;
use Flow\Model\Header;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\UrlGenerator;
use IContextSource;

class HeaderBlock extends AbstractBlock {
	/**
	 * @var Header|null
	 */
	protected $header;

	/**
	 * New revision created via submission.
	 *
	 * @var Header|null
	 */
	protected $newRevision;

	/**
	 * @var boolean
	 */
	protected $needCreate = false;

	/**
	 * @var string[]
	 */
	protected $supportedPostActions = array( 'edit-header' );

	/**
	 * @var string[]
	 */
	protected $requiresWikitext = array( 'edit-header', 'compare-header-revisions' );

	/**
	 * @var string[]
	 */
	protected $supportedGetActions = array( 'view', 'compare-header-revisions', 'edit-header', 'view-header' );

	// @Todo - fill in the template names
	protected $templates = array(
		'view' => '',
		'compare-header-revisions' => 'diff_view',
		'edit-header' => 'edit',
		'view-header' => 'single_view',
	);

	/**
	 * @var RevisionActionPermissions Allows or denies actions to be performed
	 */
	protected $permissions;

	public function init( IContextSource $context, $action ) {
		parent::init( $context, $action );

		// Basic initialisation done -- now, load data if applicable
		if ( $this->workflow->isNew() ) {
			$this->needCreate = true;
			return;
		}

		// Get the latest revision attached to this workflow
		$found = $this->storage->find(
			'Header',
			array( 'rev_type_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		if ( $found ) {
			$this->header = reset( $found );
		}
	}

	protected function validate() {
		// @todo some sort of restriction along the lines of article protection
		// @todo this is superseeded by SubmissionHandler::onSubmit checking
		// 	Title::userCan() ?
		if ( !$this->context->getUser()->isAllowed( 'edit' ) ) {
			$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
			return;
		}
		if ( !isset( $this->submitted['content'] ) ) {
			$this->addError( 'content', $this->context->msg( 'flow-error-missing-header-content' ) );
		}

		if ( $this->header ) {
			$this->validateNextRevision();
		} else {
			// simpler case
			$this->validateFirstRevision();
		}
	}

	protected function validateNextRevision() {
		if ( !$this->permissions->isAllowed( $this->header, 'edit-header' ) ) {
			$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
			return;
		}

		if ( empty( $this->submitted['prev_revision'] ) ) {
			$this->addError( 'prev_revision', $this->context->msg( 'flow-error-missing-prev-revision-identifier' ) );
		} elseif ( $this->header->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
			// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
			// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
			// parent we and the submitter think is the latest, our insert will fail.
			// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
			// handing user back to specific dialog indicating race condition
			$this->addError(
				'prev_revision',
				$this->context->msg( 'flow-error-prev-revision-mismatch' )->params(
					$this->submitted['prev_revision'],
					$this->header->getRevisionId()->getAlphadecimal(),
					$this->context->getUser()->getName()
				),
				array( 'revision_id' => $this->header->getRevisionId()->getAlphadecimal() ) // save current revision ID
			);
		}

		// this isn't really part of validate, but we want the error-rendering template to see the users edited header
		$this->newRevision = $this->header->newNextRevision(
			$this->context->getUser(),
			$this->submitted['content'],
			'edit-header',
			$this->workflow->getArticleTitle()
		);

		if ( !$this->checkSpamFilters( $this->header, $this->newRevision ) ) {
			return;
		}

	}

	protected function validateFirstRevision() {
		if ( !$this->permissions->isAllowed( null, 'create-header' ) ) {
			$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
			return;
		}
		if ( isset( $this->submitted['prev_revision'] ) && $this->submitted['prev_revision'] ) {
			// User submitted a previous revision, but we couldn't find one.  This is likely
			// an internal error and not a user error, consider better handling
			// is this even worth checking?
			$this->addError( 'prev_revision', $this->context->msg( 'flow-error-prev-revision-does-not-exist' ) );
			return;
		}

		$this->newRevision = Header::create(
			$this->workflow,
			$this->context->getUser(),
			$this->submitted['content'],
			'create-header'
		);

		if ( !$this->checkSpamFilters( null, $this->newRevision ) ) {
			return;
		}
	}

	public function needCreate() {
		return $this->needCreate;
	}

	public function commit() {
		switch( $this->action ) {
			case 'edit-header':
				$this->storage->put( $this->newRevision, array(
					'workflow' => $this->workflow,
				) );
				// Reload $this->header for renderApi() after save
				$this->header = $this->newRevision;
				return array(
					'header-revision-id' => $this->newRevision->getRevisionId(),
				);

			default:
				throw new InvalidActionException( 'Unrecognized commit action', 'invalid-action' );
		}
	}

	public function renderApi( array $options ) {
		$output = array(
			'type' => $this->getName(),
			'editToken' => $this->getEditToken(),
		);

		switch ( $this->action ) {
			case 'view':
			case 'edit-header':
				$output += $this->renderRevisionApi();
				break;

			case 'view-header':
				if ( isset( $options['revId'] ) && $options['revId'] ) {
					$output += $this->renderSingleViewApi( $options['revId'] );
				} else {
					if ( isset( $options['contentFormat'] ) && $options['contentFormat'] === 'wikitext' ) {
						$this->requiresWikitext[] = 'view-header';
					}
					$output += $this->renderRevisionApi();
				}
				break;

			case 'compare-header-revisions':
				$output += $this->renderDiffviewApi( $options );
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
				'errors' => array()
			);
		}

		return $output;
	}

	// @Todo - duplicated logic in other diff view block
	protected function renderDiffviewApi( array $options ) {
		if ( !isset( $options['newRevision'] ) ) {
			throw new InvalidInputException( 'A revision must be provided for comparison', 'revision-comparison' );
		}
		$oldRevision = null;
		if ( isset( $options['oldRevision'] ) ) {
			$oldRevision = $options['newRevision'];
		}
		/** @var HeaderViewQuery $query */
		$query = Container::get( 'query.header.view' );
		list( $new, $old ) = $query->getDiffViewResult( UUID::create( $options['newRevision'] ), UUID::create( $oldRevision ) );
		/** @var RevisionDiffViewFormatter $formatter */
		$formatter = Container::get( 'formatter.revision.diff.view' );

		return array(
			'revision' => $formatter->formatApi( $new, $old, $this->context )
		);
	}

	// @Todo - duplicated logic in other single view block
	protected function renderSingleViewApi( $revId ) {
		/** @var HeaderViewQuery $query */
		$query = Container::get( 'query.header.view' );
		$row = $query->getSingleViewResult( $revId );
		/** @var RevisionViewFormatter $formatter */
		$formatter = Container::get( 'formatter.revisionview' );

		return array(
			'revision' => $formatter->formatApi( $row, $this->context )
		);
	}

	protected function renderRevisionApi() {
		$output = array();
		if ( $this->header === null ) {
			/** @var UrlGenerator $urlGenerator */
			$urlGenerator = Container::get( 'url_generator' );
			$output['revision'] = array(
				// @todo
				'actions' => array(
					'edit' => $urlGenerator
						->createHeaderAction( $this->workflow->getArticleTitle() ),
				),
				'links' => array(
				),
			);
		} else {
			$row = new FormatterRow;
			$row->workflow = $this->workflow;
			$row->revision = $this->header;
			$row->currentRevision = $this->header;

			$serializer = Container::get( 'formatter.revision' );
			if ( false !== array_search( $this->action, $this->requiresWikitext ) ) {
				$serializer->setContentFormat( 'wikitext' );
			}

			$output['revision'] = $serializer->formatApi( $row, $this->context );
		}
		return $output;
	}

	public function getName() {
		return 'header';
	}
}
