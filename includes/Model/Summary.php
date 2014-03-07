<?php

namespace Flow\Model;

use User;

class Summary extends AbstractRevision {

	/**
	 * The id of the entity to be summarized
	 * @var UUID
	 */
	protected $summaryId;

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
		$obj->summaryId = $entity->getId();
		return $obj;
	}

	static public function fromStorageRow( array $row, $obj = null ) {
		$obj = parent::fromStorageRow( $row, $obj );
		$obj->summaryId = UUID::create( $row['rev_type_id'] );
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return parent::toStorageRow( $obj ) + array(
			'rev_type' => 'summary',
			'rev_type_id' => $obj->summaryId->getBinary(),
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
	public function getSummaryId() {
		return $this->summaryId;
	}

	/**
	 * @return UUID
	 */
	public function getCollectionId() {
		return $this->getSummaryId();
	}

	/**
	 * @return HeaderCollection
	 */
	public function getCollection() {
		return SummaryCollection::newFromRevision( $this );
	}

}
