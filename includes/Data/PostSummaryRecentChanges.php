<?php

namespace Flow\Data;

use Flow\FlowActions;
use Flow\Model\PostSummary;
use Language;

class PostSummaryRecentChanges extends RecentChanges {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var Language Content Language
	 */
	protected $contLang;

	public function __construct( FlowActions $actions, UserNameBatch $usernames, ManagerGroup $storage, Language $contLang ) {
		parent::__construct( $actions, $usernames );
		$this->storage = $storage;
		$this->contLang = $contLang;
	}

	/**
	 * @param PostSummary $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row ) {
		$workflowId = $object->getCollection()->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			// unless in unit test, write to log
			wfDebugLog( 'Flow', __METHOD__ . ": could not locate workflow for post summary " . $object->getRevisionId()->getAlphadecimal() );
			return;
		}

		$this->insert(
			$object,
			'topicsummary',
			'PostSummary',
			$row,
			$workflow,
			array(
				'content' => $this->contLang->truncate( $object->getContent(), self::TRUNCATE_LENGTH ),
			)
		);
	}
}
