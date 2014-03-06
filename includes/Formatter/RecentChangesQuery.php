<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
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

	public function loadMetadataBatch( $rows ) {
		$needed = array();
		foreach ( $rows as $row ) {
			if ( !isset( $row->rc_params ) ) {
				wfDebugLog( __CLASS__, __FUNCTION__ . ': Bad row without rc_params passed in $rows' );
				continue;
			}
			$params = unserialize( $row->rc_params );
			$changeData = $params['flow-workflow-change'];
			/**
			 * Check to make sure revision_type exists, this is to make sure corrupted
			 * flow recent change data doesn't throw error on the page.
			 * See bug 59106 for more detail
			 */
			if ( !isset( $changeData['revision_type'] ) ) {
				continue;
			}
			$revisionType = $changeData['revision_type'];
			$needed[$revisionType][] = UUID::create( $changeData['revision'] );
		}

		$found = array();
		$requested = $received = 0;
		foreach ( $needed as $type => $uids ) {
			$requested += count( $uids );
			$found[] = $this->storage->getMulti( $type, $uids );
			$received += count( end( $found ) );
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
	 * @param ChangesList $cl
	 * @param RecentChange $rc
	 * @param bool $watchlist
	 * @return RecentChangesRow|null
	 * @throws FlowException
	 */
	public function getResult( ChangesList $cl, RecentChange $rc, $watchlist = false ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
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

		$alpha = UUID::create( $changeData['revision'] )->getAlphadecimal();
		if ( !isset( $this->revisionCache[$alpha] ) ) {
			throw new FlowException( "Revision not found in revisionCache: $alpha" );
		}
		$revision = $this->revisionCache[$alpha];

		// Only show most recent items for watchlist
		// @todo figure out if we can not load hidden records in
		//  loadMetadataBatch
		if ( $watchlist && $this->hideRecord( $revision, $changeData ) ) {
			return false;
		}

		$res = new RecentChangesRow;
		$this->buildResult( $revision, 'timestamp', $res );
		$res->recentChange = $rc;

		return $res;
	}

	/**
	 * Determines if a flow record should be displayed in Special:Watchlist
	 */
	protected function hideRecord( AbstractRevision $revision, array $changeData ) {
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
			case 'edit-header':
				if ( isset( $this->displayStatus['header-' . $changeData['workflow']] ) ) {
					return true;
				}
				$this->displayStatus['header-' . $changeData['workflow']] = true;
			break;

			case 'new-post':
			case 'reply':
			case 'edit-post':
			case 'edit-title':
				if ( !$revision instanceof PostRevision ) {
					throw new FlowException( 'Expected PostRevision but received ' . get_class( $revision ) );
				}
				// A new topic
				// @todo resolve these from a recentchanges row directly so we can
				// filter before loading the metadata.
				if ( $revision->isTopicTitle() && $revision->isFirstRevision() ) {
					return false;
				}
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
