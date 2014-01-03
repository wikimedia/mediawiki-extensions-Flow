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
	 * @var array
	 */
	protected $workflows;

	/**
	 * @param History $history
	 * @param Templating $templating
	 * @param Block $block
	 */
	public function __construct( Templating $templating, Block $block ) {
		$this->templating = $templating;
		$this->block = $block;
		$this->workflows = array();
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
	 * @return array The keys of this array are properly escaped for output as
	 *     raw html, the values are an associative array containing 'from' and
	 *     'to' keys mapping to MWTimestamp objects.
	 */
	public function getTimespans( History $history ) {
		global $wgLang;

		if ( $history->numRows() === 0 ) {
			return array();
		}

		$this->batchLoadWorkflow( $history );

		// Get history for pre-determined timespans.
		$timestampLast4 = new MWTimestamp( strtotime( '4 hours ago' ) );
		$timestampDay = new MWTimestamp( strtotime( date( 'Y-m-d' ) ) );
		$timestampWeek = new MWTimestamp( strtotime( '1 week ago' ) );

		$timespans = array();
		$timespans[wfMessage( 'flow-history-last4' )->escaped()] = array( 'from' => $timestampLast4, 'to' => null );
		// if now is within first 4 hours of the day, all histories would be included in '4 hours ago'
		if ( $timestampDay < $timestampLast4 ) {
			$timespans[wfMessage( 'flow-history-day' )->escaped()] = array( 'from' => $timestampDay, 'to' => $timestampLast4 );
		}
		$timespans[wfMessage( 'flow-history-week' )->escaped()] = array( 'from' => $timestampWeek, 'to' => $timestampDay );

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
	 * @param History $history
	 */
	protected function batchLoadWorkflow( $history ) {
		$workflows = $ids = $revs = array();
		foreach ( $history as $record ) {
			$revision = $record->getRevision();
			// Topic/Post history
			if ( $this->block->getName() === 'topic' ) {
				$workflowId = $this->block->getWorkflowId();
			// Board history
			} else {
				// Only topics in board history for now, which means it's always a workflowId
				if ( $revision->getRevisionType() === 'post' ) {
					$workflowId = $revision->getPostId();
				} else {
					$workflowId = $revision->getWorkflowId();
				}
			}
			$ids[$workflowId->getHex()] = $workflowId;
			$revs[$workflowId->getHex()][] = $revision->getRevisionId()->getHex();
		}

		$res = \Flow\Container::get( 'storage.workflow' )->getMulti( $ids );

		foreach ( $res as $workflow ) {
			if ( isset( $ids[$workflow->getId()->getHex()] ) ) {
				foreach ( $revs[$workflow->getId()->getHex()] as $revId ) {
					$workflows[$revId] = $workflow;
				}
			}
		}

		$this->workflows = $workflows;
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
			if ( isset( $this->workflows[$revision->getRevisionId()->getHex()] ) ) {
				$workFlowId = $this->workflows[$revision->getRevisionId()->getHex()];
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
				$this->block->getWorkflowId(),
				$this->block->getName()
			),
			'timestamp' => $record->getTimestamp(),
			'children' => $children,
			'historicalLink' => $historicalLink,
		), true );
	}
}
