<?php

namespace Flow\Model;

use Title;

abstract class Reference {
	protected $workflowId, $title, $objectId, $type;

	protected $validTypes = array(
		'link',
	);

	/**
	 * Standard constructor. Called from subclasses only
	 * @param UUID   $srcWorkflow Source Workflow's ID
	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        The type of reference
	 */
	protected function __construct( UUID $srcWorkflow, Title $srcTitle, UUID $objectId, $type ) {
		$this->workflowId = $srcWorkflow;
		$this->objectId = $objectId;
		$this->type = $type;
		$this->srcTitle = $srcTitle;

		if ( !in_array( $type, $this->validTypes ) ) {
			throw new Flow\Exception\InvalidInputException(
				"Invalid type $type specified for reference " . get_class( $this )
			);
		}
	}

	/**
	 * Gives the UUID of the source Workflow
	 * @return UUID
	 */
	public function getWorkflowId() {
		return $this->workflowId;
	}

	/**
	 * Gives the UUID of the source object
	 * @return UUID
	 */
	public function getObjectId() {
		return $this->objectId;
	}

	/**
	 * Gives the type of Reference
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns the storage row for this Reference.
	 * For this abstract reference, only partial.
	 * @return array
	 */
	public function getStorageRow() {
		return array(
			'ref_src_workflow_id' => $this->workflowId,
			'ref_src_namespace' => $this->srcTitle->getNamespace(),
			'ref_src_title' => $this->srcTitle->getDBkey(),
			'ref_src_object_id' => $this->objectId,
			'ref_type' => $this->type,
		);
	}

	/**
	 * Returns the name of the table that stores this
	 * reference in core MediaWiki
	 * @return string
	 */
	abstract public function getWikiTableName();

	/**
	 * @return string Unique string identifier for the target of this reference.
	 */
	abstract public function getTargetIdentifier();

	public function getIdentifier() {
		return $this->getType() . ':' . $this->getTargetIdentifier();
	}
}

class WikiReference extends Reference {
	protected $target;

	/**
	 * @param UUID   $srcWorkflow ID of the source Workflow
	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        Type of reference
	 * @param Title  $title       Title of the reference's target.
	 */
	public function __construct( UUID $srcWorkflow, Title $srcTitle, UUID $objectId, $type, Title $targetTitle ) {
		$this->target = $targetTitle;

		$this->validTypes = array_merge( $this->validTypes,
			array(
				'file',
				'template',
			)
		);

		parent::__construct( $srcWorkflow, $srcTitle, $objectId, $type );
	}

	/**
	 * Gets the storage row for this WikiReference
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
	 * @param  StdClass $row
	 * @return WikiReference
	 */
	public static function fromStorageRow( $row ) {
		$workflow = UUID::create( $row['ref_src_workflow_id'] );
		$objectId = UUID::create( $row['ref_src_object_id'] );
		$srcTitle = Title::makeTitleSafe( $row['ref_src_namespace'], $row['ref_src_title'] );
		$targetTitle = Title::makeTitleSafe( $row['ref_target_namespace'], $row['ref_target_title'] );
		$type = $row['ref_type'];

		return new WikiReference( $workflow, $srcTitle, $objectId, $type, $targetTitle );
	}

	/**
	 * Gets the storage row from an object.
	 * Helper for BasicObjectMapper.
	 */
	public static function toStorageRow( WikiReference $object ) {
		return $object->getStorageRow();
	}

	public function getWikiTableName() {
		switch( $this->getType() ) {
			case 'link':
				return 'pagelinks';
				break;
			case 'file':
				return 'imagelinks';
				break;
			case 'template':
				return 'templatelinks';
				break;
		}
	}

	public function getTitle() {
		return $this->target;
	}

	public function getTargetIdentifier() {
		return 'title:' . $this->getTitle()->getPrefixedDBKey();
	}
}

class URLReference extends Reference {
	protected $url;

	/**
	 * @param UUID   $srcWorkflow ID of the source Workflow
	 * @param Title  $srcTitle    Title of the page that the Workflow exists on
 	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        Type of reference
	 * @param string $url         URL of the reference's target.
	 */
	public function __construct( UUID $srcWorkflow, Title $srcTitle, UUID $objectId, $type, $url ) {
		$this->url = $url;

		if ( !is_array( wfParseUrl( $url ) ) ) {
			throw new Flow\Exception\InvalidInputException(
				"Invalid URL $url specified for reference " . get_class( $this )
			);
		}

		parent::__construct( $srcWorkflow, $srcTitle, $objectId, $type );
	}

	/**
	 * Gets the storage row for this URLReference
	 * @return array
	 */
	public function getStorageRow() {
		return parent::getStorageRow() + array(
			'ref_target' => $this->url,
		);
	}

	/**
	 * Instantiates a URLReference object from a storage row.
	 * @param  StdClass $row
	 * @return WikiReference
	 */
	public static function fromStorageRow( $row ) {
		$workflow = UUID::create( $row['ref_src_workflow_id'] );
		$objectId = UUID::create( $row['ref_src_object_id'] );
		$url = $row['ref_target'];
		$type = $row['ref_type'];
		$srcTitle = Title::makeTitleSafe( $row['ref_src_namespace'], $row['ref_src_title'] );

		return new URLReference( $workflow, $srcTitle, $objectId, $type, $url );
	}

	/**
	 * Gets the storage row from an object.
	 * Helper for BasicObjectMapper.
	 */
	public static function toStorageRow( URLReference $object ) {
		return $object->getStorageRow();
	}

	public function getWikiTableName() {
		switch( $this->getType() ) {
			case 'link':
				return 'externallinks';
				break;
		}
	}

	public function getUrl() {
		return $this->url;
	}

	public function getTargetIdentifier() {
		return 'url:' . $this->getUrl();
	}
}
