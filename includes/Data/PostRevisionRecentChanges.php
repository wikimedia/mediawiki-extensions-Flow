<?php

namespace Flow\Data;

use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\Repository\TreeRepository;
use Language;

class PostRevisionRecentChanges extends RecentChanges {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var TreeRepository
	 */
	protected $tree;

	/**
	 * @var Language
	 */
	protected $contLang;

	public function __construct( FlowActions $actions, UserNameBatch $usernames, ManagerGroup $storage, TreeRepository $tree, Language $contLang ) {
		parent::__construct( $actions, $usernames );
		$this->storage = $storage;
		$this->tree = $tree;
		$this->contLang = $contLang;
	}

	/**
	 * @param PostRevision $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row ) {
		// The workflow id is the same as the root's post id
		$workflowId = $object->getRootPost()->getPostId();
		// These are likely already in the in-process cache
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			// unless in unit test, write to log
			wfDebugLog( 'Flow', __METHOD__ . ": could not locate workflow " . $workflowId->getAlphadecimal() );
			return;
		}

		$this->insert(
			$object,
			'topic',
			'PostRevision',
			$row,
			$workflow,
			array(
				'post' => $object->getPostId()->getAlphadecimal(),
				'topic' => $this->getTopicTitle( $object ),
			)
		);
	}

	protected function getTopicTitle( PostRevision $rev ) {
		$content = $rev->getRootPost()->getContent( 'wikitext' );
		if ( is_object( $content ) ) {
			// moderated
			return null;
		}

		return $this->contLang->truncate( $content, self::TRUNCATE_LENGTH );
	}
}
