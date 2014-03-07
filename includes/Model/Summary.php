<?php

namespace Flow\Model;

use Flow\Collection\SummaryCollection;
use User;

class Summary extends AbstractRevision {

	/**
	 * The id of the entity to be summarized
	 * @var UUID
	 */
	protected $summaryTargetId;

	/**
	 * @param Summarizable $entity
	 * @param User $user
	 * @param string $content
	 * @param string $changeType
	 */
	static public function create( Summarizable $entity, User $user, $content, $changeType ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->userId = $user->getId();
		if ( !$obj->userId ) {
			$obj->userIp = $user->getName();
		}
		$obj->prevRevision = null;
		$obj->setContent( $content );
		$obj->changeType = $changeType;
		$obj->summaryTargetId = $entity->getId();
		return $obj;
	}

	static public function fromStorageRow( array $row, $obj = null ) {
		$obj = parent::fromStorageRow( $row, $obj );
		$obj->summaryTargetId = UUID::create( $row['rev_type_id'] );
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return parent::toStorageRow( $obj ) + array(
			'rev_type' => 'summary',
			'rev_type_id' => $obj->summaryTargetId->getBinary(),
		);
	}

	/**
	 * @return string
	 */
	public function getRevisionType() {
		return 'summary';
	}

	/**
	 * @return UUID
	 */
	public function getSummaryTargetId() {
		return $this->summaryTargetId;
	}

	/**
	 * @return UUID
	 */
	public function getCollectionId() {
		return $this->getSummaryTargetId();
	}

	/**
	 * @return HeaderCollection
	 */
	public function getCollection() {
		return SummaryCollection::newFromRevision( $this );
	}

}
