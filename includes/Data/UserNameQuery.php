<?php
/**
 * Provide usernames filtered by per-wiki ipblocks. Batches together
 * database requests for multiple usernames when possible.
 */
namespace Flow\Data;

Interface UserNameQuery {
	/**
	 * @param string $wiki wiki id
	 * @param array $userIds List of user ids to lookup
	 * @return \ResultWrapper|bool Containing objects with user_id and
	 *   user_name properies.
	 */
	function execute( $wiki, array $userIds );
}
