<?php

namespace Flow;

use Flow\Model\AbstractRevision;
use Title;
use Status;
// not using AbuseFilter namespaces, since AbuseFilter may not be installed

abstract class AbuseFilterUtils {
	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision[optional] $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public static function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		if ( !self::enabled() ) {
			return true;
		}

		global $wgUser, $wgFlowAbuseFilterGroup;

		$vars = new \AbuseFilterVariableHolder;
		$vars->addHolders( \AbuseFilter::generateUserVars( $wgUser ), \AbuseFilter::generateTitleVars( $title , 'ARTICLE' ) );
		$vars->setVar( 'ACTION', $newRevision->getChangeType() );

		/**
		 * This should not roundtrip to Parsoid; AbuseFilter checks will be
		 * performed upon submitting new content, and content is always
		 * submitted in wikitext. It will only be transformed once it's being
		 * saved to DB.
		 */
		$vars->setVar( 'new_wikitext', $newRevision->getContent( 'wikitext' ) );
		$vars->setLazyLoadVar( 'new_size', 'length', array( 'length-var' => 'new_wikitext' ) );

		// @todo: this may roundtrip to Parsoid (if stored in HTML) - not implementing for now
//		$vars->setVar( 'old_wikitext', $oldRevision ? $oldRevision->getContent( 'wikitext' ) : '' );
//		$vars->setLazyLoadVar( 'old_size', 'length', array( 'length-var' => 'old_wikitext' ) );

		return \AbuseFilter::filterAction( $vars, $title, $wgFlowAbuseFilterGroup );
	}

	/**
	 * Checks if AbuseFilter is installed.
	 *
	 * @return bool
	 */
	public static function enabled() {
		global $wgFlowAbuseFilterGroup;
		return class_exists( 'AbuseFilter' ) && (bool) $wgFlowAbuseFilterGroup;
	}
}
