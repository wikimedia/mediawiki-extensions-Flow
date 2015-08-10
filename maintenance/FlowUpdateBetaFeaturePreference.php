<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Sets Flow beta feature preference to true
 * for users who are already using flow on
 * their user talk page.
 *
 * @ingroup Maintenance
 */
class FlowUpdateBetaFeaturePreference extends LoggedUpdateMaintenance {

	/**
	 * When the Flow beta feature is enable, it finds users
	 * who already have Flow enabled on their user talk page
	 * and opt them in the beta feature so their preferences
	 * and user talk page state are in sync.
	 *
	 * @return bool
	 * @throws MWException
	 */
	protected function doDBUpdates() {
		global $wgFlowEnableOptInBetaFeature;
		if ( !$wgFlowEnableOptInBetaFeature ) {
			return true;
		}

		$db = $this->getDB( DB_MASTER );

		$namespace = NS_USER_TALK;
		$contentModel = $db->addQuotes( CONTENT_MODEL_FLOW_BOARD );
		$propertyName = $db->addQuotes( BETA_FEATURE_FLOW_USER_TALK_PAGE );

		$sql = <<<EOD
SELECT
    user.user_id
FROM
    page
        JOIN
    user ON page.page_title = REPLACE(user.user_name, ' ', '_')
WHERE
    page.page_namespace = $namespace
	AND page.page_content_model = $contentModel
	AND user.user_id NOT IN (SELECT
            up_user
        FROM
            user_properties
        WHERE
            up_property = $propertyName
			AND up_value = 1)
EOD;

		$result = $db->query( $sql );
		$users = UserArray::newFromResult( $result );
		foreach ( $users as $user ) {
			$user->setOption( BETA_FEATURE_FLOW_USER_TALK_PAGE, 1 );
			$user->saveSettings();
		}

		return true;
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * Returns a different key when the beta feature is enabled or disable
	 * so that enabling it would trigger this script
	 * to execute so it can correctly update users preferences.
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		global $wgFlowEnableOptInBetaFeature;
		return $wgFlowEnableOptInBetaFeature ? 'FlowBetaFeatureEnable' : 'FlowBetaFeatureDisable';
	}
}

$maintClass = 'FlowUpdateBetaFeaturePreference'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
