<?php

namespace Flow\View\History;

use Flow\Block\Block;
use Flow\Templating;

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
			} elseif ( $record->isBundled() &&count( $bundles[$class] ) > 1 ) {
				$bundle = new HistoryBundle( $bundles[$class] );
				$output .= $this->renderLine( $bundle );

				// Make sure this bundle is not rendered again.
				unset( $bundles[$class] );

				// Check
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
		if ( $record instanceof HistoryBundle ) {
			foreach ( $record->getData() as $revision ) {
				$historyRecord = new HistoryRecord( $revision );
				$children .= $this->renderLine( $historyRecord );
			}
		}

		return $this->templating->render( 'flow:topic-history-line.html.php', array(
			'class' => $record->getClass(),
			'message' => $record->getMessage(
				// Arguments for the i18n messages' parameter callbacks.
				$record->getData(),
				$this->templating->urlGenerator,
				$wgUser,
				$this->block
			),
			'timestamp' => $record->getTimestamp(),
			'children' => $children
		), true );
	}
}
