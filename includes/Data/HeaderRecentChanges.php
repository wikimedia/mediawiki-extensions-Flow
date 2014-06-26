<?php

namespace Flow\Data;

use Flow\FlowActions;
use Flow\Model\Header;
use Flow\Parsoid\Utils;
use Language;

class HeaderRecentChanges extends RecentChanges {
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
	 * @param Header $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row ) {
		$workflowId = $object->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			// unless in unit test, write to log
			wfDebugLog( 'Flow', __METHOD__ . ": could not locate workflow for header " . $object->getRevisionId()->getAlphadecimal() );
			return;
		}

		$this->insert(
			$object,
			'header',
			'Header',
			$row,
			$workflow,
			array(
				'content' => Utils::htmlToPlaintext(
					$object->getContent(),
					self::TRUNCATE_LENGTH,
					$this->contLang
				),
			)
		);
	}
}
