<?php

namespace Flow\Data\Listener;

use Article;
use Flow\Model\Workflow;
use Flow\OccupationController;
use SplQueue;

class TopicPageCreationListener extends AbstractListener {
	/** @var OccupationController **/
	protected $occupationController;

	/** @var SplQueue */
	protected $deferredQueue;

	/** @var string **/
	protected $defaultType;

	/**
	 * @param OccupationController $occupationController The OccupationController to create the page with.
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

	public function onAfterLoad( $object, array $old ) {
		// Nothing
	}

	public function onAfterInsert( $object, array $new, array $metadata ) {
		if ( !$object instanceof Workflow ) {
			return;
		}

		// make sure this Topic:xyz page exists
		$controller = $this->occupationController;
		$this->deferredQueue->push( function() use ( $controller, $object ) {
			$controller->ensureFlowRevision(
				new Article( $object->getArticleTitle() ),
				$object
			);
		} );
	}
}
