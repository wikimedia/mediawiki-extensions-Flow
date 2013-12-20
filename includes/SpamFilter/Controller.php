<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use Title;
use Status;
// not using AbuseFilter namespaces, since AbuseFilter may not be installed

class Controller {
	/**
	 * Set up AbuseFilter for Flow extension
	 *
	 * @param string $group AbuseFilter group name
	 * @param array[optional] $emergencyDisable AbuseFilter emergency disable values
	 */
	public function setup( $group, array $emergencyDisable = array() ) {
		global
			$wgAbuseFilterValidGroups,
			$wgAbuseFilterEmergencyDisableThreshold,
			$wgAbuseFilterEmergencyDisableCount,
			$wgAbuseFilterEmergencyDisableAge;

		// if no Flow-specific emergency disable threshold given, use defaults
		if ( !isset( $emergencyDisable['threshold'] ) ) {
			$emergencyDisable['threshold'] = $wgAbuseFilterEmergencyDisableThreshold['default'];
		}
		if ( !isset( $emergencyDisable['count'] ) ) {
			$emergencyDisable['count'] = $wgAbuseFilterEmergencyDisableCount['default'];
		}
		if ( !isset( $emergencyDisable['age'] ) ) {
			$emergencyDisable['age'] = $wgAbuseFilterEmergencyDisableAge['default'];
		}

		// register Flow's AbuseFilter filter group
		if ( !in_array( $group, $wgAbuseFilterValidGroups ) ) {
			$wgAbuseFilterValidGroups[] = $group;

			// AbuseFilter emergency disable values for Flow
			$wgAbuseFilterEmergencyDisableThreshold[$group] = $emergencyDisable['threshold'];
			$wgAbuseFilterEmergencyDisableCount[$group] = $emergencyDisable['count'];
			$wgAbuseFilterEmergencyDisableAge[$group] = $emergencyDisable['age'];
		}
	}

	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision[optional] $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		if ( !self::enabled() ) {
			return true;
		}

		global $wgUser, $wgFlowAbuseFilterGroup;

		$vars = new \AbuseFilterVariableHolder;
		$vars->addHolders( \AbuseFilter::generateUserVars( $wgUser ), \AbuseFilter::generateTitleVars( $title , 'ARTICLE' ) );
		$vars->setVar( 'ACTION', $newRevision->getChangeType() );

		/*
		 * This should not roundtrip to Parsoid; AbuseFilter checks will be
		 * performed upon submitting new content, and content is always
		 * submitted in wikitext. It will only be transformed once it's being
		 * saved to DB.
		 */
		$vars->setLazyLoadVar( 'new_wikitext', 'FlowRevisionContent', array( 'revision' => $newRevision ) );
		$vars->setLazyLoadVar( 'new_size', 'length', array( 'length-var' => 'new_wikitext' ) );

		/*
		 * This may roundtrip to Parsoid if content is stored in HTML.
		 * Since the variable is lazy-loaded, it will not roundtrip unless the
		 * variable is actually used.
		 */
		$vars->setLazyLoadVar( 'old_wikitext', 'FlowRevisionContent', array( 'revision' => $oldRevision ) );
		$vars->setLazyLoadVar( 'old_size', 'length', array( 'length-var' => 'old_wikitext' ) );

		return \AbuseFilter::filterAction( $vars, $title, $wgFlowAbuseFilterGroup );
	}

	/**
	 * Checks if AbuseFilter is installed.
	 *
	 * @return bool
	 */
	public function enabled() {
		global $wgFlowAbuseFilterGroup;
		return class_exists( 'AbuseFilter' ) && (bool) $wgFlowAbuseFilterGroup;
	}

	/**
	 * Additional lazy-load methods for dealing with AbstractRevision objects,
	 * to delay processing data until/if variables are actually used.
	 *
	 * @return array
	 */
	public function lazyLoadMethods() {
		return array(
			/**
			 * @param string $method: Method to generate the variable
			 * @param AbuseFilterVariableHolder $vars
			 * @param array $parameters Parameters with data to compute the value
			 * @param mixed &$result Result of the computation
			 */
			'FlowRevisionContent' => function ( \AbuseFilterVariableHolder $vars, $parameters ) {
					$revision = $parameters['revision'];
					return $revision ? $revision->getContent( 'wikitext' ) : '';
				}
		);
	}
}
