<?php

namespace MediaWiki\Extension\StructuredDiscussions\Hooks;

use Flow\Data\Listener\RecentChangesListener;
use Flow\Hooks;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterGenerateVarsForRecentChangeHook;
use MediaWiki\Extension\AbuseFilter\VariableGenerator\RCVariableGenerator;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\RecentChanges\RecentChange;
use MediaWiki\User\User;

class AbuseFilterHandler implements AbuseFilterGenerateVarsForRecentChangeHook {
	/**
	 * @inheritDoc
	 */
	public function onAbuseFilterGenerateVarsForRecentChange(
		RCVariableGenerator $generator,
		RecentChange $rc,
		VariableHolder $vars,
		User $contextUser
	): bool {
		if ( $rc->getAttribute( 'rc_source' ) !== RecentChangesListener::SRC_FLOW ) {
			return false;
		}

		Hooks::getAbuseFilter()->generateRecentChangesVars( $rc, $vars, $contextUser );
		return true;
	}
}
