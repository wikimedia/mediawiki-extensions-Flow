<?php

namespace Flow\Block;

use Flow\Model\Workflow;
use Flow\Model\Post;
use Flow\Data\ManagerGroup;
use Flow\Templating;
use User;


interface Block {
	/**
	 * Perform validation of data model
	 * @return boolean True if data model is valid
	 */
	function onSubmit( $action, User $user, array $data );

	/**
	 * Write updates to storage
	 */
	function commit();

	/**
	 * Load whatever is necessary for rendering an use $templating to
	 * render it.
	 */
	function render( Templating $templating, array $options );

	/**
	 * Render the API output of this Block.
	 * Templating is provided for convenience
	 */
	function renderAPI( Templating $templating, array $options );

	/**
	 * @return string Unique name among all blocks on an object
	 */
	function getName();
}

abstract class AbstractBlock implements Block {

	protected $workflow;
	protected $storage;

	protected $user;
	protected $submitted;
	protected $errors;
	protected $action;
	protected $supportedActions = array();

	public function __construct( Workflow $workflow, ManagerGroup $storage ) {
		$this->workflow = $workflow;
		$this->storage = $storage;
	}

	abstract protected function validate();
	abstract public function render( Templating $templating, array $options );
	abstract public function renderAPI( Templating $templating, array $options );
	abstract public function commit();

	public function init( $action, $user ) {
		$this->action = $action;
		$this->user = $user;
	}

	public function onSubmit( $action, User $user, array $data  ) {
		if ( false === array_search( $action, $this->supportedActions ) ) {
			return null;
		}

		$this->user = $user;
		$this->submitted = $data;

		$this->validate();

		return !$this->errors;
	}

	public function hasErrors( $type = null ) {
		if ( $type === null ) {
			return (bool) $this->errors;
		}
		return isset( $this->errors[$type] );
	}

	public function getErrors() {
		return $this->errors;
	}

	public function getError( $type ) {
		return isset( $this->errors[$type] ) ? $this->errors[$type] : null;
	}

	public function getWorkflow() {
		return $this->workflow;
	}

	public function getWorkflowId() {
		return $this->workflow->getId();
	}
}
