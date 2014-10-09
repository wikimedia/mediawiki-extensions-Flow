<?php

namespace Flow\Data\RecentChanges;

use Flow\Container;
use Flow\Data\LifecycleHandler;
use Flow\Repository\UserNameBatch;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\Workflow;
use Closure;

/**
 * Abstract class for inserting mw recentchange rows for flow AbstractRevision
 * instances.  Each revision type must extend this class implemnting the
 * self::onAfterInsert method which calls self::insert.  Those handlers must then
 * be attached to the appropriate ObjectManager.
 */
abstract class RecentChanges implements LifecycleHandler {

	// Value used in rc_source field of recentchanges to identify flow specific changes
	const SRC_FLOW = "flow";

	// Maximum length any user generated content is truncated to before storing
	// in recentchanges
	const TRUNCATE_LENGTH = 164;

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @var UserNameBatch
	 */
	protected $usernames;

	/**
	 * @var RecentChangeFactory
	 */
	protected $rcFactory;

	/**
	 * @param FlowActions $actions
	 * @param UserNameBatch $usernames
	 * @param RecentChangeFactory $rcFactory Creates mw RecentChange instances
	 */
	public function __construct( FlowActions $actions, UserNameBatch $usernames, RecentChangeFactory $rcFactory ) {
		$this->actions = $actions;
		$this->usernames = $usernames;
		$this->rcFactory = $rcFactory;
	}

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		// Moderation.  Doesn't need to log anything because all moderation also inserts
		// a new null revision to track who and when.
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		// Deletion. Not kinda-sorta deleted, like 100% GONE. Should never happen.
	}

	public function onAfterLoad( $object, array $row ) {
		// nothing to do
	}

	/**
	 * @param AbstractRevision $revision Revision object
	 * @param string $block The block object's ->getName()
	 * @param string $revisionType Classname of the Revision object
	 * @param array $row Revision row
	 * @param Workflow $workflow
	 * @param array $changes
	 */
	protected function insert( AbstractRevision $revision, $block, $revisionType, array $row, Workflow $workflow, array $changes ) {
		global $wgRCFeeds;

		$action = $revision->getChangeType();
		$revisionId = $revision->getRevisionId()->getAlphadecimal();
		$timestamp = $revision->getRevisionId()->getTimestamp();

		if ( !$this->isAllowed( $revision, $action ) ) {
			return;
		}

		$title = $this->getRcTitle( $workflow, $revision->getChangeType() );
		$attribs = array(
			'rc_namespace' => $title->getNamespace(),
			'rc_title' => $title->getDBkey(),
			'rc_user' => $row['rev_user_id'],
			'rc_user_text' => $this->usernames->get( wfWikiId(), $row['rev_user_id'], $row['rev_user_ip'] ),
			'rc_type' => RC_FLOW,
			'rc_source' => self::SRC_FLOW,
			'rc_minor' => 0,
			'rc_bot' => 0, // TODO: is revision by bot
			'rc_patrolled' => 0,
			'rc_old_len' => $revision->getPreviousContentLength(),
			'rc_new_len' => $revision->getContentLength(),
			'rc_this_oldid' => 0,
			'rc_last_oldid' => 0,
			'rc_log_type' => null,
			'rc_params' => serialize( array(
				'flow-workflow-change' => array(
					'action' => $action,
					'block' => $block,
					'revision_type' => $revisionType,
					'revision' => $revisionId,
					'workflow' => $workflow->getId()->getAlphadecimal(),
					'prev_revision' => $revision->isFirstRevision()
						? null
						: $revision->getPrevRevisionId()->getAlphadecimal()
				) + $changes,
			) ),
			'rc_cur_id' => 0,
			'rc_comment' => '',
			'rc_timestamp' => $timestamp,
			'rc_deleted' => 0,
		);

		$rc = $this->rcFactory->newFromRow( (object)$attribs );
		$rc->save( /* $noudp = */ true );  // Insert into db
		$feeds = $wgRCFeeds;
		// Override the IRC formatter with our own formatter
		foreach ( array_keys( $feeds ) as $name ) {
			$feeds[$name]['original_formatter'] = $feeds[$name]['formatter'];
			$feeds[$name]['formatter'] = Container::get( 'formatter.irclineurl' );
		}
		$rc->notifyRCFeeds( $feeds );
	}

	/**
	 * @param Workflow $workflow
	 * @param string $action
	 * @return \Title
	 */
	public function getRcTitle( Workflow $workflow, $action ) {
		if ( $this->actions->getValue( $action, 'rc_title' ) === 'owner' ) {
			return $workflow->getOwnerTitle();
		} else {
			return $workflow->getArticleTitle();
		}
	}

	/**
	 * @param AbstractRevision $revision
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed( AbstractRevision $revision, $action ) {
		$allowed = $this->actions->getValue( $action, 'rc_insert' );
		if ( $allowed instanceof Closure ) {
			$allowed = $allowed( $revision, $this );
		}

		return (bool) $allowed;
	}
}
