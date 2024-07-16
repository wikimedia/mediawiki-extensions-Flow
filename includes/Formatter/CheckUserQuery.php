<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use MediaWiki\MediaWikiServices;

class CheckUserQuery extends AbstractQuery {

	/**
	 * Revision data will be stored in cuc_comment & prefixed with this string
	 * so we can distinguish between different kinds of data in there, should we
	 * change that data format later.
	 */
	public const VERSION_PREFIX = 'v1';

	/**
	 * @param \stdClass[] $rows List of checkuser database rows
	 * @suppress PhanParamSignatureMismatch The signature doesn't match though
	 */
	public function loadMetadataBatch( $rows ) {
		$needed = [];

		$commentStore = MediaWikiServices::getInstance()->getCommentStore();

		foreach ( $rows as $row ) {
			if ( $row->cuc_type != RC_FLOW || !$commentStore->getComment( 'cuc_comment', $row )->text ) {
				continue;
			}

			$ids = self::extractActionAndIds( $row );
			if ( !$ids ) {
				continue;
			}

			/** @noinspection PhpUnusedLocalVariableInspection */
			[ $action, $workflowId, $revisionId ] = $ids;

			switch ( $action ) {
				case 'create-topic-summary':
				case 'edit-topic-summary':
					$needed['PostSummary'][] = $revisionId;
					break;
				case 'create-header':
				case 'edit-header':
					$needed['Header'][] = $revisionId;
					break;
				default:
					$needed['PostRevision'][] = $revisionId;
					break;
			}
		}

		$found = [];
		foreach ( $needed as $type => $uids ) {
			$found[] = $this->storage->getMulti( $type, $uids );
		}

		$count = count( $found );
		if ( $count === 0 ) {
			$results = [];
		} elseif ( $count === 1 ) {
			$results = reset( $found );
		} else {
			$results = array_merge( ...array_values( $found ) );
		}

		if ( $results ) {
			parent::loadMetadataBatch( $results );
		}
	}

	/**
	 * @param \StdClass $row
	 * @return FormatterRow|bool
	 * @throws FlowException
	 */
	public function getResult( $row ) {
		if ( $row->cuc_type != RC_FLOW ||
			!MediaWikiServices::getInstance()
				->getCommentStore()
				->getComment( 'cuc_comment', $row )->text
		) {
			return false;
		}

		$ids = self::extractActionAndIds( $row );
		if ( !$ids ) {
			return false;
		}

		// order of $ids is (workflowId, revisionId)
		$alpha = $ids[2]->getAlphadecimal();
		if ( !isset( $this->revisionCache[$alpha] ) ) {
			throw new FlowException( "Revision not found in revisionCache: $alpha" );
		}
		$revision = $this->revisionCache[$alpha];

		$res = new FormatterRow();
		$this->buildResult( $revision, 'cuc_timestamp', $res );

		return $res;
	}

	/**
	 * Extracts the workflow, revision & post ID (if any) from the CU's
	 * comment-column (cuc_comment), where they're stored in comma-separated
	 * format.
	 *
	 * @param \StdClass $row
	 * @return false|array{0:string,1:UUID,2:UUID} Array with workflow and revision id, or false on error
	 */
	protected function extractActionAndIds( $row ) {
		$comment = MediaWikiServices::getInstance()
			->getCommentStore()
			->getComment( 'cuc_comment', $row )->text;

		// anything not prefixed v1 is a pre-versioned check user comment
		// if it changes again the prefix can be updated.
		if ( !str_starts_with( $comment, self::VERSION_PREFIX ) ) {
			return false;
		}

		$data = explode( ',', $comment );
		if ( count( $data ) !== 4 ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Invalid number of ids received from cuc_comment.' .
				' Expected 4 but received ' . count( $data ) );
			return false;
		}

		[ , $action, $workflowId, $revisionId ] = $data;
		return [ $action, UUID::create( $workflowId ), UUID::create( $revisionId ) ];
	}
}
