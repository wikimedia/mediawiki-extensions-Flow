<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Data\RecentChanges;
use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use ChangesList;
use RecentChange;

class RecentChangesQuery extends AbstractQuery {

	/**
	 * Check if the most recent action for an entity has been displayed already
	 *
	 * @var array
	 */
	protected $displayStatus = array();

	/**
	 * @var FlowActions
	 */
	protected $actions;

	public function __construct( ManagerGroup $storage, TreeRepository $treeRepo, FlowActions $actions ) {
		parent::__construct( $storage, $treeRepo );
		$this->actions = $actions;
	}

	/**
	 * @param stdClass[] List of recentchange database rows
	 * @param bool $isWatchlist
	 */
	public function loadMetadataBatch( $rows, $isWatchlist = false ) {
		$needed = array();
		foreach ( $rows as $row ) {
			if ( !isset( $row->rc_source ) || $row->rc_source !== RecentChanges::SRC_FLOW ) {
				continue;
			}
			if ( !isset( $row->rc_params ) ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Bad row without rc_params passed in $rows' );
				continue;
			}
			$params = unserialize( $row->rc_params );
			if ( !$params ) {
				wfDebugLog( 'Flow', __METHOD__ . ": rc_params does not contain serialized content: {$row->rc_params}" );
				continue;
			}
			$changeData = $params['flow-workflow-change'];
			/**
			 * Check to make sure revision_type exists, this is to make sure corrupted
			 * flow recent change data doesn't throw error on the page.
			 * See bug 59106 for more detail
			 */
			if ( !isset( $changeData['revision_type'] ) ) {
				continue;
			}
			if ( $isWatchlist && $this->isRecordHidden( $changeData ) ) {
				continue;
			}
			$revisionType = $changeData['revision_type'];
			$needed[$revisionType][] = UUID::create( $changeData['revision'] );
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
	 * @param null $cl No longer used
	 * @param RecentChange $rc
	 * @param bool $isWatchlist
	 * @return RecentChangesRow|null
	 * @throws FlowException
	 */
	public function getResult( $cl, RecentChange $rc, $isWatchlist = false ) {
		$rcParams = $rc->getAttribute( 'rc_params' );
		$params = unserialize( $rcParams );
		if ( !$params ) {
			throw new FlowException( 'rc_params does not contain serialized content: ' . $rcParams );
		}
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			throw new FlowException( 'Flow data missing in recent changes.' );
		}

		/**
		 * Check to make sure revision_type exists, this is to make sure corrupted
		 * flow recent change data doesn't throw error on the page.
		 * See bug 59106 for more detail
		 */
		if ( !isset( $changeData['revision_type'] ) ) {
			throw new FlowException( 'Corrupted rc without changeData: ' . $rc->getAttribute( 'rc_id' ) );
		}

		// Only show most recent items for watchlist
		if ( $isWatchlist && $this->isRecordHidden( $changeData ) ) {
			return false;
		}

		$alpha = UUID::create( $changeData['revision'] )->getAlphadecimal();
		if ( !isset( $this->revisionCache[$alpha] ) ) {
			throw new FlowException( "Revision not found in revisionCache: $alpha" );
		}
		$revision = $this->revisionCache[$alpha];

		$res = new RecentChangesRow;
		$this->buildResult( $revision, 'timestamp', $res );
		$res->recentChange = $rc;

		return $res;
	}

	/**
	 * Determines if a flow record should be displayed in Special:Watchlist
	 */
	protected function isRecordHidden( array $changeData ) {
		// Check for legacy action names and convert it
		$alias = $this->actions->getValue( $changeData['action'] );
		if ( is_string( $alias ) ) {
			$action = $alias;
		} else {
			$action = $changeData['action'];
		}
		// * Display the most recent new post, edit post, edit title for a topic
		// * Display the most recent header edit
		// * Display all new topic and moderation actions
		switch ( $action ) {
			case 'create-header':
			case 'edit-header':
				if ( isset( $this->displayStatus['header-' . $changeData['workflow']] ) ) {
					return true;
				}
				$this->displayStatus['header-' . $changeData['workflow']] = true;
			break;

			case 'hide-post':
			case 'hide-topic':
			case 'delete-post':
			case 'delete-topic':
			case 'suppress-post':
			case 'suppress-topic':
			case 'restore-post':
			case 'restore-topic':
			case 'close-topic':
				// moderation actions are always shown when visible to the user
				return false;

			case 'new-post':
			case 'reply':
			case 'edit-post':
			case 'edit-title':
			case 'create-topic-summary':
			case 'edit-topic-summary':
				if ( isset( $this->displayStatus['topic-' . $changeData['workflow']] ) ) {
					return true;
				}
				$this->displayStatus['topic-' . $changeData['workflow']] = true;
			break;
		}

		return false;
	}

	// @todo:
	// This is a temporary fix.
	// We will add a param to core hook to determine if this is watchlist page
	// Or add a method to the ChangesList to test for its $watchlist property
	public function isWatchList( array $classes ) {
		foreach ( $classes as $class ) {
			if ( substr( $class, 0, 10 ) === 'watchlist-' ) {
				return true;
			}
		}
		return false;
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}
}

class RecentChangesRow extends FormatterRow {
	/**
	 * @var RecentChange
	 */
	public $recentChange;
}
