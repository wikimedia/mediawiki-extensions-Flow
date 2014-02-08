<?php

namespace Flow\Block;

use Flow\RevisionActionPermissions;
use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\Container;
use Flow\Model\Header;
use Flow\Templating;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\View\HeaderRevisionView;

class HeaderBlock extends AbstractBlock {

	protected $header;
	protected $needCreate = false;
	protected $supportedPostActions = array( 'edit-header' );
	protected $supportedGetActions = array( 'view', 'compare-header-revisions', 'edit-header', 'header-view' );

	/**
	 * @var RevisionActionPermissions $permissions Allows or denies actions to be performed
	 */
	protected $permissions;

	public function init( $action, $user ) {
		parent::init( $action, $user );

		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );

		// Basic initialisation done -- now, load data if applicable
		if ( $this->workflow->isNew() ) {
			$this->needCreate = true;
			return;
		}

		// Get the latest revision attached to this workflow
		$found = $this->storage->find(
			'Header',
			array( 'header_workflow_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		if ( $found ) {
			$this->header = reset( $found );
		}
	}

	protected function validate() {
		// @todo some sort of restriction along the lines of article protection
		if ( !$this->user->isAllowed( 'edit' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-header-content' ) );
		}

		if ( $this->header ) {
			if ( !$this->permissions->isAllowed( $this->header, 'edit-header' ) ) {
				$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
				return;
			}

			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->addError( 'prev_revision', wfMessage( 'flow-error-missing-prev-revision-identifier' ) );
			} elseif ( $this->header->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
				// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
				// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
				// parent we and the submitter think is the latest, our insert will fail.
				// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
				// handing user back to specific dialog indicating race condition
				$this->addError(
					'prev_revision',
					wfMessage( 'flow-error-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $this->header->getRevisionId()->getAlphadecimal() ),
					array( 'revision_id' => $this->header->getRevisionId()->getAlphadecimal() ) // save current revision ID
				);
			}

			// this isn't really part of validate, but we want the error-rendering template to see the users edited header
			$oldHeader = $this->header;
			$this->header = $this->header->newNextRevision( $this->user, $this->submitted['content'], 'edit-header' );

			if ( !$this->checkSpamFilters( $oldHeader, $this->header ) ) {
				return;
			}

		} else {
			if ( !$this->permissions->isAllowed( null, 'create-header' ) ) {
				$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
				return;
			}

			if ( empty( $this->submitted['prev_revision'] ) ) {
				$title = $this->workflow->getArticleTitle();
				if ( !$title->exists() ) {
					// if $wgFlowContentFormat is set to html the Header::create
					// call will convert the wikitext input into html via parsoid, and
					// parsoid requires the page exist.
					Container::get( 'occupation_controller' )->ensureFlowRevision( new \Article( $title, 0 ) );
				}

				$this->header = Header::create( $this->workflow, $this->user, $this->submitted['content'], 'create-header' );

				if ( !$this->checkSpamFilters( null, $this->header ) ) {
					return;
				}
			} else {
				// User submitted a previous revision, but we couldn't find one.  This is likely
				// an internal error and not a user error, consider better handling
				$this->addError( 'prev_revision', wfMessage( 'flow-error-prev-revision-does-not-exist' ) );
			}
		}
	}

	public function needCreate() {
		return $this->needCreate;
	}

	public function commit() {
		switch( $this->action ) {
			case 'edit-header':
				$this->storage->put( $this->header );

				$header = $this->header;

				return array(
					'new-revision-id' => $this->header->getRevisionId(),
					'render-function' => function( $templating ) use ( $header ) {
						return $templating->getContent( $header, 'html' );
					},
				);

			default:
				throw new InvalidActionException( 'Unrecognized commit action', 'invalid-action' );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		$templating->getOutput()->addModuleStyles( array( 'ext.flow.header' ) );
		$templating->getOutput()->addModules( array( 'ext.flow.header' ) );

		switch ( $this->action ) {
			case 'compare-header-revisions':
				if ( !isset( $options['newRevision'] ) ) {
					throw new InvalidInputException( 'A revision must be provided for comparison', 'revision-comparison' );
				}
				$revisionView = HeaderRevisionView::newFromId( $options['newRevision'], $templating, $this, $this->user );
				if ( !$revisionView ) {
					throw new InvalidInputException( 'An invalid revision was provided for comparison', 'revision-comparison' );
				}

				if ( isset( $options['oldRevision'] ) ) {
					return $revisionView->renderDiffViewAgainst( $options['oldRevision'], $return );
				} else {
					return $revisionView->renderDiffViewAgainstPrevious( $return );
				}
			break;

			case 'edit-header':
				return $templating->renderHeader( $this->header, $this, $this->user, 'flow:edit-header.html.php', $return );
			break;

			default:
				if ( isset( $options['revId'] ) ) {
					$revisionView = HeaderRevisionView::newFromId( $options['revId'], $templating, $this, $this->user );
					if ( !$revisionView ) {
						throw new InvalidInputException( 'The requested revision could not be found', 'missing-revision' );
					} else if ( !$this->permissions->isAllowed( $revisionView->getRevision(), 'view' ) ) {
						$this->addError( 'moderation', wfMessage( 'flow-error-not-allowed' ) );
						return null;
					}
					return $revisionView->renderSingleView( $return );
				} else {
					return $templating->renderHeader( $this->header, $this, $this->user, 'flow:header.html.php', $return );
				}
			break;
		}
	}

	public function renderAPI( Templating $templating, array $options ) {
		$output = array();
		$output['type'] = 'header';

		$contentFormat = 'wikitext';

		if ( isset( $options['contentFormat'] ) ) {
			$contentFormat = $options['contentFormat'];
		}

		if ( $this->header !== null ) {
			$output['*'] = $templating->getContent( $this->header, $contentFormat );
			$output['format'] = $contentFormat;
			$output['header-id'] = $this->header->getRevisionId()->getAlphadecimal();
		} else {
			$output['missing'] = 'missing';
		}

		$output = array(
			'_element' => 'header',
			0 => $output,
		);

		return $output;
	}

	public function getName() {
		return 'header';
	}

}
