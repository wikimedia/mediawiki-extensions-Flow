<?php

namespace Flow\Data;

use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\Header;
use Flow\Parsoid\Utils;
use Language;

class HeaderRecentChanges extends RecentChanges {
	/**
	 * @var Language Content Language
	 */
	protected $contLang;

	public function __construct( FlowActions $actions, UserNameBatch $usernames, Language $contLang ) {
		parent::__construct( $actions, $usernames );
		$this->contLang = $contLang;
	}

	/**
	 * @param Header $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row, array $metadata ) {
		$workflowId = $object->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !isset( $metadata['workflow'] ) ) {
			throw new FlowException( 'Missing required metadata: workflow' );
		}

		$this->insert(
			$object,
			'header',
			'Header',
			$row,
			$metadata['workflow'],
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
