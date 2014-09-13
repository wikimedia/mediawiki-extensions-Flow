<?php

namespace Flow\Data\Listener;

use Article;
use Flow\Data\LifecycleHandler;
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

	/** @var bool **/
	protected $enabled = true;

	/**
	 * @param OccupationController $occupationController The OccupationController to occupy the page with.
	 * @param string               $defaultType          The workflow type to look for
	 */
	public function __construct( OccupationController $occupationController, $defaultType ) {
		$this->occupationController = $occupationController;
		$this->defaultType = $defaultType;
	}

	/**
	 * Disabling the listener is required if you want to load contributions
	 * or other flow history from pages that were enabled but are not anymore.
	 *
	 * @param bool $enabled
	 */
	public function setEnabled( $enabled ) {
		$this->enabled = (bool)$enabled;
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
		if ( $this->enabled ) {
			$this->occupationController->ensureFlowRevision(
				new Article( $workflow->getArticleTitle() ),
				$workflow
			);
		}
	}

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		// Nothing
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		// Nothing
	}
}
