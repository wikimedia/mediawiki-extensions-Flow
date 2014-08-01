<?php

namespace Flow\Data;

use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\PostSummary;
use Language;

/**
 * Create mw recentchange rows for PostSummary instances
 */
class PostSummaryRecentChanges extends RecentChanges {
	/**
	 * @var Language Content Language
	 */
	protected $contLang;

	public function __construct( FlowActions $actions, UserNameBatch $usernames, Language $contLang ) {
		parent::__construct( $actions, $usernames );
		$this->contLang = $contLang;
	}

	/**
	 * @param PostSummary $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row, array $metadata ) {
		if ( !isset( $metadata['workflow'] ) ) {
			throw new FlowException( 'Missing required metadata: workflow' );
		}

		$this->insert(
			$object,
			'topicsummary',
			'PostSummary',
			$row,
			$metadata['workflow'],
			array(
				'content' => $this->contLang->truncate( $object->getContent(), self::TRUNCATE_LENGTH ),
				'rev_type_id' => $object->getCollectionId()->getAlphadecimal()
			)
		);
	}
}
