<?php

namespace Flow\Data\Listener;

use Article;
use Flow\Data\LifecycleHandler;
use Flow\Data\ManagerGroup;
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

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/** @var bool **/
	protected $enabled = true;

	/**
	 * @param OccupationController $occupationController The OccupationController to occupy the page with.
	 * @param SplQueue             $deferredQueue        Queue of callbacks to run only if commit succeeds
	 * @param ManagerGroup         $storage
	 * @param string               $defaultType          The workflow type to look for
	 */
	public function __construct(
		OccupationController $occupationController,
		SplQueue $deferredQueue,
		ManagerGroup $storage,
		$defaultType
	) {
		$this->occupationController = $occupationController;
		$this->deferredQueue = $deferredQueue;
		$this->storage = $storage;
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
			$user = $this->occupationController->getTalkpageManager();
			$this->occupationController->allowCreation( $object->getArticleTitle(), $user, false );
		}

		$this->ensureOccupation( $object, $object->getArticleTitle() );

		if ( $new['workflow_page_id'] === 0 ) {
			// Don't allowCreation() here: a board has to be explicitly created,
			// or allowed via the occupyNamespace & occupyPages globals, in
			// which case allowCreation() won't be needed
			$this->ensureOccupation( $object, $object->getOwnerTitle() );

			$storage = $this->storage;
			$this->deferredQueue->push( function() use ( $object, $storage ) {
				// fetch id from newly inserted page, update workflow_page_id &
				// re-save the workflow
				$pageId = $object->getOwnerTitle()->getArticleID( \Title::GAID_FOR_UPDATE );
				$object->setPageId( $pageId );
				$storage->put( $object, array() );
			} );
		}
	}

	protected function ensureOccupation( Workflow $workflow, \Title $title ) {
		if ( $this->enabled ) {
			$controller = $this->occupationController;
			$this->deferredQueue->push( function() use ( $controller, $workflow, $title ) {
				$controller->ensureFlowRevision(
					new Article( $title ),
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
