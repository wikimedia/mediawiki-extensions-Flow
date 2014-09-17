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
		if ( !$title->isLocal() ) {
			return Status::newFatal( 'flow-activation-error-invalidactivatepage' );
		}
		if ( $title->getContentModel() === 'flow-board' ) {
			return Status::newFatal( 'flow-activtion-error-alreadyactive' );
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

		$status = Container::get( 'activator' )->activate( $title, $data['comment'], $this->getUser() );
		if ( $status->isGood() ) {
			$this->getOutput()->redirect( $title->getLinkURL() );
		}

		return $status;
	}
}
