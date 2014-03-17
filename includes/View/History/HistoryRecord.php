<?php

namespace Flow\View\History;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
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
	 * @throws InvalidActionException If the action does not exist
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

		if ( $details['class'] instanceof \Closure ) {
			$details['class'] = $details['class']( $this->getData() );
		}

		return $details['class'];
	}

	/**
	 * Returns the message object for this change type. The arguments passed on
	 * to this method, be be passed along to the callback functions that
	 * generate the final message parameters, per message.
	 *
	 * @return Message
	 */
	public function getMessageParams() {
		$details = $this->getActionDetails( $this->getType() );
		$params = isset( $details['i18n-params'] ) ? $details['i18n-params'] : array();
		return array( $details['i18n-message'], $params );
	}

	/**
	 * @return bool
	 */
	public function isBundled() {
		$details = $this->getActionDetails( $this->getType() );
		return isset( $details['bundle'] );
	}
}
