<?php

namespace Flow\Data;

use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\PostRevision;
use Language;

class PostRevisionRecentChanges extends RecentChanges {
	/**
	 * @var Language
	 */
	protected $contLang;

	public function __construct( FlowActions $actions, UserNameBatch $usernames, Language $contLang ) {
		parent::__construct( $actions, $usernames );
		$this->contLang = $contLang;
	}

	/**
	 * @param PostRevision $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row, array $metadata ) {
		if ( !isset( $metadata['workflow'] ) ) {
			throw new FlowException( 'Missing required metadata: workflow' );
		}
		if ( !isset( $metadata['topic-title'] ) ) {
			throw new FlowException( 'Missing required metadata: topic-title' );
		}

		$topic = $this->contLang->truncate(
			$metadata['topic-title']->getContent( 'wikitext' ),
			self::TRUNCATE_LENGTH
		);

		$this->insert(
			$object,
			'topic',
			'PostRevision',
			$row,
			$metadata['workflow'],
			array(
				'post' => $object->getPostId()->getAlphadecimal(),
				'topic' => $topic,
			)
		);
	}
}
