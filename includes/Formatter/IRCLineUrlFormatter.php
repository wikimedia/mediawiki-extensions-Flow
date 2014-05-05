<?php

namespace Flow\Formatter;

use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use RecentChange;

/**
 * Generates URL's to be inserted into the IRC
 * recent changes feed.
 */
class IRCLineUrlFormatter extends AbstractFormatter {

	/**
	 * @param RecentChange $rc
	 * @return string|null
	 */
	public function format( RecentChange $rc ) {

		$encoded = $rc->getAttribute( 'rc_params' );
		if ( !$encoded ) {
			wfDebugLog( 'Flow', __METHOD__ . 'Missing rc_params' );
			return null;
		}
		$params = unserialize( $encoded );
		if ( !isset( $params['flow-workflow-change'] ) ) {
			wfDebugLog( 'Flow', __METHOD__ . 'Missing flow-workflow-change' );
			return null;
		}
		$change = $params['flow-workflow-change'];

		// The rc has all data necessary to render the links
		// we need, fetching the data models would be more
		// work than necessary. We suppress/restore the warnings
		// since we arn't creating the real full data models
		$row = new FormatterRow;
		wfSuppressWarnings();
		$row->revision = $this->mockRevision( $rc, $change );
		$row->currentRevision = $row->revision;
		$row->workflow = $this->mockWorkflow( $rc, $change );
		wfRestoreWarnings();
		$links = $this->serializer->buildLinks( $row );

		// Listed in order of preference
		$accept = array(
			'diff',
			'post-history', 'topic-history', 'board-history',
			'post', 'topic',
			'workflow'
		);

		foreach ( $accept as $key ) {
			if ( isset( $links[$key] ) ) {
<<<<<<< HEAD   (b68c36 Avoid Firefox errors in mw-ui.enhance)
				return $links[$key]['url'];
=======
				return $links[$key]->getFullUrl();
>>>>>>> BRANCH (3ce681 Merge "API: Use a standard edit token")
			}
		}

		wfDebugLog( 'Flow', __METHOD__
				. ': No url generated for action ' . $change['action']
				. ' on revision ' . $change['revision']
		);
		return null;
	}

	protected function mockRevision( RecentChange $rc, array $change ) {
		// the exact revision type doesn't currently matter
		// but when it does
		switch( $change['revision_type'] ) {
		case 'PostRevision':
			$class = 'Flow\\Model\\PostRevision';
			$row = array(
				'rev_type_id' => $change['post'],
				'tree_rev_id' => $change['revision'],
			);
			break;

		case 'Header':
			$class = 'Flow\\Model\\Header';
			$row = array( 'rev_type_id' => $change['workflow'] );
			break;

		case 'PostSummary':
			$class = 'Flow\\Model\\PostSummary';
			$row = array( 'rev_type_id' => $change['rev_type_id'] );
			break;

		default:
			throw new FlowException( 'Unknown revision type in recent change row ' . $rc->getAttribute( 'rc_id' ) );
		}

		return $class::fromStorageRow( $row + array(
			'rev_id' => $change['revision'],
			'rev_change_type' => $change['action'],
			'rev_parent_id' => $change['prev_revision'],
		) );
	}

	protected function mockWorkflow( RecentChange $rc, array $change ) {
		return Workflow::fromStorageRow( array(
			'workflow_id' => $change['workflow'],
			'workflow_title_text' => $rc->getAttribute( 'rc_title' ),
			'workflow_namespace' => $rc->getAttribute( 'rc_namespace' ),
			'workflow_wiki' => wfWikiId(),
		) );
	}
}
