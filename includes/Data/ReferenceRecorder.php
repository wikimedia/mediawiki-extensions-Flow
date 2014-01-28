<?php

namespace Flow\Data;

class ReferenceRecorder implements LifeCycleHandler {
	protected $referenceExtractor, $storage, $linksTableUpdater;

	function __construct( $referenceExtractor, $linksTableUpdater, $storage ) {
		$this->referenceExtractor = $referenceExtractor;
		$this->linksTableUpdater = $linksTableUpdater;
		$this->storage = $storage;
	}

	function onAfterLoad( $object, array $old ) {
		// Nuthin
	}

	function onAfterInsert( $object, array $new ) {
		// TODO delete old stuff

		$content = $object->getContent( 'html' );

		$references = $this->referenceExtractor->getReferences(
			$object->getWorkflowId(),
			$object->getObjectId(),
			$content
		);

		$this->storage->multiPut( $references );

		$this->linksTableUpdater->updateReferences( $object, $references );
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// Nuthin
	}

	function onAfterRemove( $object, array $old ) {
		// Nuthin
	}
}