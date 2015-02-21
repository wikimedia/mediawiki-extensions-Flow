<?php

namespace Flow\Log;

use Closure;
use Flow\Container;
use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use ManualLogEntry;
use Title;
use User;

class ModerationLogger {

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
	 * Adds a moderation activity item to the log under the appropriate action
	 *
	 * @param PostRevision $post
	 * @param string $action The action we'll be logging
	 * @param string $reason Comment, reason for the moderation
	 * @param UUID $workflowId Workflow being worked on
	 * @return int The id of the newly inserted log entry
	 */
	public function log( PostRevision $post, $action, $reason, UUID $workflowId ) {
		if ( !$this->canLog( $post, $action ) ) {
			return null;
		}

		$params = array();

		if ( $post->isTopicTitle() ) {
			$params['topicId'] = $workflowId;
		} else {
			$params['postId'] = $post->getPostId();
		}

		$logType = $this->getLogType( $post, $action );

		// reasonably likely this is already loaded in-process and just returns that object
		/** @var Workflow $workflow */
		$workflow = Container::get( 'storage.workflow' )->get( $workflowId );
		if ( $workflow ) {
			$title = $workflow->getArticleTitle();
		} else {
			$title = false;
		}
		$error = false;
		if ( !$title ) {
			// We dont want to fail logging due to this, so repoint it at Main_Page which
			// will probably be noticed, also log it below once we know the logId
			$title = Title::newMainPage();
			$error = true;
		}

		// insert logging entry
		$logEntry = new ManualLogEntry( $logType, "flow-$action" );
		$logEntry->setTarget( $title );
		$logEntry->setPerformer( $this->user );
		$logEntry->setParameters( $params );
		$logEntry->setComment( $reason );
		$logEntry->setTimestamp( $post->getModerationTimestamp() );

		$logId = $logEntry->insert();

		if ( $error ) {
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
