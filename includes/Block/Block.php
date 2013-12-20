<?php

namespace Flow\Block;

use Flow\Model\Workflow;
use Flow\Model\Post;
use Flow\NotificationController;
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
	protected $errors = array();
	protected $action;
	protected $supportedActions = array();
	protected $notificationController;

	public function __construct( Workflow $workflow, ManagerGroup $storage, NotificationController $notificationController ) {
		$this->workflow = $workflow;
		$this->storage = $storage;
		$this->notificationController = $notificationController;
	}

	abstract protected function validate();
	// These methods exist in the Block interface and as such cannot be abstract
	// until php 5.3.9, but MediaWiki requires PHP version 5.3.2 or later (and
	// some of our test machines are on 5.3.3).
	//abstract public function render( Templating $templating, array $options );
	//abstract public function renderAPI( Templating $templating, array $options );
	//abstract public function commit();

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

	/**
	 * Checks if any errors have occurred in the block (no argument), or if a
	 * specific error has occurred (argument being the error type)
	 *
	 * @param string[optional] $type
	 * @return bool
	 */
	public function hasErrors( $type = null ) {
		if ( $type === null ) {
			return (bool) $this->errors;
		}
		return isset( $this->errors[$type] );
	}

	/**
	 * Returns an array of all error types encountered in this block. The values
	 * in the returned array can be used to pass to getErrorMessage() or
	 * getErrorExtra() to respectively fetch the specific error message or
	 * additional details.
	 *
	 * @return array
	 */
	public function getErrors() {
		return array_keys( $this->errors );
	}

	/**
	 * @param $type
	 * @return Message
	 */
	public function getErrorMessage( $type ) {
		return isset( $this->errors[$type]['message'] ) ? $this->errors[$type]['message'] : null;
	}

	/**
	 * @param $type
	 * @return mixed
	 */
	public function getErrorExtra( $type ) {
		return isset( $this->errors[$type]['extra'] ) ? $this->errors[$type]['extra'] : null;
	}

	/**
	 * @param string $type
	 * @param Message $message
	 * @param mixed[optional] $extra
	 */
	public function addError( $type, \Message $message, $extra = null ) {
		$this->errors[$type] = array(
			'message' => $message,
			'extra' => $extra,
		);
	}

	public function getWorkflow() {
		return $this->workflow;
	}

	public function getWorkflowId() {
		return $this->workflow->getId();
	}

	public function getStorage() {
		return $this->storage;
	}
}
