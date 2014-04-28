<?php

namespace Flow\Block;

use Flow\Exception\InvalidInputException;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\NotificationController;
use Flow\Data\ManagerGroup;
use Flow\SpamFilter\Controller as SpamFilterController;
use Flow\Templating;
use Flow\Model\AbstractRevision;
use Flow\Container;
use User;


interface Block {
	/**
	 * Perform validation of data model
	 *
	 * @param string $action
	 * @param User $user
	 * @param array $data
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
	 *
	 * @param Templating $templating
	 * @param array $options
	 * @return string
	 */
	function render( Templating $templating, array $options );

	/**
	 * Render the API output of this Block.
	 * Templating is provided for convenience
	 *
	 * @param Templating $templating
	 * @param array $options
	 * @return array
	 */
	function renderAPI( Templating $templating, array $options );

	/**
	 * @return string Unique name among all blocks on an object
	 */
	function getName();

	/**
	 * @return UUID
	 */
	function getWorkflowId();
}

abstract class AbstractBlock implements Block {

	protected $workflow;
	protected $storage;

	/** @var User $user */
	protected $user;
	protected $submitted = null;
	protected $errors = array();
	protected $action;

	/**
	 * A list of supported post actions
	 * @var array
	 */
	protected $supportedPostActions = array();

	/**
	 * A list of supported get actions
	 * @var array
	 */
	protected $supportedGetActions = array();

	/**
	 * Templates for each view actions
	 * @var array
	 */
	protected $templates = array();

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
		$this->action = $this->getActionName( $action );
		$this->user = $user;
	}

	/**
	 * Returns true of the block can submit the requested action, or false
	 * otherwise.
	 *
	 * @param string $action
	 * @return bool
	 */
	public function canSubmit( $action ) {
		return in_array( $this->getActionName( $action ), $this->supportedPostActions );
	}

	/**
	 * Returns true of the block can render the requested action, or false
	 * otherwise.
	 *
	 * @param string $action
	 * @return bool
	 */
	public function canRender( $action ) {
		return in_array( $this->getActionName( $action ), $this->supportedGetActions );
	}

	/**
	 * Get the template name for a specific action or an array of template
	 * for all possible view actions in this block
	 *
	 * @param string|null
	 * @return string|array
	 */
	public function getTemplate( $action = null ) {
		if ( $action === null ) {
			return $this->templates;
		}
		if ( !isset( $this->templates[$action] ) ) {
			throw new InvalidInputException( 'Template is not defined for action: ' . $action, 'invalid-input' );
		}
		return $this->templates[$action];
	}

	public function onSubmit( $action, User $user, array $data ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		if ( !$this->canSubmit( $action ) ) {
			return null;
		}

		$this->user = $user;
		$this->submitted = $data;

		$this->validate();

		return !$this->hasErrors();
	}

	public function wasSubmitted() {
		return $this->submitted !== null;
	}

	public function onRender( $action, Templating $templating, array $options ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		if ( !$this->canRender( $action ) ) {
			return false;
		}

		$this->render( $templating, $options );
		return true;
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
	 * @return \Message
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
	 * @param \Message $message
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

	/**
	 * Given a certain action name, this returns the valid action name. This is
	 * meant for BC compatibility with renamed actions.
	 *
	 * @param string $action
	 * @return string
	 */
	public function getActionName( $action ) {
		// BC for renamed actions
		$alias = Container::get( 'flow_actions' )->getValue( $action );
		if ( is_string( $alias ) ) {
			// All proper actions return arrays, but aliases return a string
			$action = $alias;
		}

		return $action;
	}

	/**
	 * Run through AbuseFilter and friends.
	 * @todo Having to call spamFilter in each place that creates a revision
	 *  is error-prone.
	 *
	 * @param AbstractRevision|null $old null when $new is first revision
	 * @param AbstractRevision $new
	 * @return boolean
	 */
	protected function checkSpamFilters( AbstractRevision $old = null, AbstractRevision $new ) {
		/** @var SpamFilterController $spamFilter */
		$spamFilter = Container::get( 'controller.spamfilter' );
		$status = $spamFilter->validate( $new, $old, $this->workflow->getArticleTitle() );
		if ( $status->isOK() ) {
			return true;
		}
		foreach ( $status->getErrorsArray() as $message ) {
			$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
		}
		return false;
	}

	public function getEditToken() {
		global $wgFlowTokenSalt, $wgRequest;
		return $this->user->getEditToken( $wgFlowTokenSalt );
	}
}
