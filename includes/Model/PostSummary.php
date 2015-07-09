<?php

namespace Flow\Model;

use Flow\Collection\PostSummaryCollection;
use Title;
use User;

class PostSummary extends AbstractSummary {

	/**
	 * @var UserTuple
	 */
	protected $creator;

	/**
	 * @param Title $title
	 * @param PostRevision $post
	 * @param User $user
	 * @param string $content
	 * @param string $format wikitext|html
	 * @param string $changeType
	 * @return PostSummary
	 */
	static public function create( Title $title, PostRevision $post, User $user, $content, $format, $changeType ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->user = UserTuple::newFromUser( $user );
		$obj->prevRevision = null;
		$obj->changeType = $changeType;
		$obj->summaryTargetId = $post->getPostId();
		$obj->setContent( $content, $format, $title );
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

	/**
	 * @return UserTuple
	 */
	public function getCreatorTuple() {
		if ( !$this->creator ) {
			if ( $this->isFirstRevision() ) {
				$this->creator = $this->user;
			} else {
				$this->creator = $this->getCollection()->getFirstRevision()->getUserTuple();
			}
		}

		return $this->creator;
	}

	/**
	 * Get the user ID of the user who created this summary.
	 *
	 * @return integer The user ID
	 */
	public function getCreatorId() {
		return $this->getCreatorTuple()->id;
	}

	/**
	 * @return string
	 */
	public function getCreatorWiki() {
		return $this->getCreatorTuple()->wiki;
	}

	/**
	 * Get the user ip of the user who created this summary if it
	 * was created by an anonymous user
	 *
	 * @return string|null String if an creator is anon, or null if not.
	 */
	public function getCreatorIp() {
		return $this->getCreatorTuple()->ip;
	}
}
