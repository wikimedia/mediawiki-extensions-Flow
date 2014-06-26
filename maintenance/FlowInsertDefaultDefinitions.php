<?php

use Flow\Container;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Inserts Flow's default definitions
 *
 * @ingroup Maintenance
 */
class FlowInsertDefaultDefinitions extends LoggedUpdateMaintenance {

	protected function doDBUpdates() {
		$container = Container::getContainer();
		/** @var \DatabaseBase $dbw */
		$dbw = $container['db.factory']->getDB( DB_MASTER );

		$res = $dbw->select(
			/* table */'flow_definition',
			/* select */'definition_id',
			/* conds */array( 'definition_name' => 'topic', 'definition_wiki' => wfWikiId() ),
			__METHOD__
		);
		if ( $res ) {
			$res = $res->fetchRow();
		}
		if ( !isset( $res['definition_id'] ) ) {
			$this->insertDefinitions( $dbw );
		}

		return true;
	}

	protected function insertDefinitions( DatabaseBase $dbw ) {
		$topicId = UUID::create();
		$discussionId = UUID::create();

		$dbw->insert(
			'flow_definition',
			array(
				'definition_id' => $topicId->getBinary(),
				'definition_wiki' => wfWikiId(),
				'definition_name' => 'topic',
				'definition_type' => 'topic',
				'definition_options' => null
			),
			__METHOD__
		);

		$dbw->insert(
			'flow_definition',
			array(
				'definition_id' => $discussionId->getBinary(),
				'definition_wiki' => wfWikiId(),
				'definition_name' => 'discussion',
				'definition_type' => 'discussion',
				'definition_options' => serialize( array(
					'topic_definition_id' => $topicId,
					'unique' => true,
				) ),
			),
			__METHOD__
		);
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'FlowInsertDefaultDefinitions';
	}
}

$maintClass = 'FlowInsertDefaultDefinitions'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
