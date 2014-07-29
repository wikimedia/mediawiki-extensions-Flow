<?php

namespace Flow\Data;

use Article;
use Flow\Model\Workflow;
use Flow\OccupationController;

/**
 * Ensures that a given workflow is occupied.  This will be unnecssary
 * once we deprecate the OccupationController white list.
 */
class OccupationListener implements LifecycleHandler {
	/** @var OccupationController **/
	protected $occupationController;

	/** @var string **/
	protected $defaultType;

	/**
	 * @param OccupationController $occupationController The OccupationController to occupy the page with.
	 * @param string               $defaultType          The workflow type to look for
	 */
	public function __construct( OccupationController $occupationController, $defaultType ) {
		$this->occupationController = $occupationController;
		$this->defaultType = $defaultType;
	}

	public function onAfterLoad( $object, array $old ) {
		if ( $object->getType() === $this->defaultType ) {
			$this->ensureOccupation( $object );
		}
	}

	public function onAfterInsert( $object, array $new, array $metadata ) {
		$this->ensureOccupation( $object );
	}

	protected function ensureOccupation( Workflow $workflow ) {
		$article = new Article( $workflow->getArticleTitle() );
		$this->occupationController->ensureFlowRevision( $article, $workflow );
	}

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		// Nothing
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		// Nothing
	}
}
