<?php

namespace Flow\Maintenance;

use LoggedUpdateMaintenance;
use MediaWiki\User\UserArray;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Sets Flow beta feature preference to true
 * for users who are already using flow on
 * their user talk page.
 *
 * @ingroup Maintenance
 */
class FlowUpdateBetaFeaturePreference extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		$this->setBatchSize( 300 );
		$this->requireExtension( 'Flow' );
	}

	/**
	 * When the Flow beta feature is enable, it finds users
	 * who already have Flow enabled on their user talk page
	 * and opt them in the beta feature so their preferences
	 * and user talk page state are in sync.
	 *
	 * @return bool
	 */
	protected function doDBUpdates() {
		global $wgFlowEnableOptInBetaFeature;
		if ( !$wgFlowEnableOptInBetaFeature ) {
			return true;
		}

		$db = $this->getDB( DB_PRIMARY );

		$innerQuery = $db->newSelectQueryBuilder()
			->select( 'up_user' )
			->from( 'user_properties' )
			->where( [
				// It works because this beta feature is exempted from auto-enroll.
				'up_property' => BETA_FEATURE_FLOW_USER_TALK_PAGE,
				'up_value' => 1
			] )
			->caller( __METHOD__ );

		$result = $db->newSelectQueryBuilder()
			->select( 'user_id' )
			->from( 'page' )
			->join( 'user', null, [
				'page_namespace' => NS_USER_TALK,
				"page_title = REPLACE(user_name, ' ', '_')"
			] )
			->where( [
				'page_content_model' => CONTENT_MODEL_FLOW_BOARD,
				'user_id NOT IN(' . $innerQuery->getSQL() . ')',
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$services = $this->getServiceContainer();
		$lbFactory = $services->getDBLoadBalancerFactory();
		$userOptionsManager = $services->getUserOptionsManager();

		$i = 0;
		$batchSize = $this->getBatchSize();
		$users = UserArray::newFromResult( $result );
		foreach ( $users as $user ) {
			// It works because this beta feature is exempted from auto-enroll.
			$userOptionsManager->setOption( $user, BETA_FEATURE_FLOW_USER_TALK_PAGE, 1 );
			$userOptionsManager->saveOptions( $user );

			if ( ++$i % $batchSize === 0 ) {
				$lbFactory->waitForReplication();
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

$maintClass = FlowUpdateBetaFeaturePreference::class;
require_once RUN_MAINTENANCE_IF_MAIN;
