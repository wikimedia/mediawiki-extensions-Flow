<?php

namespace Flow\Block;

use Flow\RevisionActionPermissions;
use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\Container;
use Flow\DbFactory;
use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use Flow\Model\Header;
use Flow\Repository\HeaderRepository;
use Flow\Templating;
use User;
use Flow\Model\UUID;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;

class HeaderBlock extends AbstractBlock {

	protected $header;
	protected $needCreate = false;
	protected $supportedActions = array( 'edit-header' );

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
			return false;
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
			} elseif ( $this->header->getRevisionId()->getHex() !== $this->submitted['prev_revision'] ) {
				// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
				// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
				// parent we and the submitter think is the latest, our insert will fail.
				// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
				// handing user back to specific dialog indicating race condition
				$this->addError(
					'prev_revision',
					wfMessage( 'flow-error-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $this->header->getRevisionId()->getHex() )
				);
			}

			// this isnt really part of validate, but we want the error-rendering template to see the users edited header
			$oldHeader = $this->header;
			$this->header = $this->header->newNextRevision( $this->user, $this->submitted['content'], 'edit-header' );

			// run through AbuseFilter
			$status = Container::get( 'controller.spamfilter' )->validate( $this->header, $oldHeader, $this->workflow->getArticleTitle() );
			if ( !$status->isOK() ) {
				foreach ( $status->getErrorsArray() as $message ) {
					$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
				}
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

				// run through AbuseFilter
				$status = Container::get( 'controller.spamfilter' )->validate( $this->header, null, $this->workflow->getArticleTitle() );
				if ( !$status->isOK() ) {
					foreach ( $status->getErrorsArray() as $message ) {
						$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
					}
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
			// @Todo - Most of the header single revision view and revision diff code duplicates
			// the post revision, need to consolidate them
			case 'compare-revisions':
				if ( ! isset( $options['oldRevision'] ) || ! isset( $options['newRevision'] ) ) {
					throw new InvalidInputException( 'Two revisions must be specified to compare them', 'revision-comparison' );
				}
	
				$oldRevId = UUID::create( $options['oldRevision'] );
				$newRevId = UUID::create( $options['newRevision'] );

				list( $oldRev, $newRev ) = $this->storage->getMulti(
					'Header',
					array(
						$oldRevId,
						$newRevId
					)
				);

				// In theory the backend will return things in increasing PK order
				// (i.e. earlier revision first), but let's be sure.
				if (
					$oldRev->getRevisionId()->getTimestamp() >
					$newRev->getRevisionId()->getTimestamp()
				) {
					$temp = $oldRev;
					$oldRev = $newRev;
					$newRev = $temp;
				}

				if ( !$oldRev->getWorkflowId()->equals( $newRev->getWorkflowId() ) ) {
					throw new InvalidInputException( 'Attempt to compare revisions of different headers', 'revision-comparison' );
				}

				return $templating->render(
					'flow:compare-revisions.html.php',
					array(
						'block' => $this,
						'user' => $this->user,
						'oldRevision' => $oldRev,
						'newRevision' => $newRev,
						'header' => $this->header,
					), $return
				);
			break;

			default:
				if ( isset( $options['revId'] ) ) {
					return $this->renderRevision( $templating, $options, $return );
				}
				// Single view of latest revision
				$templateName = ( $this->action == 'edit-header' ) ? 'edit-header' : 'header';
				return $templating->renderHeader( $this->header, $this, $this->user, 'flow:' . $templateName . '.html.php' );
			break;
		}
	}

	protected function renderRevision( Templating $templating, array $options, $return = false ) {
		$postRevision = $this->loadRequestedRevision( $options['revId'] );

		if ( !$postRevision ) {
			return;
		}

		$prefix = $templating->render(
			'flow:revision-permalink-warning.html.php',
			array(
				'block' => $this,
				'revision' => $postRevision,
			),
			$return
		);

		return $prefix . $templating->renderHeader(
			$postRevision,
			$this,
			$this->user,
			'flow:header.html.php',
			$return
		);
	}

	protected function loadRequestedRevision( $revisionId ) {
		if ( !$revisionId instanceof UUID ) {
			$revisionId = UUID::create( $revisionId );
		}

		$found = $this->storage->get( 'Header', $revisionId );

		if ( !$found ) {
			throw new InvalidInputException( 'The requested revision could not be found', 'missing-revision' );
		} else if ( !$this->permissions->isAllowed( $found, 'view' ) ) {
			$this->addError( 'moderation', wfMessage( 'flow-error-not-allowed' ) );
			return null;
		}

		return $found;
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
			$output['header-id'] = $this->header->getRevisionId()->getHex();
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
