<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Double check that a few configuration options are reasonable for production
 *
 * @ingroup Maintenance
 */
class FlowEnsureProductionSettings extends Maintenance {

	protected $fails = 0;

	public function execute() {
		$this->checkStorageFormat();
		$this->checkVisualEdnitor();

		if ( $this->fails > 0 ) {
			echo "\nWARNING: $fails checks failed, do not enable any pages with the Flow extension\n\n";
		} else {
			echo "\nNothing obviously broken\n\n";
		}
	}

	public function checkStorageFormat() {
		global $wgFlowStorageFormat, $wgFlowExternalStore;

		if ( $wgFlowExternalStore === false ) {
			$this->fail( 'ExternalStore should be used in production' );
		}

		switch( $wgFlowStorageFormat ) {
		case 'html':
			try {
				$html = Flow\ParsoidUtils::convert( 'wt', 'html', '[[Foo]]' );
				if ( false === strpos( $html, 'data-parsoid' ) ) {
					$this->fail( 'Parsoid does not appear to be working' );
				}
			} catch ( \Exception $e ) {
				$this->fail( $e );
			}
			break;

		case 'wikitext':
			// always works
			break;

		default:
			$this->fail( "Unknown \$wgFlowStorageFormat: $wgFlowStorageFormat" );
			break;
		}
	}

	public function checkVisualEditor() {
		global $wgFlowEditorList;

		if ( !in_array( 'visualeditor', $wgFlowEditorList ) ) {
			return;
		}

		if ( !class_exists( 'ApiVisualEditor' ) ) {
			$this->fail( 'VisualEditor is enabled in $wgFlowEditorList, but was not found' );
		}
	}

	protected function fail( $message ) {
		$this->fails++;
		echo "FAIL: ";
		if ( $message instanceof Exception ) {
			MWExceptionHandler::report( $message );
		} else {
			echo "$message\n\n";
		}
	}
}

$maintClass = 'FlowInsertDefaultDefinitions'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
