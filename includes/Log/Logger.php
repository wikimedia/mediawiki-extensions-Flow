<?php

namespace Flow\Log;

use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\UrlGenerator;
use ManualLogEntry;
use User;
use Closure;
use Flow\Model\UUID;

class Logger {
	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

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
	 * @param UrlGenerator $urlGenerator
	 * @param User $user
	 */
	public function __construct( FlowActions $actions, UrlGenerator $urlGenerator, User $user ) {
		$this->actions = $actions;
		$this->urlGenerator = $urlGenerator;
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
		$section = new \ProfileSection( __METHOD__ );

		if ( !$this->canLog( $post, $action ) ) {
			return null;
		}

		$logType = $this->getLogType( $post, $action );

		list( $title ) = $this->urlGenerator->generateUrlData(
			$workflowId,
			'view',
			$params
		);

		// insert logging entry
		$logEntry = new ManualLogEntry( $logType, "flow-$action" );
		$logEntry->setTarget( $title );
		$logEntry->setPerformer( $this->user );
		$logEntry->setParameters( $params );
		$logEntry->setComment( $reason );
		$logId = $logEntry->insert();

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
