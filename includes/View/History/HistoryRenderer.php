<?php

namespace Flow\View\History;

use Flow\Block\Block;
use Flow\Container;
use Flow\Data\ObjectManager;
use Flow\Exception\FlowException;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use MWTimestamp;

/**
 * HistoryRenderer will use Templating to render a given list of History.
 */
class HistoryRenderer extends RevisionFormatter {
	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * @var Block
	 */
	protected $block;

	/**
	 * @var Workflow[]
	 */
	protected $workflows;

	/**
	 * @var integer The unix timestamp which is used as base for the calculation
	 * 		of relative dates.
	 */
	protected $now;

	/**
	 * @param RevisionActionPermissions $permissions
	 * @param Templating $templating
	 * @param Block $block
	 * @param integer|null $now The unix timestamp which is used as base for the
	 * 		calculation of relative dates.
	 */
	public function __construct( RevisionActionPermissions $permissions, Templating $templating, Block $block, $now = null ) {
		$this->templating = $templating;
		$this->block = $block;
		$this->workflows = array();
		if ( $now === null ) {
			$this->now = time();
		} elseif ( $now instanceof MWTimestamp ) {
			$this->now = $now->getTimestamp( TS_UNIX );
		} else {
			$this->now = $now;
		}

		parent::__construct( $permissions, $templating );
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
	 * @throws FlowException
	 */
	public function getTimespans( History $history ) {
		global $wgLang;

		if ( $history->numRows() === 0 ) {
			return array();
		}

		$this->batchLoadWorkflow( $history );

		// Get history for pre-determined timespans.
		$timestampFuture = new MWTimestamp( 1<<31 ); // end of unix epoch, to catch bugged future entries
		$timestampLast4 = new MWTimestamp( strtotime( '4 hours ago', $this->now ) );
		$timestampDay = new MWTimestamp( strtotime( date( 'Y-m-d', $this->now ) ) );
		$timestampWeek = new MWTimestamp( strtotime( '1 week ago', $this->now ) );

		/** @var MWTimestamp[][] $timespans */
		$timespans = array();
		$timespans[wfMessage( 'flow-history-last4' )->escaped()] = array( 'from' => $timestampLast4, 'to' => $timestampFuture );
		// if now is within first 4 hours of the day, all histories would be included in '4 hours ago'
		if ( $timestampDay < $timestampLast4 ) {
			$timespans[wfMessage( 'flow-history-day' )->escaped()] = array( 'from' => $timestampDay, 'to' => $timestampLast4 );
		}
		$timespans[wfMessage( 'flow-history-week' )->escaped()] = array(
			'from' => $timestampWeek,
			'to' => min( $timestampLast4, $timestampDay )
		);

		// Find last timestamp.
		$history->seek( $history->numRows() - 1 );
		/** @var HistoryRecord $next */
		$next = $history->next();
		$lastTimestamp = $next->getTimestamp()->getTimestamp( TS_UNIX );

		// Start from last week, we've already fetched everything before that.
		$month = $timestampWeek->getTimestamp( TS_UNIX );
		$i = 0;
		do {
			// First and last moments in that month.
			$start = mktime( 0, 0, 0, date( 'n', $month ), 1, date( 'Y', $month ) );
			$end = mktime( 23, 59, 59, date( 'n', $month ), date( 't', $month ), date( 'Y', $month ) );

			// Make sure there's no overlap between end time & what we have already (e.g. last week)
			$previous = end( $timespans );
			/** @var MWTimestamp $previousFrom */
			$previousFrom = $previous['from'];
			$start = new MWTimestamp( $start );
			$end = new MWTimestamp( min( $end, $previousFrom->getTimestamp( TS_UNIX ) ) );

			// Build message.
			$text = $wgLang->sprintfDate( 'F Y', $end->getTimestamp( TS_MW ) );

			// Add month to list.
			$timespans[$text] = array( 'from' => $start, 'to' => $end );

			// Now go back 1 month for the next.
			$month = strtotime( '-1 month', $start->getTimestamp( TS_UNIX ) );
		} while ( $start->getTimestamp( TS_UNIX ) > $lastTimestamp && $i++ < 9999 );

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
			/** @var HistoryRecord $record */
			// Build arrays, per type, of records to be bundled.
			if ( $record->isBundled() ) {
				$bundles[$record->getType()][] = $record->getRevision();
			}
		}

		foreach ( $history as $record ) {
			/** @var HistoryRecord $record */
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
	 * @throws FlowException
	 */
	protected function batchLoadWorkflow( $history ) {
		$revs = $uuids = array();
		foreach ( $history as $record ) {
			/** @var HistoryRecord $record */
			$revision = $record->getRevision();
			// Topic/Post history
			if ( $this->block->getName() === 'topic' ) {
				$workflowId = $this->block->getWorkflowId();
			// Board history
			} else {
				// Only topics in board history for now, which means it's always a workflowId
				if ( $revision instanceof PostRevision ) {
					if ( !$revision->isTopicTitle() ) {
						wfWarn( __METHOD__ . ': Non-topic post gets queried in board history: ' . $revision->getRevisionId() );
						continue;
					}
					$workflowId = $revision->getPostId();
				} elseif ( $revision instanceof Header ) {
					$workflowId = $revision->getWorkflowId();
				} else {
					throw new FlowException( 'Expected Header or PostRevision but received ' . get_class( $revision ) );
				}
			}
			$revs[$workflowId->getBinary()][] = $revision->getRevisionId()->getAlphadecimal();
			$uuids[] = $workflowId;
		}

		/** @var ObjectManager $storage */
		$storage = Container::get( 'storage.workflow' );
		/** @var Workflow[] $res */
		$res = $storage->getMulti( $uuids );

		foreach ( $res as $workflow ) {
			$uuid = $workflow->getId()->getBinary();
			if ( isset( $revs[$uuid] ) ) {
				foreach ( $revs[$uuid] as $revId ) {
					$this->workflows[$revId] = $workflow;
				}
			}
		}
	}

	/**
	 * @param HistoryRecord $record
	 * @return string
	 */
	protected function renderLine( HistoryRecord $record ) {
		$children = '';
		$historicalLink = null;
		if ( $record instanceof HistoryBundle ) {
			foreach ( $record->getData() as $revision ) {
				$historyRecord = new HistoryRecord( $revision );
				$children .= $this->renderLine( $historyRecord );
			}
		} else {
			$revision = $record->getRevision();
			if ( isset( $this->workflows[$revision->getRevisionId()->getAlphadecimal()] ) ) {
				$workflow = $this->workflows[$revision->getRevisionId()->getAlphadecimal()];
				if ( $revision instanceof PostRevision ) {
					$historicalLink = $this->templating->getUrlGenerator()
						->postRevisionLink(
							$workflow->getArticleTitle(),
							$workflow->getId(),
							$revision->getPostId(),
							$revision->getRevisionId()
						)
						->getFullUrl();
				} else {
					$historicalLink = $this->templating->getUrlGenerator()
						->workflowHistoryLink(
							$workflow->getArticleTitle(),
							$workflow->getId()
						)
						->getFullUrl();
				}
			}
		}

		// build i18n message
		list( $msg, $params ) = $record->getMessageParams();
		$ctx = \RequestContext::getMain();
		foreach ( $params as &$param ) {
			$param = $this->processParam(
				// Arguments for the i18n messages' parameter callbacks.
				$param,
				$record->getData(),
				$this->block->getWorkflowId(),
				$ctx
			);
		}

		return $this->templating->render( 'flow:history-line.html.php', array(
			'class' => $record->getClass(),
			'message' => wfMessage( $msg, $params ),
			'timestamp' => $record->getTimestamp(),
			'children' => $children,
			'historicalLink' => $historicalLink,
		), true );
	}
}
