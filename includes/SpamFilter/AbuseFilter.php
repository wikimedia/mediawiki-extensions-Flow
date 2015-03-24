<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use IContextSource;
use Status;
use Title;
use User;

class AbuseFilter implements SpamFilter {
	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @param User $user The user submitting content
	 * @param string $group The abuse filter group to use
	 */
	public function __construct( User $user, $group ) {
		$this->user = $user;
		$this->group = $group;
	}

	/**
	 * Set up AbuseFilter for Flow extension
	 *
	 * @param array $emergencyDisable optional AbuseFilter emergency disable values
	 */
	public function setup( array $emergencyDisable = array() ) {
		global
		$wgAbuseFilterValidGroups,
		$wgAbuseFilterEmergencyDisableThreshold,
		$wgAbuseFilterEmergencyDisableCount,
		$wgAbuseFilterEmergencyDisableAge;

		if ( !$this->enabled() ) {
			return;
		}

		// if no Flow-specific emergency disable threshold given, use defaults
		$emergencyDisable += array(
			'threshold' => $wgAbuseFilterEmergencyDisableThreshold['default'],
			'count' => $wgAbuseFilterEmergencyDisableCount['default'],
			'age' => $wgAbuseFilterEmergencyDisableAge['default'],
		);

		// register Flow's AbuseFilter filter group
		if ( !in_array( $this->group, $wgAbuseFilterValidGroups ) ) {
			$wgAbuseFilterValidGroups[] = $this->group;

			// AbuseFilter emergency disable values for Flow
			$wgAbuseFilterEmergencyDisableThreshold[$this->group] = $emergencyDisable['threshold'];
			$wgAbuseFilterEmergencyDisableCount[$this->group] = $emergencyDisable['count'];
			$wgAbuseFilterEmergencyDisableAge[$this->group] = $emergencyDisable['age'];
		}
	}

	/**
	 * @param IContextSource $context
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( IContextSource $context, AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		$vars = \AbuseFilter::getEditVars( $title );
		$vars->addHolders( \AbuseFilter::generateUserVars( $this->user ), \AbuseFilter::generateTitleVars( $title , 'ARTICLE' ) );
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

		return \AbuseFilter::filterAction( $vars, $title, $this->group );
	}

	/**
	 * Checks if AbuseFilter is installed.
	 *
	 * @return bool
	 */
	public function enabled() {
		return class_exists( 'AbuseFilter' ) && (bool) $this->group;
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
			 * @param \AbuseFilterVariableHolder $vars
			 * @param array $parameters Parameters with data to compute the value
			 * @param mixed &$result Result of the computation
			 */
			'FlowRevisionContent' => function ( \AbuseFilterVariableHolder $vars, $parameters ) {
					if ( !isset( $parameters['revision'] ) ) {
						return '';
					}
					$revision = $parameters['revision'];
					if ( $revision instanceof AbstractRevision ) {
						return $revision->getContent( 'wikitext' );
					} else {
						return '';
					}
				}
		);
	}
}
