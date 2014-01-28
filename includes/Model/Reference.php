<?php

namespace Flow\Model;

use Title;

abstract class Reference {
	protected $workflow, $objectId, $type;

	protected $validTypes = array(
		'link',
	);

	/**
	 * Standard constructor. Called from subclasses only
	 * @param UUID   $srcWorkflow Source Workflow's ID
	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        The type of reference
	 */
	protected function __construct( UUID $srcWorkflow, UUID $objectId, $type ) {
		$this->workflow = $srcWorkflow;
		$this->objectId = $objectId;
		$this->type = $type;

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
		return $this->workflow;
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
			'ref_src_workflow_id' => $this->workflow,
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
	protected $title;

	/**
	 * @param UUID   $srcWorkflow ID of the source Workflow
	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        Type of reference
	 * @param Title  $title       Title of the reference's target.
	 */
	public function __construct( UUID $srcWorkflow, UUID $objectId, $type, Title $title ) {
		$this->title = $title;

		$this->validTypes = array_merge( $this->validTypes,
			array(
				'file',
				'template',
			)
		);

		parent::__construct( $srcWorkflow, $objectId, $type );
	}

	/**
	 * Gets the storage row for this WikiReference
	 * @return array
	 */
	public function getStorageRow() {
		return parent::getStorageRow() + array(
			'ref_target_namespace' => $this->title->getNamespace(),
			'ref_target_title' => $this->title->getDBkey(),
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
		$title = Title::makeTitleSafe( $row['ref_target_namespace'], $row['ref_target_title'] );
		$type = $row['ref_type'];

		return new WikiReference( $workflow, $objectId, $type, $title );
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
		return $this->title;
	}

	public function getTargetIdentifier() {
		return 'title:' . $this->getTitle()->getPrefixedDBKey();
	}
}

class URLReference extends Reference {
	protected $url;

	/**
	 * @param UUID   $srcWorkflow ID of the source Workflow
 	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        Type of reference
	 * @param string $url         URL of the reference's target.
	 */
	public function __construct( UUID $srcWorkflow, UUID $objectId, $type, $url ) {
		$this->url = $url;

		if ( !is_array( wfParseUrl( $url ) ) ) {
			throw new Flow\Exception\InvalidInputException(
				"Invalid URL $url specified for reference " . get_class( $this )
			);
		}

		parent::__construct( $srcWorkflow, $objectId, $type );
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

		return new URLReference( $workflow, $objectId, $type, $url );
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
