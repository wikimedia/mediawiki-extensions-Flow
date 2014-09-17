<?php

namespace Flow;

use Flow\Container;
use Flow\Content\BoardContent;
use Flow\Model\Workflow;
use FormSpecialPage;
use HTMLForm;
use Status;
use Title;
use WikiPage;

class SpecialFlowActivation extends FormSpecialPage {

	public function __construct() {
		parent::__construct( 'FlowActivation' );
	}

	public function getRestriction() {
		return 'flow-create-board';
	}

	protected function getFormFields() {
		return array(
			'activatepage' => array(
				'id' => 'mw-flow-foo-bar',
				'name' => 'activatepage',
				'type' => 'text',
				'label-message' => 'flow-activation-label-title',
			),
			'comment' => array(
				'id' => 'mw-flow-activation-comment',
				'name' => 'comment',
				'type' => 'text',
				'label-message' => 'flow-activation-label-comment',
			),
		);
	}

	protected function alterForm( HTMLForm $form ) {
		$form->setDisplayFormat( 'vform' );
		$form->setWrapperLegend( false );
		$form->setSubmitText( $this->msg( 'flow-activation-form-submit' )->escaped() );
	}

	public function onSubmit( array $data ) {
		if ( !isset( $data['activatepage'] ) ) {
			return Status::newFatal( 'flow-activation-error-invalidactivatepage' );
		}

		$title = Title::newFromText( $data['activatepage'] );
		if ( !$title ) {
			return Status::newFatal( 'flow-activation-error-invalidactivatepage' );
		}
		$page = WikiPage::factory( $title );
		if ( $page->getId() ) {
			return Status::newFatal( 'flow-activation-error-pageexists' );
		}

		if ( isset( $data['comment'] ) && strlen( $data['comment'] ) > 0 ) {
			$comment = $data['comment'];
		} else {
			$comment = '/* Taken over by Flow */';
		}

		$om = Container::get( 'storage.workflow' );
		$found = $om->find( array(
			'workflow_wiki' => wfWikiId(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDbKey(),
			'workflow_type' => 'discussion',
		) );
		if ( $found ) {
			$workflow = reset( $found );
		} else {
			$workflow = Workflow::create( 'discussion', $title );
		}

		Container::get( 'occupation' )->set( (string)$title, true );
		$status = $page->doEditContent(
			/* content */new BoardContent( 'flow-board', $workflow ),
			/* comment */ $comment,
			/* flags */ 0,
			/* baseRevId */ false,
			/* user */ $this->getUser()
		);

		if ( !$status->isGood() ) {
			return $status;
		}

		if ( $workflow->isNew() ) {
			$om->put( $workflow );
		}

		$this->getOutput()->redirect( $title->getLinkUrl() );

		return $status;
	}
}
