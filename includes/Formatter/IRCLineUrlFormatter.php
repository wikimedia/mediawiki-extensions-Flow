<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Model\Workflow;
use RCFeedFormatter;
use RecentChange;

/**
 * Generates URL's to be inserted into the IRC
 * recent changes feed.
 */
class IRCLineUrlFormatter extends AbstractFormatter implements RCFeedFormatter {

	/**
	 * Allows us to set the rc_comment field
	 */
	public function getLine( array $feed, RecentChange $rc, $actionComment ) {
		/** @var RecentChangesQuery $query */
		$query = Container::get( 'query.recentchanges' );
		//throw new \Exception($rc->mAttribs['rc_params']);
		$query->loadMetadataBatch( array( (object)$rc->mAttribs ) );
		$rcRow = $query->getResult( null, $rc );
		$ctx = \RequestContext::getMain();
		$data = $this->serializer->formatApi( $rcRow, $ctx );
		$rc->mAttribs['rc_comment'] = $this->formatDescription( $data );
		/** @var RCFeedFormatter $formatter */
		$formatter = new $feed['original_formatter']();
		return $formatter->getLine( $feed, $rc, $actionComment );
	}

	/**
	 * Generate a plaintext revision description suitable for IRC consumption
	 *
	 * @param array $data
	 * @return string
	 */
	protected function formatDescription( array $data ) {
		// Build description message, piggybacking on history i18n
		$changeType = $data['changeType'];
		$actions = $this->permissions->getActions();
		$key = $actions->getValue( $changeType, 'history', 'i18n-message' );
		$msg = wfMessage( $key . '-irc' );
		if ( !$msg->exists() ) {
			// Some messages are already suitable for IRC consumption, so they don't
			// have -irc copies
			$msg = wfMessage( $key );
		}
		$source = $actions->getValue( $changeType, 'history', 'i18n-params' );

		$params = array();
		foreach ( $source as $param ) {
			if ( isset( $data['properties'][$param] ) ) {
				$params[] = $data['properties'][$param];
			} else {
				wfDebugLog( 'Flow', __METHOD__ . ": Missing expected parameter $param for change type $changeType" );
				$params[] = '';
			}
		}

		return $msg->params( $params )->inLanguage( 'en' )->text();

	}


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
		$links = $this->serializer->buildActionLinks( $row );

		// Listed in order of preference
		$accept = array(
			'diff',
			'post-history', 'topic-history', 'board-history',
			'post', 'topic',
			'workflow'
		);

		foreach ( $accept as $key ) {
			if ( isset( $links[$key] ) ) {
				return $links[$key]->getCanonicalURL();
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
