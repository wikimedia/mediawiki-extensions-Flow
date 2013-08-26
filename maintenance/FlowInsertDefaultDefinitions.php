<?php

use Flow\Model\UUID;

/**
 * Remove invalid events from echo_event and echo_notification
 *
 * @ingroup Maintenance
 */
require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Maintenance script that removes invalid notifications
 *
 * @ingroup Maintenance
 */
class FlowInsertDefaultDefinitions extends Maintenance {

	public function execute() {
		$dbw = MWEchoDbFactory::getDB( DB_MASTER );

		$res = $dbw->select(
			/* table */'flow_definition',
			/* select */'definition_id',
			/* conds */array( 'definition_name' => 'topic' ),
			__METHOD__
		);
		if ( $res ) {
			$res = $res->fetchRow();
		}
		if ( !isset( $res['definition_id'] ) ) {
			$this->insertDefinitions();
		}

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
}

$maintClass = 'FlowInsertDefaultDefinitions'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
