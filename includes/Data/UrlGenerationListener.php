<?php

namespace Flow\Data;

use Flow\Model\Workflow;
use Flow\UrlGenerator;

/**
 * The url generator needs to know about loaded workflow instances so it
 * can generate urls pointing to the correct pages.
 */
class UrlGenerationListener implements LifecycleHandler {
	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @param UrlGenerator $urlGenerator
	 */
	public function __construct( UrlGenerator $urlGenerator ) {
		$this->urlGenerator = $urlGenerator;
	}

	public function onAfterLoad( $object, array $old ) {
		if ( $object instanceof Workflow ) {
			$this->urlGenerator->withWorkflow( $object );
		}
	}

	public function onAfterInsert( $object, array $new ) {
		if ( $object instanceof Workflow ) {
			$this->urlGenerator->withWorkflow( $object );
		}
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		// Nothing
	}

	public function onAfterRemove( $object, array $old ) {
		// Nothing
	}
}
