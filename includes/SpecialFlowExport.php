<?php

/**
 * A special page that redirects to a workflow or PostRevision given a UUID
 */

namespace Flow;

use Flow\Exception\FlowException;
use FormSpecialPage;
use HTMLForm;
use Status;
use Title;

class SpecialFlowExport extends FormSpecialPage {

	/**
	 * The type of content, e.g. 'post', 'workflow'
	 * @var string $type
	 */
	protected $type;

	/**
	 * Flow UUID
	 * @var string $uuid
	 */
	protected $uuid;

	public function __construct() {
		parent::__construct( 'FlowExport' );
	}

	protected function getFormFields() {
		return array(
			'export' => array(
				'id' => 'mw-flow-special-export-title',
				'name' => 'export',
				'type' => 'text',
				'label-message' => 'flow-special-export-title-label',
			),
		);
	}

	/**
	 * Description shown at the top of the page
	 * @return string
	 */
	protected function preText() {
		return '<p>' . $this->msg( 'flow-special-export-desc' )->escaped() . '</p>';
	}

	protected function alterForm( HTMLForm $form ) {
		// Style the form.
		$form->setDisplayFormat( 'vform' );
		$form->setWrapperLegend( false );

		$form->setMethod( 'get' ); // This also submits the form every time the page loads.
	}

	/**
	 * Set redirect and return true if $data['uuid'] or $this->par exists and is
	 * a valid UUID; otherwise return false or a Status object encapsulating any
	 * error, which causes the form to be shown.
	 * @param array $data
	 * @return bool|Status
	 */
	public function onSubmit( array $data ) {
		if ( !isset( $data['export'] ) ) {
			return false;
		}

		$title = Title::newFromText( $data['export'] );
		if ( !$title ) {
			return Status::newFatal( 'flow-special-export-error-invalid-title' );
		}
		if ( !$title->exists() ) {
			return Status::newFatal( 'flow-special-export-error-non-existent-title' );
		}
		if ( $title->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return Status::newFatal( 'flow-special-export-error-wrong-content-model' );
		}
		if ( $title->getNamespace() === NS_TOPIC ) {
			return Status::newFatal( 'flow-special-export-error-topic-not-implemented' );
		}

		$exporter = new \Flow\Utils\Export;
		$renderer = Container::get( 'lightncandy' )->getTemplate( 'flow_board_export_wikitext' );

		$this->getOutput()->addHTML( $renderer( array(
			'title' => $title->getPrefixedText(),
			'wikitext' => $exporter->export( $title ),
		) ) );

		return true;
	}
}
