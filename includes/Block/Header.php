<?php

namespace Flow\Block;

use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\DbFactory;
use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use Flow\Model\Header;
use Flow\Repository\HeaderRepository;
use Flow\Templating;
use User;

class HeaderBlock extends AbstractBlock {

	protected $header;
	protected $needCreate = false;
	protected $supportedActions = array( 'edit-header' );

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
	}

	protected function validate() {
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-header-content' );
		}

		if ( $this->header ) {
			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->errors['prev_revision'] = wfMessage( 'flow-missing-prev-revision-identifier' );
			} elseif ( $this->header->getRevisionId()->getHex() !== $this->submitted['prev_revision'] ) {
				// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
				// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
				// parent we and the submitter think is the latest, our insert will fail.
				// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
				// handing user back to specific dialog indicating race condition
				$this->errors['prev_revision'] = wfMessage( 'flow-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $this->header->getRevisionId()->getHex() );
			}
			// this isnt really part of validate, but we want the error-rendering template to see the users edited header
			$this->header = $this->header->newNextRevision( $this->user, $this->submitted['content'], 'flow-edit-header' );
		} else {
			if ( empty( $this->submitted['prev_revision'] ) ) {
				// this isnt really part of validate either, should validate be renamed or should this logic be redone?
				$this->header = Header::create( $this->workflow, $this->user, $this->submitted['content'] );
			} else {
				// User submitted a previous revision, but we couldn't find one.  This is likely
				// an internal error and not a user error, consider better handling
				$this->errors['prev_revision'] = wfMessage( 'flow-prev-revision-does-not-exist' );
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

			return array(
				'new-revision-id' => $this->header->getRevisionId(),
				'rendered' => $this->header->getContent( $this->user, 'html' ),
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
			$templating->getOutput()->addModules( 'ext.flow.history' );
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
			$templating->getOutput()->addModules( 'ext.flow.header' );
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
			$output['*'] = $this->header->getContent( $this->user, $contentFormat );
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
