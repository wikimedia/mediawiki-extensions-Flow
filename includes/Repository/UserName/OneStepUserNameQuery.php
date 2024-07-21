<?php

namespace Flow\Repository\UserName;

use Flow\DbFactory;
use MediaWiki\Block\HideUserUtils;
use Wikimedia\Rdbms\IResultWrapper;

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
	 * @var HideUserUtils
	 */
	protected $hideUserUtils;

	public function __construct( DbFactory $dbFactory, HideUserUtils $hideUserUtils ) {
		$this->dbFactory = $dbFactory;
		$this->hideUserUtils = $hideUserUtils;
	}

	/**
	 * Look up usernames while respecting ipblocks with one query.
	 * Unused, check to see if this is reasonable to use.
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return IResultWrapper|null
	 */
	public function execute( $wiki, array $userIds ) {
		$dbr = $this->dbFactory->getWikiDB( DB_REPLICA, $wiki );
		return $dbr->newSelectQueryBuilder()
			->select( [ 'user_id', 'user_name' ] )
			->from( 'user' )
			->where( [ 'user_id' => $userIds ] )
			->andWhere( $this->hideUserUtils->getExpression( $dbr ) )
			->caller( __METHOD__ )
			->fetchResultSet();
	}
}
