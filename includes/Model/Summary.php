<?php

namespace Flow\Model;

use Flow\Collection\PostSummaryCollection;
use Flow\Model\PostRevision;
use User;

/**
 * We share uuid for different entities in some cases. If both entities happen
 * to have a summary, it's not easy to distinguish them with the same rev_type
 */
abstract class AbstractSummary extends AbstractRevision {

	/**
	 * The id of the entity to be summarized
	 * @var UUID
	 */
	protected $summaryTargetId;

	static public function fromStorageRow( array $row, $obj = null ) {
		$obj = parent::fromStorageRow( $row, $obj );
		$obj->summaryTargetId = UUID::create( $row['rev_type_id'] );
		return $obj;
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

}

class PostSummary extends AbstractSummary {

	/**
	 * @param PostRevision $post
	 * @param User $user
	 * @param string $content
	 * @param string $changeType
	 */
	static public function create( PostRevision $post, User $user, $content, $changeType ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->userId = $user->getId();
		if ( !$obj->userId ) {
			$obj->userIp = $user->getName();
		}
		$obj->userWiki = wfWikiId();
		$obj->prevRevision = null;
		$obj->changeType = $changeType;
		$obj->summaryTargetId = $post->getPostId();
		$obj->setContent( $content );
		return $obj;
	}

	/**
	 * @return string
	 */
	public function getRevisionType() {
		return 'post-summary';
	}

	/**
	 * @return PostSummaryCollection
	 */
	public function getCollection() {
		return PostSummaryCollection::newFromRevision( $this );
	}

}
