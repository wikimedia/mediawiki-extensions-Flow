<?php

namespace Flow\Model;

use Flow\Collection\HeaderCollection;
use User;

/**
 * @Todo - Header is just a summary to the discussion workflow, it could be just
 * migrated to Summary revision with rev_change_type: create-header-summary,
 * edit-header-summary
 */
class Header extends AbstractRevision {

	/**
	 * @var UUID
	 */
	protected $workflowId;

	/**
	 * @param Workflow $workflow
	 * @param User $user
	 * @param string $content
	 * @param string[optional] $changeType
	 * @return Header
	 */
	static public function create( Workflow $workflow, User $user, $content, $changeType = 'create-header' ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->workflowId = $workflow->getId();
		list( $obj->userId, $obj->userIp, $obj->userWiki ) = self::userFields( $user );
		$obj->prevRevision = null; // no prior revision
		$obj->setContent( $content, $workflow->getArticleTitle() );
		$obj->changeType = $changeType;
		return $obj;
	}

	/**
	 * @param string[] $row
	 * @param Header|null $obj
	 * @return Header
	 */
	static public function fromStorageRow( array $row, $obj = null ) {
		/** @var $obj Header */
		$obj = parent::fromStorageRow( $row, $obj );
		$obj->workflowId = UUID::create( $row['rev_type_id'] );
		return $obj;
	}

	/**
	 * @return string
	 */
	public function getRevisionType() {
		return 'header';
	}

	/**
	 * @return UUID
	 */
	public function getWorkflowId() {
		return $this->workflowId;
	}

	/**
	 * @return UUID
	 */
	public function getCollectionId() {
		return $this->getWorkflowId();
	}

	/**
	 * @return HeaderCollection
	 */
	public function getCollection() {
		return HeaderCollection::newFromRevision( $this );
	}

	public function getObjectId() {
		return $this->getWorkflowId();
	}
}
