<?php

namespace Flow;

use Flow\Maintenance\FlowCreateTemplates;
use Flow\Maintenance\FlowFixLinks;
use Flow\Maintenance\FlowFixLog;
use Flow\Maintenance\FlowPopulateLinksTables;
use Flow\Maintenance\FlowPopulateRefId;
use Flow\Maintenance\FlowSetUserIp;
use Flow\Maintenance\FlowUpdateBetaFeaturePreference;
use Flow\Maintenance\FlowUpdateRecentChanges;
use Flow\Maintenance\FlowUpdateRevisionTypeId;
use Flow\Maintenance\FlowUpdateUserWiki;
use Flow\Maintenance\FlowUpdateWorkflowPageId;
use MediaWiki\Installer\DatabaseUpdater;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class SchemaHooks implements LoadExtensionSchemaUpdatesHook {

	/**
	 * Hook: LoadExtensionSchemaUpdates
	 *
	 * @param DatabaseUpdater $updater
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dir = dirname( __DIR__ ) . '/sql';
		$dbType = $updater->getDB()->getType();
		$updater->addExtensionTable( 'flow_revision', "$dir/$dbType/tables-generated.sql" );

		if ( $dbType === 'mysql' ) {
			// 1.35 (backported to 1.34)
			$updater->modifyExtensionField( 'flow_wiki_ref', 'ref_src_wiki',
				"$dir/$dbType/patch-increase-varchar-flow_wiki_ref-ref_src_wiki.sql" );
			$updater->modifyExtensionField( 'flow_ext_ref', 'ref_src_wiki',
				"$dir/$dbType/patch-increase-varchar-flow_ext_ref-ref_src_wiki.sql" );

			// 1.39
			$updater->modifyExtensionField(
				'flow_revision',
				'rev_mod_timestamp',
				"$dir/$dbType/patch-flow_revision-rev_mod_timestamp.sql"
			);
		}

		$updater->addPostDatabaseUpdateMaintenance( FlowUpdateRecentChanges::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowSetUserIp::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowUpdateUserWiki::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowUpdateRevisionTypeId::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowPopulateLinksTables::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowFixLog::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowUpdateWorkflowPageId::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowCreateTemplates::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowFixLinks::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowUpdateBetaFeaturePreference::class );

		$updater->addPostDatabaseUpdateMaintenance( FlowPopulateRefId::class );
	}
}
