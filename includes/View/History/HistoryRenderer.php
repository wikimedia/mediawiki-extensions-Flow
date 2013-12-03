<?php

namespace Flow\View\History;

use Flow\Block\Block;
use Flow\Templating;
use MWTimestamp;

/**
 * HistoryRenderer will use Templating to render a given list of History.
 */
class HistoryRenderer {
	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * @var Block
	 */
	protected $block;

	/**
	 * @param History $history
	 * @param Templating $templating
	 * @param Block $block
	 */
	public function __construct( Templating $templating, Block $block ) {
		$this->templating = $templating;
		$this->block = $block;
	}

	/**
	 * Returns timespan data for a specific history.
	 *
	 * It will return an associative array with a string defining the period as
	 * key, and an associative array (with keys 'to' and 'from') containing the
	 * start & end time for said period.
	 *
	 * There are 3 pre-defined periods: the last 4 hours, today & last week.
	 * Other than that, all month since the oldest post in the $history will be
	 * included.
	 *
	 * @param History $history
	 * @return array
	 */
	public function getTimespans( History $history ) {
		global $wgLang;

		// Get history for pre-determined timespans.
		$timestampLast4 = new MWTimestamp( strtotime( '4 hours ago' ) );
		$timestampDay = new MWTimestamp( strtotime( date( 'Y-m-d' ) ) );
		$timestampWeek = new MWTimestamp( strtotime( '1 week ago' ) );

		$timespans = array(
			wfMessage( 'flow-history-last4' )->escaped() => array( 'from' => $timestampLast4, 'to' => null ),
			wfMessage( 'flow-history-day' )->escaped() => array( 'from' => $timestampDay, 'to' => $timestampLast4 ),
			wfMessage( 'flow-history-week' )->escaped() => array( 'from' => $timestampWeek, 'to' => $timestampDay ),
		);

		// Find last timestamp.
		$history->seek( $history->numRows() - 1 );
		$lastTimestamp = $history->next()->getTimestamp();
		$lastTimestamp = $lastTimestamp->getTimestamp( TS_UNIX );

		// Start from last week, we've already fetched everything before that.
		$previousTimestamp = $timestampWeek->getTimestamp( TS_UNIX );
		while ( $previousTimestamp > $lastTimestamp ) {
			// First and last moments in that month.
			$start = mktime( 0, 0, 0, date( 'n', $previousTimestamp ), 1, date( 'Y', $previousTimestamp ) );
			$end = mktime( 23, 59, 59, date( 'n', $previousTimestamp ), date( 't', $previousTimestamp ), date( 'Y', $previousTimestamp ) );

			// Make sure there's no overlap between end time & what we have already (e.g. last week)
			$start = new MWTimestamp( $start );
			$end = new MWTimestamp( min( $end, $previousTimestamp ) );

			// Build message.
			$text = $wgLang->sprintfDate( 'F Y', $end->getTimestamp( TS_MW ) );

			// Add month to list.
			$timespans[$text] = array( 'from' => $start, 'to' => $end );

			// Now go back 1 month for the next.
			$previousTimestamp = strtotime( '-1 month', $end->getTimestamp( TS_UNIX ) );
		}

		return $timespans;
	}

	/**
	 * @param History $history
	 * @return string
	 */
	public function render( History $history ) {
		$bundles = array();
		$output = '';

		foreach ( $history as $record ) {
			// Build arrays, per type, of records to be bundled.
			if ( $record->isBundled() ) {
				$bundles[$record->getType()][] = $record->getRevision();
			}
		}

		foreach ( $history as $record ) {
			$class = $record->getType();

			// This record is part of an already-rendered bundle.
			if ( $record->isBundled() && !isset( $bundles[$class] ) ) {
				continue;

			// This record is part of a bundle, render it.
			} elseif ( $record->isBundled() && count( $bundles[$class] ) > 1 ) {
				$bundle = new HistoryBundle( $bundles[$class] );
				$output .= $this->renderLine( $bundle );

				// Make sure this bundle is not rendered again.
				unset( $bundles[$class] );

			// Normal record ro be rendered.
			} else {
				$output .= $this->renderLine( $record );
			}
		}

		return '<ul>' . $output . '</ul>';
	}

	/**
	 * @param HistoryRecord $record
	 * @return string
	 */
	protected function renderLine( HistoryRecord $record ) {
		global $wgUser;

		$children = '';
		$historicalLink = null;
		if ( $record instanceof HistoryBundle ) {
			foreach ( $record->getData() as $revision ) {
				$historyRecord = new HistoryRecord( $revision );
				$children .= $this->renderLine( $historyRecord );
			}
		} else {
			$revision = $record->getRevision();
			$workFlowId = null;

			// Topic/Post history
			if ( $this->block->getName() === 'topic' ) {
				$workFlowId = $this->block->getWorkflowId();
			// Board history
			} else {
				if ( $revision->getRevisionType() === 'post' ) {
					$workFlowId = \Flow\Container::get( 'repository.tree' )->findRoot( $revision->getPostId() );
				} else {
					$workFlowId = $revision->getWorkflowId();
				}
			}
			if ( $workFlowId ) {
				$historicalLink = $this->templating->getUrlGenerator()->generateBlockUrl(
					$workFlowId,
					$revision,
					true
				);
			}
		}

		return $this->templating->render( 'flow:history-line.html.php', array(
			'class' => $record->getClass(),
			'message' => $record->getMessage(
				// Arguments for the i18n messages' parameter callbacks.
				$record->getData(),
				$this->templating,
				$wgUser,
				$this->block
			),
			'timestamp' => $record->getTimestamp(),
			'children' => $children,
			'historicalLink' => $historicalLink,
		), true );
	}
}
