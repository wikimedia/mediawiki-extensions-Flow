<?php

class FlowHooks {
	/**
	 * Initialize Flow extension with necessary data, this function is invoked
	 * from $wgExtensionFunctions
	 */
	public static function initFlowExtension() {
	}

	/**
	 * @param $updater DatabaseUpdater object
	 * @return bool true in all cases
	 */
	public static function getSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__;
		$baseSQLFile = "$dir/flow.sql";
		$updater->addExtensionTable( 'flow_revision', $baseSQLFile );

		return true;
	}

	/**
	 * Handler for UnitTestsList hook.
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 * @param &$files Array of unit test files
	 * @return bool true in all cases
	 */
	static function getUnitTests( &$files ) {
		$dir = dirname( __FILE__ ) . '/tests';
		//$files[] = "$dir/DiscussionParserTest.php";
		return true;
	}

	public static function onOldChangesListRecentChangesLine( \ChangesList &$changesList, &$s, \RecentChange $rc, &$classes = array() ) {

		$rcType = $rc->getAttribute( 'rc_type' );
		if ( $rcType == RC_EXTERNAL ) {

			$line = Flow\RecentChanges\ExternalChangesLines::changesLine( $changesList, $rc );
			if ( $line === false ) {
				return false;
			}

			$classes[] = 'flow-something';
			$s = $line;
		}

		return true;
	}
}
