<?php

namespace Flow\Log;

use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\UrlGenerator;
use ManualLogEntry;
use User;
use Closure;
use Flow\Model\UUID;

class Logger {

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @param FlowActions $actions
	 * @param User $user
	 */
	public function __construct( FlowActions $actions, User $user ) {
		$this->actions = $actions;
		$this->user = $user;
	}

	/**
	 * Check if an action should be logged (= if a log_type is set)
	 *
	 * @param PostRevision $post
	 * @param string $action
	 * @return bool
	 */
	public function canLog( PostRevision $post, $action ) {
		return (bool) $this->getLogType( $post, $action );
	}

	/**
	 * Adds an activity item to the log under the flow|suppress.
	 *
	 * @param PostRevision $post
	 * @param string $action The action we'll be logging
	 * @param string $reason Comment, reason for the moderation
	 * @param UUID $workflowId Workflow being worked on
	 * @param array $params Additional parameters to be saved
	 * @return int The id of the newly inserted log entry
	 */
	public function log( PostRevision $post, $action, $reason, UUID $workflowId, $params = array() ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );

		if ( !$this->canLog( $post, $action ) ) {
			return null;
		}

		$logType = $this->getLogType( $post, $action );

		// reasonably likely this is already loaded in-process and just returns that object
		$workflow = \Flow\Container::get( 'storage.workflow' )->get( $workflowId );
		if ( $workflow ) {
			$title = $workflow->getArticleTitle();
		} else {
			// We dont want to fail logging due to this, so repoint it at Main_Page which
			// will probably be noticed, also log it below once we know the logId
			$title = Title::newMainPage();
		}

		// insert logging entry
		$logEntry = new ManualLogEntry( $logType, "flow-$action" );
		$logEntry->setTarget( $title );
		$logEntry->setPerformer( $this->user );
		$logEntry->setParameters( $params );
		$logEntry->setComment( $reason );
		$logId = $logEntry->insert();

		if ( $title === null ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Could not map workflowId to workflow object for ' . $workflowId->getAlphadecimal() . " log entry $logId defaulted to Main_Page");
		}

		return $logId;
	}

	/**
	 * @param PostRevision $post
	 * @param string $action
	 * @return string
	 */
	public function getLogType( PostRevision $post, $action ) {
		$logType = $this->actions->getValue( $action, 'log_type' );
		if ( $logType instanceof Closure) {
			$logType = $logType( $post, $this );
		}

		return $logType;
	}
}
