<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;

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

		foreach ( $rows as $row ) {
			if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
				continue;
			}

			$ids = self::extractActionAndIds( $row );
			if ( !$ids ) {
				continue;
			}

			/** @noinspection PhpUnusedLocalVariableInspection */
			list( $action, $workflowId, $revisionId ) = $ids;

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
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
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
		$data = explode( ',', $row->cuc_comment );

		// anything not prefixed v1 is a pre-versioned check user comment
		// if it changes again the prefix can be updated.
		if ( strpos( $row->cuc_comment, self::VERSION_PREFIX ) !== 0 ) {
			return false;
		}

		// remove the version specifier
		array_shift( $data );

		$action = array_shift( $data );
		$revisionId = null;
		$workflowId = null;
		switch ( count( $data ) ) {
			case 2:
				$revisionId = UUID::create( $data[1] );
				$workflowId = UUID::create( $data[0] );
				break;
			default:
				wfDebugLog( 'Flow', __METHOD__ . ': Invalid number of ids received from cuc_comment.' .
					' Expected 2 but received ' . count( $data ) );
				return false;
		}

		return [ $action, $workflowId, $revisionId ];
	}
}
