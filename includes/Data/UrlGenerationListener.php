<?php

namespace Flow\Data;

use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\UrlGenerator;

/**
 * The Url generation needs to know about two sets of loaded values:
 *
 *   workflows   - to know how a workflow id converts into a core Title object
 *   topicTitles - to know what to append to Topic namespace urls as the
 *                 'huamn readable' portion
 */
class UrlGenerationListener implements LifecycleHandler {
	/** @var OccupationController **/
	protected $occupationController;

	/** @var UUID **/
	protected $definitionId;

	/**
	 * @param UrlGenerator $urlGenerator
	 */
	public function __construct( UrlGenerator $urlGenerator ) {
		$this->urlGenerator = $urlGenerator;
	}

	public function onAfterLoad( $object, array $old ) {
		if ( $object instanceof Workflow ) {
			$this->urlGenerator->withWorkflow( $object );
		} elseif ( $object instanceof PostRevision && $object->isTopicTitle() ) {
			$this->urlGenerator->withTopicTitle( $object );
		}
	}

	function onAfterInsert( $object, array $new ) {
		// Nothing
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// Nothing
	}

	function onAfterRemove( $object, array $old ) {
		// Nothing
	}
}
