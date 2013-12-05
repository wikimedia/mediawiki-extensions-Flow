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
use Flow\Container;
use User;

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

		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );
	}

	protected function validate() {
		// @todo some sort of restriction along the lines of article protection
		if ( !$this->user->isAllowed( 'edit' ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			return false;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-error-missing-header-content' );
		}

		if ( $this->header ) {
			if ( !$this->permissions->isAllowed( $this->header, 'edit-header' ) ) {
				$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
				return;
			}

			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->errors['prev_revision'] = wfMessage( 'flow-error-missing-prev-revision-identifier' );
			} elseif ( $this->header->getRevisionId()->getHex() !== $this->submitted['prev_revision'] ) {
				// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
				// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
				// parent we and the submitter think is the latest, our insert will fail.
				// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
				// handing user back to specific dialog indicating race condition
				$this->errors['prev_revision'] = wfMessage( 'flow-error-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $this->header->getRevisionId()->getHex() );
			}
			// this isnt really part of validate, but we want the error-rendering template to see the users edited header
			$this->header = $this->header->newNextRevision( $this->user, $this->submitted['content'], 'edit-header' );
		} else {
			if ( !$this->permissions->isAllowed( null, 'create-header' ) ) {
				$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
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
			} else {
				// User submitted a previous revision, but we couldn't find one.  This is likely
				// an internal error and not a user error, consider better handling
				$this->errors['prev_revision'] = wfMessage( 'flow-error-prev-revision-does-not-exist' );
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
			$user = $this->user;

			return array(
				'new-revision-id' => $this->header->getRevisionId(),
				'render-function' => function( $templating ) use ( $header, $user ) {
					return $templating->getContent( $header, 'html', $user );
				},
			);
			break;

		default:
			throw new \MWException( 'Unrecognized commit action' );
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

			$historyRecord = $this->loadBoardHistory();
			if ( $historyRecord ) {
				$tplVars['historyExists'] = true;
				$tplVars['history'] = new History( $historyRecord );
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
			$output['*'] = $templating->getContent( $this->header, $contentFormat, $this->user );
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

	protected function loadBoardHistory() {
		$found = $this->storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 300 )
		);

		if ( $found === false ) {
			throw new \MWException( "Unable to load topic list history for " . $this->workflow->getId()->getHex() );
		}

		return $found;
	}

	public function getName() {
		return 'header';
	}

}
