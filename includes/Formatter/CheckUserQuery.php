<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use CheckUser;

class CheckUserQuery extends AbstractQuery {
	/**
	 * Revision data will be stored in cuc_comment & prefixed with this string
	 * so we can distinguish between different kinds of data in there, should we
	 * change that data format later.
	 *
	 * @var string
	 */
	const VERSION_PREFIX = 'v1';

	/**
	 * @param stdClass[] List of checkuser database rows
	 */
	public function loadMetadataBatch( $rows ) {
		$needed = array();

		foreach ( $rows as $row ) {
			if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
				continue;
			}

			$ids = self::extractIds( $row );
			if ( !$ids ) {
				continue;
			}

			list( $workflowId, $revisionId, $postId ) = $ids;

			/*
			 * We'll load all revisions based on their revision id. There could
			 * be revisions from multiple models, so figure out what the id
			 * actually belongs to.
			 * This isn't really the most robust way to identify a revision
			 * type, but it'll work for now.
			 */
			$revisionType = $postId ? 'PostRevision' : 'Header';
			$needed[$revisionType][] = $revisionId;
		}

		$found = array();
		foreach ( $needed as $type => $uids ) {
			$found[] = $this->storage->getMulti( $type, $uids );
		}

		$count = count( $found );
		if ( $count === 0 ) {
			$results = array();
		} elseif ( $count === 1 ) {
			$results = reset( $found );
		} else {
			$results = call_user_func_array( 'array_merge', $found );
		}

		if ( $results ) {
			parent::loadMetadataBatch( $results );
		}
	}

	/**
	 * @param CheckUser $checkUser
	 * @param \StdClass $row
	 * @param bool $isWatchlist
	 * @return CheckUserRow|null
	 * @throws FlowException
	 */
	public function getResult( CheckUser $checkUser, $row ) {
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
			return false;
		}

		$ids = self::extractIds( $row );
		if ( !$ids ) {
			return false;
		}

		list( $workflowId, $revisionId, $postId ) = $ids;

		$alpha = $revisionId->getAlphadecimal();
		if ( !isset( $this->revisionCache[$alpha] ) ) {
			throw new FlowException( "Revision not found in revisionCache: $alpha" );
		}
		$revision = $this->revisionCache[$alpha];

		$res = new CheckUserRow;
		$this->buildResult( $revision, 'cuc_timestamp', $res );
		$res->checkUser = $checkUser;

		return $res;
	}

	/**
	 * Extracts the workflow, revision & post ID (if any) from the CU's
	 * comment-column (cuc_comment), where they're stored in comma-separated
	 * format.
	 *
	 * @param \StdClass $row
	 * @return array|false Array with workflow, revision & post id (when
	 *  applicable), or false on error
	 */
	protected function extractIds( $row ) {
		$data = explode( ',', $row->cuc_comment );

		// anything not prefixed v1 is a pre-versioned check user comment
		// if it changes again the prefix can be updated.
		if ( strpos( $row->cuc_comment, self::VERSION_PREFIX ) !== 0 ) {
			return false;
		}

		// remove the version specifier
		array_shift( $data );

		$postId = null;
		switch ( count( $data ) ) {
			/** @noinspection PhpMissingBreakStatementInspection */
			case 4:
				$postId = UUID::create( $data[3] );
			// fall-through to 3 parameter case
			case 3:
				$revisionId = UUID::create( $data[2] );
				$workflowId = UUID::create( $data[1] );
				$action = $data[0];
				break;
			default:
				wfDebugLog( 'Flow', __METHOD__ . ': Invalid number of parameters received from cuc_comment.  Expected 2 or 3 but received ' . count( $data ) );
				return false;
		}

		return array( $workflowId, $revisionId, $postId );
	}
}

class CheckUserRow extends FormatterRow {
	/**
	 * @var CheckUser
	 */
	public $checkUser;
}
