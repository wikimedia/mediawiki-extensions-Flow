<?php

namespace Flow\Data\Listener;

use Article;
use Flow\Data\LifecycleHandler;
use Flow\Exception\FlowException;
use Flow\Model\Workflow;
use Flow\OccupationController;
use SplQueue;

/**
 * Ensures that a given workflow is occupied.  This will be unnecssary
 * once we deprecate the OccupationController white list.
 */
class OccupationListener implements LifecycleHandler {
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
	 * @param SplQueue             $deferredQueue        Queue of callbacks to run only if commit succedes
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
		if ( !$object instanceof Workflow ) {
			return;
		}
		if ( $object->getType() === $this->defaultType ) {
			// We don't want to defer the load event, the request
			// may require this to actually exist to render properly.
			$this->occupationController->ensureFlowRevision(
				new Article( $object->getArticleTitle() ),
				$object
			);
		}
	}

	public function onAfterInsert( $object, array $new, array $metadata ) {
		if ( !$object instanceof Workflow ) {
			return;
		}
		if ( isset( $metadata['imported'] ) && $metadata['imported'] ) {
			$this->occupationController->allowCreation( $object->getArticleTitle() );
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

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		// Nothing
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		// Nothing
	}
}
