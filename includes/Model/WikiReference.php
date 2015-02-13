<?php

namespace Flow\Model;

use Flow\Exception\InvalidInputException;
use Title;

class WikiReference extends Reference {
	const TYPE_FILE = 'file';
	const TYPE_TEMPLATE = 'template';

	protected $target;

	/**
	 * @param UUID   $srcWorkflow ID of the source Workflow
	 * @param Title  $srcTitle    Title of the reference's target.
	 * @param string $objectType  Output of getRevisionType for the AbstractRevision that this reference comes from.
	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        Type of reference
	 * @param Title  $targetTitle Title of the reference's target.
	 */
	public function __construct( UUID $srcWorkflow, Title $srcTitle, $objectType, UUID $objectId, $type, Title $targetTitle ) {
		$this->target = $targetTitle;

		$this->validTypes = array_merge( $this->validTypes,
			array(
				self::TYPE_FILE,
				self::TYPE_TEMPLATE,
			)
		);

		parent::__construct( $srcWorkflow, $srcTitle, $objectType, $objectId, $type );
	}

	/**
	 * Gets the storage row for this WikiReference
	 *
	 * @return array
	 */
	public function getStorageRow() {
		return parent::getStorageRow() + array(
			'ref_target_namespace' => $this->target->getNamespace(),
			'ref_target_title' => $this->target->getDBkey(),
		);
	}

	/**
	 * Instantiates a WikiReference object from a storage row.
	 *
	 * @param  \StdClass $row
	 * @return WikiReference
	 */
	public static function fromStorageRow( $row ) {
		$workflow = UUID::create( $row['ref_src_workflow_id'] );
		$objectType = $row['ref_src_object_type'];
		$objectId = UUID::create( $row['ref_src_object_id'] );
		$srcTitle = self::makeTitle( $row['ref_src_namespace'], $row['ref_src_title'] );
		$targetTitle = self::makeTitle( $row['ref_target_namespace'], $row['ref_target_title'] );
		$type = $row['ref_type'];

		return new WikiReference( $workflow, $srcTitle, $objectType, $objectId, $type, $targetTitle );
	}

	/**
	 * Gets the storage row from an object.
	 * Helper for BasicObjectMapper.
	 */
	public static function toStorageRow( WikiReference $object ) {
		return $object->getStorageRow();
	}

	/**
	 * Many loaded references typically point to the same Title, cache those instead
	 * of generating a bunch of duplicate title classes.
	 */
	public static function makeTitle( $namespace, $title ) {
		try {
			return Workflow::getFromTitleCache( wfWikiId(), $namespace, $title );
		} catch ( InvalidInputException $e ) {
			// duplicate Title::makeTitleSafe which returns null on failure,
			// but only for InvalidInputException
			return null;
		}
	}

	public function getTitle() {
		return $this->target;
	}

	public function getTargetIdentifier() {
		return 'title:' . $this->getTitle()->getPrefixedDBKey();
	}
}
