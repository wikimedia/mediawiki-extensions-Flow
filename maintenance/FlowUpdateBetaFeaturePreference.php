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

	public function __construct() {
		$this->setBatchSize( 300 );
	}

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

		$innerQuery = $db->selectSQLText(
			'user_properties',
			'up_user',
			array(
				'up_property' => BETA_FEATURE_FLOW_USER_TALK_PAGE,
				'up_value' => 1
			)
		);

		$result = $db->select(
			array( 'page', 'user' ),
			'user_id',
			array(
				'page_content_model' => CONTENT_MODEL_FLOW_BOARD,
				"user_id NOT IN($innerQuery)"
			),
			__METHOD__,
			array(),
			array(
				'user' => array( 'JOIN', array(
					'page_namespace' => NS_USER_TALK,
					"page_title = REPLACE(user_name, ' ', '_')"
				) ),
			)
		);

		$i = 0;
		$users = UserArray::newFromResult( $result );
		foreach ( $users as $user ) {
			$user->setOption( BETA_FEATURE_FLOW_USER_TALK_PAGE, 1 );
			$user->saveSettings();

			if ( ++$i % $this->mBatchSize === 0 ) {
				wfWaitForSlaves();
			}
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
