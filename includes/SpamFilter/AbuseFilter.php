<?php

namespace Flow\SpamFilter;

use ExtensionRegistry;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use IContextSource;
use MediaWiki\Extension\AbuseFilter\AbuseFilterServices;
use MediaWiki\Extension\AbuseFilter\VariableGenerator\RCVariableGenerator;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\MediaWikiServices;
use RecentChange;
use Status;
use Title;
use User;

class AbuseFilter implements SpamFilter {
	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @param string $group The abuse filter group to use
	 */
	public function __construct( $group ) {
		$this->group = $group;
	}

	/**
	 * Set up AbuseFilter for Flow extension
	 *
	 * @param array $emergencyDisable optional AbuseFilter emergency disable values
	 */
	public function setup( array $emergencyDisable = [] ) {
		global
		$wgAbuseFilterValidGroups,
		$wgAbuseFilterEmergencyDisableThreshold,
		$wgAbuseFilterEmergencyDisableCount,
		$wgAbuseFilterEmergencyDisableAge;

		if ( !$this->enabled() ) {
			return;
		}

		// if no Flow-specific emergency disable threshold given, use defaults
		$emergencyDisable += [
			'threshold' => $wgAbuseFilterEmergencyDisableThreshold['default'],
			'count' => $wgAbuseFilterEmergencyDisableCount['default'],
			'age' => $wgAbuseFilterEmergencyDisableAge['default'],
		];

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
	 * @param Title $ownerTitle
	 * @return Status
	 */
	public function validate(
		IContextSource $context,
		AbstractRevision $newRevision,
		?AbstractRevision $oldRevision,
		Title $title,
		Title $ownerTitle
	) {
		$vars = AbuseFilterServices::getVariableGeneratorFactory()->newGenerator()
			->addEditVars( MediaWikiServices::getInstance()->getWikiPageFactory()
				->newFromTitle( $title ), $context->getUser() )
			->addUserVars( $context->getUser() )
			->addTitleVars( $title, 'page' )
			->addTitleVars( $ownerTitle, 'board' )
			->getVariableHolder();

		$vars->setVar( 'action', $newRevision->getChangeType() );

		/*
		 * This should not roundtrip to Parsoid; AbuseFilter checks will be
		 * performed upon submitting new content, and content is always
		 * submitted in wikitext. It will only be transformed once it's being
		 * saved to DB.
		 */
		$vars->setLazyLoadVar( 'new_wikitext', 'FlowRevisionContent', [ 'revision' => $newRevision ] );

		/*
		 * This may roundtrip to Parsoid if content is stored in HTML.
		 * Since the variable is lazy-loaded, it will not roundtrip unless the
		 * variable is actually used.
		 */
		$vars->setLazyLoadVar( 'old_wikitext', 'FlowRevisionContent', [ 'revision' => $oldRevision ] );

		$runnerFactory = AbuseFilterServices::getFilterRunnerFactory();
		$runner = $runnerFactory->newRunner( $context->getUser(), $title, $vars, $this->group );
		return $runner->run();
	}

	/**
	 * @see RCVariableGenerator::addEditVarsForRow()
	 * @param RecentChange $recentChange
	 * @param VariableHolder $vars
	 * @param User $contextUser
	 */
	public function generateRecentChangesVars(
		RecentChange $recentChange,
		VariableHolder $vars,
		User $contextUser
	): void {
		$changeData = $recentChange->parseParams()['flow-workflow-change'];
		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );
		/** @var AbstractRevision $rev */
		$rev = $storage->get( $changeData['revision_type'], UUID::create( $changeData['revision'] ) );

		$vars->setVar( 'action', $rev->getChangeType() );
		$vars->setLazyLoadVar( 'new_wikitext', 'FlowRevisionContent', [ 'revision' => $rev ] );

		$prevRev = $rev->getCollection()->getPrevRevision( $rev );
		if ( $prevRev ) {
			$vars->setLazyLoadVar( 'old_wikitext', 'FlowRevisionContent', [ 'revision' => $prevRev ] );
		} else {
			$vars->setVar( 'old_wikitext', '' );
		}

		$title = $recentChange->getTitle();
		AbuseFilterServices::getVariableGeneratorFactory()->newGenerator( $vars )
			->addUserVars( $recentChange->getPerformerIdentity() )
			->addTitleVars( $title, 'page' )
			->addTitleVars( $rev->getCollection()->getWorkflow()->getOwnerTitle(), 'board' )
			->addEditVars( MediaWikiServices::getInstance()->getWikiPageFactory()
				->newFromTitle( $title ), $contextUser );
	}

	/**
	 * Checks if AbuseFilter is installed.
	 *
	 * @return bool
	 */
	public function enabled() {
		return ExtensionRegistry::getInstance()->isLoaded( 'Abuse Filter' ) && (bool)$this->group;
	}

	/**
	 * Additional lazy-load methods for dealing with AbstractRevision objects,
	 * to delay processing data until/if variables are actually used.
	 *
	 * @return array
	 */
	public function lazyLoadMethods() {
		return [
			/**
			 * @param VariableHolder $vars
			 * @param array $parameters Parameters with data to compute the value
			 */
			'FlowRevisionContent' => static function ( VariableHolder $vars, array $parameters ) {
				if ( !isset( $parameters['revision'] ) ) {
					return '';
				}
				$revision = $parameters['revision'];
				if ( $revision instanceof AbstractRevision ) {
					return $revision->getContentInWikitext();
				} else {
					return '';
				}
			}
		];
	}
}
