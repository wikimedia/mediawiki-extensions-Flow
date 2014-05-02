<?php

namespace Flow\Data;

use Article;
use Flow\Model\Definition;
use Flow\Model\Workflow;
use Flow\OccupationController;

class OccupationListener implements LifecycleHandler {
	/** @var OccupationController **/
	protected $occupationController;

	/** @var UUID **/
	protected $definitionId;

	/**
	 * @param OccupationController $occupationController The OccupationController to occupy the page with.
	 * @param Definition           $definitionId         The definition to look for.
	 */
	public function __construct( OccupationController $occupationController, Definition $definition ) {
		$this->occupationController = $occupationController;
		$this->definitionId = $definition->getId();
	}

	public function onAfterLoad( $object, array $old ) {
		$this->ensureOccupation( $object );
	}

	function onAfterInsert( $object, array $new ) {
		$this->ensureOccupation( $object );
	}

	protected function ensureOccupation( Workflow $workflow ) {
		if ( $this->definitionId->equals( $workflow->getDefinitionId() ) ) {
			$article = new Article( $workflow->getArticleTitle() );

			$this->occupationController->ensureFlowRevision( $article, $workflow );
		}
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// Nothing
	}

	function onAfterRemove( $object, array $old ) {
		// Nothing
	}
}