<?php

namespace Flow\OOUI;

/**
 * A widget representing a simple post
 */
class BaseUiWidget extends \OOUI\Widget {

	protected function makeTable( $cells ) {
		$table = new \OOUI\Tag( 'div' );
		$table->addClasses( [ 'mw-flow-ui-table' ] );

		$row = new \OOUI\Tag( 'div' );
		$row->addClasses( [ 'mw-flow-ui-row' ] );

		foreach ( $cells as $cellData ) {
			$cell = new \OOUI\Tag( 'div' );
			if ( isset( $cellData['classes'] ) ) {
				$cell->addClasses( $cellData['classes'] );
				$cell->addClasses( [ 'mw-flow-ui-cell' ] );
			}
			if ( isset( $cellData['content'] ) ) {
				$cell->appendContent( $cellData['content'] );
			}

			$row->appendContent( $cell );
		}

		$table->appendContent( $row );

		return $table;
	}

	protected function makeSection( $name, $class = '' ) {
		$tag = new \OOUI\Tag( 'div' );

		$class = $class ? $class : 'flow-ui-topicWidget-' . $name;
		$tag->addClasses( [ $class ] );
		return $tag;
	}

	protected function getOnClickDeferredAction( $action ) {
		$outputAction = json_encode( $action );
		return 'function () {' .
			'window.mwSDInitActions = window.mwSDInitActions || [];' .
			'window.mwSDInitActions.push( [this,' . $outputAction . ']);' .
			'}';
	}
}