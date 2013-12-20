<?php

namespace Flow\View\History;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use MWException;
use MWTimestamp;
use Message;
use Flow\Exception\InvalidActionException;

/**
 * HistoryRecord accepts an AbstractRevision and, based on FlowActions, provides
 * some methods to access history-related information for this revision's
 * specific action.
 */
class HistoryRecord {
	/**
	 * @var AbstractRevision
	 */
	protected $data;

	/**
	 * @param AbstractRevision $revision
	 */
	public function __construct( AbstractRevision $revision ) {
		$this->data = $revision;
	}

	/**
	 * @return FlowActions
	 */
	protected function getActions() {
		/*
		 * It's not pretty how this is just pulled form container, but I don't
		 * want to pass along the actions config to all classes.
		 * I think pulling config is perhaps not that bad ;)
		 */
		return Container::get( 'flow_actions' );
	}

	/**
	 * Returns action details.
	 *
	 * @param string $action
	 * @return array|bool Array of action details or false if invalid
	 */
	protected function getActionDetails( $action ) {
		$details = $this->getActions()->getValue( $action, 'history' );
		if ( $details === null ) {
			throw new InvalidActionException( "History action '$action' is not defined.", 'invalid-action' );
		}

		return $details;
	}

	/**
	 * @return AbstractRevision
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return AbstractRevision
	 */
	public function getRevision() {
		return $this->getData();
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->getRevision()->getChangeType();
	}

	/**
	 * @return MWTimestamp
	 */
	public function getTimestamp() {
		return new MWTimestamp( $this->getRevision()->getRevisionId()->getTimestampObj() );
	}

	/**
	 * @return string
	 */
	public function getClass() {
		$details = $this->getActionDetails( $this->getType() );
		return $details['class'];
	}

	/**
	 * Returns the message object for this change type. The arguments passed on
	 * to this method, be be passed along to the callback functions that
	 * generate the final message parameters, per message.
	 *
	 * @param $callbackParam1[optional] Callback parameter
	 * @param $callbackParam2[optional] Second callback parameter (this method
	 * can be overloaded, all params will be passed along)
	 * @return Message
	 */
	public function getMessage( $callbackParam1 = null /*[, $callbackParam2[, ...]] */ ) {
		$details = $this->getActionDetails( $this->getType() );
		$params = isset( $details['i18n-params'] ) ? $details['i18n-params'] : array();
		return $this->buildMessage( $details['i18n-message'], $params, func_get_args() );
	}

	/**
	 * @return bool
	 */
	public function isBundled() {
		$details = $this->getActionDetails( $this->getType() );
		return isset( $details['bundle'] );
	}

	/**
	 * Returns i18n message for $msg.
	 *
	 * Complex parameters can be injected in the i18n messages. Anything in
	 * $params will be call_user_func'ed, with these given $arguments.
	 * Those results will be used as message parameters.
	 *
	 * Note: return array( 'raw' => $value ) or array( 'num' => $value ) for
	 * raw or numeric parameter input.
	 *
	 * @param string $msg i18n key
	 * @param array[optional] $params Callbacks for parameters
	 * @param array[optional] $arguments Arguments for the callbacks
	 * @return Message
	 */
	protected function buildMessage( $msg, array $params = array(), array $arguments = array() ) {
		foreach ( $params as &$param ) {
			$param = call_user_func_array( $param, $arguments );
		}

		return wfMessage( $msg, $params );
	}
}
