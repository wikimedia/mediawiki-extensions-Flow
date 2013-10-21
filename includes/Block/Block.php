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
	protected $errors;
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

	public function findWorkflow( $option ) {
		$defStorage = $this->storage->getStorage( 'Definition' );
		$sourceDef = $defStorage->get( $this->workflow->getDefinitionId() );
		$requestedDef = $defStorage->get( $sourceDef->getOption( $option ) );
		if ( !$requestedDef ) {
			throw new \MWException( "Invalid definition owns this ". get_class() .", needs a valid $option option assigned" );
		}

		return $requestedDef->createWorkflow( $this->user, $this->workflow->getArticleTitle() );
	}
}
