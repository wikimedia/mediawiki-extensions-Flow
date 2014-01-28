<?php

namespace Flow\Model;

use Flow\Collection\HeaderCollection;
use User;

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
		$obj->userId = $user->getId();
		if ( !$user->getId() ) {
			$obj->userIp = $user->getName();
		}
		$obj->userWiki = wfWikiId();
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
		$obj->workflowId = UUID::create( $row['header_workflow_id'] );
		return $obj;
	}

	/**
	 * @param Header $obj
	 * @return string[]
	 */
	static public function toStorageRow( $obj ) {
		return parent::toStorageRow( $obj ) + array(
			'header_rev_id' => $obj->revId->getBinary(),
			'header_workflow_id' => $obj->workflowId->getBinary(),
		);
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
