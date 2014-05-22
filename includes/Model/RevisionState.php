<?php

namespace Flow\Model;

use Flow\Exception\DataModelException;
use User;

/**
 * Model class mapping to a revision state row
 */
class RevisionState {

	/**
	 * @var UUID revision id
	 */
	protected $revId;

	/**
	 * @var string Revision state
	 */
	protected $state;

	/**
	 * @var int|null User id setting the revision state
	 */
	protected $userId;

	/**
	 * @var string|null User ip setting the revision state
	 */
	protected $userIp;

	/**
	 * @var string User wiki setting the revision state
	 */
	protected $userWiki;

	/**
	 * @var string Comment for setting the revision state
	 */
	protected $comment;

	/**
	 * Create a RevisionState object
	 *
	 * @param User
	 * @param string
	 * @param string
	 * @return RevisionState
	 */
	public function create( User $user, $state, $comment = '' ) {
		$obj = new self();
		$obj->revId = UUID::create();
		list( $obj->userId, $obj->userIp, $obj->userWiki ) = AbstractRevision::userFields( $user );
		$obj->comment = $comment;
		return $obj;
	}

	/**
	 * @param array
	 * @param RevisionState|null
	 * @return RevisionState
	 * @throws DataModelException
	 */
	public static function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new self;
		} elseif ( !$obj instanceof self ) {
			throw new DataModelException( 'Wrong obj type: ' . get_class( $obj ), 'process-data' );
		}
		$obj->revId = UUID::create( $row['frs_rev_id'] );
		$obj->state = $row['frs_state'];
		$obj->userId = $row['frs_user_id'];
		$obj->userIp = $row['frs_user_ip'];
		$obj->userWiki = $row['frs_user_wiki'];
		$obj->comment = $row['frs_comment'];
		return $obj;
	}

	/**
	 * @param RevisionState
	 * @return array
	 */
	public static function toStorageRow( RevisionState $obj ) {
		return array(
			'frs_rev_id' => $obj->revId->getBinary(),
			'frs_state' => $obj->state,
			'frs_user_id' => $obj->userId,
			'frs_user_ip' => $obj->userIp,
			'frs_user_wiki' => $obj->userWiki,
			'frs_comment' => $obj->comment,
		);
	}

	/**
	 * @return UUID
	 */
	public function getRevId() {
		return $this->revId;
	}

	/**
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getUserIp() {
		return $this->userIp;
	}

	/**
	 * @return string
	 */
	public function getUserWiki() {
		return $this->userWiki;
	}

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

}
