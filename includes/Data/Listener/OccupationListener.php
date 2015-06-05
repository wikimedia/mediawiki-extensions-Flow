<?php

namespace Flow\Data\Listener;

use Article;
use Flow\Model\Workflow;
use Flow\OccupationController;
use SplQueue;

/**
 * Ensures that a given workflow is occupied.  This will be unnecssary
 * once we deprecate the OccupationController white list.
 */
class OccupationListener extends AbstractListener {
	/** @var OccupationController **/
	protected $occupationController;

	/** @var SplQueue */
	protected $deferredQueue;

	/** @var string **/
	protected $defaultType;

	/** @var bool **/
	protected $enabled = true;

	/**
	 * @param OccupationController $occupationController The OccupationController to occupy the page with.
	 * @param SplQueue             $deferredQueue        Queue of callbacks to run only if commit succeeds
	 * @param string               $defaultType          The workflow type to look for
	 */
	public function __construct(
		OccupationController $occupationController,
		SplQueue $deferredQueue,
		$defaultType
	) {
		$this->occupationController = $occupationController;
		$this->deferredQueue = $deferredQueue;
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
		// Nothing
	}

	public function onAfterInsert( $object, array $new, array $metadata ) {
		if ( !$object instanceof Workflow ) {
			return;
		}

		$this->ensureOccupation( $object );
	}

	protected function ensureOccupation( Workflow $workflow ) {
		if ( $this->enabled ) {
			$controller = $this->occupationController;
			$this->deferredQueue->push( function() use ( $controller, $workflow ) {
				$controller->ensureFlowRevision(
					new Article( $workflow->getArticleTitle() ),
					$workflow
				);
			} );
		}
	}
}
