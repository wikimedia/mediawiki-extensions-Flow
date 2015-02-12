<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidInputException;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\RevisionActionPermissions;
use Flow\SpamFilter\Controller as SpamFilterController;
use Flow\Templating;
use IContextSource;

interface Block {
	/**
	 * @param IContextSource $context
	 * @param string $action
	 */
	function init( IContextSource $context, $action );

	/**
	 * Perform validation of data model
	 *
	 * @param array $data
	 * @return boolean True if data model is valid
	 */
	function onSubmit( array $data );

	/**
	 * Write updates to storage
	 */
	function commit();

	/**
	 * Render the API output of this Block.
	 * Templating is provided for convenience
	 *
	 * @param array $options
	 * @return array
	 */
	function renderApi( array $options );

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

	/** @var Workflow */
	protected $workflow;
	/** @var ManagerGroup */
	protected $storage;

	/** @var IContextSource */
	protected $context;
	/** @var array|null */
	protected $submitted = null;
	/** @var array */
	protected $errors = array();

	/**
	 * @var string|null The commitable action being submitted, or null
	 *  for read-only actions.
	 */
	protected $action;

	/** @var RevisionActionPermissions */
	protected $permissions;

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

	/** @var array */
	protected $requiresWikitext = array();

	/**
	 * Templates for each view actions
	 * @var array
	 */
	protected $templates = array();

	public function __construct( Workflow $workflow, ManagerGroup $storage ) {
		$this->workflow = $workflow;
		$this->storage = $storage;
	}

	/**
	 * Called by $this->onSubmit to populate $this->errors based
	 * on $this->action and $this->submitted.
	 */
	abstract protected function validate();

	// This method exists in the Block interface and as such cannot be abstract
	// until php 5.3.9, but MediaWiki requires PHP version 5.3.2 or later (and
	// some of our test machines are on 5.3.3).
	//abstract public function commit();

	/**
	 * @var IContextSource $context
	 * @var string $action
	 */
	public function init( IContextSource $context, $action ) {
		$this->context = $context;
		$this->action = $action;
		// @todo not guaranteed that $this->permissions->getUser() === $context->getUser();
		$this->permissions = Container::get( 'permissions' );
	}

	/**
	 * @return IContextSource
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Returns true if the block can submit the requested action, or false
	 * otherwise.
	 *
	 * @param string $action
	 * @return bool
	 */
	public function canSubmit( $action ) {
		return in_array( $this->getActionName( $action ), $this->supportedPostActions );
	}

	/**
	 * Returns true if the block can render the requested action, or false
	 * otherwise.
	 *
	 * @param string $action
	 * @return bool
	 */
	public function canRender( $action ) {
		return
			// GET actions can be rendered
			in_array( $this->getActionName( $action ), $this->supportedGetActions ) ||
			// POST actions are usually redirected to 'view' after successfully
			// completing the request, but can also be rendered (e.g. to show
			// error message after unsuccessful submission)
			$this->canSubmit( $action );
	}

	/**
	 * Get the template name for a specific action or an array of template
	 * for all possible view actions in this block
	 *
	 * @param string|null
	 * @return string|array
	 * @throws InvalidInputException
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

	/**
	 * @param array $data
	 * @return bool|null true when accepted, false when not accepted.
	 *  null when this action does not support submission.
	 */
	public function onSubmit( array $data ) {
		if ( !$this->canSubmit( $this->action ) ) {
			return null;
		}

		$this->submitted = $data;
		$this->validate();

		return !$this->hasErrors();
	}

	public function wasSubmitted() {
		return $this->submitted !== null;
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
		/** @var FlowActions $actions */
		$actions = Container::get( 'flow_actions' );
		$alias = $actions->getValue( $action );
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
	 * @return boolean True when content is allowed by spam filter
	 */
	protected function checkSpamFilters( AbstractRevision $old = null, AbstractRevision $new ) {
		/** @var SpamFilterController $spamFilter */
		$spamFilter = Container::get( 'controller.spamfilter' );
		$status = $spamFilter->validate( $new, $old, $this->workflow->getArticleTitle() );
		if ( $status->isOK() ) {
			return true;
		}

		$this->addError( 'spamfilter', $status->getMessage() );
		return false;
	}

	/**
	 * @return string The new edit token
	 */
	public function getEditToken() {
		return $this->context->getUser()->getEditToken();
	}

	/**
	 * @param string $action
	 */
	public function unsetRequiresWikitext( $action ) {
		$key = array_search( $action, $this->requiresWikitext );
		if ( $key !== false ) {
			unset( $this->requiresWikitext[$key] );
		}
	}

	/**
	 * @param Templating $templating
	 * @param \OutputPage $out
	 */
	public function setPageTitle( Templating $templating, \OutputPage $out ) {
		if ( $out->getPageTitle() ) {
			// Don't override page title if another block has already set it.
			// If this should *really* be done, the specific block extending
			// this AbstractBlock should just implement this itself ;)
			return;
		}

		$out->setPageTitle( $this->workflow->getArticleTitle()->getFullText() );
	}
}
