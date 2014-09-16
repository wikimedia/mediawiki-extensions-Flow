<?php

namespace Flow\Model;

use Flow\Collection\PostSummaryCollection;
use User;

class PostSummary extends AbstractSummary {

	/**
	 * @param PostRevision $post
	 * @param User $user
	 * @param string $content
	 * @param string $changeType
	 * @return PostSummary
	 */
	static public function create( PostRevision $post, User $user, $content, $changeType ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->user = UserTuple::newFromUser( $user );
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
