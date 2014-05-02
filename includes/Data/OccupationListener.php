<?php

namespace Flow\Data;

use Flow\OccupationController;

class OccupationListener implements LifecycleHandler {
	/** @var OccupationController **/
	protected $occupationController;

	public function __construct( OccupationController $occupationController ) {
		$this->occupationController = $occupationController;
	}

	public function onAfterLoad( $object, array $old ) {
		/** @var Flow\Model\Workflow **/
		$workflow = $object;

		$article = new Article( $workflow->getArticleTitle() );

		$this->occupationController->ensureFlowRevision( $workflow->getArticle(), $workflow );
	}

	function onAfterInsert( $object, array $new ) {
		/** @var Flow\Model\Workflow **/
		$workflow = $object;

		$article = new Article( $workflow->getArticleTitle() );

		$this->occupationController->ensureFlowRevision( $workflow->getArticle(), $workflow );
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// Nothing
	}

	function onAfterRemove( $object, array $old ) {
		// Nothing
	}
}