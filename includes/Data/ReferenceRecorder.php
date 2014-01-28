<?php

namespace Flow\Data;

class ReferenceRecorder implements LifeCycleHandler {
	protected $referenceExtractor, $storage;

	function __construct( $referenceExtractor, $storage ) {
		$this->referenceExtractor = $referenceExtractor;
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
			$object->getRevisionId(),
			$content
		);

		$this->storage->multiPut( $references );
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// Nuthin
	}

	function onAfterRemove( $object, array $old ) {
		// Nuthin
	}
}