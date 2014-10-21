<?php

namespace Flow\Repository\UserName;

use Flow\DbFactory;

/**
 * Provide usernames filtered by per-wiki ipblocks. Batches together
 * database requests for multiple usernames when possible.
 */
class OneStepUserNameQuery implements UserNameQuery {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @param DbFactory $dbFactory
	 */
	public function __construct( DbFactory $dbFactory ) {
		$this->dbFactory = $dbFactory;
	}

	/**
	 * Look up usernames while respecting ipblocks with one query.
	 * Unused, check to see if this is reasonable to use.
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return \ResultWrapper|null
	 */
	public function execute( $wiki, array $userIds ) {
		$dbr = $this->dbFactory->getWikiDb( DB_SLAVE, array(), $wiki );
		return $dbr->select(
			/* table */ array( 'user', 'ipblocks' ),
			/* select */ array( 'user_id', 'user_name' ),
			/* conds */ array(
				'user_id' => $userIds,
				// only accept records that did not match ipblocks
				'ipb_deleted is null'
			),
			__METHOD__,
			/* options */ array(),
			/* join_conds */ array(
				'ipblocks' => array( 'LEFT OUTER', array(
					'ipb_user' => 'user_id',
					// match only deleted users
					'ipb_deleted' => 1,
				) )
			)
		);
	}
}
