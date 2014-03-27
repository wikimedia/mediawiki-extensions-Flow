<?php

/**
 * A special page that redirects to a workflow or PostRevision given a UUID
 */

namespace Flow;

use FormSpecialPage;
use HTMLForm;
use Status;
use Flow\Container;
use Flow\Model\UUID;
use Flow\Exception\FlowException;

class SpecialFlow extends FormSpecialPage {

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

	function __construct() {
		parent::__construct( 'Flow' );
	}

	/**
	 * Initialize $this->type and $this-uuid using the subpage string.
	 * @param string $par
	 */
	protected function setParameter( $par ) {
		$tokens = explode( '/', $par, 2 );
		$this->type = $tokens[0];
		if ( count( $tokens ) > 1 ) {
			$this->uuid = $tokens[1];
		}
	}

	/**
	 * Get the mapping between display text and value for the type dropdown.
	 * @return array
	 */
	protected function getTypes() {
		$mapping = array(
			'flow-special-type-post' => 'post',
			'flow-special-type-workflow' => 'workflow',
		);

		$types = array();
		foreach ( $mapping as $msgKey => $option ) {
			$types[$this->msg( $msgKey )->escaped()] = $option;
		}
		return $types;
	}

	protected function getFormFields() {
		return array(
			'type' => array(
				'id' => 'mw-flow-special-type',
				'name' => 'type',
				'type' => 'select',
				'label-message' => 'flow-special-type',
				'options' => $this->getTypes(),
				'default' => empty( $this->type ) ? 'post' : $this->type,
			),
			'uuid' => array(
				'id' => 'mw-flow-special-uuid',
				'name' => 'uuid',
				'type' => 'text',
				'label-message' => 'flow-special-uuid',
				'default' => $this->uuid,
			),
		);
	}

	/**
	 * Description shown at the top of the page
	 * @return string
	 */
	protected function preText() {
		return '<p>' . $this->msg( 'flow-special-desc' )->escaped() . '</p>';
	}

	protected function alterForm( HTMLForm $form ) {
		// Style the form.
		$form->setDisplayFormat( 'vform' );
		$form->setWrapperLegend( false );

		$form->setMethod( 'get' ); // This also submits the form every time the page loads.
	}

	/**
	 * Get the URL of a UUID for a PostRevision.
	 * @return string|null
	 */
	protected function getPostUrl() {
		try {
			$postId = UUID::create( $this->uuid );
			$rootId = Container::get( 'repository.tree' )->findRoot( $postId );
			return Container::get( 'url_generator' )->postLink(
				null,
				$rootId,
				$postId
			)->getFullUrl();
		} catch ( FlowException $e ) {
			return null; // The UUID is invalid or has no root post.
		}
	}

	/**
	 * Get the URL of a UUID for a workflow.
	 * @return string|null
	 */
	protected function getWorkflowUrl() {
		try {
			return Container::get( 'url_generator' )->workflowLink(
				null,
				UUID::create( $this->uuid )
			)->getFullUrl();
		} catch ( FlowException $e ) {
			return null; // The UUID is invalid or has no root post.
		}
	}

	/**
	 * Set redirect and return true if $data['uuid'] or $this->par exists and is
	 * a valid UUID; otherwise return false or a Status object encapsulating any
	 * error, which causes the form to be shown.
	 * @param array $data
	 * @return bool|Status
	 */
	public function onSubmit( array $data ) {
		if ( !empty( $data['type'] ) && !empty( $data['uuid'] ) ) {
			$this->setParameter( $data['type'] . '/' . $data['uuid'] );
		}

		// Assume no data has been passed in if there is no UUID.
		if ( empty( $this->uuid ) ) {
			return false; // Display the form.
		}

		switch ( $this->type ) {
			case 'post':
				$url = $this->getPostUrl();
				break;
			case 'workflow':
				$url = $this->getWorkflowUrl();
				break;
			default:
				$url = null;
				break;
		}

		if ( $url ) {
			$this->getOutput()->redirect( $url );
			return true;
		} else {
			$this->getOutput()->setStatusCode( 404 );
			return Status::newFatal( 'flow-special-invalid-uuid' );
		}
	}
}
