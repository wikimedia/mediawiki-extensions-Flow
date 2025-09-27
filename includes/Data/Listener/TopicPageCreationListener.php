<?php

namespace Flow\Data\Listener;

use Flow\Model\Workflow;
use Flow\OccupationController;
use MediaWiki\MediaWikiServices;
use SplQueue;

class TopicPageCreationListener extends AbstractListener {
	/** @var OccupationController */
	protected $occupationController;

	/** @var SplQueue */
	protected $deferredQueue;

	/**
	 * @param OccupationController $occupationController The OccupationController to create the page with.
	 * @param SplQueue $deferredQueue Queue of callbacks to run only if commit succeeds
	 */
	public function __construct(
		OccupationController $occupationController,
		SplQueue $deferredQueue
	) {
		$this->occupationController = $occupationController;
		$this->deferredQueue = $deferredQueue;
	}

	/** @inheritDoc */
	public function onAfterLoad( $object, array $old ) {
		// Nothing
	}

	/** @inheritDoc */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		if ( !$object instanceof Workflow ) {
			return;
		}

		// make sure this Topic:xyz page exists
		$controller = $this->occupationController;
		$this->deferredQueue->push( static function () use ( $controller, $object ) {
			$controller->ensureFlowRevision(
				MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $object->getArticleTitle() ),
				$object
			);
		} );
	}
}
