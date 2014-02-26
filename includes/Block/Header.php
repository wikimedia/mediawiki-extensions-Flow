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
			return;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-header-content' ) );
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

	}

	protected function validateFirstRevision() {
		if ( !$this->permissions->isAllowed( null, 'create-header' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}
		if ( isset( $this->submitted['prev_revision'] ) ) {
			// User submitted a previous revision, but we couldn't find one.  This is likely
			// an internal error and not a user error, consider better handling
			// is this even worth checking?
			$this->addError( 'prev_revision', wfMessage( 'flow-error-prev-revision-does-not-exist' ) );
			return;
		}

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

	public function render( Templating $templating, array $options ) {
		// Render board history view in header block, topiclist block will not be renderred
		// when action = 'board-history'
		if ( $this->action === 'board-history' ) {
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.history' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.history' ) );
			$tplVars = array(
				'title' => wfMessage( 'flow-board-history', $this->workflow->getArticleTitle() )->escaped(),
				'historyExists' => false,
			);

			$history = $this->loadBoardHistory();
			if ( $history ) {
				$tplVars['historyExists'] = true;
				$tplVars['history'] = new History( $history );
				$tplVars['historyRenderer'] = new HistoryRenderer( $templating, $this );
			}

			$templating->render( "flow:board-history.html.php", $tplVars );
		} else {
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.header' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.header' ) );
			$templateName = ( $this->action == 'edit-header' ) ? 'edit-header' : 'header';
			$templating->render( "flow:$templateName.html.php", array(
				'block' => $this,
				'workflow' => $this->workflow,
				'header' => $this->header,
				'user' => $this->user,
			) );
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

	protected function loadBoardHistory() {
		$history = $this->storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 300 )
		);

		if ( !$history ) {
			throw new InvalidDataException( 'Unable to load topic list history for ' . $this->workflow->getId()->getAlphadecimal(), 'fail-load-history' );
		}

		// get rid of history entries user doesn't have sufficient permissions for
		foreach ( $history as $i => $revision ) {
			/** @var PostRevision|Header $revision */

			// only check against the specific revision, ignoring the most recent
			if ( !$this->permissions->isAllowed( $revision, 'post-history' ) ) {
				unset( $history[$i] );
			}
		}

		return $history;
	}

	public function getName() {
		return 'header';
	}

}
