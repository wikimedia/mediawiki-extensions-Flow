<?php

namespace Flow\Block;

use Flow\DbFactory;
use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use Flow\Model\Summary;
use Flow\Repository\SummaryRepository;
use Flow\Templating;
use User;

class SummaryBlock extends AbstractBlock {

	protected $summary;
	protected $needCreate = false;
	protected $supportedActions = array( 'edit-summary' );

	public function init( $action ) {
		parent::init( $action );
		if ( $this->workflow->isNew() ) {
			$this->needCreate = true;
			return;
		}
		// Get the latest summary attached to this workflow
		$found = $this->storage->find(
			'Summary',
			array( 'summary_workflow_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		if ( $found ) {
			$this->summary = reset( $found );
		}
	}

	protected function validate() {
		if ( $this->summary ) {
			if ( empty( $this->submitted['prev_revision'] ) ) {
				$this->errors['prev_revision'] = wfMessage( 'flow-missing-prev-revision-identifier' );
			} elseif ( $this->summary->getRevisionId()->getHex() !== $this->submitted['prev_revision'] ) {
				// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
				// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
				// parent we and the submitter think is the latest, our insert will fail.
				// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
				// handing user back to specific dialog indicating race condition
				$this->errors['prev_revision'] = wfMessage( 'flow-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $this->summary->getRevisionId()->getHex() );
			}
			// this isnt really part of validate, but we want the error-rendering template to see the users edited summary
			$this->summary = $this->summary->newNextRevision( $this->user, $this->submitted['content'] );
		} else {
			if ( empty( $this->submitted['prev_revision'] ) ) {
				// this isnt really part of validate either, should validate be renamed or should this logic be redone?
				$this->summary = Summary::create( $this->workflow, $this->user, $this->submitted['content'] );
			} else {
				// User submitted a previous revision, but we couldn't find one.  This is likely
				// an internal error and not a user error, consider better handling
				$this->errors['prev_revision'] = wfMessage( 'flow-prev-revision-does-not-exist' );
			}
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-summary-content' );
		}
	}

	public function needCreate() {
		return $this->needCreate;
	}

	public function commit() {
		switch( $this->action ) {
		case 'edit-summary':
			$this->storage->put( $this->summary );
			break;

		default:
			throw new \MWException( 'Unrecognized commit action' );
		}
	}

	public function render( Templating $templating, array $options ) {
		$templating->render( "flow:summary.html.php", array(
			'block' => $this,
			'workflow' => $this->workflow,
			'summary' => $this->summary,
		) );
	}

	public function renderAPI( array $options ) {
		$output = array();
		$output['type'] = 'summary';
		$output['*'] = $this->summary->getContent();
		$output['summary-id'] = $this->workflow->getId()->getHex();

		$output = array(
			'_element' => 'summary',
			0 => $output,
		);

		return $output;
	}

	public function getName() {
		return 'summary';
	}

}
